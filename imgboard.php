<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
setcookie(session_name(), session_id(), time() + 2592000);
ob_implicit_flush();
if (function_exists('ob_get_level')) {
	while (ob_get_level() > 0) {
		ob_end_flush();
	}
}

/* ==[ Utils ]============================================================================================= */

function fancyDie($message) {
	die('<head>
	<link rel="stylesheet" type="text/css" href="/' . ATOM_BOARD . '/css/atomboard.css?2023081500">
</head>
<body align="center">
	<br>
	<div class="reply" style="display: inline-block; font-size: 1.25em;">' . $message . '</div>
	<br><br>
	- <a href="./">Click here to go back</a> -
</body>');
}

/* ==[ Administration requests ]=========================================================================== */

function managementRequest() {
	global $access;
	$isAdmin = $access == 'admin';

	/* --------[ Show login form ]-------- */

	if ($access == 'disabled') {
		die(managePage(manageLoginForm(), 'login'));
	}

	/* --------[ Rebuild all posts ]-------- */

	if (isset($_GET['rebuildall']) && $isAdmin) {
		$getThreads = getThreads();
		foreach ($getThreads as $thread) {
			rebuildThreadPage($thread['id']);
		}
		rebuildIndexPages();
		die(managePage(manageInfo('Rebuilt board.')));
	}

	/* --------[ Update board ]-------- */

	if (isset($_GET['update']) && $isAdmin) {
		if (is_dir('.git')) {
			die(managePage('<blockquote class="reply" style="padding: 7px;font-size: 1.25em;">
	<pre style="margin: 0;padding: 0;">Attempting update...' . "\n\n" . shell_exec('git pull 2>&1') . '</pre>
</blockquote>
<p><b>Note:</b> If atomboard updates and you have made custom modifications,
	<a href="https://github.com/SthephanShinkufag/atomboard/commits/master" target="_blank">
		review the changes</a> which have been merged into your installation.<br>
	Ensure that your modifications do not interfere with any new/modified files.<br>
	See the <a href="https://github.com/SthephanShinkufag/atomboard#readme">README</a> for more information.
</p>'));
		}
		die(managePage('<p><b>atomboard was not installed via Git!</b></p>
<p>If you installed atomboard without Git, you must
	<a href="https://github.com/SthephanShinkufag/atomboard/#updating">update manually</a><br>
	If you did install with Git, ensure the script has read and write access to the <b>.git</b> folder.
</p>'));
	}

	/* --------[ Flatfile to MySQLi migration ]-------- */

	if (isset($_GET['dbmigrate']) && $isAdmin) {
		if (!ATOM_DBMIGRATE) {
			fancyDie('settings.php: Set ATOM_DBMIGRATE to true to use this feature.');
		}
		if (!isset($_GET['go'])) {
			die(managePage('<p>
	This tool currently only supports migration from a flat file database to MySQL.<br>
	Your original database will not be deleted.<br>
	If the migration fails, disable the tool and your board will be unaffected.<br>
	See the <a href="https://github.com/SthephanShinkufag/atomboard#migrating" target="_blank">README</a>
	<small>(<a href="README.md" target="_blank">alternate link</a>)</small> for instructions.<br><br>
	<a href="?manage&dbmigrate&go"><b>Start the migration</b></a>
</p>'));
		}
		if (ATOM_DBMODE != 'flatfile') {
			fancyDie('settings.php: Set ATOM_DBMODE to "flatfile" and enter your MySQL' .
				' settings before migrating.');
		}
		if (!function_exists('mysqli_connect')) {
			fancyDie('Please install the <a href="http://php.net/manual/en/book.mysqli.php">' .
				'MySQLi extension</a> and try again.');
		}
		$link = @mysqli_connect(ATOM_DBHOST, ATOM_DBUSERNAME, ATOM_DBPASSWORD);
		if (!$link) {
			fancyDie('Could not connect to database: ' . (
				is_object($link) ? mysqli_error($link) :
				(($linkError = mysqli_connect_error()) ? $linkError : '(unknown error)')
			));
		}
		if (!@mysqli_query($link, 'USE ' . constant('ATOM_DBNAME'))) {
			fancyDie('Could not select database: ' . (
				is_object($link) ? mysqli_error($link) :
				(($linkError = mysqli_connect_error()) ? $linkError : '(unknown error)')
			));
		}
		mysqli_set_charset($link, 'utf8mb4');
		if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBPOSTS . "'")) != 0) {
			fancyDie('Posts table (' . ATOM_DBPOSTS . ') already exists!<br>' .
				'Please DROP this table and try again.');
		}
		if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBBANS . "'")) != 0) {
			fancyDie('Bans table (' . ATOM_DBBANS . ') already exists!<br>' .
				'Please DROP this table and try again.');
		}
		if (mysqli_num_rows(mysqli_query($link, "SHOW TABLES LIKE '" . ATOM_DBLIKES . "'")) != 0) {
			fancyDie('Likes table (' . ATOM_DBLIKES . ') already exists!<br>' .
				'Please DROP this table and try again.');
		}
		mysqli_query($link, $postsQuery);
		mysqli_query($link, $bansQuery);
		mysqli_query($link, $likesQuery);
		$maxId = 0;
		$threads = getThreads();
		foreach ($threads as $thread) {
			$posts = getThreadPosts($thread['id']);
			foreach ($posts as $post) {
				mysqli_query($link, 'INSERT INTO `' . ATOM_DBPOSTS . '` (
					`id`,
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
					`stickied`,
					`likes`
				) VALUES (' .
					$post['id'] . ', ' .
					$post['parent'] . ', ' .
					time() . ', ' .
					time() . ", '" .
					$_SERVER['REMOTE_ADDR'] . "', '" .
					mysqli_real_escape_string($link, $post['name']) . "', '" .
					mysqli_real_escape_string($link, $post['tripcode']) . "', '" .
					mysqli_real_escape_string($link, $post['email']) . "', '" .
					mysqli_real_escape_string($link, $post['nameblock']) . "', '" .
					mysqli_real_escape_string($link, $post['subject']) . "', '" .
					mysqli_real_escape_string($link, $post['message']) . "', '" .
					mysqli_real_escape_string($link, $post['password']) . "', '" .
					$post['file0'] . "', '" .
					$post['file0_hex'] . "', '" .
					mysqli_real_escape_string($link, $post['file0_original']) . "', " .
					$post['file0_size'] . ", '" .
					$post['file0_size_formatted'] . "', " .
					$post['image0_width'] . ", " .
					$post['image0_height'] . ", '" .
					$post['thumb0'] . "', " .
					$post['thumb0_width'] . ', ' .
					$post['thumb0_height'] . ", '" .
					$post['file1'] . "', '" .
					$post['file1_hex'] . "', '" .
					mysqli_real_escape_string($link, $post['file1_original']) . "', " .
					$post['file1_size'] . ", '" .
					$post['file1_size_formatted'] . "', " .
					$post['image1_width'] . ", " .
					$post['image1_height'] . ", '" .
					$post['thumb1'] . "', " .
					$post['thumb1_width'] . ', ' .
					$post['thumb1_height'] . ", '" .
					$post['file2'] . "', '" .
					$post['file2_hex'] . "', '" .
					mysqli_real_escape_string($link, $post['file2_original']) . "', " .
					$post['file2_size'] . ", '" .
					$post['file2_size_formatted'] . "', " .
					$post['image2_width'] . ", " .
					$post['image2_height'] . ", '" .
					$post['thumb2'] . "', " .
					$post['thumb2_width'] . ', ' .
					$post['thumb2_height'] . ", '" .
					$post['file3'] . "', '" .
					$post['file3_hex'] . "', '" .
					mysqli_real_escape_string($link, $post['file3_original']) . "', " .
					$post['file3_size'] . ", '" .
					$post['file3_size_formatted'] . "', " .
					$post['image3_width'] . ", " .
					$post['image3_height'] . ", '" .
					$post['thumb3'] . "', " .
					$post['thumb3_width'] . ', ' .
					$post['thumb3_height'] . ', ' .
					$post['stickied'] . ', ' .
					$post['likes'] .
				')');
				$maxId = max($maxId, $post['id']);
			}
		}
		if ($maxId > 0 && !mysqli_query($link,
			'ALTER TABLE `' . ATOM_DBPOSTS . '` AUTO_INCREMENT = ' . ($maxId + 1))
		) {
			die(managePage('<p><b>Warning!</b></p>' .
				'<p>Unable to update the <code>AUTO_INCREMENT</code> value for table <code>' .
				ATOM_DBPOSTS . '</code>, please set it to ' . ($maxId + 1) . '.</p>'));
		}
		$maxId = 0;
		$bans = getAllBans();
		foreach ($bans as $ban) {
			$maxId = max($maxId, $ban['id']);
			mysqli_query($link,
				'INSERT INTO `' . ATOM_DBBANS . "` (
					`id`,
					`ip`,
					`timestamp`,
					`expire`,
					`reason`
				) VALUES ('" .
					mysqli_real_escape_string($link, $ban['id']) . "', '" .
					mysqli_real_escape_string($link, $ban['ip']) . "', '" .
					mysqli_real_escape_string($link, $ban['timestamp']) . "', '" .
					mysqli_real_escape_string($link, $ban['expire']) . "', '" .
					mysqli_real_escape_string($link, $ban['reason']) . "')");
		}
		if ($maxId > 0 && !mysqli_query($link,
			'ALTER TABLE `' . ATOM_DBBANS . '` AUTO_INCREMENT = ' . ($maxId + 1))
		) {
			die(managePage('<p><b>Warning!</b></p>' .
				'<p>Unable to update the <code>AUTO_INCREMENT</code> value for table <code>' .
				ATOM_DBBANS . '</code>, please set it to ' . ($maxId + 1) . '.</p>'));
		}
		$maxId = 0;
		$likes = getAllLikes();
		foreach ($likes as $like) {
			$maxId = max($maxId, $like['id']);
			mysqli_query($link,
				'INSERT INTO `' . ATOM_DBLIKES . "` (
					`id`,
					`ip`,
					`board`,
					`postnum`,
					`islike`
				) VALUES ('" .
					mysqli_real_escape_string($link, $like['id']) . "', '" .
					mysqli_real_escape_string($link, $like['ip']) . "', '" .
					mysqli_real_escape_string($link, $like['board']) . "', '" .
					mysqli_real_escape_string($link, $like['postnum']) . "', '" .
					mysqli_real_escape_string($link, $like['islike']) . "')");
		}
		if ($maxId > 0 && !mysqli_query($link,
			'ALTER TABLE `' . ATOM_DBLIKES . '` AUTO_INCREMENT = ' . ($maxId + 1))
		) {
			die(managePage('<p><b>Warning!</b></p>' .
				'<p>Unable to update the <code>AUTO_INCREMENT</code> value for table <code>' .
				ATOM_DBLIKES . '</code>, please set it to ' . ($maxId + 1) . '.</p>'));
		}
		die(managePage('<p><b>Database migration complete!</b></p>' .
			'<p>Set <code>ATOM_DBMODE</code> to <code>mysqli</code> and <code>ATOM_DBMIGRATE</code> to' .
			' <code>false</code> in your settings.php file,<br>Then click <b>[Rebuild All]</b> above and' .
			' ensure everything looks the way it should.</p>'));
	}

	/* --------[ Show ban form and list of bans ]-------- */

	if (isset($_GET['bans']) && $access != 'janitor') {
		clearExpiredBans();
		$text = '';
		if (isset($_POST['ip'])) {
			if ($_POST['ip'] != '') {
				$banexists = banByIP($_POST['ip']);
				if ($banexists) {
					fancyDie('Sorry, there is already a ban on record for that IP address.');
				}
				$ban = array();
				$ban['ip'] = $_POST['ip'];
				$ban['expire'] = $_POST['expire'] > 0 ? time() + $_POST['expire'] : 0;
				$ban['reason'] = $_POST['reason'];
				insertBan($ban);
				$text .= manageInfo('Ban record added for ' . $ban['ip']);
			}
		} elseif (isset($_GET['lift'])) {
			$ban = banByID($_GET['lift']);
			if ($ban) {
				deleteBan($_GET['lift']);
				$text .= manageInfo('Ban record lifted for ' . $ban['ip']);
			}
		}
		die(managePage(manageBanForm() . manageBansTable(), 'bans'));
	}

	/* --------[ Show moderation log ]-------- */

	if (isset($_GET['modlog']) && $access != 'janitor') {
		$fromtime = 0;
		$totime = 0;
		if (isset($_POST['from']) && isset($_POST['to'])) {
			if (($fromtime = strtotime($_POST['from'])) === false ||
				($totime = strtotime($_POST['to'])) === false
			) {
				fancyDie('Wrong time format. Use yyyy-mm-dd format.');
			}
			$fromtime = intval(strtotime($_POST['from']));
			$totime = intval(strtotime($_POST['to']));
		}
		die(managePage(generateModLogForm() . generateModLogTable(true, $fromtime, $totime)));
	}

	/* --------[ Delete post or thread ]-------- */

	if (isset($_GET['delete'])) {
		$post = getPost($_GET['delete']);
		if (!$post) {
			fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
		}
		$id = $post['id'];
		deletePost($id);
		if (isOp($post)) {
			modLog('Deleted thread №' . $id . '.', '0', 'Black');
		} else {
			$thrId = $post['parent'];
			rebuildThreadPage($thrId);
			modLog('Deleted post №' . $id . ' in thread №' . $thrId . '.', '0', 'Black');
		}
		rebuildIndexPages();
		die(managePage(manageInfo('Post №' . $id . ' are deleted.')));
	}

	/* --------[ Delete all posts from ip ]-------- */

	if (isset($_GET['delall'])) {
		$ip = $_GET['delall'];
		$posts = getPostsByIP($ip);
		$deletedPosts = '';
		$updThreads = array();
		foreach ($posts as $post) {
			$id = $post['id'];
			$thrId = $post['parent'];
			deletePost($id);
			$deletedPosts .= $id . (next($posts) ? ', ' : '');
			if(!isOp($post) && !in_array($thrId, $updThreads)) {
				$updThreads[] = $thrId;
			}
		}
		foreach ($updThreads as $updThreadId) {
			rebuildThreadPage($updThreadId);
		}
		modLog('Deleted all posts from ip ' . $ip . ': №' . $deletedPosts . '.', '0', 'Black');
		rebuildIndexPages();
		die(managePage(manageInfo('Posts from ip ' . $ip . ' are deleted:<br>№' . $deletedPosts . '.')));
	}

	/* --------[ Delete/hide images ]-------- */

	if (isset($_GET['delete-img']) && isset($_GET['delete-img-mod']) && isset($_GET['action'])) {
		$post = getPost($_GET['delete-img']);
		if (!$post) {
			fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
		}
		$id = $post['id'];
		$thrId = getThreadId($post);
		if ($_GET['action'] == 'delete') {
			deletePostImages($post, $_GET['delete-img-mod']);
			rebuildThread($thrId);
			modLog('Deleted image(s) of ' . (isOp($post) ? 'op-post in thread №' . $id :
				'post №' . $id . ' in thread №' . $thrId) . '.', '0', 'Black');
			die(managePage(manageInfo('Selected images from post №' . $id . ' are deleted.')));
		}
		if ($_GET['action'] == 'hide') {
			hidePostImages($post, $_GET['delete-img-mod']);
			rebuildThread($thrId);
			modLog('Hidden thumbnail(s) of ' . (isOp($post) ? 'op-post in thread №' . $id :
				'post №' . $id . ' in thread №' . $thrId) . '.', '0', 'Black');
			die(managePage(manageInfo('Thumbnails for selected images from post №' . $id . ' are changed.')));
		}
	}

	/* --------[ Edit message in post ]-------- */

	if (isset($_GET['editpost']) && isset($_POST['message'])) {
		$post = getPost($_GET['editpost']);
		if (!$post) {
			fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
		}
		$id = $post['id'];
		$thrId = getThreadId($post);
		editPostMessage($id, $_POST['message'] . '<br><br><span style="color: purple;">Message edited: ' .
			date('d.m.y D H:i:s', time()) . '</span>');
		rebuildThread($thrId);
		modLog('Edited message of ' . (isOp($post) ? 'op-post in thread №' . $id :
			'post №' . $id . ' in thread №' . $thrId) . '.', '0', 'Black');
		die(managePage(manageInfo('Message in post №' . $id . ' changed.')));
	}

	/* --------[ Approve post if premoderation enabled (see ATOM_REQMOD) ]-------- */

	if (isset($_GET['approve']) && $_GET['approve'] > 0) {
		$post = getPost($_GET['approve']);
		if (!$post) {
			fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
		}
		$id = $post['id'];
		$thrId = getThreadId($post);
		approvePost($id);
		if (ATOM_THREAD_LIMIT == 0 || getThreadPostsCount($thrId) <= ATOM_THREAD_LIMIT) {
			if (strtolower($post['email']) != 'sage') {
				bumpThread($thrId);
			}
		} elseif (ATOM_THREAD_LIMIT != 0) {
			trimThreadPostsCount($thrId);
		}
		rebuildThread($thrId);
		die(managePage(manageInfo('Post №' . $id . ' approved.')));
	}

	/* --------[ Show post moderation form ]-------- */

	if (isset($_GET['moderate'])) {
		if ($_GET['moderate'] > 0) {
			$post = getPost($_GET['moderate']);
			if (!$post) {
				fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
			}
			die(managePage(manageModeratePost($post)));
		}
		die(managePage(manageModeratePostForm(), 'moderate'));
	}

	/* --------[ Sticky thread ]-------- */

	if (isset($_GET['sticky']) && isset($_GET['setsticky'])) {
		if ($_GET['sticky'] <= 0) {
			fancyDie('Form data was lost. Please go back and try again.');
		}
		$post = getPost($_GET['sticky']);
		if (!$post || !isOp($post)) {
			fancyDie('Sorry, there doesn\'t appear to be a thread with that ID.');
		}
		$isStickied = intval($_GET['setsticky']);
		$id = $post['id'];
		toggleStickyThread($id, $isStickied);
		rebuildThread($id);
		$stickiedText = $isStickied == 1 ? 'stickied' : 'un-stickied';
		modLog(ucfirst($stickiedText) . ' thread №' . $id . '.', '0', 'Black');
		die(managePage(manageInfo('Thread №' . $id . ' is ' . $stickiedText . '.')));
	}

	/* --------[ Lock thread ]--------= */

	if (isset($_GET['locked']) && isset($_GET['setlocked'])) {
		if ($_GET['locked'] <= 0) {
			fancyDie('Form data was lost. Please go back and try again.');
		}
		$post = getPost($_GET['locked']);
		if (!$post || !isOp($post)) {
			fancyDie('Sorry, there doesn\'t appear to be a thread with that ID.');
		}
		$isLocked = intval($_GET['setlocked']);
		$id = $post['id'];
		toggleLockThread($id, $isLocked);
		rebuildThread($id);
		$lockedText = $isLocked == 1 ? 'locked' : 'un-locked';
		modLog(ucfirst($lockedText) . ' thread №' . $id . '.', '0', 'Black');
		die(managePage(manageInfo('Thread №' . $id . ' is ' . $lockedText . '.')));
	}

	/* --------[ Make endless threads ]-------- */

	if (isset($_GET['endless']) && isset($_GET['setendless'])) {
		if ($_GET['endless'] <= 0) {
			fancyDie('Form data was lost. Please go back and try again.');
		}
		$post = getPost($_GET['endless']);
		if (!$post || !isOp($post)) {
			fancyDie('Sorry, there doesn\'t appear to be a thread with that ID.');
		}
		$isEndless = intval($_GET['setendless']);
		$id = $post['id'];
		toggleEndlessThread($id, $isEndless);
		rebuildThread($id);
		$endlessText = $isEndless == 1 ? 'made endless' : 'made non-endless';
		modLog(ucfirst($endlessText) . ' thread №' . $id . '.', '0', 'Black');
		die(managePage(manageInfo('Thread №' . $id . ' is ' . $endlessText . '.')));
	}

	/* --------[ Raw post sending ]-------- */

	if (isset($_GET['staffPost'])) {
		die(managePage(buildPostForm(0, true), 'staffPost'));
	}

	/* --------[ Log out ]-------- */

	if (isset($_GET['logout'])) {
		$_SESSION['atomboard'] = '';
		session_destroy();
		if (!$isAdmin) {
			modLog('Logout', '1', 'BlueViolet');
		};
		die('<meta http-equiv="refresh" content="0;url=' . basename($_SERVER['PHP_SELF']) . '?manage">');
	}

	/* --------[ Show status for posts ]-------- */

	die(managePage(manageStatus()));
}

/* ==[ Posting requests ]================================================================================== */

function postingRequest() {
	if (ATOM_DBMIGRATE) {
		fancyDie('Posting is currently disabled.<br>Please try again in a few moments.');
	}

	/* --------[ Post submission check ]-------- */

	global $access, $atom_embeds, $atom_hidefields, $atom_hidefieldsop, $atom_uploads;
	$hasAccess = $access != 'disabled';

	if (!$hasAccess) {

		// Check for recaptcha
		if (ATOM_CAPTCHA == 'recaptcha') {
			require_once 'inc/recaptcha/autoload.php';
			$captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
			$failed_captcha = true;
			$recaptcha = new \ReCaptcha\ReCaptcha(ATOM_RECAPTCHA_SECRET);
			$resp = $recaptcha->verify($captcha, $_SERVER['REMOTE_ADDR']);
			if ($resp->isSuccess()) {
				$failed_captcha = false;
			}
			if ($failed_captcha) {
				$captchaError = 'Failed CAPTCHA.';
				$errCodes = $resp->getErrorCodes();
				$errReason = '';
				if (count($errCodes) == 1) {
					$errCodes = $errCodes;
					$errReason = $errCodes[0];
				}
				if ($errReason == 'missing-input-response') {
					$captchaError .= ' Please click the checkbox labeled "I\'m not a robot".';
				} else {
					$captchaError .= ' Reason:';
					foreach ($errCodes as $error) {
						$captchaError .= '<br>' . $error;
					}
				}
				fancyDie($captchaError);
			}
		}

		// Check for simple captcha
		elseif (ATOM_CAPTCHA) {
			$captcha = isset($_POST['captcha']) ? strtolower(trim($_POST['captcha'])) : '';
			if ($captcha == '') {
				fancyDie('Please enter the CAPTCHA text.');
			}
			if ($captcha != (isset($_SESSION['atom_captcha']) ?
				strtolower(trim($_SESSION['atom_captcha'])) : '')
			) {
				fancyDie('Incorrect CAPTCHA text entered, please try again.<br>' .
					'Click the image to retrieve a new CAPTCHA.');
			}
			unset($_SESSION['atom_captcha']);
		}

		// Check for ban
		$ban = banByIP($_SERVER['REMOTE_ADDR']);
		if ($ban) {
			if ($ban['expire'] == 0 || $ban['expire'] > time()) {
				$expire = $ban['expire'] > 0 ?
					'<br>This ban will expire ' . date('y.m.d D H:i:s', $ban['expire']) :
					'<br>This ban is permanent and will not expire.';
				$reason = $ban['reason'] == '' ? '' : '<br>Reason: ' . $ban['reason'];
				fancyDie('Your IP address ' . $ban['ip'] .
					' has been banned from posting on this image board. ' . $expire . $reason);
			} else {
				clearExpiredBans();
			}
		}

		// Check for message size
		if (strlen($_POST['message']) > 8000) {
			fancyDie('Please shorten your message, or post it in multiple parts.<br>Your message is ' .
				strlen($_POST['message']) . ' characters long, and the maximum allowed is 8000.');
		}

		// Check for floodF
		if (ATOM_POSTING_DELAY > 0) {
			$lastpost = getLastPostByIP();
			if ($lastpost && (time() - $lastpost['timestamp']) < ATOM_POSTING_DELAY) {
				fancyDie('Please wait a moment before posting again.<br>' .
					'You will be able to make another post in ' .
					(ATOM_POSTING_DELAY - (time() - $lastpost['timestamp'])) .
					' ' . plural('second', (ATOM_POSTING_DELAY - (time() - $lastpost['timestamp']))) . '.');
			}
		}
	}

	// Check for parent thread
	$parentId = ATOM_NEWTHREAD;
	if (isset($_POST['parent'])) {
		if ($_POST['parent'] != ATOM_NEWTHREAD) {
			if (!isThreadExists($_POST['parent'])) {
				fancyDie('Invalid parent thread ID supplied, unable to create post.');
			}
			$parentId = $_POST['parent'];
		}
	}

	/* --------[ Filling post fields ]-------- */

	// Initialize default post fields
	$post = newPost($parentId);
	$isOp = isOp($post);
	if (!$isOp && !$hasAccess && getPost($post['parent'])['locked']) {
		fancyDie('Posting in this thread is currently disabled.<br>Thread is locked.');
	}

	$hideFields = $isOp ? $atom_hidefieldsop : $atom_hidefields;
	$post['ip'] = $_SERVER['REMOTE_ADDR'];
	$staffPost = isstaffPost();

	// Get name/tripcode
	if ($staffPost || !in_array('name', $hideFields)) {
		$postName = $_POST['name'];
		if (preg_match('/(#|!)(.*)/', $postName, $regs)) {
			$cap = $regs[2];
			$cap_full = '#' . $regs[2];
			if (function_exists('mb_convert_encoding')) {
				$recoded_cap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
				if ($recoded_cap != '') {
					$cap = $recoded_cap;
				}
			}
			if (strpos($postName, '#') === false) {
				$cap_delimiter = '!';
			} elseif (strpos($postName, '!') === false) {
				$cap_delimiter = '#';
			} else {
				$cap_delimiter = strpos($postName, '#') < strpos($postName, '!') ? '#' : '!';
			}
			if (preg_match('/(.*)(' . $cap_delimiter . ')(.*)/', $cap, $regs_secure)) {
				$cap = $regs_secure[1];
				$cap_secure = $regs_secure[3];
				$is_secure_trip = true;
			} else {
				$is_secure_trip = false;
			}
			$postTripcode = '';
			if ($cap != '') {
				$cap = strtr($cap, '&amp;', '&');
				$cap = strtr($cap, '&#44;', ', ');
				$salt = substr($cap . 'H.', 1, 2);
				$salt = preg_replace('/[^\.-z]/', '.', $salt);
				$salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');
				$postTripcode = substr(crypt($cap, $salt), -10);
			}
			if ($is_secure_trip) {
				if ($cap != '') {
					$postTripcode .= '!';
				}
				$postTripcode .= '!' . substr(md5($cap_secure . ATOM_TRIPSEED), 2, 10);
			}
			$post['name'] = preg_replace('/(' . $cap_delimiter . ')(.*)/', '', $postName);
			$post['tripcode'] = $postTripcode;
		} else {
			$post['name'] = $postName;
			$post['tripcode'] = '';
		}
		$post['name'] = escapeHTML(substr($post['name'], 0, 75));
	}

	// Get email
	if ($staffPost || !in_array('email', $hideFields)) {
		$post['email'] = escapeHTML(str_replace('"', '&quot;', substr($_POST['email'], 0, 75)));
	}

	// Get subject
	if ($staffPost || !in_array('subject', $hideFields)) {
		$post['subject'] = escapeHTML(substr($_POST['subject'], 0, 75));
	}

	// Get message with markup and >>links
	if (!in_array('message', $hideFields)) {
		$post['message'] = $_POST['message'];
		// Markup text formatting
		if (!$staffPost) {
			$msg = escapeHTML(rtrim($post['message']));
			if (ATOM_WORDBREAK > 0) {
				$msg = preg_replace(
					'/([^\s]{' . ATOM_WORDBREAK . '})(?=[^\s])/',
					'$1' . ATOM_WORDBREAK_IDENTIFIER,
					$msg);
			}
			// [code]Block code[/code]
			$msg = preg_replace_callback('/\[code\]\r?\n?([\s\S]*?)\r?\n?\[\/code\]/i', function($matches) {
				$m = $matches[1];
				$m = str_replace("\r\n", '@!@ATOM_LINE_END@!@', $m);
				$m = str_replace("\r", '@!@ATOM_LINE_END@!@', $m);
				$m = str_replace("\n", '@!@ATOM_LINE_END@!@', $m);
				$m = str_replace('`', '&#96;', $m);
				$m = str_replace('<', '&lt;', $m);
				$m = preg_replace('/>|&gt;/', '@!@ATOM_GT@!@', $m);
				$m = str_replace('[', '&#91;', $m);
				$m = str_replace(']', '&#93;', $m);
				$m = str_replace('*', '&#42;', $m);
				$m = str_replace('__', '&#95;&#95;', $m);
				$m = str_replace('~~', '&#126;&#126;', $m);
				$m = str_replace('%%', '&#37;&#37;', $m);
				return '<pre>' . $m . '</pre>';
			}, $msg);
			// `Inline code`
			$msg = preg_replace_callback('/`([^\`\r\n]+)`/', function($matches) {
				$m = $matches[1];
				$m = str_replace('<', '&lt;', $m);
				$m = preg_replace('/>|&gt;/', '@!@ATOM_GT@!@', $m);
				$m = str_replace('[', '&#91;', $m);
				$m = str_replace(']', '&#93;', $m);
				$m = str_replace('*', '&#42;', $m);
				$m = str_replace('__', '&#95;&#95;', $m);
				$m = str_replace('~~', '&#126;&#126;', $m);
				$m = str_replace('%%', '&#37;&#37;', $m);
				return '<code>' . $m . '</code>';
			}, $msg);
			// Post >>links
			$msg = preg_replace_callback('/&gt;&gt;([0-9]+)/', function($matches) {
				$post = getPost($matches[1]);
				if ($post) {
					return '<a class="' . (isOp($post) ? 'refop' : 'refreply') . '" href="/' . ATOM_BOARD .
						'/res/' . getThreadId($post) . '.html#' . $matches[1] . '">' . $matches[0] . '</a>';
				}
				return $matches[0];
			}, $msg);
			// > Quote
			$msg = preg_replace('/^(&gt;.*?)\r?\n?$/m', '<span class="unkfunc">$1</span>', $msg);
			// **Bold**
			$msg = preg_replace('/\*\*([^\*\r\n]+)\*\*/', '<b>$1</b>', $msg);
			// [b]Bold[/b]
			$msg = preg_replace('/\[b\]\r?\n?([\s\S]*?)\r?\n?\[\/b\]/i', '<b>$1</b>', $msg);
			// *Italic*
			$msg = preg_replace('/\*([^\*\r\n]+)\*/', '<i>$1</i>', $msg);
			// [i]Italic[/i]
			$msg = preg_replace('/\[i\]\r?\n?([\s\S]*?)\r?\n?\[\/i\]/i', '<i>$1</i>', $msg);
			// __Underline__
			$msg = preg_replace('/__([^~\r\n]+)__/', '<span class="underline">$1</span>', $msg);
			// [u]Underline[/u]
			$msg = preg_replace('/\[u\]\r?\n?([\s\S]*?)\r?\n?\[\/u\]/i',
				'<span class="underline">$1</span>', $msg);
			// ~~Strike~~
			$msg = preg_replace('/~~([^~\r\n]+)~~/', '<del>$1</del>', $msg);
			// [s]Strike[/s]
			$msg = preg_replace('/\[s\]\r?\n?([\s\S]*?)\r?\n?\[\/s\]/i', '<del>$1</del>', $msg);
			// %%Spoiler%%
			$msg = preg_replace('/%%([^\%\r\n]+)%%/', '<span class="spoiler">$1</span>', $msg);
			// [spoiler]Spoiler[/spoiler]
			$msg = preg_replace('/\[spoiler\]\r?\n?([\s\S]*?)\r?\n?\[\/spoiler\]/i',
				'<span class="spoiler">$1</span>', $msg);
			// Clickable links
			$msg = preg_replace(
				'/((?:f|ht)tps?:\/\/)(.*?)($|\s|<|[,.?!):]+(?:[\s<]|$))/i',
				'<a href="$1$2" target="_blank">$1$2</a>$3', $msg);
			$msg = preg_replace(
				'/\[(.*?)\]\(<a href="(.*?)" target="_blank">(.*?)<\/a>\)/i',
				'<a href="$2" target="_blank">$1</a>', $msg);
			// Linebreaks
			$msg = str_replace("\r\n", '<br>', $msg);
			$msg = str_replace("\r", '<br>', $msg);
			$msg = str_replace("\n", '<br>', $msg);
			$msg = str_replace('<br>', "<br>\r\n", $msg);
			$msg = str_replace('@!@ATOM_GT@!@', '&gt;', $msg);
			$msg = str_replace('@!@ATOM_LINE_END@!@', "\r\n", $msg);
			if (ATOM_WORDBREAK > 0) {
				$msg = str_replace(
					ATOM_WORDBREAK_IDENTIFIER,
					'<br>',
					preg_replace_callback('/<a(.*?)href="([^"]*?)"(.*?)>(.*?)<\/a>/', function($matches) {
						return '<a' . $matches[1] . 'href="' .
							str_replace(ATOM_WORDBREAK_IDENTIFIER, '', $matches[2]) . '"' . $matches[3] . '>' .
							str_replace(ATOM_WORDBREAK_IDENTIFIER, '<br>', $matches[4]) . '</a>';
					}, msg)
				);
			}
			$post['message'] = $msg;
		}
	}

	// Get password
	if ($staffPost || !in_array('password', $hideFields)) {
		$post['password'] = $_POST['password'] != '' ? md5(md5($_POST['password'])) : '';
	}

	// Get Nameblock
	$postName = $post['name'];
	$postTripcode = $post['tripcode'];
	$postEmail = $post['email'];
	$postNameBlock = '<span class="postername' .
		($hasAccess && $postName != '' ?
			($access == 'admin' ? ' postername-admin' : ' postername-mod') : '') . '">' .
		($postName == '' && $postTripcode == '' ? ATOM_POSTERNAME : $postName) .
		($postTripcode != '' ? '</span><span class="postertrip">!' . $postTripcode : '') . '</span>';
	if($hasAccess && !($postName == '' && $postTripcode == '')) {
		switch($access) {
		case 'admin': $postNameBlock .= ' <span class="postername-admin">## Admin</span>'; break;
		case 'janitor': $postNameBlock .= ' <span class="postername-mod">## Janitor</span>'; break;
		case 'moderator': $postNameBlock .= ' <span class="postername-mod">## Mod</span>'; break;
		}
	}
	if (ATOM_POSTERUID) {
		$hash = substr(md5($post['ip'] . intval($post['parent']) . ATOM_TRIPSEED), 0, 8);
		$hashint = hexdec('0x' . $hash);
		$red = $hashint >> 24 & 255;
		$green = $hashint >> 16 & 255;
		$blue = $hashint >> 8 & 255;
		$isBlack = 0.299 * $red + 0.587 * $green + 0.114 * $blue > 125;
		$postNameBlock .=  ' <span class="posteruid" data-uid="' . $hash . '" style="background-color: rgb(' .
			$red . ', ' . $green . ', ' . $blue . '); color: ' .
			($isBlack ? 'black' : 'white') . ';">' . $hash . '</span>';
	}
	$lowEmail = strtolower($postEmail);
	if ($postEmail != '' && $lowEmail != 'noko') {
		$postNameBlock = '<a href="mailto:' . $postEmail . '"' .
			($lowEmail == 'sage' ? ' class="sage"' : '') . '>' . $postNameBlock . '</a>';
	}
	$post['nameblock'] = $postNameBlock . ' ' . date('d.m.y D H:i:s', time());

	/* --------[ Embed URL upload ]-------- */

	if (isset($_POST['embed']) &&
		trim($_POST['embed']) != '' &&
		($staffPost || !in_array('embed', $hideFields))
	) {
		if (isset($_FILES['file']) && $_FILES['file']['name'][0] != '') {
			fancyDie('Embedding a URL and uploading a file at the same time is not supported.');
		}
		list($service, $embed) = getEmbed(trim($_POST['embed']));
		if (empty($embed) ||
			!isset($embed['html']) ||
			!isset($embed['title']) ||
			!isset($embed['thumbnail_url'])
		) {
			fancyDie('Invalid embed URL.<br>Only ' .
				(implode('/', array_keys($atom_embeds))) . ' URLs are supported.');
		}
		$post['file0_hex'] = $service;
		$fileName = time() . substr(microtime(), 2, 3) . '-0';
		$fileLocation = 'thumb/' . $fileName;
		file_put_contents($fileLocation, url_get_contents($embed['thumbnail_url']));
		$fileInfo = getimagesize($fileLocation);
		$post['image0_width'] = $fileInfo[0];
		$post['image0_height'] = $fileInfo[1];
		switch(mime_content_type($fileLocation)) {
		case 'image/jpeg': $post['thumb0'] = $fileName . '.jpg'; break;
		case 'image/png': $post['thumb0'] = $fileName . '.png'; break;
		case 'image/gif': $post['thumb0'] = $fileName . '.gif'; break;
		case 'image/webp': $post['thumb0'] = $fileName . '.webp'; break;
		default: fancyDie('Error while processing audio/video.');
		}
		$thumbLocation = 'thumb/' . $post['thumb0'];
		list($thumbMaxWidth, $thumbMaxHeight) = getThumbnailDimensions($post, '0');
		if (!createThumbnail($fileLocation, $thumbLocation, $thumbMaxWidth, $thumbMaxHeight)) {
			@unlink($fileLocation);
			fancyDie('Could not create thumbnail.');
		}
		@unlink($fileLocation);
		if (ATOM_VIDEO_OVERLAY) {
			addVideoOverlay($thumbLocation);
		}
		$thumbInfo = getimagesize($thumbLocation);
		$post['thumb0_width'] = $thumbInfo[0];
		$post['thumb0_height'] = $thumbInfo[1];
		$post['file0_original'] = escapeHTML($embed['title']);
		$embedHtml = $embed['html'];
		if($service == 'YouTube.com') {
			$embedHtml = preg_replace('/width="\d+"/', 'width="' . $fileInfo[0] . '"', $embedHtml);
			$embedHtml = preg_replace('/height="\d+"/', 'height="' . $fileInfo[1] . '"', $embedHtml);
		}
		$post['file0'] = str_ireplace(array('src="https://', 'src="http://'), 'src="//', $embedHtml);
	}

	/* --------[ Images upload ]-------- */

	elseif (isset($_FILES['file']) && $_FILES['file']['name'][0] != '' &&
		($staffPost || !in_array('file', $hideFields))
	) {
		$fileIdx = 0;
		$filesCount = 0;
		$fileBytes = 0;
		foreach ($_FILES['file']['error'] as $index => $error) {
			$fileIdx++;
			if ($filesCount >= ATOM_FILES_COUNT || $fileIdx > 1 && $error == UPLOAD_ERR_NO_FILE) {
				continue;
			}

			// Check for upload errors
			$fileIdxTxt = 'File №' . $fileIdx;
			switch ($error) {
			case UPLOAD_ERR_OK: break;
			case UPLOAD_ERR_FORM_SIZE:
				fancyDie($fileIdxTxt . ' is larger than ' . ATOM_FILE_MAXKBDESC . '.');
				break;
			case UPLOAD_ERR_INI_SIZE:
				fancyDie($fileIdxTxt . ' exceeds the upload_max_filesize directive (' .
					ini_get('upload_max_filesize') . ') in php.ini.');
				break;
			case UPLOAD_ERR_PARTIAL: fancyDie($fileIdxTxt . ' was only partially uploaded.'); break;
			case UPLOAD_ERR_NO_FILE: fancyDie('No file was uploaded.'); break;
			case UPLOAD_ERR_NO_TMP_DIR: fancyDie('Missing a temporary folder.'); break;
			case UPLOAD_ERR_CANT_WRITE: fancyDie('Failed to write ' . $fileIdxTxt . ' to disk.'); break;
			case UPLOAD_ERR_EXTENSION:
				fancyDie('Unable to save the uploaded ' . $fileIdxTxt . '. Extension error.');
				break;
			default: fancyDie('Unable to save the uploaded ' . $fileIdxTxt . '.');
			}
			if (!is_file($_FILES['file']['tmp_name'][$index]) ||
				!is_readable($_FILES['file']['tmp_name'][$index])
			) {
				fancyDie($fileIdxTxt . ' transfer failure.<br>Please retry the submission.');
			}

			// Check for bytes size restriction
			$fileBytes = filesize($_FILES['file']['tmp_name'][$index]);
			if ((ATOM_FILE_MAXKB > 0) && ($fileBytes > (ATOM_FILE_MAXKB * 1024))) {
				fancyDie($fileIdxTxt . ' is larger than ' . ATOM_FILE_MAXKBDESC . '.');
			}

			// Get post image fields
			$post['file' . $index . '_original'] = trim(
				htmlentities(substr(basename($_FILES['file']['name'][$index]), 0, 50), ENT_QUOTES, 'UTF-8'));
			$post['file' . $index . '_hex'] = md5_file($_FILES['file']['tmp_name'][$index]);
			$post['file' . $index . '_size'] = $_FILES['file']['size'][$index];

			// Convert file bytes
			$fileBytes = $post['file' . $index . '_size'];
			$bytesLen = strlen($fileBytes);
			if ($bytesLen < 4) {
				$fileBytes = sprintf("%dB", $fileBytes);
			} elseif ($bytesLen <= 6) {
				$fileBytes = sprintf("%0.2fKB", $fileBytes / 1024);
			} elseif ($bytesLen <= 9) {
				$fileBytes = sprintf("%0.2fMB", $fileBytes / 1024 / 1024);
			} else {
				$fileBytes = sprintf("%0.2fGB", $fileBytes / 1024 / 1024 / 1024);
			}
			$post['file' . $index . '_size_formatted'] = $fileBytes;

			// Check for file duplicates
			if (ATOM_FILE_DUPLICATE === false) {
				$hex = $post['file' . $index . '_hex'];
				$hexMatches = getPostsByImageHex($hex);
				if (count($hexMatches) > 0) {
					foreach ($hexMatches as $hexMatch) {
						fancyDie('Duplicate ' . $fileIdxTxt .
							' uploaded.<br>That file has already been posted <a href="' .
							'res/' . getThreadId($hexMatch) . '.html#' . $hexMatch['id'] . '">here</a>.');
					}
				}
			}

			// Check for supported file types
			$fileMimeSplit = explode(' ', trim(mime_content_type($_FILES['file']['tmp_name'][$index])));
			if (count($fileMimeSplit) > 0) {
				$fileMime = strtolower(array_pop($fileMimeSplit));
			} else {
				if (!@getimagesize($_FILES['file']['tmp_name'][$index])) {
					fancyDie('Failed to read the MIME type and size of the uploaded ' .
						$fileIdxTxt . '.<br>' . 'Please retry the submission.');
				}
				$fileInfo = getimagesize($_FILES['file']['tmp_name'][$index]);
				$fileMime = mime_content_type($_FILES['file']['tmp_name'][$index]);
			}
			if (empty($fileMime) || !isset($atom_uploads[$fileMime])) {
				fancyDie(supportedFileTypes());
			}

			// Generate file name and location
			$fileName = time() . substr(microtime(), 2, 3) . '-' . $index;
			$post['file' . $index] = $fileName . '.' . $atom_uploads[$fileMime][0];
			$fileLocation = 'src/' . $post['file' . $index];

			// Upload file
			if (!move_uploaded_file($_FILES['file']['tmp_name'][$index], $fileLocation)) {
				fancyDie('Could not copy uploaded ' . $fileIdxTxt . '.');
			}
			if ($_FILES['file']['size'][$index] != filesize($fileLocation)) {
				@unlink($fileLocation);
				fancyDie($fileIdxTxt . ' transfer failure.<br>Please go back and try again.');
			}

			// Get video info and its thumbnail
			if ($fileMime == 'audio/webm' ||
				$fileMime == 'video/webm' ||
				$fileMime == 'video/mp4' ||
				$fileMime == 'video/quicktime'
			) {
				$post['image' . $index . '_width'] = max(0, intval(shell_exec(
					'mediainfo --Inform="Video;%Width%" ' . $fileLocation)));
				$post['image' . $index . '_height'] = max(0, intval(shell_exec(
					'mediainfo --Inform="Video;%Height%" ' . $fileLocation)));
				if ($post['image' . $index . '_width'] > 0 && $post['image' . $index . '_height'] > 0) {
					list($thumbMaxWidth, $thumbMaxHeight) = getThumbnailDimensions($post, $index);
					$post['thumb' . $index] = $fileName . 's.jpg';
					shell_exec('ffmpegthumbnailer -t 1 -s ' . max($thumbMaxWidth, $thumbMaxHeight) .
						' -i ' . $fileLocation . ' -o thumb/' . $post['thumb' . $index]);
					$thumbInfo = getimagesize('thumb/' . $post['thumb' . $index]);
					$post['thumb' . $index . '_width'] = $thumbInfo[0];
					$post['thumb' . $index . '_height'] = $thumbInfo[1];
					if ($post['thumb' . $index . '_width'] <= 0 ||
						$post['image' . $index . '_width'] > 32766 ||
						$post['thumb' . $index . '_height'] <= 0 ||
						$post['image' . $index . '_height'] > 32766
					) {
						@unlink($fileLocation);
						@unlink('thumb/' . $post['thumb' . $index]);
						fancyDie('Sorry, your video ' . $fileIdxTxt . ' appears to be corrupt.');
					}
					if (ATOM_VIDEO_OVERLAY) {
						addVideoOverlay('thumb/' . $post['thumb' . $index]);
					}
				}
				$duration = intval(shell_exec('mediainfo --Inform="General;%Duration%" ' . $fileLocation));
				if ($duration > 0) {
					$mins = floor(round($duration / 1000) / 60);
					$secs = str_pad(floor(round($duration / 1000) % 60), 2, '0', STR_PAD_LEFT);
					$post['file' . $index . '_original'] = $mins . ':' . $secs .
						($post['file' . $index . '_original'] != '' ?
							(', ' . $post['file' . $index . '_original']) : '');
				}
			}

			// Get image info
			elseif (in_array($fileMime, array(
				'image/jpeg',
				'image/pjpeg',
				'image/png',
				'image/gif',
				'image/webp',
				'application/x-shockwave-flash'))
			) {
				$fileInfo = getimagesize($fileLocation);
				$post['image' . $index . '_width'] = $fileInfo[0];
				$post['image' . $index . '_height'] = $fileInfo[1];
			}

			// Get optional image thumbnail
			if (isset($atom_uploads[$fileMime][1])) {
				$thumbFileSplit = explode('.', $atom_uploads[$fileMime][1]);
				$post['thumb' . $index] = $fileName . 's.' . array_pop($thumbFileSplit);
				if (!copy($atom_uploads[$fileMime][1], 'thumb/' . $post['thumb' . $index])) {
					@unlink($fileLocation);
					fancyDie('Could not create thumbnail for ' . $fileIdxTxt . '.');
				}
				if ($fileMime == 'application/x-shockwave-flash') {
					ATOM_VIDEO_OVERLAY ? addVideoOverlay('thumb/' . $post['thumb' . $index]):'';
				}
			}

			// Get default image thumbnail
			elseif (in_array($fileMime, array(
				'image/jpeg',
				'image/pjpeg',
				'image/png',
				'image/gif',
				'image/webp'
			))) {
				$post['thumb' . $index] = $fileName . 's.' . $atom_uploads[$fileMime][0];
				list($thumbMaxWidth, $thumbMaxHeight) = getThumbnailDimensions($post, $index);
				if (!createThumbnail(
					$fileLocation,
					'thumb/' . $post['thumb' . $index],
					$thumbMaxWidth,
					$thumbMaxHeight)
				) {
					@unlink($fileLocation);
					fancyDie('Could not create thumbnail for ' . $fileIdxTxt . '.');
				}
			}

			// Get thumbnail info
			if ($post['thumb' . $index] != '') {
				$thumbInfo = getimagesize('thumb/' . $post['thumb' . $index]);
				$post['thumb' . $index . '_width'] = $thumbInfo[0];
				$post['thumb' . $index . '_height'] = $thumbInfo[1];
			}

			$filesCount++;
		}
	}

	/* --------[ No file upload ]-------- */

	if ($post['file0'] == '') {
		$allowed = '';
		if (!empty($atom_uploads) && ($staffPost || !in_array('file', $hideFields))) {
			$allowed = 'file';
		}
		if (!empty($atom_embeds) && ($staffPost || !in_array('embed', $hideFields))) {
			if ($allowed != '') {
				$allowed .= ' or ';
			}
			$allowed .= 'embed URL';
		}
		if (isOp($post) && $allowed != '' && !ATOM_NOFILEOK) {
			fancyDie('A ' . $allowed . ' is required to start a thread.');
		}
		if (!$staffPost && str_replace('<br>', '', $post['message']) == '') {
			$dieMsg = '';
			if (!in_array('message', $hideFields)) {
				$dieMsg .= 'enter a message ' . ($allowed != '' ? ' and/or ' : '');
			}
			if ($allowed != '') {
				$dieMsg .= 'upload a ' . $allowed;
			}
			fancyDie('Please ' . $dieMsg . '.');
		}
	}

	$slowRedirect = false;
	if (!$hasAccess && (($post['file0'] != '' && ATOM_REQMOD == 'files') || ATOM_REQMOD == 'all')) {
		$slowRedirect = true;
		$post['moderated'] = '0';
		echo 'Your ' . (isOp($post) ? 'thread' : 'post') .
			' will be shown <b>once it has been approved</b>.<br>';
	}

	$post['likes'] = 0;
	$post['id'] = insertPost($post);

	/* --------[ Post/thread creation ]-------- */

	$redirectPath = ATOM_INDEX;
	if ($post['moderated'] == '1') {
		$id = $post['id'];
		$thrId = getThreadId($post);
		if (ATOM_ALWAYSNOKO || strtolower($post['email']) == 'noko') {
			$redirectPath = '/' . ATOM_BOARD . '/res/' . $thrId . '.html#' . $id;
		}
		trimThreadsCount();
		rebuildThreadPage($thrId);
		if (!isOp($post)) {
			if (ATOM_THREAD_LIMIT == 0 || getThreadPostsCount($thrId) <= ATOM_THREAD_LIMIT) {
				if (strtolower($post['email']) != 'sage') {
					bumpThread($thrId);
				}
			} elseif (ATOM_THREAD_LIMIT != 0) {
				trimThreadPostsCount($thrId);
			}
		}
		rebuildIndexPages();
	}

	if ($slowRedirect) {
		die('<meta http-equiv="refresh" content="3; url=' . $redirectPath . '">');
	}
	header('Location: ' . $redirectPath, true, 303);
	exit();
}

/* ==[ Deletion request ]================================================================================== */

function deletionRequest() {
	global $access;
	if (!isset($_POST['delete'])) {
		fancyDie('Tick the box next to a post and click "Delete" to delete it.');
	}
	if (ATOM_DBMIGRATE) {
		fancyDie('Post deletion is currently disabled.<br>Please try again in a few moments.');
	}
	$post = getPost($_POST['delete']);
	if (!$post) {
		fancyDie('Sorry, an invalid post identifier was sent.<br>' .
			'Please go back, refresh the page, and try again.');
	}
	if ($access != 'disabled' && $_POST['password'] == '') {
		// Redirect to post moderation page
		die('<meta http-equiv="refresh" content="0;url=' . basename($_SERVER['PHP_SELF']) .
			'?manage&moderate=' . $_POST['delete'] . '">');
	} elseif ($post['password'] == '' || md5(md5($_POST['password'])) != $post['password']) {
		fancyDie('Invalid password.');
	}
	deletePost($post['id']);
	rebuildThread(getThreadId($post));
	fancyDie('Post deleted.');
}

/* ==[ Like request ]====================================================================================== */

function likeRequest() {
	$postNum = $_GET['like'];
	$result = toggleLikePost($postNum, $_SERVER['REMOTE_ADDR']);
	$post = getPost($postNum);
	$post['likes'] = $result;
	rebuildThread(getThreadId($post));
	die('{
		"status": "ok",
		"message": "' . (
			$result[0] ? 'Post №' . $postNum . ' succesfully liked!' :
			'The like to post №' . $postNum . ' is cancelled!'
		) . '",
		"likes": ' . $result[1] . ' }');
}

