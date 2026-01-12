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
	$posts = [];
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
			cidr2ip($ip));
	if ($result) {
		while ($post = $result->fetch_assoc()) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function getPostsByImageHex($hex) {
	global $mysqli;
	$posts = [];
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (file0_hex = ? OR file1_hex = ? OR file2_hex = ? OR file3_hex = ?)
			AND moderated = 1 LIMIT 1",
		[$hex, $hex, $hex, $hex]);
	if ($result) {
		while ($post = $result->fetch_assoc()) {
			$posts[] = $post;
		}
		$result->close();
	}
	return $posts;
}

function getLatestPosts($moderated, $limit) {
	global $mysqli;
	$posts = [];
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE moderated = ?
		ORDER BY timestamp DESC LIMIT " . (int)$limit,
		[$moderated ? '1' : '0']);
	if ($result) {
		while ($post = $result->fetch_assoc()) {
			$posts[] = $post;
		}
		$result->close();
	}
	return $posts;
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
			deletePostImagesFiles($post);
			$mysqli->execute_query(
				"DELETE FROM " . ATOM_DBPOSTS . "
				WHERE id = ? LIMIT 1",
				[$post['id']]);
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == ATOM_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImagesFiles($thispost);
		$mysqli->execute_query(
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = ? LIMIT 1",
			[$thispost['id']]);
	}
	deleteReports($id);
	deleteLikes($id);
}

function deletePostImages($post, $imgList) {
	global $mysqli;
	deletePostImagesFiles($post, $imgList);
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
	deletePostImagesFilesThumbFiles($post, $imgList);
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
	$threads = [];
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1
		ORDER BY stickied DESC, bumped DESC");
	if ($result) {
		while ($thread = $result->fetch_assoc()) {
			$threads[] = $thread;
		}
		$result->close();
	}
	return $threads;
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
			$result->close();
		}
	}
}

function getThreadPosts($id, $moderatedOnly = true) {
	global $mysqli;
	$posts = [];
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (id = ? OR parent = ?)" .
		($moderatedOnly ? " AND moderated = 1" : "") . "
		ORDER BY id ASC",
		[(int)$id, (int)$id]);
	if ($result) {
		while ($post = $result->fetch_assoc()) {
			$posts[] = $post;
		}
		$result->close();
	}
	return $posts;
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
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE id = ? LIMIT 1",
		[(int)$id]);
	return $result->fetch_assoc();
}

function banByIP($ip) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE ? BETWEEN ip_from AND ip_to LIMIT 1",
		[ip2long($ip)]);
	return $result->fetch_assoc();
}

function getAllBans() {
	global $mysqli;
	$bans = [];
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBBANS . "
		ORDER BY timestamp DESC");
	if ($result) {
		while ($ban = $result->fetch_assoc()) {
			$bans[] = $ban;
		}
		$result->close();
	}
	return $bans;
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
		WHERE ip = ? LIMIT 1",
		[$ip]);
	return $result->fetch_assoc();
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	global $mysqli;
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBIPLOOKUPS . "
		(ip, abuser, vps, proxy, tor, vpn)
		VALUES (?, ?, ?, ?, ?, ?)",
		[(int)$ip, (int)$abuser, (int)$vps, (int)$proxy, (int)$tor, (int)$vpn]);
	return $mysqli->insert_id;
}

/* ==[ Posts reports ]===================================================================================== */

function reportsByPostID($id) {
	global $mysqli;
	$reports = [];
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE postnum = ?
		ORDER BY timestamp DESC",
		[(int)$id]);
	if ($result) {
		while ($report = $result->fetch_assoc()) {
			$reports[] = $report;
		}
		$result->close();
	}
	return $reports;
}

function getAllReports() {
	global $mysqli;
	$reports = [];
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE board = ?
		ORDER BY postnum DESC, timestamp DESC",
		[ATOM_BOARD]);
	if ($result) {
		while ($report = $result->fetch_assoc()) {
			$reports[] = $report;
		}
		$result->close();
	}
	return $reports;
}

