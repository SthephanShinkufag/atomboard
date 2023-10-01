<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

if (!function_exists('sqlite_open')) {
	fancyDie("SQLite library is not installed");
}

if (!$db = sqlite_open('atomboard.db', 0666, $error)) {
	fancyDie("Could not connect to database: " . $error);
}

// Create the posts table if it does not exist
$result = sqlite_query($db,
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . ATOM_DBPOSTS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . ATOM_DBPOSTS . " (
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
		file0_size INTEGER NOT NULL DEFAULT '0',
		file0_size_formatted TEXT NOT NULL,
		image0_width INTEGER NOT NULL DEFAULT '0',
		image0_height INTEGER NOT NULL DEFAULT '0',
		thumb0 TEXT NOT NULL,
		thumb0_width INTEGER NOT NULL DEFAULT '0',
		thumb0_height INTEGER NOT NULL DEFAULT '0',
		file1 TEXT NOT NULL,
		file1_hex TEXT NOT NULL,
		file1_original TEXT NOT NULL,
		file1_size INTEGER NOT NULL DEFAULT '0',
		file1_size_formatted TEXT NOT NULL,
		image1_width INTEGER NOT NULL DEFAULT '0',
		image1_height INTEGER NOT NULL DEFAULT '0',
		thumb1 TEXT NOT NULL,
		thumb1_width INTEGER NOT NULL DEFAULT '0',
		thumb1_height INTEGER NOT NULL DEFAULT '0',
		file2 TEXT NOT NULL,
		file2_hex TEXT NOT NULL,
		file2_original TEXT NOT NULL,
		file2_size INTEGER NOT NULL DEFAULT '0',
		file2_size_formatted TEXT NOT NULL,
		image2_width INTEGER NOT NULL DEFAULT '0',
		image2_height INTEGER NOT NULL DEFAULT '0',
		thumb2 TEXT NOT NULL,
		thumb2_width INTEGER NOT NULL DEFAULT '0',
		thumb2_height INTEGER NOT NULL DEFAULT '0',
		file3 TEXT NOT NULL,
		file3_hex TEXT NOT NULL,
		file3_original TEXT NOT NULL,
		file3_size INTEGER NOT NULL DEFAULT '0',
		file3_size_formatted TEXT NOT NULL,
		image3_width INTEGER NOT NULL DEFAULT '0',
		image3_height INTEGER NOT NULL DEFAULT '0',
		thumb3 TEXT NOT NULL,
		thumb3_width INTEGER NOT NULL DEFAULT '0',
		thumb3_height INTEGER NOT NULL DEFAULT '0',
		likes INTEGER NOT NULL DEFAULT '0',
		stickied INTEGER NOT NULL DEFAULT '0',
		locked INTEGER NOT NULL DEFAULT '0',
		endless INTEGER NOT NULL DEFAULT '0',
		pass TEXT
	)");
}

// Create the bans table if it does not exist
$result = sqlite_query($db,
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . ATOM_DBBANS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . ATOM_DBBANS . " (
		id INTEGER PRIMARY KEY,
		ip_from INTEGER NOT NULL,
		ip_to INTEGER NOT NULL,
		timestamp TIMESTAMP NOT NULL,
		expire TIMESTAMP NOT NULL,
		reason TEXT NOT NULL
	)");
}

// Create the likes table if it does not exist
$result = sqlite_query($db,
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . ATOM_DBLIKES . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . ATOM_DBLIKES . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		board TEXT NOT NULL,
		postnum INTEGER NOT NULL,
		islike INTEGER NOT NULL DEFAULT '1'
	)");
}

// Create the modlog table if it does not exist
$result = sqlite_query($db,
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . ATOM_DBMODLOG . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . ATOM_DBMODLOG . " (
		id INTEGER PRIMARY KEY,
		timestamp TIMESTAMP NOT NULL,
		boardname TEXT NOT NULL,
		username TEXT NOT NULL,
		action TEXT NOT NULL,
		color TEXT NOT NULL,
		private INTEGER NOT NULL DEFAULT '1'
	)");
}

