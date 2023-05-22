<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

if (!function_exists('mysqli_connect')) {
	fancyDie("MySQL library is not installed");
}

$link = @mysqli_connect(TINYIB_DBHOST, TINYIB_DBUSERNAME, TINYIB_DBPASSWORD);
if (!$link) {
	fancyDie("Could not connect to database: " .
		((is_object($link)) ? mysqli_error($link) :
			(($link_error = mysqli_connect_error()) ? $link_error : '(unknown error)')));
}
$db_selected = @mysqli_query($link, "USE " . constant('TINYIB_DBNAME'));
if (!$db_selected) {
	fancyDie("Could not select database: " .
		((is_object($link)) ? mysqli_error($link) :
			(($link_error = mysqli_connect_error()) ? $link_error : '(unknown error')));
}
mysqli_query($link, "SET NAMES 'utf8'");

// Create the posts table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . TINYIB_DBPOSTS . "'")) == 0) {
	mysqli_query($link, $posts_sql);
}

// Create the bans table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . TINYIB_DBBANS . "'")) == 0) {
	mysqli_query($link, $bans_sql);
}

// Create the likes table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . TINYIB_DBLIKES . "'")) == 0) {
	mysqli_query($link, $likes_sql);
}

// Create the modlog table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . TINYIB_DBMODLOG . "'")) == 0) {
	mysqli_query($link, $modlog_sql);
}

# Utililty
function mysqli_result($res, $row, $field = 0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}

# Post Functions
function uniquePosts() {
	global $link;
	$row = mysqli_fetch_row(mysqli_query($link,
		"SELECT COUNT(DISTINCT(`ip`)) FROM " . TINYIB_DBPOSTS));
	return $row[0];
}

function postByID($id) {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBPOSTS . "`
		WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			return $post;
		}
	}
}

function threadExistsByID($id) {
	global $link;
	return mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "`
		WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "'
			AND `parent` = 0 AND `moderated` = 1 LIMIT 1"), 0, 0) > 0;
}

function insertPost($post) {
	global $link;
	mysqli_query($link,
		"INSERT INTO `" . TINYIB_DBPOSTS . "` (
			`parent`,
			`timestamp`,
			`bumped`,
			`ip`,
			`name`,
			`tripcode`,
			`email`,
			`nameblock`,
			`subject`,
			`message`,
			`password`,
			`file0`,
			`file0_hex`,
			`file0_original`,
			`file0_size`,
			`file0_size_formatted`,
			`image0_width`,
			`image0_height`,
			`thumb0`,
			`thumb0_width`,
			`thumb0_height`,
			`file1`,
			`file1_hex`,
			`file1_original`,
			`file1_size`,
			`file1_size_formatted`,
			`image1_width`,
			`image1_height`,
			`thumb1`,
			`thumb1_width`,
			`thumb1_height`,
			`file2`,
			`file2_hex`,
			`file2_original`,
			`file2_size`,
			`file2_size_formatted`,
			`image2_width`,
			`image2_height`,
			`thumb2`,
			`thumb2_width`,
			`thumb2_height`,
			`file3`,
			`file3_hex`,
			`file3_original`,
			`file3_size`,
			`file3_size_formatted`,
			`image3_width`,
			`image3_height`,
			`thumb3`,
			`thumb3_width`,
			`thumb3_height`,
			`moderated`,
			`likes`
		) VALUES (
			" . $post['parent'] . ",
			" . time() . ",
			" . time() . ",
			'" . $_SERVER['REMOTE_ADDR'] . "',
			'" . mysqli_real_escape_string($link, $post['name']) . "',
			'" . mysqli_real_escape_string($link, $post['tripcode']) . "',
			'" . mysqli_real_escape_string($link, $post['email']) . "',
			'" . mysqli_real_escape_string($link, $post['nameblock']) . "',
			'" . mysqli_real_escape_string($link, $post['subject']) . "',
			'" . mysqli_real_escape_string($link, $post['message']) . "',
			'" . mysqli_real_escape_string($link, $post['password']) . "',
			'" . $post['file0'] . "',
			'" . $post['file0_hex'] . "',
			'" . mysqli_real_escape_string($link, $post['file0_original']) . "',
			" . $post['file0_size'] . ",
			'" . $post['file0_size_formatted'] . "',
			" . $post['image0_width'] . ",
			" . $post['image0_height'] . ",
			'" . $post['thumb0'] . "',
			" . $post['thumb0_width'] . ",
			" . $post['thumb0_height'] . ",
			'" . $post['file1'] . "',
			'" . $post['file1_hex'] . "',
			'" . mysqli_real_escape_string($link, $post['file1_original']) . "',
			" . $post['file1_size'] . ",
			'" . $post['file1_size_formatted'] . "',
			" . $post['image1_width'] . ",
			" . $post['image1_height'] . ",
			'" . $post['thumb1'] . "',
			" . $post['thumb1_width'] . ",
			" . $post['thumb1_height'] . ",
			'" . $post['file2'] . "',
			'" . $post['file2_hex'] . "',
			'" . mysqli_real_escape_string($link, $post['file2_original']) . "',
			" . $post['file2_size'] . ",
			'" . $post['file2_size_formatted'] . "',
			" . $post['image2_width'] . ",
			" . $post['image2_height'] . ",
			'" . $post['thumb2'] . "',
			" . $post['thumb2_width'] . ",
			" . $post['thumb2_height'] . ",
			'" . $post['file3'] . "',
			'" . $post['file3_hex'] . "',
			'" . mysqli_real_escape_string($link, $post['file3_original']) . "',
			" . $post['file3_size'] . ",
			'" . $post['file3_size_formatted'] . "',
			" . $post['image3_width'] . ",
			" . $post['image3_height'] . ",
			'" . $post['thumb3'] . "',
			" . $post['thumb3_width'] . ",
			" . $post['thumb3_height'] . ",
			" . $post['moderated'] . ",
			" . $post['likes'] . "
		)");
	return mysqli_insert_id($link);
}

