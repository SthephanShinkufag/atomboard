<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

# Posts database structure
define('POSTS_FILE', '.posts');
define('POST_ID', 0);
define('POST_PARENT', 1);
define('POST_TIMESTAMP', 2);
define('POST_BUMPED', 3);
define('POST_IP', 4);
define('POST_NAME', 5);
define('POST_TRIPCODE', 6);
define('POST_EMAIL', 7);
define('POST_NAMEBLOCK', 8);
define('POST_SUBJECT', 9);
define('POST_MESSAGE', 10);
define('POST_PASSWORD', 11);
define('POST_FILE0', 12);
define('POST_FILE0_HEX', 13);
define('POST_FILE0_ORIGINAL', 14);
define('POST_FILE0_SIZE', 15);
define('POST_FILE0_SIZE_FORMATTED', 16);
define('POST_IMAGE0_WIDTH', 17);
define('POST_IMAGE0_HEIGHT', 18);
define('POST_THUMB0', 19);
define('POST_THUMB0_WIDTH', 20);
define('POST_THUMB0_HEIGHT', 21);
define('POST_FILE1', 22);
define('POST_FILE1_HEX', 23);
define('POST_FILE1_ORIGINAL', 24);
define('POST_FILE1_SIZE', 25);
define('POST_FILE1_SIZE_FORMATTED', 26);
define('POST_IMAGE1_WIDTH', 27);
define('POST_IMAGE1_HEIGHT', 28);
define('POST_THUMB1', 29);
define('POST_THUMB1_WIDTH', 30);
define('POST_THUMB1_HEIGHT', 31);
define('POST_FILE2', 32);
define('POST_FILE2_HEX', 33);
define('POST_FILE2_ORIGINAL', 34);
define('POST_FILE2_SIZE', 35);
define('POST_FILE2_SIZE_FORMATTED', 36);
define('POST_IMAGE2_WIDTH', 37);
define('POST_IMAGE2_HEIGHT', 38);
define('POST_THUMB2', 39);
define('POST_THUMB2_WIDTH', 40);
define('POST_THUMB2_HEIGHT', 41);
define('POST_FILE3', 42);
define('POST_FILE3_HEX', 43);
define('POST_FILE3_ORIGINAL', 44);
define('POST_FILE3_SIZE', 45);
define('POST_FILE3_SIZE_FORMATTED', 46);
define('POST_IMAGE3_WIDTH', 47);
define('POST_IMAGE3_HEIGHT', 48);
define('POST_THUMB3', 49);
define('POST_THUMB3_WIDTH', 50);
define('POST_THUMB3_HEIGHT', 51);
define('POST_LIKES', 52);
define('POST_STICKIED', 53);
define('POST_LOCKED', 54);
define('POST_ENDLESS', 55);
define('POST_PASS', 56);

# Bans database structure
define('BANS_FILE', '.bans');
define('BAN_ID', 0);
define('BAN_IP_FROM', 1);
define('BAN_TIMESTAMP', 2);
define('BAN_EXPIRE', 3);
define('BAN_REASON', 4);
define('BAN_IP_TO', 5);

# IP lookups database structure
define('IPLOOKUP_FILE', '.iplookup');
define('IPLOOKUP_ID', 0);
define('IPLOOKUP_IP', 1);
define('IPLOOKUP_ABUSER', 2);
define('IPLOOKUP_VPS', 3);
define('IPLOOKUP_PROXY', 4);
define('IPLOOKUP_TOR', 5);
define('IPLOOKUP_VPN', 6);

# Likes database structure
define('LIKES_FILE', '.likes');
define('LIKES_ID', 0);
define('LIKES_IP', 1);
define('LIKES_BOARD', 2);
define('LIKES_POSTNUM', 3);
define('LIKES_ISLIKE', 4);

# Modlog database structure
define('MODLOG_FILE', '.modlog');
define('MODLOG_ID', 0);
define('MODLOG_TIMESTAMP', 1);
define('MODLOG_BOARDNAME', 2);
define('MODLOG_USERNAME', 3);
define('MODLOG_ACTION', 4);
define('MODLOG_COLOR', 5);
define('MODLOG_PRIVATE', 6);

