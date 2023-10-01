<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

if (!function_exists('mysqli_connect')) {
	fancyDie("MySQL library is not installed");
}

$link = @mysqli_connect(ATOM_DBHOST, ATOM_DBUSERNAME, ATOM_DBPASSWORD);
if (!$link) {
	fancyDie("Could not connect to database: " .
		(is_object($link) ? mysqli_error($link) :
			(($linkError = mysqli_connect_error()) ? $linkError : '(unknown error)')));
}
$dbSelected = @mysqli_query($link, "USE " . constant('ATOM_DBNAME'));
if (!$dbSelected) {
	fancyDie("Could not select database: " .
		(is_object($link) ? mysqli_error($link) :
			(($linkError = mysqli_connect_error()) ? $linkError : '(unknown error')));
}
mysqli_query($link, "SET NAMES 'utf8mb4'");

// Create the posts table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBPOSTS . "'")) == 0) {
	mysqli_query($link, $postsQuery);
}

// Create the bans table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBBANS . "'")) == 0) {
	mysqli_query($link, $bansQuery);
}

// Create the bans table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBPASS . "'")) == 0) {
	mysqli_query($link, $passQuery);
}

// Create the likes table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBLIKES . "'")) == 0) {
	mysqli_query($link, $likesQuery);
}

// Create the modlog table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBMODLOG . "'")) == 0) {
	mysqli_query($link, $modlogQuery);
}

// Create the ip lookups table if it does not exist
if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBIPLOOKUPS . "'")) == 0) {
	mysqli_query($link, $ipLookupsQuery);
}

function mysqli_result($res, $row, $field = 0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}

/* ==[ Posts ]============================================================================================= */

function insertPost($post) {
	global $link;
	mysqli_query($link,
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
			" . $post['likes'] . ",
			" . $post['moderated'] . ",
			" . $post['stickied'] . ",
			" . $post['locked'] . ",
			" . $post['endless'] . ",
			" . intval($post['pass']) . "
		)");
	return mysqli_insert_id($link);
}

function getPost($id) {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "' LIMIT 1");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			return $post;
		}
	}
}

