<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

if (!extension_loaded('sqlite3')) {
	fancyDie("SQLite3 extension is either not installed or loaded");
}

$db = new SQLite3('tinyib.db');
if (!$db) {
	fancyDie("Could not connect to database: " . $db->lastErrorMsg());
}

// Create the posts table if it does not exist
$result = $db->query(
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . TINYIB_DBPOSTS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBPOSTS . " (
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
		file TEXT NOT NULL,
		file_hex TEXT NOT NULL,
		file_original TEXT NOT NULL,
		file_size INTEGER NOT NULL DEFAULT '0',
		file_size_formatted TEXT NOT NULL,
		image_width INTEGER NOT NULL DEFAULT '0',
		image_height INTEGER NOT NULL DEFAULT '0',
		thumb TEXT NOT NULL,
		thumb_width INTEGER NOT NULL DEFAULT '0',
		thumb_height INTEGER NOT NULL DEFAULT '0',
		stickied INTEGER NOT NULL DEFAULT '0',
		likes INTEGER NOT NULL DEFAULT '0'
	)");
}

// Create the bans table if it does not exist
$result = $db->query(
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . TINYIB_DBBANS . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBBANS . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		timestamp TIMESTAMP NOT NULL,
		expire TIMESTAMP NOT NULL,
		reason TEXT NOT NULL
	)");
}

// Create the likes table if it does not exist
$result = $db->query(
	"SELECT name FROM sqlite_master" .
	" WHERE type='table' AND name='" . TINYIB_DBLIKES . "'");
if (!$result->fetchArray()) {
	$db->exec("CREATE TABLE " . TINYIB_DBLIKES . " (
		id INTEGER PRIMARY KEY,
		ip TEXT NOT NULL,
		board TEXT NOT NULL,
		postnum INTEGER NOT NULL,
		islike INTEGER NOT NULL DEFAULT '1'
	)");
}

// Add stickied column if it isn't present
@$db->exec(
	"ALTER TABLE " . TINYIB_DBPOSTS .
	" ADD COLUMN stickied INTEGER NOT NULL DEFAULT '0'");

# Post Functions
function uniquePosts() {
	global $db;
	return $db->querySingle("SELECT COUNT(ip) FROM (SELECT DISTINCT ip FROM " . TINYIB_DBPOSTS . ")");
}

function postByID($id) {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBPOSTS .
		" WHERE id = '" . $db->escapeString($id) . "' LIMIT 1");
	while ($post = $result->fetchArray()) {
		return $post;
	}
}

function threadExistsByID($id) {
	global $db;
	return $db->querySingle(
		"SELECT COUNT(*) FROM " . TINYIB_DBPOSTS .
		" WHERE id = '" . $db->escapeString($id) . "' AND parent = 0 LIMIT 1") > 0;
}

function insertPost($post) {
	global $db;
	$db->exec(
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
			file,
			file_hex,
			file_original,
			file_size,
			file_size_formatted,
			image_width,
			image_height,
			thumb,
			thumb_width,
			thumb_height,
			likes
		) VALUES (" .
			$post['parent'] . ", " .
			time() . ", " .
			time() . ", '" .
			$_SERVER['REMOTE_ADDR'] . "', '" .
			$db->escapeString($post['name']) . "', '" .
			$db->escapeString($post['tripcode']) . "', '" .
			$db->escapeString($post['email']) . "', '" .
			$db->escapeString($post['nameblock']) . "', '" .
			$db->escapeString($post['subject']) . "', '" .
			$db->escapeString($post['message']) . "', '" .
			$db->escapeString($post['password']) . "', '" .
			$post['file'] . "', '" .
			$post['file_hex'] . "', '" .
			$db->escapeString($post['file_original']) . "', " .
			$post['file_size'] . ", '" .
			$post['file_size_formatted'] . "', " .
			$post['image_width'] . ", " .
			$post['image_height'] . ", '" .
			$post['thumb'] . "', " .
			$post['thumb_width'] . ", " .
			$post['thumb_height'] . ", " .
			$post['likes'] .
		")");
	return $db->lastInsertRowID();
}

function stickyThreadByID($id, $setsticky) {
	global $db;
	$db->exec(
		"UPDATE " . TINYIB_DBPOSTS .
		" SET stickied = '" . $db->escapeString($setsticky) .
		"' WHERE id = " . $id);
}

function bumpThreadByID($id) {
	global $db;
	$db->exec(
		"UPDATE " . TINYIB_DBPOSTS .
		" SET bumped = " . time() .
		" WHERE id = " . $id);
}

function countThreads() {
	global $db;
	return $db->querySingle(
		"SELECT COUNT(*) FROM " . TINYIB_DBPOSTS .
		" WHERE parent = 0");
}

function allThreads() {
	global $db;
	$threads = array();
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBPOSTS .
		" WHERE parent = 0 ORDER BY stickied DESC, bumped DESC");
	while ($thread = $result->fetchArray()) {
		$threads[] = $thread;
	}
	return $threads;
}

function numRepliesToThreadByID($id) {
	global $db;
	return $db->querySingle(
		"SELECT COUNT(*) FROM " . TINYIB_DBPOSTS .
		" WHERE parent = " . $id);
}