function approvePostByID($id) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . TINYIB_DBPOSTS . "`
		SET `moderated` = 1
		WHERE `id` = " . $id . " LIMIT 1");
}

function stickyThreadByID($id, $setsticky) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . TINYIB_DBPOSTS . "`
		SET `stickied` = '" . mysqli_real_escape_string($link, $setsticky) . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function lockThreadByID($id, $setlocked) {
	global $link;
	if ($setlocked == 1) {
		$setlocked = TINYIB_LOCKTHR_COOKIE;
	} elseif ($setlocked == 0) {
		$setlocked = '';
	}
	mysqli_query($link,
		"UPDATE `" . TINYIB_DBPOSTS . "`
		SET `email` = '" . mysqli_real_escape_string($link, $setlocked) . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function bumpThreadByID($id) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . TINYIB_DBPOSTS . "`
		SET `bumped` = " . time() . "
		WHERE `id` = " . $id . " LIMIT 1");
}

function countThreads() {
	global $link;
	return mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "`
		WHERE `parent` = 0 AND `moderated` = 1"), 0, 0);
}

function allThreads() {
	global $link;
	$threads = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBPOSTS . "`
		WHERE `parent` = 0 AND `moderated` = 1
		ORDER BY `stickied` DESC, `bumped` DESC");
	if ($result) {
		while ($thread = mysqli_fetch_assoc($result)) {
			$threads[] = $thread;
		}
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	global $link;
	return mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . TINYIB_DBPOSTS . "`
		WHERE `parent` = " . $id . " AND `moderated` = 1"), 0, 0);
}

function postsInThreadByID($id, $moderated_only = true) {
	global $link;
	$posts = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBPOSTS . "`
		WHERE (`id` = " . $id . " OR `parent` = " . $id . ")" .
		($moderated_only ? " AND `moderated` = 1" : "") .
		" ORDER BY `id` ASC");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function postsByHex($hex) {
	global $link;
	$posts = array();
	$result = mysqli_query($link,
		"SELECT `id`, `parent` FROM `" . TINYIB_DBPOSTS . "`
		WHERE (
			`file0_hex` = '" . mysqli_real_escape_string($link, $hex) . "'
			OR `file1_hex` = '" . mysqli_real_escape_string($link, $hex) . "'
			OR `file2_hex` = '" . mysqli_real_escape_string($link, $hex) . "'
			OR `file3_hex` = '" . mysqli_real_escape_string($link, $hex) . "'
		) AND `moderated` = 1 LIMIT 1");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function latestPosts($moderated = true) {
	global $link;
	$posts = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBPOSTS . "`
		WHERE `moderated` = " . ($moderated ? '1' : '0') . "
		ORDER BY `timestamp` DESC LIMIT 10");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function deletePostByID($id) {
	global $link;
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
			mysqli_query($link,
				"DELETE FROM `" . TINYIB_DBPOSTS . "`
				WHERE `id` = " . $post['id'] . " LIMIT 1");
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == TINYIB_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImages($thispost);
		mysqli_query($link,
			"DELETE FROM `" . TINYIB_DBPOSTS . "`
			WHERE `id` = " . $thispost['id'] . " LIMIT 1");
	}
}

function deleteImagesByImageID($post, $imgList) {
	global $link;
	deletePostImages($post, $imgList);
	if ($imgList && count($imgList) <= TINYIB_MAXIMUM_FILES) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			mysqli_query($link,
				"UPDATE `" . TINYIB_DBPOSTS . "`
				SET `file" . $index . "` = '',
					`file" . $index . "_hex` = '',
					`file" . $index . "_original` = '',
					`file" . $index . "_size` = 0,
					`file" . $index . "_size_formatted` = '',
					`image" . $index . "_width` = 0,
					`image" . $index . "_height` = 0,
					`thumb" . $index . "` = '',
					`thumb" . $index . "_width` = 0,
					`thumb" . $index . "_height` = 0
				WHERE `id` = " . $post['id'] . " LIMIT 1");
		}
	}
}

function hideImagesByImageID($post, $imgList) {
	global $link;
	deletePostImagesThumb($post, $imgList);
	if ($imgList && (count($imgList) <= TINYIB_MAXIMUM_FILES) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			mysqli_query($link,
				"UPDATE `" . TINYIB_DBPOSTS . "`
				SET `thumb" . $index . "` = 'spoiler.png',
					`thumb" . $index . "_width` = " . TINYIB_MAXW . ",
					`thumb" . $index . "_height` = " . TINYIB_MAXW . "
				WHERE `id` = " . $post['id']);
		}
	}
}

function editMessageInPostById($id, $newMessage) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . TINYIB_DBPOSTS . "`
		SET `message` = '" . $newMessage . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function trimThreads() {
	global $link;
	if (TINYIB_MAXTHREADS > 0) {
		$result = mysqli_query($link,
			"SELECT `id` FROM `" . TINYIB_DBPOSTS . "`
			WHERE `parent` = 0 AND `moderated` = 1
			ORDER BY `stickied` DESC, `bumped` DESC LIMIT " . TINYIB_MAXTHREADS . ", 10");
		if ($result) {
			while ($post = mysqli_fetch_assoc($result)) {
				deletePostByID($post['id']);
			}
		}
	}
}

function lastPostByIP() {
	global $link;
	$replies = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBPOSTS . "`
		WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "'
		ORDER BY `id` DESC LIMIT 1");
	if ($replies) {
		while ($post = mysqli_fetch_assoc($replies)) {
			return $post;
		}
	}
}

function likePostByID($id, $ip) {
	global $link;
	$isAlreadyLiked = mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . TINYIB_DBLIKES . "`
		WHERE `ip` = '" . $ip . "'
			AND `board` = '" . TINYIB_BOARD . "'
			AND `postnum` = " . $id), 0, 0);
	if ($isAlreadyLiked) {
		mysqli_query($link,
			"DELETE FROM `" . TINYIB_DBLIKES . "`
			WHERE `ip` = '" . $ip . "'
				AND `board` = '" . TINYIB_BOARD . "'
				AND postnum = " . $id);
	} else {
		mysqli_query($link,
			"INSERT INTO `" . TINYIB_DBLIKES . "`
			(`ip`, `board`, `postnum`)
			VALUES ('" . $ip . "', '" . TINYIB_BOARD . "', " . $id . ")");
	}
	$countOfPostLikes = mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . TINYIB_DBLIKES . "`
		WHERE `board` = '" . TINYIB_BOARD . "' AND `postnum` = " . $id), 0, 0);
	mysqli_query($link,
		"UPDATE `" . TINYIB_DBPOSTS . "`
		SET `likes` = " . $countOfPostLikes . "
		WHERE `id` = " . $id);
	return array(!$isAlreadyLiked, $countOfPostLikes);
}

# Ban Functions
function banByID($id) {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBBANS . "`
		WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function banByIP($ip) {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBBANS . "`
		WHERE `ip` = '" . mysqli_real_escape_string($link, $ip) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function allBans() {
	global $link;
	$bans = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBBANS . "`
		ORDER BY `timestamp` DESC");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			$bans[] = $ban;
		}
	}
	return $bans;
}

function insertBan($ban) {
	global $link;
	mysqli_query($link,
		"INSERT INTO `" . TINYIB_DBBANS . "`
		(`ip`, `timestamp`, `expire`, `reason`)
		VALUES (
			'" . mysqli_real_escape_string($link, $ban['ip']) . "',
			" . time() . ",
			'" . mysqli_real_escape_string($link, $ban['expire']) . "',
			'" . mysqli_real_escape_string($link, $ban['reason']) . "'
		)");
	return mysqli_insert_id($link);
}

function clearExpiredBans() {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM `" . TINYIB_DBBANS . "`
		WHERE `expire` > 0 AND `expire` <= " . time());
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			mysqli_query($link,
				"DELETE FROM `" . TINYIB_DBBANS . "`
				WHERE `id` = " . $ban['id'] . " LIMIT 1");
		}
	}
}

function deleteBanByID($id) {
	global $link;
	mysqli_query($link,
		"DELETE FROM `" . TINYIB_DBBANS . "`
		WHERE `id` = " . mysqli_real_escape_string($link, $id) . " LIMIT 1");
}

// Modlog functions
function allModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	global $link;
	$modLogs = array();
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$result = mysqli_query($link,
				"SELECT `timestamp`, `username`, `action`, `color` FROM `" . TINYIB_DBMODLOG . "`
				WHERE `boardname` = '" . TINYIB_BOARD . "'
				ORDER BY `timestamp` DESC LIMIT 100");
			if ($result) {
				while ($row = mysqli_fetch_assoc($result)) {
					$modLogs[] = $row;
				}
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$result = mysqli_query($link,
				"SELECT `timestamp`, `username`, `action`, `color` FROM `" . TINYIB_DBMODLOG . "`
				WHERE `boardname` = '" . TINYIB_BOARD . "'
					AND `timestamp` >= " . $periodStartDate . "
					AND `timestamp` <= " . $periodEndDate . "
				ORDER BY `timestamp` DESC");
			if ($result) {
				while ($row = mysqli_fetch_assoc($result)) {
					$modLogs[] = $row;
				}
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$result = mysqli_query($link,
			"SELECT `timestamp`, `action` FROM `" . TINYIB_DBMODLOG . "`
			WHERE `boardname` = '" . TINYIB_BOARD . "'
				AND `private` = '0'
			ORDER BY `timestamp` DESC LIMIT 100");
		if ($result) {
			while ($row = mysqli_fetch_assoc($result)) {
				$modLogs[] = $row;
			}
		}
	}
	return $modLogs;
}

function modLog($action, $private = '1', $color = 'Black') {
	global $link;
	// modLog('Text to show in modlog', '[1, 0]', 'Color');
	// '[1, 0]': 1 = Private record. 0 = Public record.
	// 'Color': Choose what to put in style="color: " for this record
	$userName = isset($_SESSION['tinyib_user']) ? $_SESSION['tinyib_user'] : 'UNKNOWN';
	mysqli_query($link,
		"INSERT INTO `" . TINYIB_DBMODLOG . "`
		(`timestamp`, `boardname`, `username`, `action`, `color`, `private`)
		VALUES (
			" . time() . ",
			'" . TINYIB_BOARD . "',
			'" . mysqli_real_escape_string($link, $userName) . "',
			'" . mysqli_real_escape_string($link, $action) . "',
			'" . $color . "',
			'" . $private . "'
		)");
}
