<?php
if (!defined('TINYIB_BOARD')) {
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
	" WHERE type='table' AND name='" . TINYIB_DBPOSTS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBPOSTS . " (
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
		stickied INTEGER NOT NULL DEFAULT '0',
		likes INTEGER NOT NULL DEFAULT '0'
	)");
}

// Create the bans table if it does not exist
$result = sqlite_query($db,
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . TINYIB_DBBANS . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBBANS . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		timestamp TIMESTAMP NOT NULL,
		expire TIMESTAMP NOT NULL,
		reason TEXT NOT NULL
	)");
}

// Create the likes table if it does not exist
$result = sqlite_query($db,
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . TINYIB_DBLIKES . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBLIKES . " (
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
	" WHERE type='table' AND name='" . TINYIB_DBMODLOG . "'");
if (sqlite_num_rows($result) == 0) {
	sqlite_query($db, "CREATE TABLE " . TINYIB_DBMODLOG . " (
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
	"ALTER TABLE " . TINYIB_DBPOSTS . "
	ADD COLUMN stickied INTEGER NOT NULL DEFAULT '0'");

# Post Functions
function uniquePosts() {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"],
		"SELECT COUNT(ip) FROM (SELECT DISTINCT ip FROM " . TINYIB_DBPOSTS . ")"));
}

function postByID($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBPOSTS . "
		WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		return $post;
	}
}

function threadExistsByID($id) {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"],
		"SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . "
		WHERE id = '" . sqlite_escape_string($id) . "' AND parent = 0 LIMIT 1")) > 0;
}

function insertPost($post) {
	sqlite_query($GLOBALS["db"],
		"INSERT INTO " . TINYIB_DBPOSTS . " (
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
			likes
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
			" . $post['likes'] . "
		)");
	return sqlite_last_insert_rowid($GLOBALS["db"]);
}

