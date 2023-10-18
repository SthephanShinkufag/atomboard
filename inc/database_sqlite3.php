<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

if (!extension_loaded('sqlite3')) {
	fancyDie("SQLite3 extension is either not installed or loaded");
}

$db = new SQLite3('atomboard.db');
if (!$db) {
	fancyDie("Could not connect to database: " . $db->lastErrorMsg());
}

// Create the posts table if it does not exist
$result = $db->query(
	"SELECT name FROM sqlite_master WHERE type='table' AND name='" . ATOM_DBPOSTS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . ATOM_DBPOSTS . " (
		id INTEGER PRIMARY KEY,
		parent INTEGER NOT NULL,
		timestamp TIMESTAMP NOT NULL,
		bumped TIMESTAMP NOT NULL,
		ip TEXT NOT NULL,
		name TEXT NOT NULL,
		tripcode TEXT NOT NULL,
		email TEXT NOT NULL,
		nameblock TEXT NOT NULL,
		subject TEXT NOT NULL,
		message TEXT NOT NULL,
		password TEXT NOT NULL,
		file0 TEXT NOT NULL,
		file0_hex TEXT NOT NULL,
		file0_original TEXT NOT NULL,
		file0_size INTEGER NOT NULL DEFAULT 0,
		file0_size_formatted TEXT NOT NULL,
		image0_width INTEGER NOT NULL DEFAULT 0,
		image0_height INTEGER NOT NULL DEFAULT 0,
		thumb0 TEXT NOT NULL,
		thumb0_width INTEGER NOT NULL DEFAULT 0,
		thumb0_height INTEGER NOT NULL DEFAULT 0,
		file1 TEXT NOT NULL,
		file1_hex TEXT NOT NULL,
		file1_original TEXT NOT NULL,
		file1_size INTEGER NOT NULL DEFAULT 0,
		file1_size_formatted TEXT NOT NULL,
		image1_width INTEGER NOT NULL DEFAULT 0,
		image1_height INTEGER NOT NULL DEFAULT 0,
		thumb1 TEXT NOT NULL,
		thumb1_width INTEGER NOT NULL DEFAULT 0,
		thumb1_height INTEGER NOT NULL DEFAULT 0,
		file2 TEXT NOT NULL,
		file2_hex TEXT NOT NULL,
		file2_original TEXT NOT NULL,
		file2_size INTEGER NOT NULL DEFAULT 0,
		file2_size_formatted TEXT NOT NULL,
		image2_width INTEGER NOT NULL DEFAULT 0,
		image2_height INTEGER NOT NULL DEFAULT 0,
		thumb2 TEXT NOT NULL,
		thumb2_width INTEGER NOT NULL DEFAULT 0,
		thumb2_height INTEGER NOT NULL DEFAULT 0,
		file3 TEXT NOT NULL,
		file3_hex TEXT NOT NULL,
		file3_original TEXT NOT NULL,
		file3_size INTEGER NOT NULL DEFAULT 0,
		file3_size_formatted TEXT NOT NULL,
		image3_width INTEGER NOT NULL DEFAULT 0,
		image3_height INTEGER NOT NULL DEFAULT 0,
		thumb3 TEXT NOT NULL,
		thumb3_width INTEGER NOT NULL DEFAULT 0,
		thumb3_height INTEGER NOT NULL DEFAULT 0,
		likes INTEGER NOT NULL DEFAULT 0,
		stickied INTEGER NOT NULL DEFAULT 0,
		locked INTEGER NOT NULL DEFAULT 0,
		endless INTEGER NOT NULL DEFAULT 0
		pass TEXT
	)");
}

// Create the bans table if it does not exist
$result = $db->query(
	"SELECT name FROM sqlite_master WHERE type='table' AND name='" . ATOM_DBBANS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . ATOM_DBBANS . " (
		id INTEGER PRIMARY KEY,
		ip_from INTEGER NOT NULL,
		ip_to INTEGER NOT NULL,
		timestamp TIMESTAMP NOT NULL,
		expire TIMESTAMP NOT NULL,
		reason TEXT NOT NULL
	)");
}

// Create the IP lookups table if it does not exist
$result = $db->query(
	"SELECT name FROM sqlite_master WHERE type='table' AND name='" . ATOM_DBIPLOOKUPS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . ATOM_DBIPLOOKUPS . " (
		ip TEXT PRIMARY KEY,
		abuser INTEGER NOT NULL DEFAULT 0,
		vps INTEGER NOT NULL DEFAULT 0,
		proxy INTEGER NOT NULL DEFAULT 0,
		tor INTEGER NOT NULL DEFAULT 0,
		vpn INTEGER NOT NULL DEFAULT 0
	)");
}

