<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

try {
	$dbh = new PDO(
		ATOM_DBDSN != '' ? ATOM_DBDSN :
			ATOM_DBDRIVER . ':host=' . ATOM_DBHOST .
			(ATOM_DBPORT > 0 ? ';port=' . ATOM_DBPORT : '') .
			';dbname=' . ATOM_DBNAME . ';charset=utf8mb4',
		ATOM_DBUSERNAME,
		ATOM_DBPASSWORD,
		array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $e) {
	fancyDie('Failed to connect to the database: ' . $e->getMessage());
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

function pdoQuery($query, $params = NULL) {
	global $dbh;
	if ($params) {
		$statement = $dbh->prepare($query);
		$statement->execute($params);
	} else {
		$statement = $dbh->query($query);
	}
	return $statement;
}

/* ==[ Posts ]============================================================================================= */

function insertPost($post) {
	global $dbh;
	$now = time();
	pdoQuery(
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
	return $dbh->lastInsertId();
}

function getPost($id) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE id = ? LIMIT 1",
		[(int)$id]);
	return $result->fetch(PDO::FETCH_ASSOC);
}

function getPostsByIP($ip) {
	$posts = [];
	$ipArr = cidr2ip($ip);
	$result = $ipArr[0] == $ipArr[1] ?
		pdoQuery(
			"SELECT * FROM " . ATOM_DBPOSTS . "
			WHERE ip = ?
			ORDER BY timestamp DESC",
			[$ip]) :
		pdoQuery(
			"SELECT * FROM " . ATOM_DBPOSTS . "
			WHERE INET_ATON(ip) >= ? AND INET_ATON(ip) <= ?
			ORDER BY timestamp DESC",
			cidr2ip($ip));
	while ($post = $result->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $post;
	}
	return $posts;
}

function getPostsByImageHex($hex) {
	$posts = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (file0_hex = ? OR file1_hex = ? OR file2_hex = ? OR file3_hex = ?)
			AND moderated = 1 LIMIT 1",
		[$hex, $hex, $hex, $hex]);
	while ($post = $result->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $post;
	}
	return $posts;
}

function getLatestPosts($moderated, $limit) {
	$posts = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE moderated = ?
		ORDER BY timestamp DESC LIMIT " . (int)$limit,
		[$moderated ? '1' : '0']);
	while ($post = $result->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $post;
	}
	return $posts;
}

function getLastPostByIP() {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE ip = ?
		ORDER BY id DESC LIMIT 1",
		[$_SERVER['REMOTE_ADDR']]);
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
		['1', (int)$id]);
}

function deletePost($id) {
	$posts = getThreadPosts((int)$id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImagesFiles($post);
			pdoQuery(
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
		pdoQuery(
			"DELETE FROM " . ATOM_DBPOSTS . "
			WHERE id = ? LIMIT 1",
			[$thispost['id']]);
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
				['', '', '', '0', '', '0', '0', '', '0', '0', $post['id']]);
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
				['spoiler.png', ATOM_FILE_MAXW, ATOM_FILE_MAXW, $post['id']]);
		}
	}
}

function editPostMessage($id, $newMessage) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET message = ?
		WHERE id = ?",
		[$newMessage, (int)$id]);
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists($id) {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE id = ? AND parent = 0 AND moderated = 1",
		[(int)$id]);
	return $result->fetchColumn() != 0;
}

function getThreads() {
	$threads = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE parent = 0 AND moderated = 1
		ORDER BY stickied DESC, bumped DESC");
	while ($thread = $result->fetch()) {
		$threads[] = $thread;
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
		$result = pdoQuery(
			"SELECT id FROM " . ATOM_DBPOSTS . "
			WHERE parent = 0 AND moderated = 1
			ORDER BY stickied DESC, bumped DESC LIMIT 100 OFFSET " . $limit);
		foreach ($result as $post) {
			deletePost($post['id']);
		}
	}
}

function getThreadPosts($id, $moderatedOnly = true) {
	$posts = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPOSTS . "
		WHERE (id = ? OR parent = ?)" .
		($moderatedOnly ? " AND moderated = 1" : "") . "
		ORDER BY id ASC",
		[(int)$id, (int)$id]);
	while ($post = $result->fetch(PDO::FETCH_ASSOC)) {
		$posts[] = $post;
	}
	return $posts;
}