require_once 'flatfile/flatfile.php';
$db = new Flatfile();
$db->datadir = 'inc/flatfile/';

/* ==[ Posts ]============================================================================================= */

function convertPostsToSQLStyle($posts, $isSinglePost = false) {
	$newposts = [];
	foreach ($posts as $oldpost) {
		$post = newPost(ATOM_NEWTHREAD);
		$post['id']                   = $oldpost[POST_ID];
		$post['parent']               = $oldpost[POST_PARENT];
		$post['timestamp']            = $oldpost[POST_TIMESTAMP];
		$post['bumped']               = $oldpost[POST_BUMPED];
		$post['ip']                   = $oldpost[POST_IP];
		$post['pass']                 = $oldpost[POST_PASS];
		$post['name']                 = $oldpost[POST_NAME];
		$post['tripcode']             = $oldpost[POST_TRIPCODE];
		$post['email']                = $oldpost[POST_EMAIL];
		$post['nameblock']            = $oldpost[POST_NAMEBLOCK];
		$post['subject']              = $oldpost[POST_SUBJECT];
		$post['message']              = $oldpost[POST_MESSAGE];
		$post['password']             = $oldpost[POST_PASSWORD];
		$post['file0']                = $oldpost[POST_FILE0];
		$post['file0_hex']            = $oldpost[POST_FILE0_HEX];
		$post['file0_original']       = $oldpost[POST_FILE0_ORIGINAL];
		$post['file0_size']           = $oldpost[POST_FILE0_SIZE];
		$post['file0_size_formatted'] = $oldpost[POST_FILE0_SIZE_FORMATTED];
		$post['image0_width']         = $oldpost[POST_IMAGE0_WIDTH];
		$post['image0_height']        = $oldpost[POST_IMAGE0_HEIGHT];
		$post['thumb0']               = $oldpost[POST_THUMB0];
		$post['thumb0_width']         = $oldpost[POST_THUMB0_WIDTH];
		$post['thumb0_height']        = $oldpost[POST_THUMB0_HEIGHT];
		$post['file1']                = $oldpost[POST_FILE1];
		$post['file1_hex']            = $oldpost[POST_FILE1_HEX];
		$post['file1_original']       = $oldpost[POST_FILE1_ORIGINAL];
		$post['file1_size']           = $oldpost[POST_FILE1_SIZE];
		$post['file1_size_formatted'] = $oldpost[POST_FILE1_SIZE_FORMATTED];
		$post['image1_width']         = $oldpost[POST_IMAGE1_WIDTH];
		$post['image1_height']        = $oldpost[POST_IMAGE1_HEIGHT];
		$post['thumb1']               = $oldpost[POST_THUMB1];
		$post['thumb1_width']         = $oldpost[POST_THUMB1_WIDTH];
		$post['thumb1_height']        = $oldpost[POST_THUMB1_HEIGHT];
		$post['file2']                = $oldpost[POST_FILE2];
		$post['file2_hex']            = $oldpost[POST_FILE2_HEX];
		$post['file2_original']       = $oldpost[POST_FILE2_ORIGINAL];
		$post['file2_size']           = $oldpost[POST_FILE2_SIZE];
		$post['file2_size_formatted'] = $oldpost[POST_FILE2_SIZE_FORMATTED];
		$post['image2_width']         = $oldpost[POST_IMAGE2_WIDTH];
		$post['image2_height']        = $oldpost[POST_IMAGE2_HEIGHT];
		$post['thumb2']               = $oldpost[POST_THUMB2];
		$post['thumb2_width']         = $oldpost[POST_THUMB2_WIDTH];
		$post['thumb2_height']        = $oldpost[POST_THUMB2_HEIGHT];
		$post['file3']                = $oldpost[POST_FILE3];
		$post['file3_hex']            = $oldpost[POST_FILE3_HEX];
		$post['file3_original']       = $oldpost[POST_FILE3_ORIGINAL];
		$post['file3_size']           = $oldpost[POST_FILE3_SIZE];
		$post['file3_size_formatted'] = $oldpost[POST_FILE3_SIZE_FORMATTED];
		$post['image3_width']         = $oldpost[POST_IMAGE3_WIDTH];
		$post['image3_height']        = $oldpost[POST_IMAGE3_HEIGHT];
		$post['thumb3']               = $oldpost[POST_THUMB3];
		$post['thumb3_width']         = $oldpost[POST_THUMB3_WIDTH];
		$post['thumb3_height']        = $oldpost[POST_THUMB3_HEIGHT];
		$post['likes']                = $oldpost[POST_LIKES];
		$post['stickied']             = $oldpost[POST_STICKIED];
		$post['locked']               = $oldpost[POST_LOCKED];
		$post['endless']              = $oldpost[POST_ENDLESS];
		if ($post['parent'] == '') {
			$post['parent'] = ATOM_NEWTHREAD;
		}
		if ($isSinglePost) {
			return $post;
		}
		$newposts[] = $post;
	}
	return $newposts;
}