// Create the likes table if it does not exist
$result = $db->query(
	"SELECT name FROM sqlite_master WHERE type='table' AND name='" . ATOM_DBLIKES . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . ATOM_DBLIKES . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		board TEXT NOT NULL,
		postnum INTEGER NOT NULL,
		islike INTEGER NOT NULL DEFAULT 1
	)");
}

// Create the modlog table if it does not exist
$result = $db->query(
	"SELECT name FROM sqlite_master WHERE type='table' AND name='" . ATOM_DBMODLOG . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . ATOM_DBMODLOG . " (
		id INTEGER PRIMARY KEY,
		timestamp TIMESTAMP NOT NULL,
		boardname TEXT NOT NULL,
		username TEXT NOT NULL,
		action TEXT NOT NULL,
		color TEXT NOT NULL,
		private INTEGER NOT NULL DEFAULT 1
	)");
}

/* ==[ Posts ]============================================================================================= */

function insertPost($post) {
	global $db;
	$db->exec(
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
			stickied,
			locked,
			endless,
			pass
		) VALUES (
			" . $post['parent'] . ",
			" . time() . ",
			" . time() . ",
			'" . $_SERVER['REMOTE_ADDR'] . "',
			'" . $db->escapeString($post['name']) . "',
			'" . $db->escapeString($post['tripcode']) . "',
			'" . $db->escapeString($post['email']) . "',
			'" . $db->escapeString($post['nameblock']) . "',
			'" . $db->escapeString($post['subject']) . "',
			'" . $db->escapeString($post['message']) . "',
			'" . $db->escapeString($post['password']) . "',
			'" . $post['file0'] . "',
			'" . $post['file0_hex'] . "',
			'" . $db->escapeString($post['file0_original']) . "',
			" . $post['file0_size'] . ",
			'" . $post['file0_size_formatted'] . "',
			" . $post['image0_width'] . ",
			" . $post['image0_height'] . ",
			'" . $post['thumb0'] . "',
			" . $post['thumb0_width'] . ",
			" . $post['thumb0_height'] . ",
			'" . $post['file1'] . "',
			'" . $post['file1_hex'] . "',
			'" . $db->escapeString($post['file1_original']) . "',
			" . $post['file1_size'] . ",
			'" . $post['file1_size_formatted'] . "',
			" . $post['image1_width'] . ",
			" . $post['image1_height'] . ",
			'" . $post['thumb1'] . "',
			" . $post['thumb1_width'] . ",
			" . $post['thumb1_height'] . ",
			'" . $post['file2'] . "',
			'" . $post['file2_hex'] . "',
			'" . $db->escapeString($post['file2_original']) . "',
			" . $post['file2_size'] . ",
			'" . $post['file2_size_formatted'] . "',
			" . $post['image2_width'] . ",
			" . $post['image2_height'] . ",
			'" . $post['thumb2'] . "',
			" . $post['thumb2_width'] . ",
			" . $post['thumb2_height'] . ",
			'" . $post['file3'] . "',
			'" . $post['file3_hex'] . "',
			'" . $db->escapeString($post['file3_original']) . "',
			" . $post['file3_size'] . ",
			'" . $post['file3_size_formatted'] . "',
			" . $post['image3_width'] . ",
			" . $post['image3_height'] . ",
			'" . $post['thumb3'] . "',
			" . $post['thumb3_width'] . ",
			" . $post['thumb3_height'] . ",
			" . $post['likes'] . ",
			" . $post['stickied'] . ",
			" . $post['locked'] . ",
			" . $post['endless'] . ",
			" . intval($post['pass']) . "
		)");
	return $db->lastInsertRowID();
}

function getPost($id) {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = '" . $db->escapeString($id) . "' LIMIT 1");
	while ($post = $result->fetchArray()) {
		return $post;
	}
}

