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
		PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
}
try {
	$dbh = new PDO($dsn, ATOM_DBUSERNAME, ATOM_DBPASSWORD, $options);
} catch (PDOException $e) {
	fancyDie("Failed to connect to the database: " . $e->getMessage());
}

// Create the posts table if it does not exist
if (ATOM_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(ATOM_DBPOSTS);
	$posts_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBPOSTS));
	$posts_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$posts_exists) {
	$dbh->exec($posts_sql);
}

// Create the bans table if it does not exist
if (ATOM_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(ATOM_DBBANS);
	$bans_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBBANS));
	$bans_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$bans_exists) {
	$dbh->exec($bans_sql);
}

// Create the likes table if it does not exist
if (ATOM_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(ATOM_DBLIKES);
	$likes_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBLIKES));
	$likes_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}
if (!$likes_exists) {
	$dbh->exec($likes_sql);
}

// Create the modlog table if it does not exist
if (ATOM_DBDRIVER === 'pgsql') {
	$query = "SELECT COUNT(*) FROM pg_catalog.pg_tables WHERE tablename LIKE " . $dbh->quote(ATOM_DBMODLOG);
	$modlog_exists = $dbh->query($query)->fetchColumn() != 0;
} else {
	$dbh->query("SHOW TABLES LIKE " . $dbh->quote(ATOM_DBMODLOG));
	$modlog_exists = $dbh->query("SELECT FOUND_ROWS()")->fetchColumn() != 0;
}

if (!$modlog_exists) {
	$dbh->exec($modlog_sql);
}

# Utililty
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

# Post Functions
function uniquePosts() {
	$result = pdoQuery(
		"SELECT COUNT(DISTINCT(ip)) FROM " . ATOM_DBPOSTS);
	return (int)$result->fetchColumn();
}

function postByID($id) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = ?",
		array($id));
	if ($result) {
		return $result->fetch();
	}
}

function threadExistsByID($id) {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE id = ? AND parent = 0 AND moderated = 1",
		array($id));
	return $result->fetchColumn() != 0;
}

// Shoud be changed if you want more files
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
			moderated,
			likes
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
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
		$post['moderated'],
		$post['likes']
	));
	return $dbh->lastInsertId();
}

function approvePostByID($id) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET moderated = ?
		WHERE id = ?",
		array('1', $id));
}

function stickyThreadByID($id, $setsticky) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET stickied = ?
		WHERE id = ?",
		array($setsticky, $id));
}

function lockThreadByID($id, $setlocked) {
	if ($setlocked == 1) {
		$setlocked = ATOM_LOCKTHR_COOKIE;
	} elseif ($setlocked == 0) {
		$setlocked = '';
	}
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET email = ?
		WHERE id = ?",
		array($setlocked, $id));
}

function bumpThreadByID($id) {
	$now = time();
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET bumped = ?
		WHERE id = ?",
		array($now, $id));
}

function countThreads() {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1");
	return (int)$result->fetchColumn();
}

function allThreads() {
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

function numRepliesToThreadByID($id) {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = ? AND moderated = 1",
		array($id));
	return (int)$result->fetchColumn();
}

function postsInThreadByID($id, $moderated_only = true) {
	$posts = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (id = ? OR parent = ?)" .
		($moderated_only ? " AND moderated = 1" : "") .
		" ORDER BY id ASC",
		array($id, $id));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

// Shoud be changed if you want more files
function postsByHex($hex) {
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

function latestPosts($moderated = true) {
	$posts = array();
	$results = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE moderated = ?
		ORDER BY timestamp DESC LIMIT 10",
		array($moderated ? '1' : '0'));
	while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $row;
	}
	return $posts;
}

function deletePostByID($id) {
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
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
		deletePostImages($thispost);
		pdoQuery(
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = ?",
			array($thispost['id']));
	}
}

function deleteImagesByImageID($post, $imgList) {
	deletePostImages($post, $imgList);
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

function hideImagesByImageID($post, $imgList) {
	deletePostImagesThumb($post, $imgList);
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

function editMessageInPostById($id, $newMessage) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET message = ?
		WHERE id = ?",
		array($newMessage, $id));
}

function trimThreads() {
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
			deletePostByID($post['id']);
		}
	}
}

function lastPostByIP() {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = ?
		ORDER BY id DESC LIMIT 1",
		array($_SERVER['REMOTE_ADDR']));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function likePostByID($id, $ip) {
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

# Ban Functions
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
		WHERE ip = ? LIMIT 1",
		array($ip));
	return $result->fetch(PDO::FETCH_ASSOC);
}

function allBans() {
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
	$now = time();
	$stm = $dbh->prepare(
		"INSERT INTO " . ATOM_DBBANS . "
		(ip, timestamp, expire, reason)
		VALUES (?, ?, ?, ?)");
	$stm->execute(array($ban['ip'], $now, $ban['expire'], $ban['reason']));
	return $dbh->lastInsertId();
}

function clearExpiredBans() {
	$now = time();
	pdoQuery(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE expire > 0 AND expire <= ?",
		array($now));
}

function deleteBanByID($id) {
	pdoQuery(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE id = ?",
		array($id));
}

// Modlog functions
function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	$records = array();
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$results = pdoQuery(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = ? ORDER BY timestamp DESC LIMIT 100",
				array(ATOM_BOARD));
			while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
				$records[] = $row;
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$results = pdoQuery(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = ? AND timestamp >= ? AND timestamp <= ? ORDER BY timestamp DESC",
				array(ATOM_BOARD, $periodStartDate, $periodEndDate));
			while ($row = $results->fetch(PDO::FETCH_ASSOC)) {
				$records[] = $row;
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$results = pdoQuery(
			"SELECT timestamp, action FROM " . ATOM_DBMODLOG . "
			WHERE boardname = ? AND private = ? ORDER BY timestamp DESC LIMIT 100",
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
