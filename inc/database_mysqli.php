<?php
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
if ($result->num_rows == 0) {
	$mysqli->query($postsQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBSTAFF . "'");
if ($result->num_rows == 0) {
	$mysqli->query($staffQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBBANS . "'");
if ($result->num_rows == 0) {
	$mysqli->query($bansQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBIPLOOKUPS . "'");
if ($result->num_rows == 0) {
	$mysqli->query($ipLookupsQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBREPORTS . "'");
if ($result->num_rows == 0) {
	$mysqli->query($reportsQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBPASS . "'");
if ($result->num_rows == 0) {
	$mysqli->query($passQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBLIKES . "'");
if ($result->num_rows == 0) {
	$mysqli->query($likesQuery);
}
$result->close();
$result = $mysqli->query("SHOW TABLES LIKE '" . ATOM_DBMODLOG . "'");
if ($result->num_rows == 0) {
	$mysqli->query($modlogQuery);
}
$result->close();

/* ==[ Posts ]============================================================================================= */

function insertPost($post) {
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
	return $mysqli->insert_id;
}

function getPost($id) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = ? LIMIT 1",
		[(int)$id]);
	return $result->fetch_assoc();
}

function getPostsByIP($ip) {
	global $mysqli;
	$ipArr = cidr2ip($ip);
	$result = $ipArr[0] == $ipArr[1] ?
		$mysqli->execute_query(
			"SELECT * FROM " . ATOM_DBPOSTS . "
			WHERE ip = ?
			ORDER BY timestamp DESC",
			[$ip]) :
		$mysqli->execute_query(
			"SELECT * FROM " . ATOM_DBPOSTS . "
			WHERE INET_ATON(ip) >= ? AND INET_ATON(ip) <= ?
			ORDER BY timestamp DESC",
			$ipArr);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getPostsByImageHex($hex) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT id, parent FROM " . ATOM_DBPOSTS . "
		WHERE (file0_hex = ? OR file1_hex = ? OR file2_hex = ? OR file3_hex = ?)
			AND moderated = 1 LIMIT 1",
		[$hex, $hex, $hex, $hex]);
	return $result->fetch_assoc();
}

function getLatestPosts($moderated, $limit) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE moderated = ?
		ORDER BY timestamp DESC LIMIT " . (int)$limit,
		[$moderated ? '1' : '0']);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getLastPostByIP() {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = ?
		ORDER BY id DESC LIMIT 1",
		[$_SERVER['REMOTE_ADDR']]);
	return $result->fetch_assoc();
}

function getUniquePostersCount() {
	global $mysqli;
	$result = $mysqli->query("SELECT COUNT(DISTINCT(ip)) FROM " . ATOM_DBPOSTS);
	return (int)$result->fetch_column();
}

function approvePost($id) {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET moderated = ?
		WHERE id = ?",
		['1', (int)$id]);
}

function deletePost($id) {
	global $mysqli;
	$posts = getThreadPosts((int)$id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImageFiles($post);
			$mysqli->execute_query(
				"DELETE FROM " . ATOM_DBPOSTS . "
				WHERE id = ?",
				[(int)$post['id']]);
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == ATOM_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImageFiles($thispost);
		$mysqli->execute_query(
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = ?",
			[(int)$thispost['id']]);
	}
	deleteReports($id);
	deleteLikes($id);
}

function deletePostImages($post, $imgList) {
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

function hidePostImages($post, $imgList) {
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

function editPostMessage($id, $newMessage) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET message = ?
		WHERE id = ?",
		[$newMessage, (int)$id]);
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists($id) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE id = ? AND parent = 0 AND moderated = 1",
		[(int)$id]);
	return $result->fetch_column() != 0;
}

function getThreads() {
	global $mysqli;
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1
		ORDER BY stickied DESC, bumped DESC");
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getThreadsCount() {
	global $mysqli;
	$result = $mysqli->query(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1");
	return (int)$result->fetch_column();
}

function trimThreadsCount() {
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

function getThreadPosts($id, $moderatedOnly = true) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (id = ? OR parent = ?)" .
		($moderatedOnly ? " AND moderated = 1" : "") . "
		ORDER BY id ASC",
		[(int)$id, (int)$id]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getThreadPostsCount($id) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = ? AND moderated = 1",
		[(int)$id]);
	return (int)$result->fetch_column();
}

function toggleStickyThread($id, $isStickied) {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET stickied = ?
		WHERE id = ?",
		[(int)$isStickied, (int)$id]);
}

