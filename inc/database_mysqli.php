<?php
declare(strict_types=1);
if (!defined('ATOM_BOARD')) {
	die('');
}

if (!extension_loaded('mysqli')) {
	fancyDie('MySQL library is not installed');
}

try {
	$mysqli = new mysqli(ATOM_DBHOST, ATOM_DBUSERNAME, ATOM_DBPASSWORD, ATOM_DBNAME);
} catch (mysqli_sql_exception $e) {
	fancyDie('Failed to connect to the database: ' . $e->getMessage());
}

$mysqli->set_charset('utf8mb4');

// Creating tables that don't exist
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBPOSTS . "'");
if ($result->num_rows === 0) {
	$mysqli->query($postsQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBSTAFF . "'");
if ($result->num_rows === 0) {
	$mysqli->query($staffQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBBANS . "'");
if ($result->num_rows === 0) {
	$mysqli->query($bansQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBIPLOOKUPS . "'");
if ($result->num_rows === 0) {
	$mysqli->query($ipLookupsQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBREPORTS . "'");
if ($result->num_rows === 0) {
	$mysqli->query($reportsQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBPASS . "'");
if ($result->num_rows === 0) {
	$mysqli->query($passQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBLIKES . "'");
if ($result->num_rows === 0) {
	$mysqli->query($likesQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBMODLOG . "'");
if ($result->num_rows === 0) {
	$mysqli->query($modlogQuery);
}
$result->close();

/* ==[ Posts ]============================================================================================= */

function insertPost(array $post): int {
	global $mysqli;
	$now = time();
	$mysqli->execute_query(
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
	return $mysqli->insert_id ?: 0;
}

function getPost(int $id): ?array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = ? LIMIT 1",
		[$id]);
	return $result->fetch_assoc();
}

function getPostsByIP(string $ip): array {
	global $mysqli;
	$ipRange = cidr2ip($ip);
	$result = $ipRange[0] === $ipRange[1] ?
		$mysqli->execute_query(
			"SELECT * FROM " . ATOM_DBPOSTS . "
			WHERE ip = ?
			ORDER BY timestamp DESC",
			[$ip]) :
		$mysqli->execute_query(
			"SELECT * FROM " . ATOM_DBPOSTS . "
			WHERE INET_ATON(ip) >= ? AND INET_ATON(ip) <= ?
			ORDER BY timestamp DESC",
			$ipRange);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getPostsByImageHex(string $hex): ?array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT id, parent FROM " . ATOM_DBPOSTS . "
		WHERE (file0_hex = ? OR file1_hex = ? OR file2_hex = ? OR file3_hex = ?)
			AND moderated = 1 LIMIT 1",
		[$hex, $hex, $hex, $hex]);
	return $result->fetch_assoc();
}

function getLatestPosts(bool $moderated, int $limit): array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE moderated = ?
		ORDER BY timestamp DESC LIMIT " . $limit,
		[$moderated ? '1' : '0']);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getLastPostByIP(): ?array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = ?
		ORDER BY id DESC LIMIT 1",
		[$_SERVER['REMOTE_ADDR']]);
	return $result->fetch_assoc();
}

function getUniquePostersCount(): int {
	global $mysqli;
	$result = $mysqli->query("SELECT COUNT(DISTINCT(ip)) FROM " . ATOM_DBPOSTS);
	return (int)$result->fetch_column();
}

function approvePost(int $id): void {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET moderated = ?
		WHERE id = ?",
		['1', $id]);
}

function deletePost(int $id): void {
	global $mysqli;
	$posts = getThreadPosts($id, false);
	foreach ($posts as $post) {
		$postId = (int)$post['id'];
		if ($postId !== $id) {
			deletePostImageFiles($post);
			$mysqli->execute_query(
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
		$mysqli->execute_query(
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = ?",
			[$thispostId]);
	}
	deleteReports($id);
	deleteLikes($id);
}

function deletePostImages(array $post, array $imgList): void {
	global $mysqli;
	deletePostImageFiles($post, $imgList);
	if ($imgList && count($imgList) <= ATOM_FILES_COUNT) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			$mysqli->execute_query(
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
	global $mysqli;
	deletePostThumbFiles($post, $imgList);
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			$mysqli->execute_query(
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
	global $mysqli;
	$result = $mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET message = ?
		WHERE id = ?",
		[$newMessage, $id]);
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists(int $id): bool {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE id = ? AND parent = 0 AND moderated = 1",
		[$id]);
	return $result->fetch_column() !== 0;
}

function getThreads(): array {
	global $mysqli;
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1
		ORDER BY stickied DESC, bumped DESC");
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getThreadsCount(): int {
	global $mysqli;
	$result = $mysqli->query(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1");
	return (int)$result->fetch_column();
}

function trimThreadsCount(): void {
	global $mysqli;
	$limit = (int)ATOM_MAXTHREADS;
	if ($limit > 0) {
		$result = $mysqli->query(
			"SELECT id FROM " . ATOM_DBPOSTS . "
			WHERE parent = 0 AND moderated = 1
			ORDER BY stickied DESC, bumped DESC LIMIT 100 OFFSET " . $limit);
		if ($result) {
			while ($post = $result->fetch_assoc()) {
				deletePost($post['id']);
			}
		}
	}
}

function getThreadPosts(int $id, bool $moderatedOnly = true): array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (id = ? OR parent = ?)" .
		($moderatedOnly ? " AND moderated = 1" : "") . "
		ORDER BY id ASC",
		[$id, $id]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getThreadPostsCount(int $id): int {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = ? AND moderated = 1",
		[$id]);
	return (int)$result->fetch_column();
}

function toggleStickyThread(int $id, int $isStickied): void {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET stickied = ?
		WHERE id = ?",
		[$isStickied, $id]);
}

function toggleLockThread(int $id, int $isLocked): void {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET locked = ?
		WHERE id = ?",
		[$isLocked, $id]);
}

function toggleEndlessThread(int $id, int $isEndless): void {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET endless = ?
		WHERE id = ?",
		[$isEndless, $id]);
}

function bumpThread(int $id): void {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET bumped = ?
		WHERE id = ?",
		[time(), $id]);
}

/* ==[ Bans ]============================================================================================== */

function banByID(int $id): ?array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT ip_from, ip_to FROM " . ATOM_DBBANS . "
		WHERE id = ? LIMIT 1",
		[$id]);
	return $result->fetch_assoc();
}

