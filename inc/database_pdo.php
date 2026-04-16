<?php
declare(strict_types=1);
if (!defined('ATOM_BOARD')) {
	die('');
}

try {
	$dsn = ATOM_DBDSN !== '' ? ATOM_DBDSN :
		ATOM_DBDRIVER . ':host=' . ATOM_DBHOST .
		(ATOM_DBPORT > 0 ? ';port=' . ATOM_DBPORT : '') .
		';dbname=' . ATOM_DBNAME;
	$dbh = new PDO(
		$dsn,
		ATOM_DBUSERNAME,
		ATOM_DBPASSWORD,
		[PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
	$dbh->exec("SET NAMES '" . ((ATOM_DBDRIVER === 'mysql') ? 'utf8mb4' : 'UTF8') . "'");
} catch (PDOException $e) {
	fancyDie('Failed to connect to the database: ' . $e->getMessage());
}

// Creating tables that don't exist
if (ATOM_DBDRIVER === 'pgsql') {
	$isPostsExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBPOSTS))->fetchColumn() !== 0;
	$isStaffExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBSTAFF))->fetchColumn() !== 0;
	$isBansExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBBANS))->fetchColumn() !== 0;
	$isIplookupExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBIPLOOKUPS))->fetchColumn() !== 0;
	$isReportsExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBREPORTS))->fetchColumn() !== 0;
	$isPassExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBPASS))->fetchColumn() !== 0;
	$isLikesExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBLIKES))->fetchColumn() !== 0;
	$isModlogExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBMODLOG))->fetchColumn() !== 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBPOSTS));
	$isPostsExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() !== 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBSTAFF));
	$isStaffExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() !== 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBBANS));
	$isBansExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() !== 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBIPLOOKUPS));
	$isIplookupExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() !== 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBREPORTS));
	$isReportsExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() !== 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBPASS));
	$isPassExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() !== 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBLIKES));
	$isLikesExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() !== 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBMODLOG));
	$isModlogExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() !== 0;
}
if (!$isPostsExists) {
	$dbh->exec($postsQuery);
}
if (!$isStaffExists) {
	$dbh->exec($staffQuery);
}
if (!$isBansExists) {
	$dbh->exec($bansQuery);
}
if (!$isReportsExists) {
	$dbh->exec($reportsQuery);
}
if (!$isPassExists) {
	$dbh->exec($passQuery);
}
if (!$isLikesExists) {
	$dbh->exec($likesQuery);
}
if (!$isModlogExists) {
	$dbh->exec($modlogQuery);
}
if (!$isIplookupExists) {
	$dbh->exec($ipLookupsQuery);
}

function pdoQuery(string $query, ?array $params = null): PDOStatement {
	global $dbh;
	if ($params) {
		$statement = $dbh->prepare($query);
		$statement->execute($params);
	} else {
		$statement = $dbh->query($query);
	}
	return $statement;
}

/* ==[ Posts ]============================================================================================= */