// Add stickied column if it isn't present
sqlite_query($db,
	"ALTER TABLE " . ATOM_DBPOSTS . "
	ADD COLUMN stickied INTEGER NOT NULL DEFAULT '0'");

/* ==[ Posts ]============================================================================================= */

function insertPost($post) {
	sqlite_query($GLOBALS['db'],
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
			'" . sqlite_escape_string($post['name']) . "',
			'" . sqlite_escape_string($post['tripcode']) . "',
			'" . sqlite_escape_string($post['email']) . "',
			'" . sqlite_escape_string($post['nameblock']) . "',
			'" . sqlite_escape_string($post['subject']) . "',
			'" . sqlite_escape_string($post['message']) . "',
			'" . sqlite_escape_string($post['password']) . "',
			'" . $post['file0'] . "',
			'" . $post['file0_hex'] . "',
			'" . sqlite_escape_string($post['file0_original']) . "',
			" . $post['file0_size'] . ",
			'" . $post['file0_size_formatted'] . "',
			" . $post['image0_width'] . ",
			" . $post['image0_height'] . ",
			'" . $post['thumb0'] . "',
			" . $post['thumb0_width'] . ",
			" . $post['thumb0_height'] . ",
			'" . $post['file1'] . "',
			'" . $post['file1_hex'] . "',
			'" . sqlite_escape_string($post['file1_original']) . "',
			" . $post['file1_size'] . ",
			'" . $post['file1_size_formatted'] . "',
			" . $post['image1_width'] . ",
			" . $post['image1_height'] . ",
			'" . $post['thumb1'] . "',
			" . $post['thumb1_width'] . ",
			" . $post['thumb1_height'] . ",
			'" . $post['file2'] . "',
			'" . $post['file2_hex'] . "',
			'" . sqlite_escape_string($post['file2_original']) . "',
			" . $post['file2_size'] . ",
			'" . $post['file2_size_formatted'] . "',
			" . $post['image2_width'] . ",
			" . $post['image2_height'] . ",
			'" . $post['thumb2'] . "',
			" . $post['thumb2_width'] . ",
			" . $post['thumb2_height'] . ",
			'" . $post['file3'] . "',
			'" . $post['file3_hex'] . "',
			'" . sqlite_escape_string($post['file3_original']) . "',
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
			" . $post['pass'] . "
		)");
	return sqlite_last_insert_rowid($GLOBALS['db']);
}

function getPost($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		return $post;
	}
}

function getPostsByIP($ip) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = '" . sqlite_escape_string($ip) . "'
		ORDER BY timestamp DESC"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function getPostsByImageHex($hex) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT id, parent FROM " . ATOM_DBPOSTS . "
		WHERE (
			file0_hex = '" . sqlite_escape_string($hex) . "'
			OR file1_hex = '" . sqlite_escape_string($hex) . "'
			OR file2_hex = '" . sqlite_escape_string($hex) . "'
			OR file3_hex = '" . sqlite_escape_string($hex) . "'
		) LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function getLatestPosts($moderated, $limit) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBPOSTS . "
		ORDER BY timestamp DESC LIMIT " . $limit), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function getLastPostByIP() {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "'
		ORDER BY id DESC LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		return $post;
	}
}

function getUniquePostersCount() {
	return sqlite_fetch_single(sqlite_query($GLOBALS['db'],
		"SELECT COUNT(ip) FROM (SELECT DISTINCT ip FROM " . ATOM_DBPOSTS . ")"));
}