function stickyThreadByID($id, $setsticky) {
	sqlite_query($GLOBALS["db"],
		"UPDATE " . TINYIB_DBPOSTS . "
		SET stickied = '" . sqlite_escape_string($setsticky) . "'
		WHERE id = " . $id);
}

function lockThreadByID($id, $setlocked) {
	if ($setlocked == 1) {
		$setlocked = TINYIB_LOCKTHR_COOKIE;
	} elseif ($setlocked == 0) {
		$setlocked = '';
	}
	sqlite_query($GLOBALS["db"],
		"UPDATE " . TINYIB_DBPOSTS . "
		SET email = '" . sqlite_escape_string($setlocked) . "'
		WHERE id = " . $id);
}

function bumpThreadByID($id) {
	sqlite_query($GLOBALS["db"],
		"UPDATE " . TINYIB_DBPOSTS . "
		SET bumped = " . time() . "
		WHERE id = " . $id);
}

function countThreads() {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"],
		"SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . "
		WHERE parent = 0"));
}

function allThreads() {
	$threads = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBPOSTS . "
		WHERE parent = 0
		ORDER BY stickied DESC, bumped DESC"), SQLITE_ASSOC);
	foreach ($result as $thread) {
		$threads[] = $thread;
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	return sqlite_fetch_single(sqlite_query($GLOBALS["db"],
		"SELECT COUNT(*) FROM " . TINYIB_DBPOSTS . "
		WHERE parent = " . $id));
}

function postsInThreadByID($id, $moderated_only = true) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBPOSTS . "
		WHERE id = " . $id . " OR parent = " . $id . "
		ORDER BY id ASC"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function postsByHex($hex) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT id, parent FROM " . TINYIB_DBPOSTS . "
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

function latestPosts($moderated = true) {
	$posts = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBPOSTS . "
		ORDER BY timestamp DESC LIMIT 10"), SQLITE_ASSOC);
	foreach ($result as $post) {
		$posts[] = $post;
	}
	return $posts;
}

function deletePostByID($id) {
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
			sqlite_query($GLOBALS["db"],
				"DELETE FROM " . TINYIB_DBPOSTS . "
				WHERE id = " . $post['id']);
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == TINYIB_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImages($thispost);
		sqlite_query($GLOBALS["db"],
			"DELETE FROM " . TINYIB_DBPOSTS . "
			WHERE id = " . $thispost['id']);
	}
}

function deleteImagesByImageID($post, $imgList) {
	deletePostImages($post, $imgList);
	if ($imgList && count($imgList) <= TINYIB_MAXIMUM_FILES) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			sqlite_query($GLOBALS["db"],
				"UPDATE " . TINYIB_DBPOSTS . "
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

function hideImagesByImageID($post, $imgList) {
	deletePostImagesThumb($post, $imgList);
	if ($imgList && (count($imgList) <= TINYIB_MAXIMUM_FILES) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			sqlite_query($GLOBALS["db"],
				"UPDATE " . TINYIB_DBPOSTS . "
				SET thumb" . $index . " = 'spoiler.png',
					thumb" . $index . "_width = " . TINYIB_MAXW . ",
					thumb" . $index . "_height = " . TINYIB_MAXW . "
				WHERE id = " . $post['id']);
		}
	}
}

function editMessageInPostById($id, $newMessage) {
	sqlite_query($GLOBALS["db"],
		"UPDATE " . TINYIB_DBPOSTS . "
		SET message = '" . $newMessage . "'
		WHERE id = " . $id);
}

function trimThreads() {
	if (TINYIB_MAXTHREADS > 0) {
		$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
			"SELECT id FROM " . TINYIB_DBPOSTS . "
			WHERE parent = 0
			ORDER BY stickied DESC, bumped DESC LIMIT " . TINYIB_MAXTHREADS . ", 10"), SQLITE_ASSOC);
		foreach ($result as $post) {
			deletePostByID($post['id']);
		}
	}
}

function lastPostByIP() {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBPOSTS . "
		WHERE ip = '" . $_SERVER['REMOTE_ADDR'] . "'
		ORDER BY id DESC LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $post) {
		return $post;
	}
}

function likePostByID($id, $ip) {
	$isAlreadyLiked = sqlite_fetch_single(sqlite_query($GLOBALS["db"],
		"SELECT COUNT(*) FROM " . TINYIB_DBLIKES . "
		WHERE ip = '" . $ip . "'
			AND board = '" . TINYIB_BOARD . "'
			AND postnum = " . $id));
	if ($isAlreadyLiked) {
		sqlite_query($GLOBALS["db"],
			"DELETE FROM " . TINYIB_DBLIKES . "
			WHERE ip = '" . $ip . "'
				AND board = '" . TINYIB_BOARD . "'
				AND postnum = " . $id);
	} else {
		sqlite_query($GLOBALS["db"],
			"INSERT INTO " . TINYIB_DBLIKES . "
			(ip, board, postnum)
			VALUES ('" . $ip . "', '" . TINYIB_BOARD . "', " . $id . ")");
	}
	$countOfPostLikes = sqlite_fetch_single(sqlite_query($GLOBALS["db"],
		"SELECT COUNT(*) FROM " . TINYIB_DBLIKES . "
		WHERE board = '" . TINYIB_BOARD . "' AND postnum = " . $id));
	sqlite_query($GLOBALS["db"],
		"UPDATE " . TINYIB_DBPOSTS . "
		SET likes = " . $countOfPostLikes . "
		WHERE id = " . $id);
	return array(!$isAlreadyLiked, $countOfPostLikes);
}

# Ban Functions
function banByID($id) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBBANS . "
		WHERE id = '" . sqlite_escape_string($id) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function banByIP($ip) {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBBANS . "
		WHERE ip = '" . sqlite_escape_string($ip) . "' LIMIT 1"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		return $ban;
	}
}

function allBans() {
	$bans = array();
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBBANS . "
		ORDER BY timestamp DESC"), SQLITE_ASSOC);
	foreach ($result as $ban) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
	sqlite_query($GLOBALS["db"],
		"INSERT INTO " . TINYIB_DBBANS . "
		(ip, timestamp, expire, reason)
		VALUES (
			'" . sqlite_escape_string($ban['ip']) . "',
			" . time() . ",
			'" . sqlite_escape_string($ban['expire']) . "',
			'" . sqlite_escape_string($ban['reason']) . "'
		)");
	return sqlite_last_insert_rowid($GLOBALS["db"]);
}

function clearExpiredBans() {
	$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"SELECT * FROM " . TINYIB_DBBANS . "
		WHERE expire > 0 AND expire <= " . time()), SQLITE_ASSOC);
	foreach ($result as $ban) {
		sqlite_query($GLOBALS["db"],
			"DELETE FROM " . TINYIB_DBBANS . "
			WHERE id = " . $ban['id']);
	}
}

function deleteBanByID($id) {
	sqlite_query($GLOBALS["db"],
		"DELETE FROM " . TINYIB_DBBANS . "
		WHERE id = " . sqlite_escape_string($id));
}

// Modlog functions
function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	$records = array();
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
				"SELECT timestamp, username, action, color FROM " . TINYIB_DBMODLOG . "
				WHERE boardname = '" . TINYIB_BOARD . "'
				ORDER BY timestamp DESC LIMIT 100"));
			foreach ($result as $row) {
				$records[] = $row;
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
				"SELECT timestamp, username, action, color FROM " . TINYIB_DBMODLOG . "
				WHERE boardname = '" . TINYIB_BOARD . "'
					AND timestamp >= " . $periodStartDate . "
					AND timestamp <= " . $periodEndDate . "
				ORDER BY timestamp DESC"));
			foreach ($result as $row) {
				$records[] = $row;
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$result = sqlite_fetch_all(sqlite_query($GLOBALS["db"],
			"SELECT timestamp, action FROM `" . TINYIB_DBMODLOG . "`
			WHERE boardname = '" . TINYIB_BOARD . "'
				AND private = '0'
			ORDER BY timestamp DESC LIMIT 100"));
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
	$userName = isset($_SESSION['atomboard_user']) ? $_SESSION['atomboard_user'] : 'UNKNOWN';
	sqlite_fetch_all(sqlite_query($GLOBALS["db"],
		"INSERT INTO " . TINYIB_DBMODLOG . "
		(timestamp, boardname, username, action, color, private)
		VALUES (
			" . time() . ",
			'" . TINYIB_BOARD . "',
			'" . sqlite_escape_string($userName) . "',
			'" . sqlite_escape_string($action) . "',
			'" . $color . "',
			'" . $private . "'
		)"));
}