function getThreadPostsCount($id) {
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBPOSTS . "
		WHERE parent = ? AND moderated = 1",
		[(int)$id]);
	return (int)$result->fetchColumn();
}

function toggleStickyThread($id, $isStickied) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET stickied = ?
		WHERE id = ?",
		[(int)$isStickied, (int)$id]);
}

function toggleLockThread($id, $isLocked) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET locked = ?
		WHERE id = ?",
		[(int)$isLocked, (int)$id]);
}

function toggleEndlessThread($id, $isEndless) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET endless = ?
		WHERE id = ?",
		[(int)$isEndless, (int)$id]);
}

function bumpThread($id) {
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET bumped = ?
		WHERE id = ?",
		[time(), (int)$id]);
}

/* ==[ Bans ]============================================================================================== */

function banByID($id) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE id = ? LIMIT 1",
		[(int)$id]);
	return $result->fetch(PDO::FETCH_ASSOC);
}

function banByIP($ip) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBBANS . "
		WHERE ? BETWEEN ip_from AND ip_to LIMIT 1",
		[ip2long($ip)]);
	return $result->fetch(PDO::FETCH_ASSOC);
}

function getAllBans() {
	$bans = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBBANS . "
		ORDER BY timestamp DESC");
	while ($ban = $result->fetch(PDO::FETCH_ASSOC)) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
	global $dbh;
	$range = cidr2ip($ban['ip']);
	$now = time();
	pdoQuery(
		"INSERT INTO " . ATOM_DBBANS . "
		(ip_from, ip_to, timestamp, expire, reason)
		VALUES (?, ?, ?, ?, ?)",
		[$range[0], $range[1], $now, $ban['expire'], $ban['reason']]);
	return $dbh->lastInsertId();
}

function deleteBan($id) {
	pdoQuery(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE id = ?",
		[(int)$id]);
}

function clearExpiredBans() {
	pdoQuery(
		"DELETE FROM " . ATOM_DBBANS . "
		WHERE expire > 1 AND expire <= ?",
		[time()]);
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP($ip) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBIPLOOKUPS . "
		WHERE ip = ? LIMIT 1",
		[$ip]);
	return $result->fetch(PDO::FETCH_ASSOC);
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	global $dbh;
	pdoQuery(
		"INSERT INTO " . ATOM_DBIPLOOKUPS . "
		(ip, abuser, vps, proxy, tor, vpn)
		VALUES (?, ?, ?, ?, ?, ?)",
		[(int)$ip, (int)$abuser, (int)$vps, (int)$proxy, (int)$tor, (int)$vpn]);
	return $dbh->lastInsertId();
}

/* ==[ Posts reports ]===================================================================================== */

function reportsByPostID($id) {
	$reports = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE postnum = ?
		ORDER BY timestamp DESC",
		[(int)$id]);
	while ($report = $result->fetch(PDO::FETCH_ASSOC)) {
		$reports[] = $report;
	}
	return $reports;
}

function getAllReports() {
	$reports = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE board = ?
		ORDER BY postnum DESC, timestamp DESC",
		[ATOM_BOARD]);
	while ($report = $result->fetch(PDO::FETCH_ASSOC)) {
		$reports[] = $report;
	}
	return $reports;
}

function insertReport($id, $board, $ip, $reason) {
	global $dbh;
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBREPORTS . "
		WHERE ip = ? AND board = ? AND postnum = ?",
		[$ip, $board, (int)$id]);
	if ((int)$result->fetchColumn()) {
		return 'exists';
	}
	pdoQuery(
		"INSERT INTO " . ATOM_DBREPORTS . "
		(ip, board, postnum, reason, timestamp)
		VALUES (?, ?, ?, ?, ?)",
		[$ip, $board, (int)$id, $reason, time()]);
	return $dbh->lastInsertId();
}

function deleteReports($id) {
	pdoQuery(
		"DELETE FROM " . ATOM_DBREPORTS . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, (int)$id]);
}

/* ==[ Passcodes ]========================================================================================= */

function passByID($passId) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE id = ? LIMIT 1",
		[$passId]);
	return $result->fetch(PDO::FETCH_ASSOC);
}