function deletePost($id) {
	$posts = getThreadPosts($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImagesFiles($post);
			sqlite_query($GLOBALS['db'],
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
		sqlite_query($GLOBALS['db'],
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = " . $thispost['id']);
	}
}

function deletePostImages($post, $imgList) {
	deletePostImagesFiles($post, $imgList);
	if ($imgList && count($imgList) <= ATOM_FILES_COUNT) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			sqlite_query($GLOBALS['db'],
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
	deletePostImagesFilesThumbFiles($post, $imgList);
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			sqlite_query($GLOBALS['db'],
				"UPDATE " . ATOM_DBPOSTS . "
				SET thumb" . $index . " = 'spoiler.png',
					thumb" . $index . "_width = " . ATOM_FILE_MAXW . ",
					thumb" . $index . "_height = " . ATOM_FILE_MAXW . "
				WHERE id = " . $post['id']);
		}
	}
}

function editPostMessage($id, $newMessage) {
	sqlite_query($GLOBALS['db'],
		"UPDATE " . ATOM_DBPOSTS . "
		SET message = '" . $newMessage . "'
		WHERE id = " . $id);
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists($id) {
	return sqlite_fetch_single(sqlite_query($GLOBALS['db'],
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE id = '" . sqlite_escape_string($id) . "' AND parent = 0 LIMIT 1")) > 0;
}

function getThreads() {
	$threads = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0
		ORDER BY stickied DESC, bumped DESC"), SQLITE_ASSOC);
	foreach ($result as $thread) {
		$threads[] = $thread;
	}
	return $threads;
}

function getThreadsCount() {
	return sqlite_fetch_single(sqlite_query($GLOBALS['db'],
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0"));
}

function trimThreadsCount() {
	if (ATOM_MAXTHREADS > 0) {
		$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
			"SELECT id FROM " . ATOM_DBPOSTS . "
			WHERE parent = 0
			ORDER BY stickied DESC, bumped DESC LIMIT " . ATOM_MAXTHREADS . ", 10"), SQLITE_ASSOC);
		foreach ($result as $post) {
			deletePost($post['id']);
		}
	}
}

function getThreadPosts($id, $moderatedOnly = true) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = " . $id . " OR parent = " . $id . "
		ORDER BY id ASC"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function getThreadPostsCount($id) {
	return sqlite_fetch_single(sqlite_query($GLOBALS['db'],
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = " . $id));
}

function toggleStickyThread($id, $isStickied) {
	sqlite_query($GLOBALS['db'],
		"UPDATE " . ATOM_DBPOSTS . "
		SET stickied = '" . $isStickied . "'
		WHERE id = " . $id);
}

function toggleLockThread($id, $isLocked) {
	sqlite_query($GLOBALS['db'],
		"UPDATE " . ATOM_DBPOSTS . "
		SET locked = '" . $isLocked . "'
		WHERE id = " . $id);
}

function toggleEndlessThread($id, $isEndless) {
	sqlite_query($GLOBALS['db'],
		"UPDATE " . ATOM_DBPOSTS . "
		SET endless = '" . $isEndless . "'
		WHERE id = " . $id);
}

function bumpThread($id) {
	sqlite_query($GLOBALS['db'],
		"UPDATE " . ATOM_DBPOSTS . "
		SET bumped = " . time() . "
		WHERE id = " . $id);
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP($ip) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = '" . sqlite_escape_string($ip) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	sqlite_query($GLOBALS['db'],
		"INSERT INTO `" . ATOM_DBIPLOOKUPS . "`
		(ip, abuser, vps, proxy, tor, vpn)
		VALUES (
			'" . sqlite_escape_string($ip) . "',
			'" . sqlite_escape_string($abuser) . "',
			'" . sqlite_escape_string($vps) . "',
			'" . sqlite_escape_string($proxy) . "',
			'" . sqlite_escape_string($tor) . "',
			'" . sqlite_escape_string($vpn) . "'
		)");
	return sqlite_last_insert_rowid($GLOBALS['db']);
}

/* ==[ Bans ]============================================================================================== */

function banByID($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function banByIP($ip) {
    $ip = ip2long($ip);
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE '" . sqlite_escape_string($ip) . "' BETWEEN `ip_from` and `ip_to` LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function getAllBans() {
	$bans = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBBANS . "
		ORDER BY timestamp DESC"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
    $range = cidr2ip($ban['ip']);
    $range_from = $range[0];
    $range_to = $range[1];
	sqlite_query($GLOBALS['db'],
		"INSERT INTO " . ATOM_DBBANS . "
		(ip_from, ip_to, timestamp, expire, reason)
		VALUES (
			'" . intval($range_from) . "',
			'" . intval($range_to) . "',
			" . time() . ",
			'" . sqlite_escape_string($ban['expire']) . "',
			'" . sqlite_escape_string($ban['reason']) . "'
		)");
	return sqlite_last_insert_rowid($GLOBALS['db']);
}

function deleteBan($id) {
	sqlite_query($GLOBALS['db'],
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE id = " . sqlite_escape_string($id));
}

function clearExpiredBans() {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE expire > 0 AND expire <= " . time()), SQLITE_ASSOC);
	foreach ($result as $ban) {
		sqlite_query($GLOBALS['db'],
			"DELETE FROM " . ATOM_DBBANS . "
			WHERE id = " . $ban['id']);
	}
}

/* ==[ Likes ]============================================================================================= */

function toggleLikePost($id, $ip) {
	$isAlreadyLiked = sqlite_fetch_single(sqlite_query($GLOBALS['db'],
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE ip = '" . $ip . "'
			AND board = '" . ATOM_BOARD . "'
			AND postnum = " . $id));
	if ($isAlreadyLiked) {
		sqlite_query($GLOBALS['db'],
			"DELETE FROM " . ATOM_DBLIKES . "
			WHERE ip = '" . $ip . "'
				AND board = '" . ATOM_BOARD . "'
				AND postnum = " . $id);
	} else {
		sqlite_query($GLOBALS['db'],
			"INSERT INTO " . ATOM_DBLIKES . "
			(ip, board, postnum)
			VALUES ('" . $ip . "', '" . ATOM_BOARD . "', " . $id . ")");
	}
	$countOfPostLikes = sqlite_fetch_single(sqlite_query($GLOBALS['db'],
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE board = '" . ATOM_BOARD . "' AND postnum = " . $id));
	sqlite_query($GLOBALS['db'],
		"UPDATE " . ATOM_DBPOSTS . "
		SET likes = " . $countOfPostLikes . "
		WHERE id = " . $id);
	return array(!$isAlreadyLiked, $countOfPostLikes);
}

/* ==[ Modlog ]============================================================================================ */

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	$records = array();
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = '" . ATOM_BOARD . "'
				ORDER BY timestamp DESC LIMIT 100"), SQLITE_ASSOC);
			foreach ($result as $row) {
				$records[] = $row;
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = '" . ATOM_BOARD . "'
					AND timestamp >= " . $periodStartDate . "
					AND timestamp <= " . $periodEndDate . "
				ORDER BY timestamp DESC"), SQLITE_ASSOC);
			foreach ($result as $row) {
				$records[] = $row;
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$result = sqlite_fetch_all(sqlite_query($GLOBALS['db'],
			"SELECT timestamp, action FROM `" . ATOM_DBMODLOG . "`
			WHERE boardname = '" . ATOM_BOARD . "'
				AND private = '0'
			ORDER BY timestamp DESC LIMIT 100"), SQLITE_ASSOC);
		foreach ($result as $row) {
			$records[] = $row;
		}
	}
	return $records;
}

function modLog($action, $private = '1', $color = 'Black') {
	// modLog('Text to show in modlog', '[1, 0]', 'Color');
	// '[1, 0]': 1 = Private record. 0 = Public record.
	// 'Color': Choose what to put in style="color: " for this record
	$userName = isset($_SESSION['atom_user']) ? $_SESSION['atom_user'] : 'UNKNOWN';
	sqlite_fetch_all(sqlite_query($GLOBALS['db'],
		"INSERT INTO " . ATOM_DBMODLOG . "
		(timestamp, boardname, username, action, color, private)
		VALUES (
			" . time() . ",
			'" . ATOM_BOARD . "',
			'" . sqlite_escape_string($userName) . "',
			'" . sqlite_escape_string($action) . "',
			'" . $color . "',
			'" . $private . "'
		)"), SQLITE_ASSOC);
}
