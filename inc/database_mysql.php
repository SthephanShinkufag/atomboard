<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

if (!function_exists('mysql_connect')) {
	fancyDie("MySQL library is not installed");
}

$link = mysql_connect(ATOM_DBHOST, ATOM_DBUSERNAME, ATOM_DBPASSWORD);
if (!$link) {
	fancyDie("Could not connect to database: " . mysql_error());
}
$db_selected = mysql_select_db(ATOM_DBNAME, $link);
if (!$db_selected) {
	fancyDie("Could not select database: " . mysql_error());
}
mysql_query("SET NAMES 'utf8mb4'");

// Create the posts table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . ATOM_DBPOSTS . "'")) == 0) {
	mysql_query($postsQuery);
}

// Create the bans table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . ATOM_DBBANS . "'")) == 0) {
	mysql_query($bansQuery);
}

// Create the pass table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . ATOM_DBPASS . "'")) == 0) {
	mysql_query($passQuery);
}

// Create the likes table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . ATOM_DBLIKES . "'")) == 0) {
	mysql_query($likesQuery);
}

// Create the modlog table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . ATOM_DBMODLOG . "'")) == 0) {
	mysql_query($modlogQuery);
}

// Create the ip lookups table if it does not exist
if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '" . ATOM_DBIPLOOKUPS . "'")) == 0) {
	mysql_query($ipLookupsQuery);
}

/* ==[ Posts ]============================================================================================= */

function insertPost($post) {
	mysql_query(
		"INSERT INTO `" . ATOM_DBPOSTS . "` (
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
			`likes`,
			`moderated`,
			`stickied`,
			`locked`,
			`endless`,
			`pass`
		) VALUES (
			" . $post['parent'] . ",
			" . time() . ",
			" . time() . ",
			'" . $_SERVER['REMOTE_ADDR'] . "',
			'" . mysql_real_escape_string($post['name']) . "',
			'" . mysql_real_escape_string($post['tripcode']) . "',
			'" . mysql_real_escape_string($post['email']) . "',
			'" . mysql_real_escape_string($post['nameblock']) . "',
			'" . mysql_real_escape_string($post['subject']) . "',
			'" . mysql_real_escape_string($post['message']) . "',
			'" . mysql_real_escape_string($post['password']) . "',
			'" . $post['file0'] . "',
			'" . $post['file0_hex'] . "',
			'" . mysql_real_escape_string($post['file0_original']) . "',
			" . $post['file0_size'] . ",
			'" . $post['file0_size_formatted'] . "',
			" . $post['image0_width'] . ",
			" . $post['image0_height'] . ",
			'" . $post['thumb0'] . "',
			" . $post['thumb0_width'] . ",
			" . $post['thumb0_height'] . ",
			'" . $post['file1'] . "',
			'" . $post['file1_hex'] . "',
			'" . mysql_real_escape_string($post['file1_original']) . "',
			" . $post['file1_size'] . ",
			'" . $post['file1_size_formatted'] . "',
			" . $post['image1_width'] . ",
			" . $post['image1_height'] . ",
			'" . $post['thumb1'] . "',
			" . $post['thumb1_width'] . ",
			" . $post['thumb1_height'] . ",
			'" . $post['file2'] . "',
			'" . $post['file2_hex'] . "',
			'" . mysql_real_escape_string($post['file2_original']) . "',
			" . $post['file2_size'] . ",
			'" . $post['file2_size_formatted'] . "',
			" . $post['image2_width'] . ",
			" . $post['image2_height'] . ",
			'" . $post['thumb2'] . "',
			" . $post['thumb2_width'] . ",
			" . $post['thumb2_height'] . ",
			'" . $post['file3'] . "',
			'" . $post['file3_hex'] . "',
			'" . mysql_real_escape_string($post['file3_original']) . "',
			" . $post['file3_size'] . ",
			'" . $post['file3_size_formatted'] . "',
			" . $post['image3_width'] . ",
			" . $post['image3_height'] . ",
			'" . $post['thumb3'] . "',
			" . $post['thumb3_width'] . ",
			" . $post['thumb3_height'] . ",
			" . $post['likes'] . ",
			" . $post['moderated'] . ",
			" . $post['stickied'] . ",
			" . $post['locked'] . ",
			" . $post['endless'] . ",
			" . mysql_real_escape_string($post['pass']) . "
		)");
	return mysql_insert_id();
}