function insertPost($newpost) {
	$post = [];
	$post[POST_ID]                   = '0';
	$post[POST_PARENT]               = $newpost['parent'];
	$post[POST_TIMESTAMP]            = time();
	$post[POST_BUMPED]               = time();
	$post[POST_IP]                   = $newpost['ip'];
	$post[POST_NAME]                 = $newpost['name'];
	$post[POST_TRIPCODE]             = $newpost['tripcode'];
	$post[POST_EMAIL]                = $newpost['email'];
	$post[POST_NAMEBLOCK]            = $newpost['nameblock'];
	$post[POST_SUBJECT]              = $newpost['subject'];
	$post[POST_MESSAGE]              = $newpost['message'];
	$post[POST_PASSWORD]             = $newpost['password'];
	$post[POST_FILE0]                = $newpost['file0'];
	$post[POST_FILE0_HEX]            = $newpost['file0_hex'];
	$post[POST_FILE0_ORIGINAL]       = $newpost['file0_original'];
	$post[POST_FILE0_SIZE]           = $newpost['file0_size'];
	$post[POST_FILE0_SIZE_FORMATTED] = $newpost['file0_size_formatted'];
	$post[POST_IMAGE0_WIDTH]         = $newpost['image0_width'];
	$post[POST_IMAGE0_HEIGHT]        = $newpost['image0_height'];
	$post[POST_THUMB0]               = $newpost['thumb0'];
	$post[POST_THUMB0_WIDTH]         = $newpost['thumb0_width'];
	$post[POST_THUMB0_HEIGHT]        = $newpost['thumb0_height'];
	$post[POST_FILE1]                = $newpost['file1'];
	$post[POST_FILE1_HEX]            = $newpost['file1_hex'];
	$post[POST_FILE1_ORIGINAL]       = $newpost['file1_original'];
	$post[POST_FILE1_SIZE]           = $newpost['file1_size'];
	$post[POST_FILE1_SIZE_FORMATTED] = $newpost['file1_size_formatted'];
	$post[POST_IMAGE1_WIDTH]         = $newpost['image1_width'];
	$post[POST_IMAGE1_HEIGHT]        = $newpost['image1_height'];
	$post[POST_THUMB1]               = $newpost['thumb1'];
	$post[POST_THUMB1_WIDTH]         = $newpost['thumb1_width'];
	$post[POST_THUMB1_HEIGHT]        = $newpost['thumb1_height'];
	$post[POST_FILE2]                = $newpost['file2'];
	$post[POST_FILE2_HEX]            = $newpost['file2_hex'];
	$post[POST_FILE2_ORIGINAL]       = $newpost['file2_original'];
	$post[POST_FILE2_SIZE]           = $newpost['file2_size'];
	$post[POST_FILE2_SIZE_FORMATTED] = $newpost['file2_size_formatted'];
	$post[POST_IMAGE2_WIDTH]         = $newpost['image2_width'];
	$post[POST_IMAGE2_HEIGHT]        = $newpost['image2_height'];
	$post[POST_THUMB2]               = $newpost['thumb2'];
	$post[POST_THUMB2_WIDTH]         = $newpost['thumb2_width'];
	$post[POST_THUMB2_HEIGHT]        = $newpost['thumb2_height'];
	$post[POST_FILE3]                = $newpost['file3'];
	$post[POST_FILE3_HEX]            = $newpost['file3_hex'];
	$post[POST_FILE3_ORIGINAL]       = $newpost['file3_original'];
	$post[POST_FILE3_SIZE]           = $newpost['file3_size'];
	$post[POST_FILE3_SIZE_FORMATTED] = $newpost['file3_size_formatted'];
	$post[POST_IMAGE3_WIDTH]         = $newpost['image3_width'];
	$post[POST_IMAGE3_HEIGHT]        = $newpost['image3_height'];
	$post[POST_THUMB3]               = $newpost['thumb3'];
	$post[POST_THUMB3_WIDTH]         = $newpost['thumb3_width'];
	$post[POST_THUMB3_HEIGHT]        = $newpost['thumb3_height'];
	$post[POST_LIKES]                = $newpost['likes'];
	$post[POST_STICKIED]             = $newpost['stickied'];
	$post[POST_LOCKED]               = $newpost['locked'];
	$post[POST_ENDLESS]              = $newpost['endless'];
	return $GLOBALS['db']->insertWithAutoId(POSTS_FILE, POST_ID, $post);
}

