<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

if (ATOM_DBDSN == '') { // Build a default (likely MySQL) DSN
	$dsn = ATOM_DBDRIVER . ":host=" . ATOM_DBHOST;
	if (ATOM_DBPORT > 0) {
		$dsn .= ";port=" . ATOM_DBPORT;
	}
	$dsn .= ";dbname=" . ATOM_DBNAME;
} else { // Use a custom DSN
	$dsn = ATOM_DBDSN;
}
if (ATOM_DBDRIVER === 'pgsql') {
	$options = array(PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
} else {
	$options = array(PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4');
}
try {
	$dbh = new PDO($dsn, ATOM_DBUSERNAME, ATOM_DBPASSWORD, $options);
	$dbh->exec('SET NAMES utf8mb4');
} catch (PDOException $e) {
	fancyDie("Failed to connect to the database: " . $e->getMessage());
}

// Creating tables that don't exist
if (ATOM_DBDRIVER === 'pgsql') {
	$isPostsExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBPOSTS))->fetchColumn() != 0;
	$isBansExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBBANS))->fetchColumn() != 0;
	$isIplookupExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBIPLOOKUPS))->fetchColumn() != 0;
	$isReportsExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBREPORTS))->fetchColumn() != 0;
	$isPassExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBPASS))->fetchColumn() != 0;
	$isLikesExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBLIKES))->fetchColumn() != 0;
	$isModlogExists = $dbh->query("SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " .
		$dbh->quote(ATOM_DBMODLOG))->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBPOSTS));
	$isPostsExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBBANS));
	$isBansExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBIPLOOKUPS));
	$isIplookupExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBREPORTS));
	$isReportsExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBPASS));
	$isPassExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBLIKES));
	$isLikesExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBMODLOG));
	$isModlogExists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$isPostsExists) {
	$dbh->exec($postsQuery);
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

function pdoQuery($sql, $params = false) {
	global $dbh;
	if ($params) {
		$statement = $dbh->prepare($sql);
		$statement->execute($params);
	} else {
		$statement = $dbh->query($sql);
	}
	return $statement;
}

/* ==[ Posts ]============================================================================================= */

function insertPost($post) {
	global $dbh;
	$now = time();
	$stm = $dbh->prepare(
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
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$stm->execute(array(
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
	));
	return $dbh->lastInsertId();
}

function getPost($id) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = ?",
		array($id));
	if ($result) {
		return $result->fetch();
	}
}

function getPostsByIP($ip) {
	$posts = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = ?
		ORDER BY timestamp DESC",
		array($ip));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function getPostsByImageHex($hex) {
	$posts = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (file0_hex = ? OR file1_hex = ? OR file2_hex = ? OR file3_hex = ?)
			AND moderated = 1 LIMIT 1",
		array($hex, $hex, $hex, $hex));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function getLatestPosts($moderated, $limit) {
	$posts = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE moderated = ?
		ORDER BY timestamp DESC LIMIT " . $limit,
		array($moderated ? '1' : '0'));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function getLastPostByIP() {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = ?
		ORDER BY id DESC LIMIT 1",
		array($_SERVER['REMOTE_ADDR']));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function getUniquePostersCount() {
	$result = pdoQuery(
		"SELECT COUNT(DISTINCT(ip)) FROM " . ATOM_DBPOSTS);
	return (int)$result->fetchColumn();
}

function approvePost($id) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET moderated = ?
		WHERE id = ?",
		array('1', $id));
}

function deletePost($id) {
	$posts = getThreadPosts($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImagesFiles($post);
			pdoQuery(
				"DELETE FROM " . ATOM_DBPOSTS . "
				WHERE id = ?",
				array($post['id']));
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == ATOM_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImagesFiles($thispost);
		pdoQuery(
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = ?",
			array($thispost['id']));
	}
	deleteReports($id);
	deleteLikes($id);
}

function deletePostImages($post, $imgList) {
	deletePostImagesFiles($post, $imgList);
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
				array('', '', '', '0', '', '0', '0', '', '0', '0', $post['id']));
		}
	}
}

function hidePostImages($post, $imgList) {
	deletePostImagesFilesThumbFiles($post, $imgList);
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT) ) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			pdoQuery(
				"UPDATE " . ATOM_DBPOSTS . "
				SET thumb" . $index . " = ?,
					thumb" . $index . "_width = ?,
					thumb" . $index . "_height = ?
				WHERE id = ?",
				array('spoiler.png', ATOM_FILE_MAXW, ATOM_FILE_MAXW, $post['id']));
		}
	}
}