function getPostsByImageHex($hex) {
	global $link;
	$posts = array();
	$result = mysqli_query($link,
		"SELECT `id`, `parent` FROM `" . ATOM_DBPOSTS . "`
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

function getLatestPosts($moderated, $limit) {
	global $link;
	$posts = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE `moderated` = " . ($moderated ? '1' : '0') . "
		ORDER BY `timestamp` DESC LIMIT " . $limit);
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function getLastPostByIP() {
	global $link;
	$replies = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE `ip` = '" . $_SERVER['REMOTE_ADDR'] . "'
		ORDER BY `id` DESC LIMIT 1");
	if ($replies) {
		while ($post = mysqli_fetch_assoc($replies)) {
			return $post;
		}
	}
}

function getUniquePostersCount() {
	global $link;
	$row = mysqli_fetch_row(mysqli_query($link,
		"SELECT COUNT(DISTINCT(`ip`)) FROM " . ATOM_DBPOSTS));
	return $row[0];
}

function approvePost($id) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `moderated` = 1
		WHERE `id` = " . $id . " LIMIT 1");
}

function deletePost($id) {
	global $link;
	$posts = getThreadPosts($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImagesFiles($post);
			mysqli_query($link,
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
		mysqli_query($link,
			"DELETE FROM `" . ATOM_DBPOSTS . "`
			WHERE `id` = " . $thispost['id'] . " LIMIT 1");
	}
}

function deletePostImages($post, $imgList) {
	global $link;
	deletePostImagesFiles($post, $imgList);
	if ($imgList && count($imgList) <= ATOM_FILES_COUNT) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			mysqli_query($link,
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
	global $link;
	deletePostImagesFilesThumbFiles($post, $imgList);
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			mysqli_query($link,
				"UPDATE `" . ATOM_DBPOSTS . "`
				SET `thumb" . $index . "` = 'spoiler.png',
					`thumb" . $index . "_width` = " . ATOM_FILE_MAXW . ",
					`thumb" . $index . "_height` = " . ATOM_FILE_MAXW . "
				WHERE `id` = " . $post['id']);
		}
	}
}

function editPostMessage($id, $newMessage) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `message` = '" . $newMessage . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists($id) {
	global $link;
	return mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . ATOM_DBPOSTS . "`
		WHERE `id` = '" . mysqli_real_escape_string($link, $id) . "'
			AND `parent` = 0 AND `moderated` = 1 LIMIT 1"), 0, 0) > 0;
}

function getThreads() {
	global $link;
	$threads = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE `parent` = 0 AND `moderated` = 1
		ORDER BY `stickied` DESC, `bumped` DESC");
	if ($result) {
		while ($thread = mysqli_fetch_assoc($result)) {
			$threads[] = $thread;
		}
	}
	return $threads;
}

function getThreadsCount() {
	global $link;
	return mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . ATOM_DBPOSTS . "`
		WHERE `parent` = 0 AND `moderated` = 1"), 0, 0);
}

function trimThreadsCount() {
	global $link;
	if (ATOM_MAXTHREADS > 0) {
		$result = mysqli_query($link,
			"SELECT `id` FROM `" . ATOM_DBPOSTS . "`
			WHERE `parent` = 0 AND `moderated` = 1
			ORDER BY `stickied` DESC, `bumped` DESC LIMIT " . ATOM_MAXTHREADS . ", 10");
		if ($result) {
			while ($post = mysqli_fetch_assoc($result)) {
				deletePost($post['id']);
			}
		}
	}
}

function getThreadPosts($id, $moderatedOnly = true) {
	global $link;
	$posts = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBPOSTS . "`
		WHERE (`id` = " . $id . " OR `parent` = " . $id . ")" .
		($moderatedOnly ? " AND `moderated` = 1" : "") .
		" ORDER BY `id` ASC");
	if ($result) {
		while ($post = mysqli_fetch_assoc($result)) {
			$posts[] = $post;
		}
	}
	return $posts;
}

function getThreadPostsCount($id) {
	global $link;
	return mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . ATOM_DBPOSTS . "`
		WHERE `parent` = " . $id . " AND `moderated` = 1"), 0, 0);
}

function toggleStickyThread($id, $isStickied) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `stickied` = '" . $isStickied . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function toggleLockThread($id, $isLocked) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `locked` = '" . $isLocked . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function toggleEndlessThread($id, $isEndless) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `endless` = '" . $isEndless . "'
		WHERE `id` = " . $id . " LIMIT 1");
}

function bumpThread($id) {
	global $link;
	mysqli_query($link,
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `bumped` = " . time() . "
		WHERE `id` = " . $id . " LIMIT 1");
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP($ip) {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = '" . mysqli_real_escape_string($ip) . "' LIMIT 1",
		array($ip));
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	global $link;
	mysqli_query($link,
		"INSERT INTO `" . ATOM_DBIPLOOKUPS . "`
		(ip, abuser, vps, proxy, tor, vpn)
		VALUES (
			'" . mysqli_real_escape_string($ip) . "',
			'" . mysqli_real_escape_string($abuser) . "',
			'" . mysqli_real_escape_string($vps) . "',
			'" . mysqli_real_escape_string($proxy) . "',
			'" . mysqli_real_escape_string($tor) . "',
			'" . mysqli_real_escape_string($vpn) . "'
		)");
	return mysqli_insert_id($link);
}

/* ==[ Bans ]============================================================================================== */

function banByID($id) {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBBANS . "`
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
		"SELECT * FROM `" . ATOM_DBBANS . "`
		WHERE '" . intval(ip2long($ip)) . "' BETWEEN `ip_from` AND `ip_to` LIMIT 1");
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			return $ban;
		}
	}
}

function getAllBans() {
	global $link;
	$bans = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBBANS . "`
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
	$range = cidr2ip($ban['ip']);
	mysqli_query($link,
		"INSERT INTO `" . ATOM_DBBANS . "`
		(`ip_from`, `ip_to`, `timestamp`, `expire`, `reason`)
		VALUES (
			'" . intval($range[0]) . "',
			'" . intval($range[1]) . "',
			" . time() . ",
			'" . mysqli_real_escape_string($link, $ban['expire']) . "',
			'" . mysqli_real_escape_string($link, $ban['reason']) . "'
		)");
	return mysqli_insert_id($link);
}

function deleteBan($id) {
	global $link;
	mysqli_query($link,
		"DELETE FROM `" . ATOM_DBBANS . "`
		WHERE `id` = " . mysqli_real_escape_string($link, $id) . " LIMIT 1");
}

function clearExpiredBans() {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBBANS . "`
		WHERE `expire` > 0 AND `expire` <= " . time());
	if ($result) {
		while ($ban = mysqli_fetch_assoc($result)) {
			mysqli_query($link,
				"DELETE FROM `" . ATOM_DBBANS . "`
				WHERE `id` = " . $ban['id'] . " LIMIT 1");
		}
	}
}

/* ==[ Passcodes ]========================================================================================= */

function passByID($passId) {
	global $link;
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBPASS . "`
		WHERE `id` = '" . mysqli_real_escape_string($link, $passId) . "' LIMIT 1");
	if ($result) {
		while ($pass = mysqli_fetch_assoc($result)) {
			return $pass;
		}
	}
}

function getAllPasscodes() {
	global $link;
	$passcodes = array();
	$result = mysqli_query($link,
		"SELECT * FROM `" . ATOM_DBPASS . "`
		ORDER BY `number` DESC");
	if ($result) {
		while ($pass = mysqli_fetch_assoc($result)) {
			$passcodes[] = $pass;
		}
	}
	return $passcodes;
}

function insertPass($expires, $meta) {
	global $link;
	$passId = bin2hex(random_bytes(32));
	$now = time();
	mysqli_query($link,
		"INSERT INTO `" . ATOM_DBPASS . "`
		(`id`, `issued`, `expires`, `blocked_till`, `meta`)
		VALUES (
			'" . mysqli_real_escape_string($link, $passId) . "',
			" . $now . ",
			'" . intval($now + $expires) . "',
			'0',
			'" . mysqli_real_escape_string($link, $meta) . "'
		)");
	return $passId;
}

function usePass($passId, $ip) {
	global $link;
	mysqli_query($link,
		"UPDATE " . ATOM_DBPASS . "
		SET `last_used` = '" . time() . "',
			`last_used_ip` = '" . mysqli_real_escape_string($link, $ip) . "'
		WHERE `id` = '" . mysqli_real_escape_string($link, $passId) . "'");
}

function blockPass($passNum, $blockTill, $blockReason) {
	global $link;
	mysqli_query($link,
		"UPDATE " . ATOM_DBPASS . "
		SET `blocked_till` = '" . intval(time() + $blockTill) . "', 
			`blocked_reason` = '" . mysqli_real_escape_string($link, $blockReason) . "'
		WHERE `number` = '" . intval($passNum) . "'");
}

function unblockPass($passNum) {
	global $link;
	mysqli_query($link,
		"UPDATE " . ATOM_DBPASS . "
		SET `blocked_till` = 0, 
			`blocked_reason` = ''
		WHERE `number` = '" . intval($passNum) . "'");
}

function deletePass($passId) {
	global $link;
	mysqli_query($link,
		"DELETE FROM `" . ATOM_DBPASS . "`
		WHERE `id` = " . mysqli_real_escape_string($link, $passId) . " LIMIT 1");
}

/* ==[ Likes ]============================================================================================= */

function toggleLikePost($id, $ip) {
	global $link;
	$isAlreadyLiked = mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . ATOM_DBLIKES . "`
		WHERE `ip` = '" . $ip . "'
			AND `board` = '" . ATOM_BOARD . "'
			AND `postnum` = " . $id), 0, 0);
	if ($isAlreadyLiked) {
		mysqli_query($link,
			"DELETE FROM `" . ATOM_DBLIKES . "`
			WHERE `ip` = '" . $ip . "'
				AND `board` = '" . ATOM_BOARD . "'
				AND postnum = " . $id);
	} else {
		mysqli_query($link,
			"INSERT INTO `" . ATOM_DBLIKES . "`
			(`ip`, `board`, `postnum`)
			VALUES ('" . $ip . "', '" . ATOM_BOARD . "', " . $id . ")");
	}
	$countOfPostLikes = mysqli_result(mysqli_query($link,
		"SELECT COUNT(*) FROM `" . ATOM_DBLIKES . "`
		WHERE `board` = '" . ATOM_BOARD . "' AND `postnum` = " . $id), 0, 0);
	mysqli_query($link,
		"UPDATE `" . ATOM_DBPOSTS . "`
		SET `likes` = " . $countOfPostLikes . "
		WHERE `id` = " . $id);
	return array(!$isAlreadyLiked, $countOfPostLikes);
}

/* ==[ Modlog ]============================================================================================ */

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	global $link;
	$records = array();
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$result = mysqli_query($link,
				"SELECT `timestamp`, `username`, `action`, `color` FROM `" . ATOM_DBMODLOG . "`
				WHERE `boardname` = '" . ATOM_BOARD . "'
				ORDER BY `timestamp` DESC LIMIT 100");
			if ($result) {
				while ($row = mysqli_fetch_assoc($result)) {
					$records[] = $row;
				}
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$result = mysqli_query($link,
				"SELECT `timestamp`, `username`, `action`, `color` FROM `" . ATOM_DBMODLOG . "`
				WHERE `boardname` = '" . ATOM_BOARD . "'
					AND `timestamp` >= " . $periodStartDate . "
					AND `timestamp` <= " . $periodEndDate . "
				ORDER BY `timestamp` DESC");
			if ($result) {
				while ($row = mysqli_fetch_assoc($result)) {
					$records[] = $row;
				}
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$result = mysqli_query($link,
			"SELECT `timestamp`, `action` FROM `" . ATOM_DBMODLOG . "`
			WHERE `boardname` = '" . ATOM_BOARD . "'
				AND `private` = '0'
			ORDER BY `timestamp` DESC LIMIT 100");
		if ($result) {
			while ($row = mysqli_fetch_assoc($result)) {
				$records[] = $row;
			}
		}
	}
	return $records;
}

function modLog($action, $private = '1', $color = 'Black') {
	global $link;
	// modLog('Text to show in modlog', '[1, 0]', 'Color');
	// '[1, 0]': 1 = Private record. 0 = Public record.
	// 'Color': Choose what to put in style="color: " for this record
	$userName = isset($_SESSION['atom_user']) ? $_SESSION['atom_user'] : 'UNKNOWN';
	mysqli_query($link,
		"INSERT INTO `" . ATOM_DBMODLOG . "`
		(`timestamp`, `boardname`, `username`, `action`, `color`, `private`)
		VALUES (
			" . time() . ",
			'" . ATOM_BOARD . "',
			'" . mysqli_real_escape_string($link, $userName) . "',
			'" . mysqli_real_escape_string($link, $action) . "',
			'" . $color . "',
			'" . $private . "'
		)");
}