function getPostsByIP($ip) {
	global $db;
	$posts = array();
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = '" . $db->escapeString($ip) . "'
		ORDER BY timestamp DESC");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function getPostsByImageHex($hex) {
	global $db;
	$posts = array();
	$result = $db->query(
		"SELECT id, parent FROM " . ATOM_DBPOSTS . "
		WHERE (
			file0_hex = '" . $db->escapeString($hex) . "'
			OR file1_hex = '" . $db->escapeString($hex) . "'
			OR file2_hex = '" . $db->escapeString($hex) . "'
			OR file3_hex = '" . $db->escapeString($hex) . "') LIMIT 1");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function getLatestPosts($moderated, $limit) {
	global $db;
	$posts = array();
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		ORDER BY timestamp DESC LIMIT " . $limit);
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function getLastPostByIP() {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "'
		ORDER BY id DESC LIMIT 1");
	while ($post = $result->fetchArray()) {
		return $post;
	}
}

function getUniquePostersCount() {
	global $db;
	return $db->querySingle("SELECT COUNT(ip) FROM (SELECT DISTINCT ip FROM " . ATOM_DBPOSTS . ")");
}

function deletePost($id) {
	global $db;
	$posts = getThreadPosts($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImagesFiles($post);
			$db->exec(
				"DELETE FROM " . ATOM_DBPOSTS . "
				WHERE id = " . $post['id']);
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == ATOM_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImagesFiles($thispost);
		$db->exec(
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = " . $thispost['id']);
	}
}

function deletePostImages($post, $imgList) {
	global $db;
	deletePostImagesFiles($post, $imgList);
	if ($imgList && count($imgList) <= ATOM_FILES_COUNT) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			$db->exec(
				"UPDATE " . ATOM_DBPOSTS . "
				SET file" . $index . " = '',
					file" . $index . "_hex = '',
					file" . $index . "_original = '',
					file" . $index . "_size = 0,
					file" . $index . "_size_formatted = '',
					image" . $index . "_width = 0,
					image" . $index . "_height = 0,
					thumb" . $index . " = '',
					thumb" . $index . "_width = 0,
					thumb" . $index . "_height = 0
				WHERE id = " . $post['id']);
		}
	}
}

function hidePostImages($post, $imgList) {
	global $db;
	deletePostImagesFilesThumbFiles($post, $imgList);
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			$db->exec(
				"UPDATE " . ATOM_DBPOSTS . "
				SET thumb" . $index . " = 'spoiler.png',
					thumb" . $index . "_width = " . ATOM_FILE_MAXW . ",
					thumb" . $index . "_height = " . ATOM_FILE_MAXW . "
				WHERE id = " . $post['id']);
		}
	}
}

function editPostMessage($id, $newMessage) {
	global $db;
	$db->exec(
		"UPDATE " . ATOM_DBPOSTS . "
		SET message = '" . $newMessage . "'
		WHERE id = " . $id);
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists($id) {
	global $db;
	return $db->querySingle(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE id = '" . $db->escapeString($id) . "' AND parent = 0 LIMIT 1") > 0;
}

function getThreads() {
	global $db;
	$threads = array();
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0
		ORDER BY stickied DESC, bumped DESC");
	while ($thread = $result->fetchArray()) {
		$threads[] = $thread;
	}
	return $threads;
}

function getThreadsCount() {
	global $db;
	return $db->querySingle(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0");
}

function trimThreadsCount() {
	global $db;
	if (ATOM_MAXTHREADS > 0) {
		$result = $db->query(
			"SELECT id FROM " . ATOM_DBPOSTS . "
			WHERE parent = 0" . "
			ORDER BY stickied DESC, bumped DESC LIMIT " . ATOM_MAXTHREADS . ", 10");
		while ($post = $result->fetchArray()) {
			deletePost($post['id']);
		}
	}
}

function getThreadPosts($id, $moderatedOnly = true) {
	global $db;
	$posts = array();
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = " . $id . " OR parent = " . $id . "
		ORDER BY id ASC");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function getThreadPostsCount($id) {
	global $db;
	return $db->querySingle(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = " . $id);
}

function toggleStickyThread($id, $isStickied) {
	global $db;
	$db->exec(
		"UPDATE " . ATOM_DBPOSTS . "
		SET stickied = '" . $isStickied . "'
		WHERE id = " . $id);
}

function toggleLockThread($id, $isLocked) {
	global $db;
	$db->exec(
		"UPDATE " . ATOM_DBPOSTS . "
		SET locked = '" . $isLocked . "'
		WHERE id = " . $id);
}

function toggleEndlessThread($id, $isEndless) {
	global $db;
	$db->exec(
		"UPDATE " . ATOM_DBPOSTS . "
		SET endless = '" . $isEndless . "'
		WHERE id = " . $id);
}

function bumpThread($id) {
	global $db;
	$db->exec(
		"UPDATE " . ATOM_DBPOSTS . "
		SET bumped = " . time() . "
		WHERE id = " . $id);
}

/* ==[ Bans ]============================================================================================== */

function banByID($id) {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE id = '" . $db->escapeString($id) . "' LIMIT 1");
	while ($ban = $result->fetchArray()) {
		return $ban;
	}
}

function banByIP($ip) {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE '" . $db->escapeString(ip2long($ip)) . "' BETWEEN `ip_from` and `ip_to` LIMIT 1");
	while ($ban = $result->fetchArray()) {
		return $ban;
	}
}

function getAllBans() {
	global $db;
	$bans = array();
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBBANS . "
		ORDER BY timestamp DESC");
	while ($ban = $result->fetchArray()) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
	global $db;
	$range = cidr2ip($ban['ip']);
	$db->exec(
		"INSERT INTO " . ATOM_DBBANS . "
		(ip_from, ip_to, timestamp, expire, reason)
		VALUES (
			'" . intval($range[0]) . "',
			'" . intval($range[1]) . "',
			'" . $db->escapeString($ban['ip']) . "',
			" . time() . ",
			'" . $db->escapeString($ban['expire']) . "',
			'" . $db->escapeString($ban['reason']) . "'
		)");
	return $db->lastInsertRowID();
}

function deleteBan($id) {
	global $db;
	$db->exec(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE id = " . $db->escapeString($id));
}

function clearExpiredBans() {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE expire > 1 AND expire <= " . time());
	while ($ban = $result->fetchArray()) {
		$db->exec(
			"DELETE FROM " . ATOM_DBBANS . "
			WHERE id = " . $ban['id']);
	}
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP($ip) {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = '" . $db->escapeString($ip) . "' LIMIT 1");
	if ($result) {
		while ($ban = $result->fetchArray()) {
			return $ban;
		}
	}
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	global $db;
	$db->exec(
		"INSERT INTO `" . ATOM_DBIPLOOKUPS . "`
		(ip, abuser, vps, proxy, tor, vpn)
		VALUES (
			'" . $db->escapeString($ip) . "',
			'" . $db->escapeString($abuser) . "',
			'" . $db->escapeString($vps) . "',
			'" . $db->escapeString($proxy) . "',
			'" . $db->escapeString($tor) . "',
			'" . $db->escapeString($vpn) . "'
		)");
	return $db->lastInsertRowID();
}

/* ==[ Likes ]============================================================================================= */

function toggleLike($id, $ip) {
	global $db;
	$isAlreadyLiked = $db->querySingle(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE ip = '" . $ip . "'
			AND board = '" . ATOM_BOARD . "'
			AND postnum = " . $id);
	if ($isAlreadyLiked) {
		$db->exec(
			"DELETE FROM " . ATOM_DBLIKES . "
			WHERE ip = '" . $ip . "'
				AND board = '" . ATOM_BOARD . "'
				AND postnum = " . $id);
	} else {
		$db->exec(
			"INSERT INTO " . ATOM_DBLIKES . "
			(ip, board, postnum)
			VALUES ('" . $ip . "', '" . ATOM_BOARD . "', " . $id . ")");
	}
	$countOfPostLikes = $db->querySingle(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE board = '" . ATOM_BOARD . "' AND postnum = " . $id);
	$db->exec(
		"UPDATE " . ATOM_DBPOSTS . "
		SET likes = " . $countOfPostLikes . "
		WHERE id = " . $id);
	return array(!$isAlreadyLiked, $countOfPostLikes);
}

/* ==[ Modlog ]============================================================================================ */

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	global $db;
	$records = array();
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$result = $db->query(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = '" . ATOM_BOARD . "'
				ORDER BY timestamp DESC LIMIT 100");
			while ($row = $result->fetchArray()) {
				$records[] = $row;
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$result = $db->query(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = '" . ATOM_BOARD . "'
					AND timestamp >= " . $periodStartDate . "
					AND timestamp <= " . $periodEndDate . "
				ORDER BY timestamp DESC");
			while ($row = $result->fetchArray()) {
				$records[] = $row;
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$result = $db->query(
			"SELECT timestamp, action FROM `" . ATOM_DBMODLOG . "`
			WHERE boardname = '" . ATOM_BOARD . "'
				AND private = '0'
			ORDER BY timestamp DESC LIMIT 100");
		while ($row = $result->fetchArray()) {
			$records[] = $row;
		}
	}
	return $records;
}

function modLog($action, $private = '1', $color = 'Black') {
	global $db;
	// modLog('Text to show in modlog', '[1, 0]', 'Color');
	// '[1, 0]': 1 = Private record. 0 = Public record.
	// 'Color': Choose what to put in style="color: " for this record
	$userName = isset($_SESSION['atom_user']) ? $_SESSION['atom_user'] : 'UNKNOWN';
	$db->query(
		"INSERT INTO " . ATOM_DBMODLOG . "
		(timestamp, boardname, username, action, color, private)
		VALUES (
			" . time() . ",
			'" . ATOM_BOARD . "',
			'" . $db->escapeString($userName) . "',
			'" . $db->escapeString($action) . "',
			'" . $color . "',
			'" . $private . "'
		)");
}