function getPostEntry($id) {
	return $GLOBALS['db']->selectWhere(
		POSTS_FILE,
		new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON),
		1);
}

function getPost($id) {
	return convertPostsToSQLStyle(getPostEntry($id), true);
}

function getPostsByIP($ip) {
	return convertPostsToSQLStyle($GLOBALS['db']->selectWhere(
		POSTS_FILE,
		new SimpleWhereClause(POST_IP, '=', $ip, STRING_COMPARISON),
		-1,
		new OrderBy(POST_TIMESTAMP, DESCENDING, INTEGER_COMPARISON)));
}

function getPostsByImageHex($hex) {
	$compClause = new OrWhereClause();
	$compClause->add(new SimpleWhereClause(POST_FILE0_HEX, '=', $hex, STRING_COMPARISON));
	$compClause->add(new SimpleWhereClause(POST_FILE1_HEX, '=', $hex, STRING_COMPARISON));
	$compClause->add(new SimpleWhereClause(POST_FILE2_HEX, '=', $hex, STRING_COMPARISON));
	$compClause->add(new SimpleWhereClause(POST_FILE3_HEX, '=', $hex, STRING_COMPARISON));
	return convertPostsToSQLStyle($GLOBALS['db']->selectWhere(POSTS_FILE, $compClause, 1));
}

function getLatestPosts($moderated, $count) {
	return convertPostsToSQLStyle($GLOBALS['db']->selectWhere(
		POSTS_FILE,
		NULL,
		$count,
		new OrderBy(POST_TIMESTAMP, DESCENDING, INTEGER_COMPARISON)));
}

function getLastPostByIP() {
	return convertPostsToSQLStyle($GLOBALS['db']->selectWhere(
		POSTS_FILE,
		new SimpleWhereClause(POST_IP, '=', $_SERVER['REMOTE_ADDR'], STRING_COMPARISON),
		1,
		new OrderBy(POST_ID, DESCENDING, INTEGER_COMPARISON)
	), true);
}

function getUniquePostersCount() {
	return 0; // Unsupported by this database option
}

function deletePost($id) {
	$posts = getThreadPosts($id, false);
	foreach ($posts as $post) {
		if ($post['id'] != $id) {
			deletePostImagesFiles($post);
			$GLOBALS['db']->deleteWhere(
				POSTS_FILE,
				new SimpleWhereClause(POST_ID, '=', $post['id'], INTEGER_COMPARISON));
		} else {
			$thispost = $post;
		}
	}
	if (!isset($thispost)) {
		return;
	}
	if ($thispost['parent'] == 0) {
		@unlink('res/' . $thispost['id'] . '.html');
	}
	deletePostImagesFiles($thispost);
	$GLOBALS['db']->deleteWhere(
		POSTS_FILE,
		new SimpleWhereClause(POST_ID, '=', $thispost['id'], INTEGER_COMPARISON));
}