function banByIP(string $ip): ?array {
	global $mysqli;
	$ipRange = cidr2ip($ip);
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE ip_from <= ? AND ip_to >= ? LIMIT 1",
		[$ipRange[1], $ipRange[0]]);
	return $result->fetch_assoc();
}

function getAllBans(): array {
	global $mysqli;
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBBANS . "
		ORDER BY timestamp DESC");
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function insertBan(string $ip, int $expire, string $reason): int {
	global $mysqli;
	$ipRange = cidr2ip($ip);
	$now = time();
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBBANS . "
		(ip_from, ip_to, timestamp, expire, reason)
		VALUES (?, ?, ?, ?, ?)",
		[$ipRange[0], $ipRange[1], $now, $expire, $reason]);
	return $mysqli->insert_id ?: 0;
}

function deleteBan(int $id): void {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE id = ?",
		[$id]);
}

function clearExpiredBans(): void {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE expire > 1 AND expire <= ?",
		[time()]);
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP(string $ip): ?array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = ? AND last_updated > ? LIMIT 1",
		[$ip, (int)(time() - (ATOM_IPLOOKUPS_TIMEOUT * 86400))]);
	return $result->fetch_assoc();
}

function storeLookupResult(string $ip, int $abuser, int $vps, int $proxy, int $tor, int $vpn): void {
	global $mysqli;
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBIPLOOKUPS . "
		(ip, abuser, vps, proxy, tor, vpn, last_updated)
		VALUES (?, ?, ?, ?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE
		abuser = VALUES(abuser), vps = VALUES(vps), proxy = VALUES(proxy),
		tor = VALUES(tor), vpn = VALUES(vpn), last_updated = VALUES(last_updated)",
		[$ip, $abuser, $vps, $proxy, $tor, $vpn, time()]);
}

function deleteOldLookups(): void {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBIPLOOKUPS . "
		WHERE last_updated < ?",
		[(int)(time() - (ATOM_IPLOOKUPS_TIMEOUT * 86400))]);
}

/* ==[ Posts reports ]===================================================================================== */

function reportsByPostID(int $id): array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE postnum = ?
		ORDER BY timestamp DESC",
		[$id]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllReports(): array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE board = ?
		ORDER BY postnum DESC, timestamp DESC",
		[ATOM_BOARD]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function insertReport(int $id, string $board, string $ip, string $reason): int|string {
	global $mysqli;
	$check = $mysqli->execute_query(
		"SELECT 1 FROM " . ATOM_DBREPORTS . "
		WHERE ip = ? AND board = ? AND postnum = ? LIMIT 1",
		[$ip, $board, $id]);
	if ($check->fetch_row()) {
		return 'exists';
	}
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBREPORTS . "
		(ip, board, postnum, reason, timestamp)
		VALUES (?, ?, ?, ?, ?)",
		[$ip, $board, $id, $reason, time()]);
	return $mysqli->insert_id ?: 0;
}

function deleteReports(int $id): void {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBREPORTS . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
}

/* ==[ Passcodes ]========================================================================================= */

function passByID(string $passId): ?array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE id = ? LIMIT 1",
		[$passId]);
	return $result->fetch_assoc();
}

function passByNum(int $passNum): ?array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE number = ? LIMIT 1",
		[$passNum]);
	return $result->fetch_assoc();
}