function editPostMessage($id, $newMessage) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET message = ?
		WHERE id = ?",
		array($newMessage, $id));
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists($id) {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE id = ? AND parent = 0 AND moderated = 1",
		array($id));
	return $result->fetchColumn() != 0;
}

function getThreads() {
	$threads = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1
		ORDER BY stickied DESC, bumped DESC");
	while ($row = $results->fetch()) {
		$threads[] = $row;
	}
	return $threads;
}

function getThreadsCount() {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1");
	return (int)$result->fetchColumn();
}

function trimThreadsCount() {
	$limit = (int)ATOM_MAXTHREADS;
	if ($limit > 0) {
		$results = pdoQuery(
			"SELECT id FROM " . ATOM_DBPOSTS . "
			WHERE parent = 0 AND moderated = 1
			ORDER BY stickied DESC, bumped DESC LIMIT 100 OFFSET " . $limit);
		# old mysql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT $limit,100
		# mysql, postgresql, sqlite3: SELECT id FROM $table ORDER BY bumped LIMIT 100 OFFSET $limit
		# oracle: SELECT id FROM (SELECT id, rownum FROM $table ORDER BY bumped) WHERE rownum >= $limit
		# MSSQL: WITH ts AS (SELECT ROWNUMBER() OVER (ORDER BY bumped) AS 'rownum', * FROM $table)
		#        SELECT id FROM ts WHERE rownum >= $limit
		foreach ($results as $post) {
			deletePost($post['id']);
		}
	}
}

function getThreadPosts($id, $moderatedOnly = true) {
	$posts = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (id = ? OR parent = ?)" .
		($moderatedOnly ? " AND moderated = 1" : "") . "
		ORDER BY id ASC",
		array($id, $id));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function getThreadPostsCount($id) {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = ? AND moderated = 1",
		array($id));
	return (int)$result->fetchColumn();
}

function toggleStickyThread($id, $isStickied) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET stickied = ?
		WHERE id = ?",
		array($isStickied, $id));
}

function toggleLockThread($id, $isLocked) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET locked = ?
		WHERE id = ?",
		array($isLocked, $id));
}

function toggleEndlessThread($id, $isEndless) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET endless = ?
		WHERE id = ?",
		array($isEndless, $id));
}

function bumpThread($id) {
	$now = time();
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET bumped = ?
		WHERE id = ?",
		array($now, $id));
}

/* ==[ Bans ]============================================================================================== */

function banByID($id) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE id = ?",
		array($id));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function banByIP($ip) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE ? BETWEEN `ip_from` AND `ip_to` LIMIT 1",
		array(ip2long($ip)));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function getAllBans() {
	$bans = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBBANS . "
		ORDER BY timestamp DESC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$bans[] = $row;
	}
	return $bans;
}

function insertBan($ban) {
	global $dbh;
	$range = cidr2ip($ban['ip']);
	$now = time();
	$stm = $dbh->prepare(
		"INSERT INTO " . ATOM_DBBANS . "
		(ip_from, ip_to, timestamp, expire, reason)
		VALUES (?, ?, ?, ?, ?)");
	$stm->execute(array($range[0], $range[1], $now, $ban['expire'], $ban['reason']));
	return $dbh->lastInsertId();
}

function deleteBan($id) {
	pdoQuery(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE id = ?",
		array($id));
}

function clearExpiredBans() {
	$now = time();
	pdoQuery(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE expire > 1 AND expire <= ?",
		array($now));
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP($ip) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = ? LIMIT 1",
		array($ip));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	global $dbh;
	$stm = $dbh->prepare(
		"INSERT INTO " . ATOM_DBIPLOOKUPS . "
		(ip, abuser, vps, proxy, tor, vpn)
		VALUES (?, ?, ?, ?, ?, ?)");
	$stm->execute(array($ip, $abuser, $vps, $proxy, $tor, $vpn));
	return $dbh->lastInsertId();
}

/* ==[ Posts reports ]===================================================================================== */

function getAllReports() {
	$reports = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE board = ?
		ORDER BY postnum DESC, timestamp DESC",
		array(ATOM_BOARD));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$reports[] = $row;
	}
	return $reports;
}

function insertReport($id, $board, $ip, $reason) {
	global $dbh;
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE ip = ? AND board = ? AND postnum = ?",
		array($ip, $board, $id));
	if ((int)$result->fetchColumn()) {
		return 'exists';
	}
	$stm = $dbh->prepare(
		"INSERT INTO " . ATOM_DBREPORTS . "
		(ip, board, postnum, reason, timestamp)
		VALUES (?, ?, ?, ?, ?)");
	$stm->execute(array($ip, $board, $id, $reason, time()));
	return $dbh->lastInsertId();
}