function postsInThreadByID($id, $moderated_only = true) {
	global $db;
	$posts = array();
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBPOSTS .
		" WHERE id = " . $id . " OR parent = " . $id .
		" ORDER BY id ASC");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function postsByHex($hex) {
	global $db;
	$posts = array();
	$result = $db->query(
		"SELECT id, parent FROM " . TINYIB_DBPOSTS .
		" WHERE file_hex = '" . $db->escapeString($hex) . "' LIMIT 1");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function latestPosts($moderated = true) {
	global $db;
	$posts = array();
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBPOSTS .
		" ORDER BY timestamp DESC LIMIT 10");
	while ($post = $result->fetchArray()) {
		$posts[] = $post;
	}
	return $posts;
}

function deletePostByID($id) {
	global $db;
	$posts = postsInThreadByID($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImages($post);
			$db->exec(
				"DELETE FROM " . TINYIB_DBPOSTS .
				" WHERE id = " . $post['id']);
		} else {
			$thispost = $post;
		}
	}
	if (isset($thispost)) {
		if ($thispost['parent'] == TINYIB_NEWTHREAD) {
			@unlink('res/' . $thispost['id'] . '.html');
		}
		deletePostImages($thispost);
		$db->exec(
			"DELETE FROM " . TINYIB_DBPOSTS .
			" WHERE id = " . $thispost['id']);
	}
}

function trimThreads() {
	global $db;
	if (TINYIB_MAXTHREADS > 0) {
		$result = $db->query(
			"SELECT id FROM " . TINYIB_DBPOSTS .
			" WHERE parent = 0" .
			" ORDER BY stickied DESC, bumped DESC LIMIT " . TINYIB_MAXTHREADS . ", 10");
		while ($post = $result->fetchArray()) {
			deletePostByID($post['id']);
		}
	}
}

function lastPostByIP() {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBPOSTS .
		" WHERE ip = '" . $_SERVER['REMOTE_ADDR'] .
		"' ORDER BY id DESC LIMIT 1");
	while ($post = $result->fetchArray()) {
		return $post;
	}
}

function likePostByID($id, $ip) {
	global $db;
	$isAlreadyLiked = $db->querySingle(
		"SELECT COUNT(*) FROM " . TINYIB_DBLIKES .
		" WHERE ip = '" . $ip .
			"' AND board = '" . TINYIB_BOARD .
			"' AND postnum = " . $id);
	if ($isAlreadyLiked) {
		$db->exec(
			"DELETE FROM " . TINYIB_DBLIKES .
			" WHERE ip = '" . $ip .
				"' AND board = '" . TINYIB_BOARD .
				"' AND postnum = " . $id);
	} else {
		$db->exec(
			"INSERT INTO " . TINYIB_DBLIKES .
			" (ip, board, postnum) VALUES ('" .
				$ip . "', '" .
				TINYIB_BOARD . "', " .
				$id . ")");
	}
	$countOfPostLikes = $db->querySingle(
		"SELECT COUNT(*) FROM " . TINYIB_DBLIKES .
		" WHERE board = '" . TINYIB_BOARD .
			"' AND postnum = " . $id);
	$db->exec(
		"UPDATE " . TINYIB_DBPOSTS .
		" SET likes = " . $countOfPostLikes .
		" WHERE id = " . $id);
	return array(!$isAlreadyLiked, $countOfPostLikes);
}

# Ban Functions
function banByID($id) {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBBANS .
		" WHERE id = '" . $db->escapeString($id) . "' LIMIT 1");
	while ($ban = $result->fetchArray()) {
		return $ban;
	}
}

function banByIP($ip) {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBBANS .
		" WHERE ip = '" . $db->escapeString($ip) . "' LIMIT 1");
	while ($ban = $result->fetchArray()) {
		return $ban;
	}
}

function allBans() {
	global $db;
	$bans = array();
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBBANS .
		" ORDER BY timestamp DESC");
	while ($ban = $result->fetchArray()) {
		$bans[] = $ban;
	}
	return $bans;
}

function insertBan($ban) {
	global $db;
	$db->exec(
		"INSERT INTO " . TINYIB_DBBANS .
		" (ip, timestamp, expire, reason)" .
		" VALUES ('" .
			$db->escapeString($ban['ip']) . "', " .
			time() . ", '" .
			$db->escapeString($ban['expire']) . "', '" .
			$db->escapeString($ban['reason']) .
		"')");
	return $db->lastInsertRowID();
}

function clearExpiredBans() {
	global $db;
	$result = $db->query(
		"SELECT * FROM " . TINYIB_DBBANS .
		" WHERE expire > 0 AND expire <= " . time());
	while ($ban = $result->fetchArray()) {
		$db->exec(
			"DELETE FROM " . TINYIB_DBBANS .
			" WHERE id = " . $ban['id']);
	}
}

function deleteBanByID($id) {
	global $db;
	$db->exec(
		"DELETE FROM " . TINYIB_DBBANS .
		" WHERE id = " . $db->escapeString($id));
}