function toggleLockThread($id, $isLocked) {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET locked = ?
		WHERE id = ?",
		[(int)$isLocked, (int)$id]);
}

function toggleEndlessThread($id, $isEndless) {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET endless = ?
		WHERE id = ?",
		[(int)$isEndless, (int)$id]);
}

function bumpThread($id) {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET bumped = ?
		WHERE id = ?",
		[time(), (int)$id]);
}

/* ==[ Bans ]============================================================================================== */

function banByID($id) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT ip_from, ip_to FROM " . ATOM_DBBANS . "
		WHERE id = ? LIMIT 1",
		[(int)$id]);
	return $result->fetch_assoc();
}

function banByIP($ip) {
	global $mysqli;
	$ip_long = ip2long($ip);
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE ip_from <= ? AND ip_to >= ? LIMIT 1",
		[$ip_long, $ip_long]);
	return $result->fetch_assoc();
}

function getAllBans() {
	global $mysqli;
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBBANS . "
		ORDER BY timestamp DESC");
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function insertBan($ban) {
	global $mysqli;
	$range = cidr2ip($ban['ip']);
	$now = time();
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBBANS . "
		(ip_from, ip_to, timestamp, expire, reason)
		VALUES (?, ?, ?, ?, ?)",
		[$range[0], $range[1], $now, $ban['expire'], $ban['reason']]);
	return $mysqli->insert_id;
}

function deleteBan($id) {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE id = ?",
		[(int)$id]);
}

function clearExpiredBans() {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE expire > 1 AND expire <= ?",
		[time()]);
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP($ip) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = ? AND last_updated > ? LIMIT 1",
		[$ip, (int)(time() - (ATOM_IPLOOKUPS_TIMEOUT * 86400))]);
	return $result->fetch_assoc();
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	global $mysqli;
	$now = time();
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBIPLOOKUPS . "
		(ip, abuser, vps, proxy, tor, vpn, last_updated)
		VALUES (?, ?, ?, ?, ?, ?, ?)
		ON DUPLICATE KEY UPDATE
		abuser = VALUES(abuser), vps = VALUES(vps), proxy = VALUES(proxy),
		tor = VALUES(tor), vpn = VALUES(vpn), last_updated = VALUES(last_updated)",
		[$ip, (int)$abuser, (int)$vps, (int)$proxy, (int)$tor, (int)$vpn, $now]);
}

function deleteOldLookups() {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBIPLOOKUPS . "
		WHERE last_updated < ?",
		[(int)(time() - (ATOM_IPLOOKUPS_TIMEOUT * 86400))]);
}

/* ==[ Posts reports ]===================================================================================== */

function reportsByPostID($id) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE postnum = ?
		ORDER BY timestamp DESC",
		[(int)$id]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllReports() {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE board = ?
		ORDER BY postnum DESC, timestamp DESC",
		[ATOM_BOARD]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function insertReport($id, $board, $ip, $reason) {
	global $mysqli;
	$check = $mysqli->execute_query(
		"SELECT id FROM " . ATOM_DBREPORTS . "
		WHERE ip = ? AND board = ? AND postnum = ? LIMIT 1",
		[$ip, $board, (int)$id]);
	if ($check->fetch_row()) {
		return 'exists';
	}
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBREPORTS . "
		(ip, board, postnum, reason, timestamp)
		VALUES (?, ?, ?, ?, ?)",
		[$ip, $board, (int)$id, $reason, time()]);
	return $mysqli->insert_id;
}

function deleteReports($id) {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBREPORTS . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, (int)$id]);
}

/* ==[ Passcodes ]========================================================================================= */

function passByID($passId) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE id = ? LIMIT 1",
		[$passId]);
	return $result->fetch_assoc();
}