function insertPost(array $post): int {
	global $dbh;
	$now = time();
	pdoQuery(
		"INSERT INTO " . ATOM_DBPOSTS . " (
			parent,
			timestamp,
			bumped,
			ip,
			name,
			tripcode,
			email,
			nameblock,
			subject,
			message,
			password,
			file0,
			file0_hex,
			file0_original,
			file0_size,
			file0_size_formatted,
			image0_width,
			image0_height,
			thumb0,
			thumb0_width,
			thumb0_height,
			file1,
			file1_hex,
			file1_original,
			file1_size,
			file1_size_formatted,
			image1_width,
			image1_height,
			thumb1,
			thumb1_width,
			thumb1_height,
			file2,
			file2_hex,
			file2_original,
			file2_size,
			file2_size_formatted,
			image2_width,
			image2_height,
			thumb2,
			thumb2_width,
			thumb2_height,
			file3,
			file3_hex,
			file3_original,
			file3_size,
			file3_size_formatted,
			image3_width,
			image3_height,
			thumb3,
			thumb3_width,
			thumb3_height,
			likes,
			moderated,
			stickied,
			locked,
			endless,
			pass
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
		[
			$post['parent'],
			$now,
			$now,
			$_SERVER['REMOTE_ADDR'],
			$post['name'],
			$post['tripcode'],
			$post['email'],
			$post['nameblock'],
			$post['subject'],
			$post['message'],
			$post['password'],
			$post['file0'],
			$post['file0_hex'],
			$post['file0_original'],
			$post['file0_size'],
			$post['file0_size_formatted'],
			$post['image0_width'],
			$post['image0_height'],
			$post['thumb0'],
			$post['thumb0_width'],
			$post['thumb0_height'],
			$post['file1'],
			$post['file1_hex'],
			$post['file1_original'],
			$post['file1_size'],
			$post['file1_size_formatted'],
			$post['image1_width'],
			$post['image1_height'],
			$post['thumb1'],
			$post['thumb1_width'],
			$post['thumb1_height'],
			$post['file2'],
			$post['file2_hex'],
			$post['file2_original'],
			$post['file2_size'],
			$post['file2_size_formatted'],
			$post['image2_width'],
			$post['image2_height'],
			$post['thumb2'],
			$post['thumb2_width'],
			$post['thumb2_height'],
			$post['file3'],
			$post['file3_hex'],
			$post['file3_original'],
			$post['file3_size'],
			$post['file3_size_formatted'],
			$post['image3_width'],
			$post['image3_height'],
			$post['thumb3'],
			$post['thumb3_width'],
			$post['thumb3_height'],
			$post['likes'],
			$post['moderated'],
			$post['stickied'],
			$post['locked'],
			$post['endless'],
			$post['pass']
		]);
	return (int)$dbh->lastInsertId();
}

function getPost(int $id): ?array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = ? LIMIT 1",
		[$id]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getPostsByIP(string $ip): array {
	if (ATOM_DBDRIVER === 'pgsql') {
		$result = pdoQuery(
			"SELECT * FROM " . ATOM_DBPOSTS . "
			WHERE ip::inet <<= ?::inet
			ORDER BY timestamp DESC",
			[$ip]);
	} else {
		$ipRange = cidr2ip($ip);
		$isSingleIp = ($ipRange[0] === $ipRange[1]);
		if ($isSingleIp) {
			$result = pdoQuery(
				"SELECT * FROM " . ATOM_DBPOSTS . "
				WHERE ip = ?
				ORDER BY timestamp DESC",
				[$ip]);
		} else {
			$result = pdoQuery(
				"SELECT * FROM " . ATOM_DBPOSTS . " 
				WHERE INET_ATON(ip) >= ? AND INET_ATON(ip) <= ? 
				ORDER BY timestamp DESC",
				$ipRange);
		}
	}
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getPostsByImageHex(string $hex): ?array {
	$result = pdoQuery(
		"SELECT id, parent FROM " . ATOM_DBPOSTS . "
		WHERE (file0_hex = ? OR file1_hex = ? OR file2_hex = ? OR file3_hex = ?)
			AND moderated = 1 LIMIT 1",
		[$hex, $hex, $hex, $hex]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getLatestPosts(bool $moderated, int $limit): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE moderated = ?
		ORDER BY timestamp DESC LIMIT " . $limit,
		[$moderated ? '1' : '0']);
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getLastPostByIP(): ?array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = ?
		ORDER BY id DESC LIMIT 1",
		[$_SERVER['REMOTE_ADDR']]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getUniquePostersCount(): int {
	$result = pdoQuery(
		"SELECT COUNT(DISTINCT(ip)) FROM " . ATOM_DBPOSTS);
	return (int)$result->fetchColumn();
}

function approvePost(int $id): void {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET moderated = ?
		WHERE id = ?",
		['1', $id]);
}

function deletePost(int $id): void {
	$posts = getThreadPosts($id, false);
	foreach ($posts as $post) {
		$postId = (int)$post['id'];
		if ($postId !== $id) {
			deletePostImageFiles($post);
			pdoQuery(
				"DELETE FROM " . ATOM_DBPOSTS . "
				WHERE id = ?",
				[$postId]);
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		$thispostId = (int)$thispost['id'];
		if ($thispost['parent'] === 0) {
			@unlink('res/' . $thispostId . '.html');
		}
		deletePostImageFiles($thispost);
		pdoQuery(
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = ?",
			[$thispostId]);
	}
	deleteReports($id);
	deleteLikes($id);
}

function deletePostImages(array $post, array $imgList): void {
	deletePostImageFiles($post, $imgList);
	if ($imgList && count($imgList) <= ATOM_FILES_COUNT) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			pdoQuery(
				"UPDATE " . ATOM_DBPOSTS . "
				SET file" . $index . " = ?,
					file" . $index . "_hex = ?,
					file" . $index . "_original = ?,
					file" . $index . "_size = ?,
					file" . $index . "_size_formatted = ?,
					image" . $index . "_width = ?,
					image" . $index . "_height = ?,
					thumb" . $index . " = ?,
					thumb" . $index . "_width = ?,
					thumb" . $index . "_height = ?
				WHERE id = ?",
				['', '', '', '0', '', '0', '0', '', '0', '0', $post['id']]);
		}
	}
}