function insertReport($id, $board, $ip, $reason) {
	global $mysqli;
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE ip = ? AND board = ? AND postnum = ?",
		[$ip, $board, (int)$id]);
	if ((int)$result->fetch_column()) {
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
	$passcodes = [];
	$result = $mysqli->query(
		"SELECT * FROM " . ATOM_DBPASS . "
		ORDER BY number ASC");
	if ($result) {
		while ($passcode = $result->fetch_assoc()) {
			$passcodes[] = $passcode;
		}
		$result->close();
	}
	return $passcodes;
}

function insertPass($expires, $meta) {
	global $mysqli;
	$passId = bin2hex(random_bytes(32));
	$now = time();
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBPASS . "
		(id, issued, expires, blocked_till, meta)
		VALUES (?, ?, ?, ?, ?)",
		[$passId, $now, $now + $expires, 0, $meta]);
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
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPASS . "
		SET meta = ?,
			" . ($expires ? "expires = ?," : "") . "
			blocked_till = ?,
			blocked_reason = ?
		WHERE number = ?",
		$expires ?
			[$meta, $expires, $blockTill, $blockReason, (int)$passNum] :
			[$meta, $blockTill, $blockReason, (int)$passNum]);
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
	$likes = [];
	$result = $mysqli->execute_query(
		"SELECT * FROM " . ATOM_DBLIKES . "
		WHERE board = ?
		ORDER BY board ASC, postnum ASC",
		[ATOM_BOARD]);
	if ($result) {
		while ($like = $result->fetch_assoc()) {
			$likes[] = $like;
		}
		$result->close();
	}
	return $likes;
}

function toggleLike($id, $ip) {
	global $mysqli;

	// Check is like exists for a post from this ip
	$result = $mysqli->execute_query(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE ip = ? AND board = ? AND postnum = ?",
		[$ip, ATOM_BOARD, (int)$id]);
	$isAlreadyLiked = (int)$result->fetch_column();
	$result->close();

	// Delete existing post like or insert new
	$mysqli->execute_query($isAlreadyLiked ?
		"DELETE FROM " . ATOM_DBLIKES . "
			WHERE ip = ? AND board = ? AND postnum = ?" :
		"INSERT INTO " . ATOM_DBLIKES . "
			(ip, board, postnum)
			VALUES (?, ?, ?)",
		[$ip, ATOM_BOARD, (int)$id]);

	// Get the number of likes for a post
	$result = $mysqli->execute_query(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
	$countOfPostLikes = (int)$result->fetch_column();
	$result->close();

	// Update the likes counter for a post in the post table
	$mysqli->execute_query(
		"UPDATE " . ATOM_DBPOSTS . "
		SET likes = ? WHERE id = ?",
		[$countOfPostLikes, (int)$id]);
	return [!$isAlreadyLiked, $countOfPostLikes];
}

function deleteLikes($id) {
	global $mysqli;
	$mysqli->execute_query(
		"DELETE FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, (int)$id]);
}

/* ==[ Modlog ]============================================================================================ */

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	global $mysqli;
	$records = [];
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$result = $mysqli->execute_query(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = ?
				ORDER BY timestamp DESC LIMIT 100",
				[ATOM_BOARD]);
			if ($result) {
				while ($record = $result->fetch_assoc()) {
					$records[] = $record;
				}
				$result->close();
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$result = $mysqli->execute_query(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = ? AND timestamp >= ? AND timestamp <= ?
				ORDER BY timestamp DESC",
				[ATOM_BOARD, $periodStartDate, $periodEndDate]);
			if ($result) {
				while ($record = $result->fetch_assoc()) {
					$records[] = $record;
				}
				$result->close();
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$result = $mysqli->execute_query(
			"SELECT timestamp, action FROM " . ATOM_DBMODLOG . "
			WHERE boardname = ? AND private = ?
			ORDER BY timestamp DESC LIMIT 100",
			[ATOM_BOARD, '0']);
		if ($result) {
			while ($record = $result->fetch_assoc()) {
				$records[] = $record;
			}
			$result->close();
		}
	}
	return $records;
}

function modLog($action, $private = '1', $color = 'Black') {
	global $mysqli;
	// modLog('Text to show in modlog', '[1, 0]', 'Color');
	// '[1, 0]': 1 = Private record. 0 = Public record.
	// 'Color': Choose what to put in style="color: " for this record
	$userName = isset($_SESSION['atom_user']) ? $_SESSION['atom_user'] : 'UNKNOWN';
	$mysqli->execute_query(
		"INSERT INTO " . ATOM_DBMODLOG . "
		(timestamp, boardname, username, action, color, private)
		VALUES (?, ?, ?, ?, ?, ?)",
		[time(), ATOM_BOARD, $userName, $action, $color, $private]);
}
