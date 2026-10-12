<?php
declare(strict_types=1);

// Expose only public post fields to the bot; never copy posting passwords.
function reportPostSnapshot(?array $post): array {
	if (!$post) {
		return [];
	}
	$keys = ['id', 'parent', 'ip', 'timestamp', 'subject', 'name', 'message'];
	for ($i = 0; $i < 4; $i++) {
		$keys[] = 'thumb' . $i;
		$keys[] = 'file' . $i;
	}
	return array_intersect_key($post, array_flip($keys));
}

// Called only after service authentication in bot-api.php.
function botQuery(string $sql, array $params = []): array {
	global $mysqli;
	if (ATOM_DBMODE === 'pdo') {
		$result = pdoQuery($sql, $params);
		return $result->columnCount() ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
	}
	$result = $mysqli->execute_query($sql, $params);
	return $result instanceof mysqli_result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function botCaseSnapshot(string $ip): array {
	$posts = array_map('reportPostSnapshot', getPostsByIP($ip));
	$reports = [];
	foreach (getAllReports() as $report) {
		$post = getPost((int)$report['postnum']);
		$poster = $post['ip'] ?? '';
		if ($poster === $ip) {
			$reports[] = array_intersect_key($report, array_flip(['id', 'postnum', 'reason', 'timestamp']));
		}
	}
	$context = [];
	foreach ($posts as $post) {
		$ids = (int)$post['parent'] ? [(int)$post['parent']] : [];
		preg_match_all('~/(?:' . preg_quote(ATOM_BOARD, '~') . ')/res/[0-9]+\.html#([0-9]+)~', $post['message'], $matches);
		$ids = array_unique(array_merge($ids, array_map('intval', $matches[1])));
		foreach ($ids as $id) {
			$referenced = getPost($id);
			$context[(string)$post['id']][(string)$id] = $referenced ? reportPostSnapshot($referenced) : ['id' => $id, 'deleted' => true];
		}
		preg_match_all('~/([a-zA-Z0-9_-]+)/res/([0-9]+)\.html#([0-9]+)~', $post['message'], $links, PREG_SET_ORDER);
		foreach ($links as $link) {
			if ($link[1] !== ATOM_BOARD) {
				$context[(string)$post['id']][$link[1] . ':' . $link[3]] = [
					'board' => $link[1], 'parent' => (int)$link[2], 'id' => (int)$link[3], 'unavailable' => true];
			}
		}
	}
	$uniquePosts = [];
	foreach ($posts as $post) {
		$uniquePosts[(string)$post['id']] = $post;
	}
	if (!lookupByIP($ip) && ATOM_IPLOOKUPS_KEY !== '') {
		// Reuse the engine's lookup/cache; it leaves metadata unknown on service failure.
		isDirtyIP($ip);
	}
	return ['board' => ATOM_BOARD, 'ip' => $ip, 'posts' => array_values($uniquePosts), 'reports' => $reports,
		'context' => $context, 'country' => getCountryCode($ip, null),
		'lookup' => lookupByIP($ip), 'ban' => banByIP($ip)];
}

function botModerate(array $command): array {
	$ip = $command['ip'] ?? '';
	$action = $command['action'] ?? '';
	$operation = $command['operation_id'] ?? '';
	// The HTTP boundary authenticates the bot service; discard legacy actor fields.
	unset($command['actor']);
	$nickname = $command['moderator'] ?? '';
	if (!is_string($nickname) || mb_strlen($nickname) > 128) {
		throw new InvalidArgumentException('Invalid moderator nickname');
	}
	$nickname = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $nickname));
	if (array_key_exists('moderator', $command)) {
		$command['moderator'] = $nickname;
	}
	ksort($command);
	$through = (int)($command['through_report'] ?? 0);
	if (!filter_var($ip, FILTER_VALIDATE_IP) || !in_array($action, ['dismiss', 'ban'], true) ||
		!preg_match('/^[a-zA-Z0-9_-]{1,64}$/D', $operation) || $through <= 0) {
		throw new InvalidArgumentException('Invalid moderation command');
	}
	if ($action === 'ban' && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		throw new InvalidArgumentException('/24 ban requires an IPv4 address');
	}
	$lock = fopen(sys_get_temp_dir() . '/atom-bot-' . hash('sha256', ATOM_BOARD . $ip) . '.lock', 'c');
	if (!$lock || !flock($lock, LOCK_EX)) {
		throw new RuntimeException('Could not lock moderation case');
	}
	try {
		botQuery('CREATE TABLE IF NOT EXISTS bot_actions (operation_id varchar(64) PRIMARY KEY, request_hash varchar(64) NOT NULL, result text NOT NULL)');
		$hash = hash('sha256', json_encode($command, JSON_THROW_ON_ERROR));
		$previous = botQuery('SELECT * FROM bot_actions WHERE operation_id = ?', [$operation]);
		if ($previous) {
			if ($previous[0]['request_hash'] !== $hash) {
				throw new InvalidArgumentException('Operation ID already used for another command');
			}
			if ($previous[0]['result'] !== '') {
				return json_decode($previous[0]['result'], true, 512, JSON_THROW_ON_ERROR);
			}
		} else {
			botQuery('INSERT INTO bot_actions (operation_id, request_hash, result) VALUES (?, ?, ?)', [$operation, $hash, '']);
		}
		$_SESSION['atom_user'] = $nickname ?: 'Telegram moderation';
		$snapshot = botCaseSnapshot($ip);
		$reports = array_filter($snapshot['reports'], static fn(array $r): bool => (int)$r['id'] <= $through);
		$threads = [];
		foreach ($snapshot['posts'] as $post) {
			foreach ($reports as $report) {
				if ((int)$post['id'] === (int)$report['postnum']) {
					$threads[] = getThreadId($post);
				}
			}
		}
		// Preserve deletion scope on retries after an interrupted filesystem/page rebuild.
		$threads = array_unique(array_merge($threads, array_map('intval', $command['threads'] ?? [])));
		$deleted = [];
		$subnet = null;
		if ($action === 'ban') {
			$subnet = long2ip(ip2long($ip) & 0xFFFFFF00) . '/24';
			$range = cidr2ip($subnet);
			$expires = (int)($command['expires'] ?? 0);
			if ($expires <= time() || $expires > time() + 1209600 + 60) {
				throw new InvalidArgumentException('Invalid two-week expiry');
			}
			$existing = array_filter(getAllBans(), static fn(array $b): bool =>
				(int)$b['ip_from'] <= $range[0] && (int)$b['ip_to'] >= $range[1] &&
				((int)$b['expire'] === 0 || (int)$b['expire'] >= $expires));
			if (!$existing) {
				insertBan($subnet, $expires, 'Reported spam (Telegram moderation)');
			}
			foreach ($snapshot['posts'] as $post) {
				if (in_array(getThreadId($post), $threads) && !($post['deleted'] ?? false)) {
					if (!getPost((int)$post['id'])) {
						continue;
					}
					if (!(int)$post['parent']) {
						foreach (getThreadPosts((int)$post['id'], false) as $reply) {
							$deleted[] = (int)$reply['id'];
						}
					} else {
						$deleted[] = (int)$post['id'];
					}
					deletePost((int)$post['id']);
				}
			}
			foreach ($threads as $thread) {
				if (getPost($thread)) {
					rebuildThreadPage($thread);
				}
			}
			rebuildIndexPages();
		}
		foreach ($reports as $report) {
			botQuery('DELETE FROM ' . ATOM_DBREPORTS . ' WHERE board = ? AND id = ?', [ATOM_BOARD, $report['id']]);
		}
		modLog('Telegram ' . $action . ' for ' . $ip . '; deleted posts: ' . implode(', ', $deleted));
		$result = ['status' => $action === 'ban' ? 'banned' : 'dismissed',
			'at' => time(), 'subnet' => $subnet, 'deleted' => $deleted];
		if ($nickname !== '') {
			$result['moderator'] = $nickname;
		}
		botQuery('UPDATE bot_actions SET result = ? WHERE operation_id = ?', [json_encode($result, JSON_THROW_ON_ERROR), $operation]);
		return $result;
	} finally {
		flock($lock, LOCK_UN);
		fclose($lock);
	}
}