function hidePostImages(array $post, array $imgList): void {
	deletePostThumbFiles($post, $imgList);
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			pdoQuery(
				"UPDATE " . ATOM_DBPOSTS . "
				SET thumb" . $index . " = ?,
					thumb" . $index . "_width = ?,
					thumb" . $index . "_height = ?
				WHERE id = ?",
				['spoiler.png', ATOM_FILE_MAXW, ATOM_FILE_MAXW, $post['id']]);
		}
	}
}

function editPostMessage(int $id, string $newMessage): void {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET message = ?
		WHERE id = ?",
		[$newMessage, $id]);
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists(int $id): bool {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE id = ? AND parent = 0 AND moderated = 1",
		[$id]);
	return $result->fetchColumn() !== 0;
}

function getThreads(): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1
		ORDER BY stickied DESC, bumped DESC");
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getThreadsCount(): int {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1");
	return (int)$result->fetchColumn();
}

function trimThreadsCount(): void {
	$limit = (int)ATOM_MAXTHREADS;
	if ($limit > 0) {
		$result = pdoQuery(
			"SELECT id FROM " . ATOM_DBPOSTS . "
			WHERE parent = 0 AND moderated = 1
			ORDER BY stickied DESC, bumped DESC LIMIT 100 OFFSET " . $limit);
		foreach ($result as $post) {
			deletePost($post['id']);
		}
	}
}

function getThreadPosts(int $id, bool $moderatedOnly = true): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (id = ? OR parent = ?)" .
		($moderatedOnly ? " AND moderated = 1" : "") . "
		ORDER BY id ASC",
		[$id, $id]);
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getThreadPostsCount(int $id): int {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = ? AND moderated = 1",
		[$id]);
	return (int)$result->fetchColumn();
}

function toggleStickyThread(int $id, int $isStickied): void {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET stickied = ?
		WHERE id = ?",
		[$isStickied, $id]);
}

function toggleLockThread(int $id, int $isLocked): void {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET locked = ?
		WHERE id = ?",
		[$isLocked, $id]);
}

function toggleEndlessThread(int $id, int $isEndless): void {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET endless = ?
		WHERE id = ?",
		[$isEndless, $id]);
}

function bumpThread(int $id): void {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET bumped = ?
		WHERE id = ?",
		[time(), $id]);
}

/* ==[ Bans ]============================================================================================== */