function deletePostImages($post, $imgList) {
	deletePostImagesFiles($post, $imgList);
	if (!$imgList || count($imgList) > ATOM_FILES_COUNT) {
		return;
	}
	foreach ($imgList as $arrayIndex => $index) {
		$idx10 = intval(trim(basename($index))) * 10;
		$posts = $GLOBALS['db']->selectWhere(
			POSTS_FILE,
			new SimpleWhereClause(POST_ID, '=', $post['id'], INTEGER_COMPARISON),
			1);
		if (count($posts) == 0) {
			continue;
		}
		foreach ($posts as $post_) {
			$post_[POST_FILE0 + $idx10] = '';
			$post_[POST_FILE0_HEX + $idx10] = '';
			$post_[POST_FILE0_ORIGINAL + $idx10] = '';
			$post_[POST_FILE0_SIZE + $idx10] = '0';
			$post_[POST_FILE0_SIZE_FORMATTED + $idx10] = '';
			$post_[POST_IMAGE0_WIDTH + $idx10] = '0';
			$post_[POST_IMAGE0_HEIGHT + $idx10] = '0';
			$post_[POST_THUMB0 + $idx10] = '';
			$post_[POST_THUMB0_WIDTH + $idx10] = '0';
			$post_[POST_THUMB0_HEIGHT + $idx10] = '0';
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post_);
		}
	}
}

function hidePostImages($post, $imgList) {
	deletePostImagesFilesThumbFiles($post, $imgList);
	if (!$imgList || count($imgList) > ATOM_FILES_COUNT) {
		return;
	}
	foreach ($imgList as $arrayIndex => $index) {
		$idx10 = intval(trim(basename($index))) * 10;
		$posts = $GLOBALS['db']->selectWhere(
			POSTS_FILE,
			new SimpleWhereClause(POST_ID, '=', $post['id'], INTEGER_COMPARISON),
			1);
		if (count($posts) == 0) {
			continue;
		}
		foreach ($posts as $post_) {
			$post_[POST_THUMB0 + $idx10] = 'spoiler.png';
			$post_[POST_THUMB0_WIDTH + $idx10] = ATOM_FILE_MAXW;
			$post_[POST_THUMB0_HEIGHT + $idx10] = ATOM_FILE_MAXW;
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post_);
		}
	}
}