function getAllPasscodes(): array {
	global $mysqli;
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBPASS . "
		ORDER BY number ASC");
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function insertPass(int $expires, string $meta, string $metaAdmin): string {
	global $mysqli;
	$passId = bin2hex(random_bytes(32));
	$now = time();
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBPASS . "
		(id, issued, expires, blocked_till, meta, meta_admin)
		VALUES (?, ?, ?, ?, ?, ?)",
		[$passId, $now, $now + $expires, 0, $meta, $metaAdmin]);
	return $passId;
}

function usePass(string $passId, string $ip): void {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPASS . "
		SET last_used = ?, last_used_ip = ?
		WHERE id = ?",
	[time(), $ip, $passId]);
}

function changePass(int $passNum, string $meta, ?int $expires, int $blockTill, string $blockReason): void {
	global $mysqli;
	$params = [$meta];
	$sql = "UPDATE " . ATOM_DBPASS . " SET meta = ?";
	if ($expires !== null) {
		$sql .= ", expires = ?";
		$params[] = $expires;
	}
	$sql .= ", blocked_till = ?, blocked_reason = ? WHERE number = ?";
	array_push($params, $blockTill, $blockReason, $passNum);
	$mysqli->execute_query($sql, $params);
}

function deletePass(string $passId): void {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBPASS . "
		WHERE id = ?",
		[$passId]);
}

/* ==[ Likes ]============================================================================================= */

function likesByPostID(int $id): array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllLikes(): array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBLIKES . "
		WHERE board = ?
		ORDER BY postnum ASC",
		[ATOM_BOARD]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function toggleLike(int $id, string $ip): array {
	global $mysqli;

	// Check is like exists for a post from this ip
	$res = $mysqli->execute_query(
		"SELECT id FROM " . ATOM_DBLIKES . "
		WHERE ip = ? AND board = ? AND postnum = ? LIMIT 1",
		[$ip, ATOM_BOARD, $id]);
	$existingLike = $res->fetch_row();

	// Inverting: if there is a like, delete it by ID; if not, create it
	if ($existingLike) {
		$mysqli->execute_query(
			"DELETE FROM " . ATOM_DBLIKES . "
			WHERE id = ?",
			[$existingLike[0]]);
		$status = false;
	} else {
		$mysqli->execute_query(
			"INSERT INTO " . ATOM_DBLIKES . "
			(ip, board, postnum)
			VALUES (?, ?, ?)",
			[$ip, ATOM_BOARD, $id]);
		$status = true;
	}

	// Count the number of likes for a post
	$res = $mysqli->execute_query(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
	$count = (int)$res->fetch_column();

	// Update the likes counter for a post in the post table
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET likes = ?
		WHERE id = ?",
		[$count, $id]);

	return [$status, $count];
}

function deleteLikes(int $id): void {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
}

/* ==[ Administration and moderation ]===================================================================== */

function getStaffMember(string $userName): ?array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT username, password_hash, role FROM " . ATOM_DBSTAFF . "
		WHERE username = ? LIMIT 1",
		[$userName]);
	return $result->fetch_assoc();
}

function getAllStaffMembers(): array {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT id, username, role, last_login FROM " . ATOM_DBSTAFF . "
		ORDER BY role ASC, last_login DESC");
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function addStaffMember(string $userName, string $passw, string $role): bool {
	global $mysqli;
	try {
		$mysqli->execute_query(
			"INSERT INTO " . ATOM_DBSTAFF . "
			(username, password_hash, role)
			VALUES (?, ?, ?)",
			[trim($userName), password_hash($passw, PASSWORD_DEFAULT), $role]
		);
		return true;
	} catch (mysqli_sql_exception $e) {
		if ($e->getCode() === 1062) { // Unique key conflict - user already exists
			return false; 
		}
		throw $e;
	}
}

function deleteStaffMember(int $id): void {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBSTAFF . "
		WHERE id = ? AND role != 'admin'",
		[$id]);
}

function changeStaffMember(string $userName, string $passw): void {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBSTAFF . "
		SET password_hash = ?
		WHERE username = ?",
		[password_hash($passw, PASSWORD_DEFAULT), $userName]);
}

function updateStaffLogin(string $username): void {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBSTAFF . "
		SET last_login = ?
		WHERE username = ?",
		[time(), $username]);
}

function getModLogRecords(int $startDate = 0, int $endDate = 0, bool $isPublicOnly = false): array {
	global $mysqli;
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
	$result = $mysqli->execute_query($sql, $params);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// modLog('Text to show in modlog', '1/0', 'Color');
// '1/0': 1 = Private record, 0 = Public record.
// 'Color': The color for this record
function modLog(string $action, string $private = '0', string $color = 'Black'): void {
	global $mysqli;
	$userName = $_SESSION['atom_user'] ?? 'UNKNOWN';
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBMODLOG . "
		(timestamp, boardname, username, action, color, private)
		VALUES (?, ?, ?, ?, ?, ?)",
		[time(), ATOM_BOARD, $userName, $action, $color, $private]);
}