function banByID(int $id): ?array {
	$result = pdoQuery(
		"SELECT ip_from, ip_to FROM " . ATOM_DBBANS . "
		WHERE id = ? LIMIT 1",
		[$id]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function banByIP(string $ip): ?array {
	$ipRange = cidr2ip($ip);
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE ip_from <= ? AND ip_to >= ? LIMIT 1",
		[$ipRange[1], $ipRange[0]]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getAllBans(): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBBANS . "
		ORDER BY timestamp DESC");
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function insertBan(string $ip, int $expire, string $reason): int {
	global $dbh;
	$ipRange = cidr2ip($ip);
	$now = time();
	pdoQuery(
		"INSERT INTO " . ATOM_DBBANS . "
		(ip_from, ip_to, timestamp, expire, reason)
		VALUES (?, ?, ?, ?, ?)",
		[$ipRange[0], $ipRange[1], $now, $expire, $reason]);
	return (int)$dbh->lastInsertId();
}

function deleteBan(int $id): void {
	pdoQuery(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE id = ?",
		[$id]);
}

function clearExpiredBans(): void {
	pdoQuery(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE expire > 1 AND expire <= ?",
		[time()]);
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP(string $ip): ?array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = ? AND last_updated > ? LIMIT 1",
		[$ip, (int)(time() - (ATOM_IPLOOKUPS_TIMEOUT * 86400))]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function storeLookupResult(string $ip, int $abuser, int $vps, int $proxy, int $tor, int $vpn): void {
	if (ATOM_DBDRIVER === 'pgsql') {
		$sql = "INSERT INTO " . ATOM_DBIPLOOKUPS . "
			(ip, abuser, vps, proxy, tor, vpn, last_updated)
			VALUES (?, ?, ?, ?, ?, ?, ?)
			ON CONFLICT (ip) DO UPDATE SET
			abuser = EXCLUDED.abuser, vps = EXCLUDED.vps, proxy = EXCLUDED.proxy,
			tor = EXCLUDED.tor, vpn = EXCLUDED.vpn, last_updated = EXCLUDED.last_updated";
	} else {
		$sql = "INSERT INTO " . ATOM_DBIPLOOKUPS . "
			(ip, abuser, vps, proxy, tor, vpn, last_updated)
			VALUES (?, ?, ?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE
			abuser = VALUES(abuser), vps = VALUES(vps), proxy = VALUES(proxy),
			tor = VALUES(tor), vpn = VALUES(vpn), last_updated = VALUES(last_updated)";
	}
	pdoQuery($sql, [$ip, $abuser, $vps, $proxy, $tor, $vpn, time()]);
}

function deleteOldLookups(): void {
	pdoQuery(
		"DELETE FROM " . ATOM_DBIPLOOKUPS . "
		WHERE last_updated < ?",
		[(int)(time() - (ATOM_IPLOOKUPS_TIMEOUT * 86400))]);
}

/* ==[ Posts reports ]===================================================================================== */

function reportsByPostID(int $id): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE postnum = ?
		ORDER BY timestamp DESC",
		[$id]);
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getAllReports(): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE board = ?
		ORDER BY postnum DESC, timestamp DESC",
		[ATOM_BOARD]);
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function insertReport(int $id, string $board, string $ip, string $reason): int|string {
	global $dbh;
	$check = pdoQuery(
		"SELECT 1 FROM " . ATOM_DBREPORTS . "
		WHERE ip = ? AND board = ? AND postnum = ? LIMIT 1",
		[$ip, $board, $id]);
	if ($check->fetchColumn()) {
		return 'exists';
	}
	pdoQuery(
		"INSERT INTO " . ATOM_DBREPORTS . "
		(ip, board, postnum, reason, timestamp)
		VALUES (?, ?, ?, ?, ?)",
		[$ip, $board, $id, $reason, time()]);
	return (int)$dbh->lastInsertId();
}

function deleteReports(int $id): void {
	pdoQuery(
		"DELETE FROM " . ATOM_DBREPORTS . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
}

/* ==[ Passcodes ]========================================================================================= */

function passByID(string $passId): ?array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE id = ? LIMIT 1",
		[$passId]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function passByNum(int $passNum): ?array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE number = ? LIMIT 1",
		[$passNum]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getAllPasscodes(): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPASS . "
		ORDER BY number ASC");
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function insertPass(int $expires, string $meta, string $metaAdmin): string {
	$passId = bin2hex(random_bytes(32));
	$now = time();
	pdoQuery(
		"INSERT INTO " . ATOM_DBPASS . "
		(id, issued, expires, blocked_till, meta, meta_admin)
		VALUES (?, ?, ?, ?, ?, ?)",
		[$passId, $now, $now + $expires, 0, $meta, $metaAdmin]);
	return $passId;
}

function usePass(string $passId, string $ip): void {
	pdoQuery(
		"UPDATE " . ATOM_DBPASS . "
		SET last_used = ?, last_used_ip = ?
		WHERE id = ?",
		[time(), $ip, $passId]);
}

function changePass(int $passNum, string $meta, ?int $expires, int $blockTill, string $blockReason): void {
	$params = [$meta];
	$sql = "UPDATE " . ATOM_DBPASS . " SET meta = ?";
	if ($expires !== null) {
		$sql .= ", expires = ?";
		$params[] = $expires;
	}
	$sql .= ", blocked_till = ?, blocked_reason = ? WHERE number = ?";
	array_push($params, $blockTill, $blockReason, $passNum);
	pdoQuery($sql, $params);
}

function deletePass(string $passId): void {
	pdoQuery(
		"DELETE FROM " . ATOM_DBPASS . "
		WHERE id = ?",
		[$passId]);
}

/* ==[ Likes ]============================================================================================= */

function likesByPostID(int $id): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function getAllLikes(): array {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBLIKES . "
		WHERE board = ?
		ORDER BY postnum ASC",
		[ATOM_BOARD]);
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function toggleLike(int $id, string $ip): array {
	// Check is like exists for a post from this ip
	$res = pdoQuery(
		"SELECT id FROM " . ATOM_DBLIKES . "
		WHERE ip = ? AND board = ? AND postnum = ? LIMIT 1",
		[$ip, ATOM_BOARD, $id]);
	$existingLike = $res->fetchColumn();

	// Inverting: if there is a like, delete it by ID; if not, create it
	if ($existingLike) {
		pdoQuery(
			"DELETE FROM " . ATOM_DBLIKES . "
			WHERE id = ?",
			[$existingLike]);
		$status = false;
	} else {
		pdoQuery(
			"INSERT INTO " . ATOM_DBLIKES . "
			(ip, board, postnum)
			VALUES (?, ?, ?)",
			[$ip, ATOM_BOARD, $id]);
		$status = true;
	}

	// Count the number of likes for a post
	$res = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
	$count = (int)$res->fetchColumn();

	// Update the likes counter for a post in the post table
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET likes = ? WHERE id = ?",
		[$count, $id]);

	return [$status, $count];
}

function deleteLikes(int $id): void {
	pdoQuery(
		"DELETE FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
}

/* ==[ Administration and moderation ]===================================================================== */

function getStaffMember(string $userName): ?array {
	$result = pdoQuery(
		"SELECT username, password_hash, role FROM " . ATOM_DBSTAFF . "
		WHERE username = ? LIMIT 1",
		[$userName]);
	return $result->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getAllStaffMembers(): array {
	$result = pdoQuery(
		"SELECT id, username, role, last_login FROM " . ATOM_DBSTAFF . "
		ORDER BY role ASC, last_login DESC");
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function addStaffMember(string $userName, string $passw, string $role): bool {
	try {
		pdoQuery(
			"INSERT INTO " . ATOM_DBSTAFF . "
			(username, password_hash, role)
			VALUES (?, ?, ?)",
			[trim($userName), password_hash($passw, PASSWORD_DEFAULT), $role]
		);
		return true; 
	} catch (PDOException $e) {
		if ($e->getCode() === '23505') { // Unique key conflict - user already exists
			return false;
		}
		throw $e;
	}
}

function deleteStaffMember(int $id): void {
	pdoQuery(
		"DELETE FROM " . ATOM_DBSTAFF . "
		WHERE id = ? AND role != 'admin'",
		[$id]);
}

function changeStaffMember(string $userName, string $passw): void {
	pdoQuery(
		"UPDATE " . ATOM_DBSTAFF . "
		SET password_hash = ?
		WHERE username = ?",
		[password_hash($passw, PASSWORD_DEFAULT), $userName]);
}

function updateStaffLogin(string $username): void {
	pdoQuery(
		"UPDATE " . ATOM_DBSTAFF . " SET last_login = ?
		WHERE username = ?",
		[time(), $username]);
}

function getModLogRecords(int $startDate = 0, int $endDate = 0, bool $isPublicOnly = false): array {
	$sql = "SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . " WHERE boardname = ?";
	$params = [ATOM_BOARD];
	if ($isPublicOnly) {
		// Public posts only (no date filter, last 100)
		$sql .= " AND private = '0' ORDER BY timestamp DESC LIMIT 100";
	} else {
		// Admin panel (private + public records)
		if ($startDate > 0 && $endDate > 0) {
			$sql .= " AND timestamp >= ? AND timestamp <= ? ORDER BY timestamp DESC";
			$params[] = $startDate;
			$params[] = $endDate;
		} else {
			$sql .= " ORDER BY timestamp DESC LIMIT 100";
		}
	}
	$result = pdoQuery($sql, $params);
	return $result->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// modLog('Text to show in modlog', '1/0', 'Color');
// '1/0': 1 = Private record, 0 = Public record.
// 'Color': The color for this record
function modLog(string $action, string $private = '0', string $color = 'Black'): void {
	$userName = $_SESSION['atom_user'] ?? 'UNKNOWN';
	pdoQuery(
		"INSERT INTO " . ATOM_DBMODLOG . "
		(timestamp, boardname, username, action, color, private)
		VALUES (?, ?, ?, ?, ?, ?)",
		[time(), ATOM_BOARD, $userName, $action, $color, $private]);
}