/* ==[ Main ]============================================================================================== */

// Settings initialization
if (!file_exists('settings.php')) {
	fancyDie('Please copy the file settings.default.php to settings.php');
}
require 'settings.php';
if (ATOM_TRIPSEED == '' || ATOM_ADMINPASS == '') {
	fancyDie('settings.php: ATOM_TRIPSEED and ATOM_ADMINPASS must be configured.');
}
if (ATOM_CAPTCHA == 'recaptcha' && (ATOM_RECAPTCHA_SITE == '' || ATOM_RECAPTCHA_SECRET == '')) {
	fancyDie('settings.php: ATOM_RECAPTCHA_SITE and ATOM_RECAPTCHA_SECRET must be configured.');
}

// Check if directories are writable by the script
$writedirs = array('res', 'src', 'thumb');
if (ATOM_DBMODE == 'flatfile') {
	$writedirs[] = 'inc/flatfile';
}
foreach ($writedirs as $dir) {
	if (!is_writable($dir)) {
		fancyDie('Directory "' . $dir . '" can not be written to.<br>Please modify its permissions.');
	}
}

// Include php files
$includes = array('inc/defines.php', 'inc/functions.php', 'inc/html.php');
if (in_array(ATOM_DBMODE, array('flatfile', 'mysql', 'mysqli', 'sqlite', 'sqlite3', 'pdo'))) {
	$includes[] = 'inc/database_' . ATOM_DBMODE . '.php';
} else {
	fancyDie('settings.php: Unknown database mode in ATOM_DBMODE specified.');
}
foreach ($includes as $include) {
	include $include;
}
if (ATOM_TIMEZONE != '') {
	date_default_timezone_set(ATOM_TIMEZONE);
}

// Check for access role [admin/moderator/janitor/disabled]
$access = checkAccessRights();

// Requests processing
if (isset($_GET['manage'])) {
	managementRequest();
}
if (isset($_GET['delete'])) {
	deletionRequest();
}
if (
	isset($_POST['name']) || isset($_POST['email']) || isset($_POST['subject']) || isset($_POST['message']) ||
	isset($_POST['file']) || isset($_POST['embed']) || isset($_POST['password'])
) {
	postingRequest();
}
if (isset($_GET['like'])) {
	likeRequest();
}

// Initialization of empty board 
if (!file_exists(ATOM_INDEX) || getThreadsCount() == 0) {
	rebuildIndexPages();
}
header('Location: ' . ATOM_INDEX, true, 307);
exit();