function deleteReports($id) {
	pdoQuery(
		"DELETE FROM " . ATOM_DBREPORTS . "
		WHERE board = ? AND postnum = ?",
		array(ATOM_BOARD, $id));
}

/* ==[ Passcodes ]========================================================================================= */

function passByID($passId) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE id = ?",
		array($passId));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function getAllPasscodes() {
	$passcodes = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBPASS . "
		ORDER BY number ASC");
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$passcodes[] = $row;
	}
	return $passcodes;
}

function insertPass($expires, $meta) {
	global $dbh;
	$passId = bin2hex(random_bytes(32));
	$now = time();
	$stm = $dbh->prepare(
		"INSERT INTO " . ATOM_DBPASS . "
		(`id`, `issued`, `expires`, `blocked_till`, `meta`)
		VALUES (?, ?, ?, ?, ?)");
	$stm->execute(array($passId, $now, $now + $expires, 0, $meta));
	return $passId;
}

function usePass($passId, $ip) {
	global $dbh;
	$stm = $dbh->prepare(
		"UPDATE " . ATOM_DBPASS . "
		SET `last_used` = ?, `last_used_ip` = ?
		WHERE `id` = ?");
	$stm->execute(array(time(), $ip, $passId));
}

function blockPass($passNum, $blockTill, $blockReason) {
	global $dbh;
	$stm = $dbh->prepare(
		"UPDATE " . ATOM_DBPASS . "
		SET `blocked_till` = ?, `blocked_reason` = ?
		WHERE `number` = ?");
	$stm->execute(array(time() + $blockTill, $blockReason, $passNum));
}

function unblockPass($passNum) {
	global $dbh;
	$stm = $dbh->prepare(
		"UPDATE " . ATOM_DBPASS . "
		SET `blocked_till` = 0, `blocked_reason` = ''
		WHERE `number` = ?");
	$stm->execute(array($passNum));
}

function deletePass($passId) {
	pdoQuery(
		"DELETE FROM " . ATOM_DBPASS . "
		WHERE id = ?",
		array($passId));
}

/* ==[ Likes ]============================================================================================= */

function getAllLikes() {
	$likes = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBLIKES . "
		WHERE board = ?
		ORDER BY board ASC, postnum ASC",
		array(ATOM_BOARD));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$likes[] = $row;
	}
	return $likes;
}

function toggleLike($id, $ip) {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE ip = ? AND board = ? AND postnum = ?",
		array($ip, ATOM_BOARD, $id));
	$isAlreadyLiked = (int)$result->fetchColumn();
	if ($isAlreadyLiked) {
		pdoQuery(
			"DELETE FROM " . ATOM_DBLIKES . "
			WHERE ip = ? AND board = ? AND postnum = ?",
			array($ip, ATOM_BOARD, $id));
	} else {
		pdoQuery(
			"INSERT INTO " . ATOM_DBLIKES . "
			(ip, board, postnum)
			VALUES (?, ?, ?)",
			array($ip, ATOM_BOARD, $id));
	}
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		array(ATOM_BOARD, $id));
	$countOfPostLikes = (int)$result->fetchColumn();
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET likes = ?
		WHERE id = ?",
		array($countOfPostLikes, $id));
	return array(!$isAlreadyLiked, $countOfPostLikes);
}

function deleteLikes($id) {
	pdoQuery(
		"DELETE FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		array(ATOM_BOARD, $id));
}

/* ==[ Modlog ]============================================================================================ */

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	$records = array();
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$results = pdoQuery(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = ?
				ORDER BY timestamp DESC LIMIT 100",
				array(ATOM_BOARD));
			while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
				$records[] = $row;
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$results = pdoQuery(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = ? AND timestamp >= ? AND timestamp <= ?
				ORDER BY timestamp DESC",
				array(ATOM_BOARD, $periodStartDate, $periodEndDate));
			while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
				$records[] = $row;
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$results = pdoQuery(
			"SELECT timestamp, action FROM " . ATOM_DBMODLOG . "
			WHERE boardname = ? AND private = ?
			ORDER BY timestamp DESC LIMIT 100",
			array(ATOM_BOARD, '0'));
		while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
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
	pdoQuery(
		"INSERT INTO " . ATOM_DBMODLOG . "
		(timestamp, boardname, username, action, color, private)
		VALUES (?, ?, ?, ?, ?, ?)",
		array(time(), ATOM_BOARD, $userName, $action, $color, $private));
}