function passByNum($passNum) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE number = ? LIMIT 1",
		[(int)$passNum]);
	return $result->fetch_assoc();
}

function getAllPasscodes() {
	global $mysqli;
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBPASS . "
		ORDER BY number ASC");
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function insertPass($expires, $meta, $metaAdmin) {
	global $mysqli;
	$passId = bin2hex(random_bytes(32));
	$now = time();
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBPASS . "
		(id, issued, expires, blocked_till, meta, meta_admin)
		VALUES (?, ?, ?, ?, ?, ?)",
		[$passId, $now, $now + (int)$expires, 0, $meta, $metaAdmin]);
	return $passId;
}

function usePass($passId, $ip) {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPASS . "
		SET last_used = ?, last_used_ip = ?
		WHERE id = ?",
	[time(), $ip, $passId]);
}

function changePass($passNum, $meta, $expires, $blockTill, $blockReason) {
	global $mysqli;
	if ($expires) {
		$mysqli->execute_query(
			"UPDATE " . ATOM_DBPASS . "
			SET meta = ?, expires = ?, blocked_till = ?, blocked_reason = ?
			WHERE number = ?",
			[$meta, (int)$expires, (int)$blockTill, $blockReason, (int)$passNum]);
	} else {
		$mysqli->execute_query(
			"UPDATE " . ATOM_DBPASS . "
			SET meta = ?, blocked_till = ?, blocked_reason = ?
			WHERE number = ?",
			[$meta, (int)$blockTill, $blockReason, (int)$passNum]);
	}
}

function deletePass($passId) {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBPASS . "
		WHERE id = ?",
		[$passId]);
}

/* ==[ Likes ]============================================================================================= */

function getAllLikes() {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBLIKES . "
		WHERE board = ?
		ORDER BY postnum ASC",
		[ATOM_BOARD]);
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function toggleLike($id, $ip) {
	global $mysqli;
	$id = (int)$id;

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

function deleteLikes($id) {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, (int)$id]);
}

/* ==[ Administration and moderation ]===================================================================== */

function getStaffMember($userName) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT username, password_hash, role FROM " . ATOM_DBSTAFF . "
		WHERE username = ? LIMIT 1",
		[$userName]);
	return $result->fetch_assoc();
}

function getAllStaffMembers() {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT id, username, role, last_login FROM " . ATOM_DBSTAFF . "
		ORDER BY role ASC, last_login DESC");
	return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function addStaffMember($userName, $passw, $role) {
	global $mysqli;
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBSTAFF . "
		(username, password_hash, role)
		VALUES (?, ?, ?)",
		[trim($userName), password_hash($passw, PASSWORD_DEFAULT), $role]);
}

function deleteStaffMember($id) {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBSTAFF . "
		WHERE id = ? AND role != 'admin'",
		[(int)$id]);
}

function changeStaffMember($userName, $passw) {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBSTAFF . "
		SET password_hash = ?
		WHERE username = ?",
		[password_hash($passw, PASSWORD_DEFAULT), $userName]);
}

function updateStaffLogin($username) {
	global $mysqli;
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBSTAFF . "
		SET last_login = ?
		WHERE username = ?",
		[time(), $username]);
}

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	global $mysqli;
	$sql = "SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . " WHERE boardname = ?";
	$params = [ATOM_BOARD];
	if ($private === '0') {
		// Public posts only (no date filter, last 100)
		$sql .= " AND private = '0' ORDER BY timestamp DESC LIMIT 100";
	} else {
		// Admin panel (private + public records)
		if ($periodStartDate > 0 && $periodEndDate > 0) {
			$sql .= " AND timestamp >= ? AND timestamp <= ? ORDER BY timestamp DESC";
			$params[] = (int)$periodStartDate;
			$params[] = (int)$periodEndDate;
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
function modLog($action, $private = '0', $color = 'Black') {
	global $mysqli;
	$userName = $_SESSION['atom_user'] ?? 'UNKNOWN';
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBMODLOG . "
		(timestamp, boardname, username, action, color, private)
		VALUES (?, ?, ?, ?, ?, ?)",
		[time(), ATOM_BOARD, $userName, $action, $color, $private]);
}