function passByNum($passNum) {
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPASS . "
		WHERE number = ? LIMIT 1",
		[(int)$passNum]);
	return $result->fetch(PDO::FETCH_ASSOC);
}

function getAllPasscodes() {
	$passcodes = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBPASS . "
		ORDER BY number ASC");
	while ($passcode = $result->fetch(PDO::FETCH_ASSOC)) {
		$passcodes[] = $passcode;
	}
	return $passcodes;
}

function insertPass($expires, $meta) {
	$passId = bin2hex(random_bytes(32));
	$now = time();
	pdoQuery(
		"INSERT INTO " . ATOM_DBPASS . "
		(id, issued, expires, blocked_till, meta)
		VALUES (?, ?, ?, ?, ?)",
		[$passId, $now, $now + $expires, 0, $meta]);
	return $passId;
}

function usePass($passId, $ip) {
	pdoQuery(
		"UPDATE " . ATOM_DBPASS . "
		SET last_used = ?, last_used_ip = ?
		WHERE id = ?",
		[time(), $ip, $passId]);
}

function changePass($passNum, $meta, $expires, $blockTill, $blockReason) {
	pdoQuery(
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
	pdoQuery(
		"DELETE FROM " . ATOM_DBPASS . "
		WHERE id = ?",
		[$passId]);
}

/* ==[ Likes ]============================================================================================= */

function getAllLikes() {
	$likes = [];
	$result = pdoQuery(
		"SELECT * FROM " . ATOM_DBLIKES . "
		WHERE board = ?
		ORDER BY board ASC, postnum ASC",
		[ATOM_BOARD]);
	while ($like = $result->fetch(PDO::FETCH_ASSOC)) {
		$likes[] = $like;
	}
	return $likes;
}

function toggleLike($id, $ip) {
	// Check is like exists for a post from this ip
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE ip = ? AND board = ? AND postnum = ?",
		[$ip, ATOM_BOARD, (int)$id]);
	$isAlreadyLiked = (int)$result->fetchColumn();

	// Delete existing post like or insert new
	pdoQuery($isAlreadyLiked ?
		"DELETE FROM " . ATOM_DBLIKES . "
			WHERE ip = ? AND board = ? AND postnum = ?" :
		"INSERT INTO " . ATOM_DBLIKES . "
			(ip, board, postnum)
			VALUES (?, ?, ?)",
		[$ip, ATOM_BOARD, (int)$id]);

	// Get the number of likes for a post
	$result = pdoQuery(
		"SELECT COUNT(*) FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, $id]);
	$countOfPostLikes = (int)$result->fetchColumn();

	// Update the likes counter for a post in the post table
	pdoQuery(
		"UPDATE " . ATOM_DBPOSTS . "
		SET likes = ? WHERE id = ?",
		[$countOfPostLikes, (int)$id]);
	return [!$isAlreadyLiked, $countOfPostLikes];
}

function deleteLikes($id) {
	pdoQuery(
		"DELETE FROM " . ATOM_DBLIKES . "
		WHERE board = ? AND postnum = ?",
		[ATOM_BOARD, (int)$id]);
}

/* ==[ Modlog ]============================================================================================ */

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	$records = [];
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$result = pdoQuery(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = ?
				ORDER BY timestamp DESC LIMIT 100",
				[ATOM_BOARD]);
			while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
				$records[] = $record;
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$result = pdoQuery(
				"SELECT timestamp, username, action, color FROM " . ATOM_DBMODLOG . "
				WHERE boardname = ? AND timestamp >= ? AND timestamp <= ?
				ORDER BY timestamp DESC",
				[ATOM_BOARD, $periodStartDate, $periodEndDate]);
			while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
				$records[] = $record;
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$result = pdoQuery(
			"SELECT timestamp, action FROM " . ATOM_DBMODLOG . "
			WHERE boardname = ? AND private = ?
			ORDER BY timestamp DESC LIMIT 100",
			[ATOM_BOARD, '0']);
		while ($record = $result->fetch(PDO::FETCH_ASSOC)) {
			$records[] = $record;
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
		[time(), ATOM_BOARD, $userName, $action, $color, $private]);
}