function getPost($id) {
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			return $post;
		}
	}
}

function getPostsByImageHex($hex) {
	$posts = array();
	$result = mysql_query(
		"SELECT `id`, `parent` FROM `" . ATOM_DBPOSTS . "`
		WHERE (
			`file0_hex` = '" . mysql_real_escape_string($hex) . "'
			OR `file1_hex` = '" . mysql_real_escape_string($hex) . "'
			OR `file2_hex` = '" . mysql_real_escape_string($hex) . "'
			OR `file3_hex` = '" . mysql_real_escape_string($hex) . "'
		) AND `moderated` = 1 LIMIT 1");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function getLatestPosts($moderated, $limit) {
	$posts = array();
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE `moderated` = " . ($moderated ? '1' : '0') . "
		ORDER BY `timestamp` DESC LIMIT " . $limit);
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function getLastPostByIP() {
	$replies = mysql_query(
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "'
		ORDER BY `id` DESC LIMIT 1");
	if ($replies) {
		while ($post = mysql_fetch_assoc($replies)) {
			return $post;
		}
	}
}

function getUniquePostersCount() {
	$row = mysql_fetch_row(mysql_query(
		"SELECT COUNT(DISTINCT(`ip`)) FROM " . ATOM_DBPOSTS));
	return $row[0];
}

function approvePost($id) {
	mysql_query(
		"UPDATE `" . ATOM_DBPOSTS .
		"` SET `moderated` = 1
		WHERE `id` = " . $id . " LIMIT 1");
}

function deletePost($id) {
	$posts = getThreadPosts($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImagesFiles($post);
			mysql_query(
				"DELETE FROM `" . ATOM_DBPOSTS . "`
				WHERE `id` = " . $post['id'] . " LIMIT 1");
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == ATOM_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImagesFiles($thispost);
		mysql_query(
			"DELETE FROM `" . ATOM_DBPOSTS . "`
			WHERE `id` = " . $thispost['id'] . " LIMIT 1");
	}
}

function deletePostImages($post, $imgList) {
	deletePostImagesFiles($post, $imgList);
	if ($imgList && count($imgList) <= ATOM_FILES_COUNT) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			mysql_query(
				"UPDATE `" . ATOM_DBPOSTS . "`
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

function hidePostImages($post, $imgList) {
	deletePostImagesFilesThumbFiles($post, $imgList);
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			mysql_query(
				"UPDATE `" . ATOM_DBPOSTS . "`
				SET `thumb" . $index . "` = 'spoiler.png',
					`thumb" . $index . "_width` = " . ATOM_FILE_MAXW . ",
					`thumb" . $index . "_height` = " . ATOM_FILE_MAXW . "
				WHERE id = " . $post['id']);
		}
	}
}

function editPostMessage($id, $newMessage) {
	mysql_query("UPDATE `" . ATOM_DBPOSTS . "`
		SET `message` = '" . $newMessage . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists($id) {
	return mysql_result(mysql_query(
		"SELECT COUNT(*) FROM `" . ATOM_DBPOSTS . "`
		WHERE `id` = '" . mysql_real_escape_string($id) . "'
		AND `parent` = 0 AND `moderated` = 1 LIMIT 1"), 0, 0) > 0;
}

function getThreads() {
	$threads = array();
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE `parent` = 0 AND `moderated` = 1
		ORDER BY `stickied` DESC, `bumped` DESC");
	if ($result) {
		while ($thread = mysql_fetch_assoc($result)) {
			$threads[] = $thread;
		}
	}
	return $threads;
}

function getThreadsCount() {
	return mysql_result(mysql_query(
		"SELECT COUNT(*) FROM `" . ATOM_DBPOSTS . "`
		WHERE `parent` = 0 AND `moderated` = 1"), 0, 0);
}

function trimThreadsCount() {
	if (ATOM_MAXTHREADS > 0) {
		$result = mysql_query(
			"SELECT `id` FROM `" . ATOM_DBPOSTS . "`
			WHERE `parent` = 0 AND `moderated` = 1
			ORDER BY `stickied` DESC, `bumped` DESC LIMIT " . ATOM_MAXTHREADS . ", 10");
		if ($result) {
			while ($post = mysql_fetch_assoc($result)) {
				deletePost($post['id']);
			}
		}
	}
}

function getThreadPosts($id, $moderatedOnly = true) {
	$posts = array();
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE (`id` = " . $id . " OR `parent` = " . $id . ")" .
		($moderatedOnly ? " AND `moderated` = 1" : "") .
		" ORDER BY `id` ASC");
	if ($result) {
		while ($post = mysql_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function getThreadPostsCount($id) {
	return mysql_result(mysql_query(
		"SELECT COUNT(*) FROM `" . ATOM_DBPOSTS . "`
		WHERE `parent` = " . $id . " AND `moderated` = 1"), 0, 0);
}

function toggleStickyThread($id, $isStickied) {
	mysql_query(
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `stickied` = '" . $isStickied . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function toggleLockThread($id, $isLocked) {
	mysql_query(
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `locked` = '" . $isLocked . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function toggleEndlessThread($id, $isEndless) {
	mysql_query(
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `endless` = '" . $isEndless . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function bumpThread($id) {
	mysql_query(
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `bumped` = " . time() . "
		WHERE `id` = " . $id . " LIMIT 1");
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP($ip) {
	$result = mysql_query(
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = '" . mysql_real_escape_string($ip) . "' LIMIT 1",
		array($ip));
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	mysql_query(
		"INSERT INTO `" . ATOM_DBIPLOOKUPS . "`
		(ip, abuser, vps, proxy, tor, vpn)
		VALUES (
			'" . mysql_real_escape_string($ip) . "',
			'" . mysql_real_escape_string($abuser) . "',
			'" . mysql_real_escape_string($vps) . "',
			'" . mysql_real_escape_string($proxy) . "',
			'" . mysql_real_escape_string($tor) . "',
			'" . mysql_real_escape_string($vpn) . "'
		)");
	return mysql_insert_id();
}

/* ==[ Bans ]============================================================================================== */

function banByID($id) {
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBBANS . "`
		WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function banByIP($ip) {
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBBANS . "`
		WHERE `ip` = '" . mysql_real_escape_string($ip) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function getAllBans() {
	$bans = array();
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBBANS . "`
		ORDER BY `timestamp` DESC");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			$bans[] = $ban;
		}
	}
	return $bans;
}

function insertBan($ban) {
	mysql_query(
		"INSERT INTO `" . ATOM_DBBANS . "`
		(`ip`, `timestamp`, `expire`, `reason`)
		VALUES (
			'" . mysql_real_escape_string($ban['ip']) . "',
			" . time() . ",
			'" . mysql_real_escape_string($ban['expire']) . "',
			'" . mysql_real_escape_string($ban['reason']) . "'
		)");
	return mysql_insert_id();
}

function deleteBan($id) {
	mysql_query(
		"DELETE FROM `" . ATOM_DBBANS . "`
		WHERE `id` = " . mysql_real_escape_string($id) . " LIMIT 1");
}

function clearExpiredBans() {
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBBANS . "`
		WHERE `expire` > 0 AND `expire` <= " . time());
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			mysql_query(
				"DELETE FROM `" . ATOM_DBBANS . "`
				WHERE `id` = " . $ban['id'] . " LIMIT 1");
		}
	}
}

/* ==[ Passcodes ]============================================================================================== */

function passByID($id) {
	$result = mysql_query(
		"SELECT * FROM `" . ATOM_DBPASS . "`
		WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1");
	if ($result) {
		while ($ban = mysql_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function blockPass($pass_id, $block_till, $block_reason) {
	$blocked_till = time() + $block_till;

	mysql_query(
		"UPDATE " . ATOM_DBPASS . "
		SET `blocked_till` = '" . intval($blocked_till) . "',
		    `blocked_reason` = '" . mysql_real_escape_string($block_reason) . "'
		WHERE `id` = '" . mysql_real_escape_string($pass_id) . "'");
}

function usePass($pass_id, $ip) {
	mysql_query(
		"UPDATE " . ATOM_DBPASS . "
		SET `last_used` = '" . time() . "',
		    `last_used_ip` = '" . mysql_real_escape_string($ip) . "'
		WHERE `id` = '" . mysql_real_escape_string($pass_id) . "'");
}

function unblockPass($pass_id) {
	mysql_query(
		"UPDATE " . ATOM_DBPASS . "
		SET `blocked_till` = 0,
		    `blocked_reason` = ''
		WHERE `id` = '" . mysql_real_escape_string($pass_id) . "'");
}

function insertPass($expires, $meta) {
    $pass_id = bin2hex(random_bytes(32));

	mysql_query(
		"INSERT INTO `" . ATOM_DBPASS . "`
		(`id`, `issued`, `expires`, `blocked_till`, `meta`)
		VALUES (
			'" . mysql_real_escape_string($pass_id) . "',
			" . time() . ",
			'" . intval(time() + $expires) . "',
			'0',
			'" . mysql_real_escape_string($meta) . "'
		)");

	return $pass_id;
}

function deletePass($id) {
	mysql_query(
		"DELETE FROM `" . ATOM_DBPASS . "`
		WHERE `id` = " . mysql_real_escape_string($id) . " LIMIT 1");
}

/* ==[ Likes ]============================================================================================= */

function toggleLikePost($id, $ip) {
	$isAlreadyLiked = mysql_result(mysql_query(
		"SELECT COUNT(*) FROM `" . ATOM_DBLIKES . "`
		WHERE `ip` = '" . $ip . "'
			AND `board` = '" . ATOM_BOARD . "'
			AND `postnum` = " . $id), 0, 0);
	if ($isAlreadyLiked) {
		mysql_query(
			"DELETE FROM `" . ATOM_DBLIKES . "`
			WHERE `ip` = '" . $ip . "'
				AND `board` = '" . ATOM_BOARD . "'
				AND postnum = " . $id);
	} else {
		mysql_query(
			"INSERT INTO `" . ATOM_DBLIKES . "`
			(`ip`, `board`, `postnum`)
			VALUES ('" . $ip . "', '" . ATOM_BOARD . "', " . $id . ")");
	}
	$countOfPostLikes = mysql_result(mysql_query(
		"SELECT COUNT(*) FROM `" . ATOM_DBLIKES . "`
		WHERE `board` = '" . ATOM_BOARD . "' AND `postnum` = " . $id), 0, 0);
	mysql_query(
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `likes` = " . $countOfPostLikes . "
		WHERE `id` = " . $id);
	return array(!$isAlreadyLiked, $countOfPostLikes);
}

/* ==[ Modlog ]============================================================================================ */

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	$records = array();
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$result = mysql_query(
				"SELECT `timestamp`, `username`, `action`, `color` FROM `" . ATOM_DBMODLOG . "`
				WHERE `boardname` = '" . ATOM_BOARD . "'
				ORDER BY `timestamp` DESC LIMIT 100");
			if ($result) {
				while ($row = mysql_fetch_assoc($result)) {
					$records[] = $row;
				}
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$result = mysql_query(
				"SELECT `timestamp`, `username`, `action`, `color` FROM `" . ATOM_DBMODLOG . "`
				WHERE `boardname` = '" . ATOM_BOARD . "'
					AND `timestamp` >= " . $periodStartDate . "
					AND `timestamp` <= " . $periodEndDate . "
				ORDER BY `timestamp` DESC");
			if ($result) {
				while ($row = mysql_fetch_assoc($result)) {
					$records[] = $row;
				}
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$result = mysql_query(
			"SELECT `timestamp`, `action` FROM `" . ATOM_DBMODLOG . "`
			WHERE `boardname` = '" . ATOM_BOARD . "'
				AND `private` = '0'
			ORDER BY `timestamp` DESC LIMIT 100");
		if ($result) {
			while ($row = mysql_fetch_assoc($result)) {
				$records[] = $row;
			}
		}
	}
	return $records;
}

function modLog($action, $private = '1', $color = 'Black') {
	// modLog('Text to show in modlog', '[1, 0]', 'Color');
	// '[1, 0]': 1 = Private record. 0 = Public record.
	// 'Color': Choose what to put in style="color: " for this record
	$userName = isset($_SESSION['atom_user']) ? $_SESSION['atom_user'] : 'UNKNOWN';
	mysql_query(
		"INSERT INTO `" . ATOM_DBMODLOG . "`
		(`timestamp`, `boardname`, `username`, `action`, `color`, `private`)
		VALUES (
			" . time() . ",
			'" . ATOM_BOARD . "',
			'" . mysql_real_escape_string($userName) . "',
			'" . mysql_real_escape_string($action) . "',
			'" . $color . "',
			'" . $private . "'
		)");
}