function editPostMessage($id, $newMessage) {
	$posts = getPostEntry($id);
	if (count($posts) > 0) {
		foreach ($posts as $post) {
			$post[POST_MESSAGE] = $newMessage;
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
}

/* ==[ Threads ]=========================================================================================== */

function isThreadExists($id) {
	$compClause = new AndWhereClause();
	$compClause->add(new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON));
	$compClause->add(new SimpleWhereClause(POST_PARENT, '=', 0, INTEGER_COMPARISON));
	return count($GLOBALS['db']->selectWhere(POSTS_FILE, $compClause, 1)) > 0;
}

function getThreads() {
	return convertPostsToSQLStyle($GLOBALS['db']->selectWhere(
		POSTS_FILE,
		new SimpleWhereClause(POST_PARENT, '=', 0, INTEGER_COMPARISON),
		-1,
		[
			new OrderBy(POST_STICKIED, DESCENDING, INTEGER_COMPARISON),
			new OrderBy(POST_BUMPED, DESCENDING, INTEGER_COMPARISON)]));
}

function getThreadsCount() {
	return count($GLOBALS['db']->selectWhere(
		POSTS_FILE,
		new SimpleWhereClause(POST_PARENT, '=', 0, INTEGER_COMPARISON)));
}

function trimThreadsCount() {
	if (ATOM_MAXTHREADS > 0) {
		$numThreads = getThreadsCount();
		if ($numThreads > ATOM_MAXTHREADS) {
			$getThreads = getThreads();
			for ($i = ATOM_MAXTHREADS; $i < $numThreads; $i++) {
				deletePost($getThreads[$i]['id']);
			}
		}
	}
}

function getThreadPosts($id, $moderatedOnly = true) {
	$compClause = new OrWhereClause();
	$compClause->add(new SimpleWhereClause(POST_ID, '=', $id, INTEGER_COMPARISON));
	$compClause->add(new SimpleWhereClause(POST_PARENT, '=', $id, INTEGER_COMPARISON));
	return convertPostsToSQLStyle($GLOBALS['db']->selectWhere(
		POSTS_FILE,
		$compClause,
		-1,
		new OrderBy(POST_ID, ASCENDING, INTEGER_COMPARISON)));
}

function getThreadPostsCount($id) {
	return count($GLOBALS['db']->selectWhere(
		POSTS_FILE,
		new SimpleWhereClause(POST_PARENT, '=', $id, INTEGER_COMPARISON)));
}

function toggleStickyThread($id, $isStickied) {
	$posts = getPostEntry($id);
	if (count($posts) > 0) {
		foreach ($posts as $post) {
			$post[POST_STICKIED] = $isStickied;
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
}

function toggleLockThread($id, $isLocked) {
	$posts = getPostEntry($id);
	if (count($posts) > 0) {
		foreach ($posts as $post) {
			$post[POST_LOCKED] = $isLocked;
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
}

function toggleEndlessThread($id, $isEndless) {
	$posts = getPostEntry($id);
	if (count($posts) > 0) {
		foreach ($posts as $post) {
			$post[POST_ENDLESS] = $isEndless;
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
}

function bumpThread($id) {
	$posts = getPostEntry($id);
	if (count($posts) > 0) {
		foreach ($posts as $post) {
			$post[POST_BUMPED] = time();
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
}

/* ==[ Bans ]============================================================================================== */

function convertBansToSQLStyle($bans, $isSingleBan = false) {
	$newbans = [];
	foreach ($bans as $oldban) {
		$ban = [
			'id' => $oldban[BAN_ID],
			'ip_from' => $oldban[BAN_IP_FROM],
			'ip_to' => $oldban[BAN_IP_TO],
			'timestamp' => $oldban[BAN_TIMESTAMP],
			'expire' => $oldban[BAN_EXPIRE],
			'reason' => $oldban[BAN_REASON]];
		if ($isSingleBan) {
			return $ban;
		}
		$newbans[] = $ban;
	}
	return $newbans;
}

function banByID($id) {
	return convertBansToSQLStyle($GLOBALS['db']->selectWhere(
		BANS_FILE,
		new SimpleWhereClause(BAN_ID, '=', $id, INTEGER_COMPARISON),
		1
	), true);
}

function banByIP($ip) {
	return convertBansToSQLStyle($GLOBALS['db']->selectWhere(
		BANS_FILE,
		new SimpleWhereClause(BAN_IP_FROM, '=', ip2long($ip), STRING_COMPARISON),
		1
	), true);
}

function getAllBans() {
	return convertBansToSQLStyle($GLOBALS['db']->selectWhere(
		BANS_FILE,
		NULL,
		-1,
		new OrderBy(BAN_TIMESTAMP, DESCENDING, INTEGER_COMPARISON)));
}

function insertBan($ban) {
	$rangeFrom = cidr2ip($ban['ip'])[0];
	$newban = [];
	$newban[BAN_ID] = '0';
	// Only single ip ban is supported
	$newban[BAN_IP_FROM] = $rangeFrom;
	$newban[BAN_IP_TO] = $rangeFrom;
	$newban[BAN_TIMESTAMP] = time();
	$newban[BAN_EXPIRE] = $ban['expire'];
	$newban[BAN_REASON] = $ban['reason'];
	return $GLOBALS['db']->insertWithAutoId(BANS_FILE, BAN_ID, $newban);
}

function deleteBan($id) {
	$GLOBALS['db']->deleteWhere(BANS_FILE, new SimpleWhereClause(BAN_ID, '=', $id, INTEGER_COMPARISON));
}

function clearExpiredBans() {
	$compClause = new AndWhereClause();
	$compClause->add(new SimpleWhereClause(BAN_EXPIRE, '>', 1, INTEGER_COMPARISON));
	$compClause->add(new SimpleWhereClause(BAN_EXPIRE, '<=', time(), INTEGER_COMPARISON));
	$bans = $GLOBALS['db']->selectWhere(BANS_FILE, $compClause, -1);
	foreach ($bans as $ban) {
		deleteBan($ban[BAN_ID]);
	}
}

/* ==[ Dirty IP lookups ]================================================================================== */

function lookupByIP($ip) {
	return convertPostsToSQLStyle($GLOBALS['db']->selectWhere(
		IPLOOKUP_FILE,
		new SimpleWhereClause(IPLOOKUP_IP, '=', $ip, STRING_COMPARISON)));
}

function storeLookupResult($ip, $abuser, $vps, $proxy, $tor, $vpn) {
	$lookup = [];
	$lookup[IPLOOKUP_IP] = $ip;
	$lookup[IPLOOKUP_ABUSER] = $abuser;
	$lookup[IPLOOKUP_VPS] = $vps;
	$lookup[IPLOOKUP_PROXY] = $proxy;
	$lookup[IPLOOKUP_TOR] = $tor;
	$lookup[IPLOOKUP_VPN] = $vpn;
	$GLOBALS['db']->insertWithAutoId(IPLOOKUP_FILE, IPLOOKUP_ID, $lookup);
}

/* ==[ Likes ]============================================================================================= */

function convertLikesToSQLStyle($likes) {
	$newlikes = [];
	foreach ($likes as $oldlike) {
		$newlikes[] = [
			'id' => $oldlike[LIKES_ID],
			'ip' => $oldlike[LIKES_IP],
			'board' => $oldlike[LIKES_BOARD],
			'postnum' => $oldlike[LIKES_POSTNUM],
			'islike' => $oldlike[LIKES_ISLIKE]];
	}
	return $newlikes;
}

function getAllLikes() {
	return convertLikesToSQLStyle($GLOBALS['db']->selectWhere(
		LIKES_FILE,
		NULL,
		-1,
		new OrderBy(LIKES_ID, ASCENDING, INTEGER_COMPARISON)));
}

function toggleLike($id, $ip) {
	$compClause = new AndWhereClause();
	$compClause->add(new SimpleWhereClause(LIKES_IP, '=', $ip, STRING_COMPARISON));
	$compClause->add(new SimpleWhereClause(LIKES_BOARD, '=', ATOM_BOARD, STRING_COMPARISON));
	$compClause->add(new SimpleWhereClause(LIKES_POSTNUM, '=', $id, INTEGER_COMPARISON));
	$isAlreadyLiked = count($GLOBALS['db']->selectWhere(LIKES_FILE, $compClause));
	if ($isAlreadyLiked) {
		$GLOBALS['db']->deleteWhere(LIKES_FILE, $compClause);
	} else {
		$like = [];
		$like[LIKES_ID] = '0';
		$like[LIKES_IP] = $ip;
		$like[LIKES_BOARD] = ATOM_BOARD;
		$like[LIKES_POSTNUM] = $id;
		$like[LIKES_ISLIKE] = '1';
		$GLOBALS['db']->insertWithAutoId(LIKES_FILE, LIKES_ID, $like);
	}
	$compClause = new AndWhereClause();
	$compClause->add(new SimpleWhereClause(LIKES_BOARD, '=', ATOM_BOARD, STRING_COMPARISON));
	$compClause->add(new SimpleWhereClause(LIKES_POSTNUM, '=', $id, INTEGER_COMPARISON));
	$countOfPostLikes = count($GLOBALS['db']->selectWhere(LIKES_FILE, $compClause));
	$posts = getPostEntry($id);
	if (count($posts) > 0) {
		foreach ($posts as $post) {
			$post[POST_LIKES] = $countOfPostLikes;
			$GLOBALS['db']->updateRowById(POSTS_FILE, POST_ID, $post);
		}
	}
	return [!$isAlreadyLiked, $countOfPostLikes];
}

/* ==[ Modlog ]============================================================================================ */

function getModLogRecords($private = '0', $periodEndDate = 0, $periodStartDate = 0) {
	$records = [];
	$rows = [];
	// If we need a modlog for the admin panel with all public+private records
	if ($private === '1') {
		if ($periodEndDate === 0 || $periodStartDate === 0) { // If the date range is not set
			$rows = $GLOBALS['db']->selectWhere(
				MODLOG_FILE,
				new SimpleWhereClause(MODLOG_BOARDNAME, '=', ATOM_BOARD, STRING_COMPARISON),
				100,
				new OrderBy(MODLOG_TIMESTAMP, DESCENDING, INTEGER_COMPARISON));
			foreach ($rows as $row) {
				$records[] = [
					'timestamp' => $row[MODLOG_TIMESTAMP],
					'username' => $row[MODLOG_USERNAME],
					'action' => $row[MODLOG_ACTION],
					'color' => $row[MODLOG_COLOR]];
			}
		} elseif ($periodEndDate !== 0 && $periodStartDate !== 0) { // If the date range is set
			$compClause = new AndWhereClause();
			$compClause->add(new SimpleWhereClause(MODLOG_BOARDNAME, '=', ATOM_BOARD, STRING_COMPARISON));
			$compClause->add(new SimpleWhereClause(MODLOG_TIMESTAMP, '>=', $periodStartDate,
				INTEGER_COMPARISON));
			$compClause->add(new SimpleWhereClause(MODLOG_TIMESTAMP, '<=', $periodEndDate,
				INTEGER_COMPARISON));
			$rows = $GLOBALS['db']->selectWhere(
				MODLOG_FILE,
				$compClause,
				-1,
				new OrderBy(MODLOG_TIMESTAMP, DESCENDING, INTEGER_COMPARISON));
			foreach ($rows as $row) {
				$records[] = [
					'timestamp' => $row[MODLOG_TIMESTAMP],
					'username' => $row[MODLOG_USERNAME],
					'action' => $row[MODLOG_ACTION],
					'color' => $row[MODLOG_COLOR]];
			}
		}
	// If we need only public records
	} elseif ($private === '0') {
		$compClause = new AndWhereClause();
		$compClause->add(new SimpleWhereClause(MODLOG_BOARDNAME, '=', ATOM_BOARD, STRING_COMPARISON));
		$compClause->add(new SimpleWhereClause(MODLOG_PRIVATE, '=', '0', INTEGER_COMPARISON));
		$rows = $GLOBALS['db']->selectWhere(
			MODLOG_FILE,
			$compClause,
			100,
			new OrderBy(MODLOG_TIMESTAMP, DESCENDING, INTEGER_COMPARISON));
		foreach ($rows as $row) {
			$records[] = [
				'timestamp' => $row[MODLOG_TIMESTAMP],
				'action' => $row[MODLOG_ACTION]];
		}
	}
	return $records;
}

function modLog($action, $private = '1', $color = 'Black') {
	// modLog('Text to show in modlog', '[1, 0]', 'Color');
	// '[1, 0]': 1 = Private record. 0 = Public record.
	// 'Color': Choose what to put in style="color: " for this record
	$row = [];
	$row[MODLOG_ID] = '0';
	$row[MODLOG_TIMESTAMP] = time();
	$row[MODLOG_BOARDNAME] = ATOM_BOARD;
	$row[MODLOG_USERNAME] = isset($_SESSION['atom_user']) ? $_SESSION['atom_user'] : 'UNKNOWN';
	$row[MODLOG_ACTION] = $action;
	$row[MODLOG_COLOR] = $color;
	$row[MODLOG_PRIVATE] = $private;
	return $GLOBALS['db']->insertWithAutoId(MODLOG_FILE, MODLOG_ID, $row);
}
