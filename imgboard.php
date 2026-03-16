<?php
// Uncomment to show debugging errors
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
ini_set('session.gc_maxlifetime', 2592000); // 30 days
session_set_cookie_params(2592000); // store session cookie for 30 days

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
	die('<!DOCTYPE html>

<html data-theme="' . ATOM_THEME . '">
<head>
	<link rel="stylesheet" type="text/css" href="/' . ATOM_BOARD . '/css/atomboard.css?2026030900">
</head>
<body align="center">
	<br>
	<div class="reply" style="display: inline-block; padding: 8px 20px; font-size: 1.25em;">' .
		$message . '</div>
	<br>
	<hr>
	<a class="link-button" href="javascript: window.history.go(-1);" title="Return to board">Return</a>
</body>');
}

/* ==[ Administration and moderation requests ]============================================================ */

function managementRequest() {
	global $loginStatus;
	$isAdmin = $loginStatus === 'admin';
	$isJanitor = $loginStatus === 'janitor';

	/* --------[ Show the login form or the post report form if not logged ]-------- */

	if ($loginStatus == 'disabled') {
		if (!empty($_GET['moderate']) && is_numeric($_GET['moderate'])) {
			$post = getPost($_GET['moderate']);
			if (!$post) {
				fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
			}
			die(managePage(buildReportPostForm($post)));
		}
		die(managePage(buildManageLoginForm(), 'login'));
	}

	/* --------[ Create an admin account if logged in with a temporary password ]-------- */
	if ($loginStatus === 'admin' && $_SESSION['atom_user'] === 'TemporaryAdmin') {
		if (isset($_POST['new_admin_user'], $_POST['new_admin_pass'])) {
			addStaffMember($_POST['new_admin_user'], $_POST['new_admin_pass'], 'admin');
			session_destroy();
			die(managePage(manageInfo(
				'Admin account created! Please <a href="?manage">log in</a> with new credentials.')));
		}
		die(managePage(buildAdminCreateForm()));
	}

	/* --------[ Manage staff accounts ]-------- */

	if (isset($_GET['staff']) && $isAdmin) {
		// Add account
		if (isset($_POST['add_user'], $_POST['add_pass'], $_POST['add_role'])) {
			$role = $_POST['add_role'];
			if (in_array($role, ['moderator', 'janitor'])) {
				addStaffMember($_POST['add_user'], $_POST['add_pass'], $role);
			}
		}
		// Delete account
		if (isset($_GET['delete_staff']) && is_numeric($_GET['delete_staff'])) {
			deleteStaffMember($_GET['delete_staff']);
		}
		die(managePage(buildStaffManager()));
	}

	/* --------[ Change account password ]-------- */

	if (isset($_GET['account'], $_SESSION['atom_user'])) {
		$msg = '';
		if (isset($_POST['old_pass'], $_POST['new_pass'], $_POST['confirm_pass'])) {
			$newPassw = $_POST['new_pass'];
			if ($newPassw === $_POST['confirm_pass']) {
				if (strlen($newPassw) >= 8) {
					$userName = $_SESSION['atom_user'];
					$user = getStaffMember($userName);
					if ($user && password_verify($_POST['old_pass'], $user['password_hash'])) {
						changeStaffMember($userName, $newPassw);
						deleteSession();
						die(managePage(manageInfo('Password changed successfully!' .
							' Please <a href="?manage">log in</a> with new credentials.')));
					} else {
						$msg = 'Old password is incorrect!';
					}
				} else {
					$msg = 'New password must be at least 8 characters long!';
				}
			} else {
				$msg = 'New password and confirmation do not match!';
			}
		}
		die(managePage(buildAccountForm($msg)));
	}

	/* --------[ Rebuild all posts ]-------- */

	if (isset($_GET['rebuildall']) && $isAdmin) {
		$getThreads = getThreads();
		foreach ($getThreads as $thread) {
			rebuildThreadPage($thread['id']);
		}
		rebuildIndexPages();
		deleteOldLookups();

		// Delete likes for deleted posts
		$likes = getAllLikes();
		foreach ($likes as $like) {
			$id = $like['postnum'];
			if (!getPost($id)) {
				deleteLikes($id);
			}
		}

		// Delete reports for deleted posts
		$reports = getAllReports();
		foreach ($reports as $report) {
			$id = $report['postnum'];
			if (!getPost($id)) {
				deleteReports($id);
			}
		}

		die(managePage(manageInfo('The board has been rebuilt.')));
	}

	/* --------[ Show the ban form and the list of bans ]-------- */

	if (isset($_GET['bans']) && !$isJanitor) {
		clearExpiredBans();
		$text = '';
		if (!empty($_POST['ip'])) {
			$ip = $_POST['ip'];
			$banexists = banByIP(long2ip(cidr2ip($ip)[0]));
			if ($banexists) {
				fancyDie('Sorry, there is already a ban on record for that IP address.');
			}
			$ban = [];
			$ban['ip'] = $ip;
			if ($_POST['expire'] == 1) {
				$expire = 1;
				$expireType = 'warning';
			} else if ($_POST['expire'] > 0) {
				$expire = time() + $_POST['expire'];
				$expireType = 'till ' . date('d.m.y D H:i:s', $expire);
			} else {
				$expire = 0;
				$expireType = 'permanent';
			}
			$ban['expire'] = $expire;
			$ban['reason'] = $_POST['reason'];
			insertBan($ban);
			modLog('Ban record (' . $expireType . ') added for ' . $ip . ', reason: ' . $ban['reason']);
			$text = 'Ban record added for ' . $ip . (isset($_POST['ban_delall']) ?
				'<br>Posts are deleted: №' . deleteAllPosts($ip, $_POST['thrid'] ?: NULL) . '.' : '');
		} elseif (isset($_GET['lift'])) {
			$ban = banByID($_GET['lift']);
			if ($ban) {
				$ip = ip2cidr($ban['ip_from'], $ban['ip_to']);
				deleteBan($_GET['lift']);
				modLog('Ban record lifted for ' . $ip);
				$text = 'Ban record lifted for ' . $ip;
			}
		}
		die(managePage(($text ? manageInfo($text) . PHP_EOL : '') . buildBansPage(), 'bans'));
	}

	/* --------[ Show the passcodes form ]-------- */

	if (ATOM_PASSCODES_ENABLED && isset($_GET['passcodes']) && !$isJanitor) {
		if ($_GET['passcodes'] == 'new') {
			die(managePage(buildPasscodesPage(), 'passcode_new'));
		} else if ($_GET['passcodes'] == 'manage') {
			die(managePage(buildPasscodesPage(), 'passcode_manage'));
		}
	}

	/* --------[ Issue a new passcode ]-------- */

	if (ATOM_PASSCODES_ENABLED && isset($_GET['issuepasscode']) && $isAdmin) {
		if (!empty($_POST['expires'])) {
			die(managePage(manageInfo('New passcode issued:<br>' .
				insertPass($_POST['expires'], $_POST['meta'], $_POST['meta_admin']))));
		}
	}

	/* --------[ Manage a passcode ]-------- */

	if (ATOM_PASSCODES_ENABLED && isset($_GET['managepasscode']) && !$isJanitor) {
		changePass(
			$_POST['id'],
			$_POST['meta'],
			!empty($_POST['expires']) ? strtotime($_POST['expires']) : 0,
			$_POST['block_till'] ? strtotime($_POST['block_till']) : 0,
			$_POST['block_reason']);
		die(managePage(manageInfo('Passcode ' . $_POST['id'] . ' has been changed.')));
	}

	/* --------[ Show the moderation log ]-------- */

	if (isset($_GET['modlog'])) {
		$fromtime = 0;
		$totime = 0;
		if (isset($_POST['from'], $_POST['to'])) {
			if (($fromtime = strtotime($_POST['from'])) === false ||
				($totime = strtotime($_POST['to'])) === false
			) {
				fancyDie('Wrong time format. Use yyyy-mm-dd format.');
			}
			$fromtime = (int)strtotime($_POST['from']);
			$totime = (int)strtotime($_POST['to']);
		}
		die(managePage(buildModLogForm() . buildModLogTable(true, $fromtime, $totime)));
	}
	
	/* --------[ View all posts from ip ]-------- */

	if (isset($_GET['ipinfo'])) {
		$ip = $_GET['ipinfo'];
		if ($ip == 'manage') {
			die(managePage(buildUserInfoForm(), 'ipinfo'));
		}
		die(managePage(buildUserInfoPage($ip, getPostsByIP($ip))));
	}

	/* --------[ Delete a post or thread ]-------- */

	if (isset($_GET['delete'])) {
		$post = getPost($_GET['delete']);
		if (!$post) {
			fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
		}
		$id = $post['id'];
		deletePost($id);
		if (isOp($post)) {
			modLog('Deleted thread №' . $id . '.');
		} else {
			$thrId = $post['parent'];
			rebuildThreadPage($thrId);
			modLog('Deleted post №' . $id . ' in thread №' . $thrId . '.');
		}
		rebuildIndexPages();
		die(managePage(manageInfo('Post №' . $id . ' has been deleted.')));
	}

	/* --------[ Delete all posts from ip ]-------- */

	if (isset($_GET['delall'])) {
		$ip = $_GET['delall'];
		if (isset($_GET['thrid'])) {
			$thrid = $_GET['thrid'];
			die(managePage(manageInfo('Posts from ip ' . $ip . ' in thread №' . $thrid .
				' have been deleted: №' . deleteAllPosts($ip, $thrid) . '.')));
		} else {
			die(managePage(manageInfo('Posts from ip ' . $ip . ' have been deleted: №' .
				deleteAllPosts($ip, NULL) . '.')));
		}
	}

	/* --------[ Delete/hide images ]-------- */

	if (isset($_GET['delete-img'], $_GET['delete-img-mod'], $_GET['action'])) {
		$post = getPost($_GET['delete-img']);
		if (!$post) {
			fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
		}
		$id = $post['id'];
		$thrId = getThreadId($post);
		if ($_GET['action'] == 'delete') {
			deletePostImages($post, $_GET['delete-img-mod']);
			rebuildThread($thrId);
			modLog('Deleted image(s) of ' . (isOp($post) ? 'OP-post in thread №' . $id :
				'post №' . $id . ' in thread №' . $thrId) . '.');
			die(managePage(manageInfo('Selected images from post №' . $id . ' have been deleted.')));
		}
		if ($_GET['action'] == 'hide') {
			hidePostImages($post, $_GET['delete-img-mod']);
			rebuildThread($thrId);
			modLog('Hidden thumbnail(s) of ' . (isOp($post) ? 'OP-post in thread №' . $id :
				'post №' . $id . ' in thread №' . $thrId) . '.');
			die(managePage(manageInfo('Thumbnails for selected images from post №' . $id .
				' have been changed.')));
		}
	}

	/* --------[ Edit a message in post ]-------- */

	if (isset($_GET['editpost'], $_POST['message'])) {
		$post = getPost($_GET['editpost']);
		if (!$post) {
			fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
		}
		$id = $post['id'];
		$thrId = getThreadId($post);
		editPostMessage($id, $_POST['message'] . '<br><br><span style="color: purple;">Message edited: ' .
			date('d.m.y D H:i:s', time()) . '</span>');
		rebuildThread($thrId);
		modLog('Edited message of ' . (isOp($post) ? 'OP-post in thread №' . $id :
			'post №' . $id . ' in thread №' . $thrId) . '.');
		die(managePage(manageInfo('Message in post №' . $id . ' have been changed.')));
	}

	/* --------[ Approve a post if premoderation enabled (see ATOM_REQMOD) ]-------- */

	if (!empty($_GET['approve']) && is_numeric($_GET['approve'])) {
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
		die(managePage(manageInfo('Post №' . $id . ' has been approved.')));
	}

	/* --------[ Show the post moderation form ]-------- */

	if (isset($_GET['moderate'])) {
		if ($_GET['moderate'] > 0) {
			$post = getPost($_GET['moderate']);
			if (!$post) {
				fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
			}
			die(managePage(buildModeratePostPage($post)));
		}
		die(managePage(buildModeratePostForm(), 'moderate'));
	}

	/* --------[ Stick a thread ]-------- */

	if (isset($_GET['sticky'], $_GET['setsticky'])) {
		if ($_GET['sticky'] <= 0) {
			fancyDie('Form data was lost. Please go back and try again.');
		}
		$post = getPost($_GET['sticky']);
		if (!$post || !isOp($post)) {
			fancyDie('Sorry, there doesn\'t appear to be a thread with that ID.');
		}
		$isStickied = (int)$_GET['setsticky'];
		$id = $post['id'];
		toggleStickyThread($id, $isStickied);
		rebuildThread($id);
		$stickiedText = $isStickied == 1 ? 'stickied' : 'un-stickied';
		modLog(ucfirst($stickiedText) . ' thread №' . $id . '.');
		die(managePage(manageInfo('Thread №' . $id . ' has been ' . $stickiedText . '.')));
	}

	/* --------[ Lock a thread ]--------= */

	if (isset($_GET['locked'], $_GET['setlocked'])) {
		if ($_GET['locked'] <= 0) {
			fancyDie('Form data was lost. Please go back and try again.');
		}
		$post = getPost($_GET['locked']);
		if (!$post || !isOp($post)) {
			fancyDie('Sorry, there doesn\'t appear to be a thread with that ID.');
		}
		$isLocked = (int)$_GET['setlocked'];
		$id = $post['id'];
		toggleLockThread($id, $isLocked);
		rebuildThread($id);
		$lockedText = $isLocked == 1 ? 'locked' : 'un-locked';
		modLog(ucfirst($lockedText) . ' thread №' . $id . '.');
		die(managePage(manageInfo('Thread №' . $id . ' has been ' . $lockedText . '.')));
	}

	/* --------[ Make an endless thread ]-------- */

	if (isset($_GET['endless'], $_GET['setendless'])) {
		if ($_GET['endless'] <= 0) {
			fancyDie('Form data was lost. Please go back and try again.');
		}
		$post = getPost($_GET['endless']);
		if (!$post || !isOp($post)) {
			fancyDie('Sorry, there doesn\'t appear to be a thread with that ID.');
		}
		$isEndless = (int)$_GET['setendless'];
		$id = $post['id'];
		toggleEndlessThread($id, $isEndless);
		rebuildThread($id);
		$endlessText = $isEndless == 1 ? 'made endless' : 'made non-endless';
		modLog(ucfirst($endlessText) . ' thread №' . $id . '.');
		die(managePage(manageInfo('Thread №' . $id . ' has been ' . $endlessText . '.')));
	}

	/* --------[ Raw post sending ]-------- */

	if (isset($_GET['staffpost'])) {
		die(managePage(buildPostForm(0, true), 'staffpost'));
	}

	/* --------[ Log out ]-------- */

	if (isset($_GET['logout'])) {
		if (!$isAdmin) {
			modLog(ucfirst($_SESSION['atom_role']) . ' logout', '1', 'BlueViolet');
		};
		deleteSession();
		header('Location: ?manage');
	}

	/* --------[ Show status for posts ]-------- */

	die(managePage(buildStatusPage()));
}

/* ==[ Posting requests ]================================================================================== */

function postingRequest() {
	/* --------[ Post submission check ]-------- */

	global $loginStatus, $atom_banned_countries, $atom_embeds, $atom_hidefields, $atom_hidefieldsop,
		$atom_replace_text, $atom_replace_rand, $atom_uploads;
	$hasAccess = $loginStatus != 'disabled';
	$passcode = checkPasscode(true);
	$validPasscode = $passcode[0];

	if (!$hasAccess) {
		// Checking for captcha if no passcode
		if (!$validPasscode) {
			checkCaptcha();
		}

		// Check for banned countries
		$ip = $_SERVER['REMOTE_ADDR'];
		if (ATOM_GEOIP && !empty($atom_banned_countries)) {
			$geoipReader = ATOM_GEOIP == 'geoip2' ?
				new GeoIp2\Database\Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb') : NULL;
			$countryCode = getCountryCode($ip, $geoipReader);
			if (in_array($countryCode, $atom_banned_countries)) {
				fancyDie('Posting from your country is prohibited: ' . $countryCode);
			}
		}

		// Check for dirty ip
		if (defined('ATOM_IPLOOKUPS_KEY') && ATOM_IPLOOKUPS_KEY && !$validPasscode && isDirtyIP($ip)) {
			fancyDie('Your IP address ' . $ip .
				' is not allowed to post due to abuse (proxy, Tor, VPN, VPS).');
		}

		// Check for ban
		$ban = banByIP($ip);
		if ($ban) {
			checkForBans($ip, $ban, $validPasscode, false, false);
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
	if (isset($_POST['parent']) && $_POST['parent'] != ATOM_NEWTHREAD) {
		if (!isThreadExists($_POST['parent'])) {
			fancyDie('Invalid parent thread ID supplied, unable to create post.');
		}
		$parentId = $_POST['parent'];
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
	$post['pass'] = $validPasscode;
	$isStaffPost = isStaffPost();

	// Get name/tripcode
	if ($isStaffPost || !in_array('name', $hideFields)) {
		$postName = $_POST['name'];
		if (preg_match('/(#|!)(.*)/', $postName, $regs)) {
			$cap = $regs[2];
			if (function_exists('mb_convert_encoding')) {
				$recodedCap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
				if ($recodedCap != '') {
					$cap = $recodedCap;
				}
			}
			if (strpos($postName, '#') === false) {
				$capDelimiter = '!';
			} elseif (strpos($postName, '!') === false) {
				$capDelimiter = '#';
			} else {
				$capDelimiter = strpos($postName, '#') < strpos($postName, '!') ? '#' : '!';
			}
			if (preg_match('/(.*)(' . $capDelimiter . ')(.*)/', $cap, $regsSecure)) {
				$cap = $regsSecure[1];
				$capSecure = $regsSecure[3];
				$isSecureTrip = true;
			} else {
				$isSecureTrip = false;
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
			if ($isSecureTrip) {
				if ($cap != '') {
					$postTripcode .= '!';
				}
				$postTripcode .= '!' . substr(md5($capSecure . ATOM_TRIPSEED), 2, 10);
			}
			$post['name'] = preg_replace('/(' . $capDelimiter . ')(.*)/', '', $postName);
			$post['tripcode'] = $postTripcode;
		} else {
			$post['name'] = $postName;
			$post['tripcode'] = '';
		}
		$post['name'] = escapeHTML(mb_substr($post['name'], 0, 75));
	}

	// Get email
	if ($isStaffPost || !in_array('email', $hideFields)) {
		$post['email'] = escapeHTML(str_replace('"', '&quot;', substr($_POST['email'], 0, 75)));
	}

	// Get subject
	if ($isStaffPost || !in_array('subject', $hideFields)) {
		$post['subject'] = escapeHTML(mb_substr($_POST['subject'], 0, 100));
	}

	// Get message
	if (!in_array('message', $hideFields)) {
		$post['message'] = $_POST['message'];
	}
	// Text formatting
	if ($post['message'] && !$isStaffPost) {
		// Message length limit
		$messageLen = mb_strlen($post['message']);
		if ($messageLen > ATOM_POSTING_MAXLEN) {
			fancyDie('Your message is too long - ' . $messageLen .
				' (maximum ' . ATOM_POSTING_MAXLEN . ' characters).');
		}

		$msg = escapeHTML(rtrim($post['message']));

		// [code]Block code[/code], `Inline code`
		// Temporarily cut out the code (protection from processing)
		$codePrefix = ":::ATOMCODE" . bin2hex(random_bytes(2)) . ":::";
		$codeBlocks = [];
		$msg = preg_replace_callback('/\[code\](?:\r?\n)?([\s\S]*?)\[\/code\]|`([^`\r\n]+)`/iu',
			function($m) use (&$codeBlocks, $codePrefix) {
				$codeBlocksCount = count($codeBlocks);
				if ($codeBlocksCount > ATOM_POSTING_MAXCODE) {
					fancyDie('Too many code blocks in one message - ' . $codeBlocksCount .
						' (maximum ' . ATOM_POSTING_MAXCODE . ').');
				}
				$isBlock = !empty($m[1]); // $m[1] is [code], $m[2] is `inline`
				$content = $isBlock ? $m[1] : $m[2];
				if ($content === null) {
					$content = '';
				}
				// Replacing line breaks with temporary tags
				$content = str_replace(["\r\n", "\r", "\n"], '@!@LINE@!@', $content);
				// Save to a temporary array, X at the end as a stopper
				$id = $codePrefix . count($codeBlocks) . ":::";
				$codeBlocks[$id] = $isBlock ? '<pre>' . $content . '</pre>' : '<code>' . $content . '</code>';
				return $id;
			}, $msg);

		// Forced wordbreaks for long words (before the main markings)
		if (ATOM_WORDBREAK > 0) {
			$msg = preg_replace('/([^\s]{' . ATOM_WORDBREAK . '})(?=[^\s])/u',
				'$1' . ATOM_WORDBREAK_IDENTIFIER, $msg);
		}

		// Post >>links
		$refLinkCount = 0;
		$msg = preg_replace_callback('/&gt;&gt;([0-9]+)/u', function($m) use (&$refLinkCount) {
			if (++$refLinkCount > ATOM_POSTING_MAXLINKS) {
				fancyDie('Too many references to other posts - ' . ++$refLinkCount .
					' (maximum ' . ATOM_POSTING_MAXLINKS . ').');
			}
			static $cache = [];
			$id = $m[1];
			if (!isset($cache[$id])) {
				$cache[$id] = getPost($id);
			}
			if ($p = $cache[$id]) {
				return sprintf('<a class="%s" href="/%s/res/%s.html#%s">%s</a>', 
					isOp($p) ? 'refop' : 'refreply', ATOM_BOARD, getThreadId($p), $id, $m[0]);
			}
			return $m[0];
		}, $msg);

		// Inline markdown and multiline BBcode
		$rules = [
			'/\*\*([^\*\r\n]+)\*\*/u'    => '<b>$1</b>', // **Bold**
			'/\*([^\*\r\n]+)\*/u'        => '<i>$1</i>', // *Italic*
			'/__([^_\r\n]+)__/u'         => '<span class="underline">$1</span>', //__Underline__
			'/~~([^~\r\n]+)~~/u'         => '<del>$1</del>', // ~~Strike~~
			'/%%([^%\r\n]+)%%/u'         => '<span class="spoiler">$1</span>', // %%Spoiler%%
			'/^(&gt;.*?)\r?\n?$/mu'      => '<span class="unkfunc">$1</span>', // > Quotes
			'/\[b\]([\s\S]*?)\[\/b\]/iu' => '<b>$1</b>', // [b]Bold[/b]
			'/\[i\]([\s\S]*?)\[\/i\]/iu' => '<i>$1</i>', // [i]Italic[/i]
			'/\[u\]([\s\S]*?)\[\/u\]/iu' => '<span class="underline">$1</span>', // [u]Underline[/u]
			'/\[s\]([\s\S]*?)\[\/s\]/iu' => '<del>$1</del>', // [s]Strike[/s]
			'/\[spoiler\]([\s\S]*?)\[\/spoiler\]/iu' =>
				'<span class="spoiler">$1</span>', // [spoiler]Spoier[/spoiler]
		];
		$msg = preg_replace(array_keys($rules), array_values($rules), $msg);

		// [Markdown links](url) and hyperlinks (with protection from javascript:)
		$urlCount = 0;
		$msg = preg_replace_callback(
			'/\[(.*?)\]\((https?:\/\/[^\s\)]+)\)|((?:f|ht)tps?:\/\/[^\s<\[]+?)(?=[,.?!:;)]?(?:\s|$|<|\[))/iu',
			function($m) use (&$urlCount) {
				if (++$urlCount > ATOM_POSTING_MAXURL) {
					fancyDie('Too many external links - ' . ++$urlCount .
						' (maximum ' . ATOM_POSTING_MAXURL . ').');
				}
				return !empty($m[3]) ? 
					'<a href="'.$m[3].'" target="_blank">'.$m[3].'</a>' : // 3=hyperlink
					'<a href="'.$m[2].'" target="_blank">'.$m[1].'</a>'; // 2=markdown URL, 1=markdown text
			}, $msg);

		// Line breaks
		$msg = str_replace(["\r\n", "\r", "\n"], '<br>', $msg);
		// Code: Restoring saved blocks back
		if (!empty($codeBlocks)) {
			$msg = strtr($msg, $codeBlocks);
		}
		// Code: Recovering line breaks in code blocks
		$msg = str_replace('@!@LINE@!@', "\r\n", $msg);

		// Handling wordbreaks in links
		if (ATOM_WORDBREAK > 0 && str_contains($msg, ATOM_WORDBREAK_IDENTIFIER)) {
			$msg = preg_replace_callback('/<a[^>]+>.*?<\/a>/su',
				fn($m) => str_replace(ATOM_WORDBREAK_IDENTIFIER, '', $m[0]), $msg);
			$msg = str_replace(ATOM_WORDBREAK_IDENTIFIER, '<br>', $msg);
		}

		// Text replacement from settings.php
		if (!empty($atom_replace_text)) {
			$callbacks = [];
			foreach ($atom_replace_text as $pattern => $replacement) {
				$callbacks[$pattern] = fn($m) => 
					'<span class="autoreplace" style="color: hsl(' . mt_rand(0, 360) . ', 90%, 50%)">' .
					preg_replace_callback('/\$(\d+)/', fn($i) => $m[$i[1]] ?? '', $replacement) . '</span>';
			}
			$msg = preg_replace_callback_array($callbacks, $msg);
		}
		if (!empty($atom_replace_rand)) {
			foreach ($atom_replace_rand as $pattern => $replacements) {
				$msg = preg_replace_callback($pattern, fn() => 
					'<span class="autoreplace" style="color: hsl(' . mt_rand(0, 360) . ', 90%, 50%)">' .
					$replacements[array_rand($replacements)] . '</span>', $msg);
			}
		}
		$post['message'] = $msg;
	}

	// Get password
	if ($isStaffPost || !in_array('password', $hideFields)) {
		$post['password'] = $_POST['password'] != '' ? md5(md5($_POST['password'])) : '';
	}

	// Get Nameblock
	$postName = $post['name'];
	$postTripcode = $post['tripcode'];
	$postEmail = $post['email'];
	$postNameBlock =
		($validPasscode && $passcode[1] ? '<img class="poster-achievement" height="18"' .
			' title="Donator" src="/' . ATOM_BOARD . '/icons/donator.png"> ' : '') .
		'<span class="postername' . ($hasAccess && $postName ?
			($loginStatus == 'admin' ? ' postername-admin' : ' postername-mod') : '') . '">' .
		(!$postName && !$postTripcode ? ATOM_POSTERNAME : $postName) .
		($postTripcode != '' ? '</span><span class="postertrip">!' . $postTripcode : '') . '</span>';
	if ($hasAccess && ($postName || $postTripcode)) {
		switch($loginStatus) {
		case 'admin': $postNameBlock .= ' <span class="postername-admin">## Admin</span>'; break;
		case 'janitor': $postNameBlock .= ' <span class="postername-mod">## Janitor</span>'; break;
		case 'moderator': $postNameBlock .= ' <span class="postername-mod">## Mod</span>'; break;
		}
	} else if (ATOM_UNIQUEID) {
		$ip = $post['ip'];
		$ipHash = substr(md5($ip . (int)$post['parent'] . ATOM_TRIPSEED), 0, 8);
		$ipHashInt = (int)hexdec('0x' . $ipHash);
		$uid = '';
		if (ATOM_UNIQUENAME) {
			global $firstNames, $lastNames;
			$firstNamesLen = count($firstNames);
			$lastNamesLen = count($lastNames);
			$firstName = '';
			$lastName = '';

			// Generate firstname by IP
			if ($firstNamesLen) {
				srand($ipHashInt);
				$firstName = $firstNames[rand() % $firstNamesLen];
			}

			// Generate lastname by subnet /20
			if ($lastNamesLen) {
				$subnet = long2ip(cidr2ip($ip . '/20')[0]);
				srand((int)hexdec('0x' . substr(md5($subnet . (int)$post['parent'] . ATOM_TRIPSEED), 0, 8)));
				$lastName = $lastNames[rand() % $lastNamesLen];
			}

			$uid = $firstName . ($firstName && $lastName ? ' ' : '') . $lastName;
		}
		$hue = 2 * pi() * ($ipHashInt / 0xFFFFFF);
		$saturation = '100%';
		$lightness = '25%';
		$postNameBlock .= ' <span class="posteruid" data-uid="' . $ipHash . '" style="color: hsl(' .
			$hue . ', ' . $saturation . ', ' . $lightness . ');">' . ($uid ? $uid : $ipHash) . '</span>';
	}
	$lowEmail = strtolower($postEmail);
	if ($postEmail != '' && $lowEmail != 'noko') {
		$postNameBlock = '<a href="mailto:' . $postEmail . '"' .
			($lowEmail == 'sage' ? ' class="sage"' : '') . '>' . $postNameBlock . '</a>';
	}
	$tt = time();
	$postDateBlock = '<span class="posterdate" data-timestamp="' . $tt . '">' .
		date('d.m.y D H:i:s', $tt) . '</span>';
	$post['nameblock'] = $postNameBlock . ' ' . $postDateBlock;

	/* --------[ Embed URL upload ]-------- */

	if (isset($_POST['embed']) &&
		trim($_POST['embed']) != '' &&
		($isStaffPost || !in_array('embed', $hideFields))
	) {
		if (isset($_FILES['file']) && $_FILES['file']['name'][0] != '') {
			fancyDie('Embedding a URL and uploading a file at the same time is not supported.');
		}
		list($service, $embed) = getEmbed(trim($_POST['embed']));
		if (empty($embed) || !isset($embed['html'], $embed['title'], $embed['thumbnail_url'])) {
			fancyDie('Invalid embed URL.<br>Only ' .
				(implode(' / ', array_keys($atom_embeds))) . ' URLs are supported.');
		}
		$post['file0_hex'] = $service;
		$fileName = time() . substr(microtime(), 2, 3) . '-0';
		$fileLocation = 'thumb/' . $fileName;
		file_put_contents($fileLocation, url_get_contents($embed['thumbnail_url']));
		$fileInfo = getimagesize($fileLocation);
		$post['image0_width'] = $fileInfo[0];
		$post['image0_height'] = $fileInfo[1];
		switch(mime_content_type($fileLocation)) {
		case 'image/avif': $post['thumb0'] = $fileName . '.avif'; break;
		case 'image/gif': $post['thumb0'] = $fileName . '.gif'; break;
		case 'image/jpeg': $post['thumb0'] = $fileName . '.jpg'; break;
		case 'image/png': $post['thumb0'] = $fileName . '.png'; break;
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
		if ($service == 'YouTube.com') {
			$embedHtml = preg_replace('/width="\d+"/', 'width="' . $fileInfo[0] . '"', $embedHtml);
			$embedHtml = preg_replace('/height="\d+"/', 'height="' . $fileInfo[1] . '"', $embedHtml);
		}
		$post['file0'] = str_ireplace(['src="https://', 'src="http://'], 'src="//', $embedHtml);
	}

	/* --------[ Images upload ]-------- */

	elseif (isset($_FILES['file']) && $_FILES['file']['name'][0] != '' &&
		($isStaffPost || !in_array('file', $hideFields))
	) {
		$fileIdx = 0;
		$filesCount = 0;
		foreach ($_FILES['file']['error'] as $index => $error) {
			$fileIdx++;
			if ($filesCount >= ATOM_FILES_COUNT || $fileIdx > 1 && $error == UPLOAD_ERR_NO_FILE) {
				continue;
			}

			$fileIdxTxt = 'File №' . $fileIdx;
			$fileSizeErrorText = $fileIdxTxt . ' is larger than ' . ATOM_FILE_MAXKBDESC . '' .
				(ATOM_PASSCODES_ENABLED ? ' (' . ATOM_FILE_MAXKBDESC_PASS . ' for passcode users).' : '.');

			// Check for upload errors
			switch ($error) {
			case UPLOAD_ERR_OK: break;
			case UPLOAD_ERR_FORM_SIZE: fancyDie($fileSizeErrorText); break;
			case UPLOAD_ERR_INI_SIZE:
				fancyDie($fileIdxTxt . ' error: exceeds the upload_max_filesize directive (' .
					ini_get('upload_max_filesize') . ').');
				break;
			case UPLOAD_ERR_PARTIAL: fancyDie($fileIdxTxt . ' error: was only partially uploaded.'); break;
			case UPLOAD_ERR_NO_FILE: fancyDie($fileIdxTxt . ' error: no file was uploaded.'); break;
			case UPLOAD_ERR_NO_TMP_DIR: fancyDie($fileIdxTxt . ' error: missing a temporary folder.'); break;
			case UPLOAD_ERR_CANT_WRITE: fancyDie($fileIdxTxt . ' error: failed to write to disk.'); break;
			case UPLOAD_ERR_EXTENSION: fancyDie($fileIdxTxt . ' error: file extension error.'); break;
			default: fancyDie($fileIdxTxt . ' error: unable to save the uploaded file.');
			}
			$file = $_FILES['file']['tmp_name'][$index];
			if (!is_file($file) || !is_readable($file)) {
				fancyDie($fileIdxTxt . ' error: file transfer failure. Please retry the submission.');
			}

			// Check for bytes size restriction (but it only applies to passcode users)
			if (ATOM_PASSCODES_ENABLED && $validPasscode) {
				if (ATOM_FILE_MAXKB_PASS > 0 && filesize($file) > ATOM_FILE_MAXKB_PASS * 1024) {
					fancyDie($fileSizeErrorText);
				}
			} else {
				if (ATOM_FILE_MAXKB > 0 && filesize($file) > ATOM_FILE_MAXKB * 1024) {
					fancyDie($fileSizeErrorText);
				}
			}

			// Get post image fields
			$filePath = pathinfo($_FILES['file']['name'][$index]);
			$post['file' . $index . '_original'] =
				trim(htmlentities(mb_substr($filePath['filename'], 0, 200) .
				'.' . $filePath['extension'], ENT_QUOTES, 'UTF-8'));
			$post['file' . $index . '_hex'] = md5_file($file);
			$post['file' . $index . '_size'] = $_FILES['file']['size'][$index];

			// Convert file bytes
			$fileBytes = $post['file' . $index . '_size'];
			$bytesLen = strlen($fileBytes);
			if ($bytesLen < 4) {
				$fileBytes = sprintf("%dB", $fileBytes);
			} elseif ($bytesLen <= 6) {
				$fileBytes = sprintf("%0.2fKB", $fileBytes / 1024);
			} elseif ($bytesLen <= 9) {
				$fileBytes = sprintf("%0.2fMB", $fileBytes / 1048576);
			} else {
				$fileBytes = sprintf("%0.2fGB", $fileBytes / 1073741824);
			}
			$post['file' . $index . '_size_formatted'] = $fileBytes;

			// Check for file duplicates
			if (ATOM_FILE_DUPLICATE === false) {
				$hex = $post['file' . $index . '_hex'];
				$hexMatch = getPostsByImageHex($hex);
				if ($hexMatch) {
					fancyDie('Duplicate ' . $fileIdxTxt .
						' uploaded.<br>That file has already been posted <a href="res/' .
						getThreadId($hexMatch) . '.html#' . $hexMatch['id'] . '">here</a>.');
				}
			}

			// Check for supported file types
			$fileMimeSplit = explode(' ', trim(mime_content_type($file)));
			if (count($fileMimeSplit) > 0) {
				$fileMime = strtolower(array_pop($fileMimeSplit));
			} else {
				if (!@getimagesize($file)) {
					fancyDie('Failed to read the MIME type and size of the uploaded ' .
						$fileIdxTxt . '.<br>' . 'Please retry the submission.');
				}
				$fileMime = mime_content_type($file);
			}
			if (empty($fileMime) || !isset($atom_uploads[$fileMime])) {
				fancyDie(supportedFileTypes());
			}

			// Generate file name and location
			$fileName = time() . substr(microtime(), 2, 3) . '-' . $index;
			$post['file' . $index] = $fileName . '.' . $atom_uploads[$fileMime][0];
			$fileLocation = 'src/' . $post['file' . $index];

			// Upload file
			if (!move_uploaded_file($file, $fileLocation)) {
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
				preg_match('/^%(\d+)%/',
					shell_exec('mediainfo --Inform="Video;%%Width%%" ' . $fileLocation), $match);
				$videoWidth = $post['image' . $index . '_width'] = max(0, (int)$match[1]);
				preg_match('/^%(\d+)%/',
					shell_exec('mediainfo --Inform="Video;%%Height%%" ' . $fileLocation), $match);
				$videoHeight = $post['image' . $index . '_height'] = max(0, (int)$match[1]);
				if ($videoWidth > 0 && $videoHeight > 0) {
					list($thumbMaxWidth, $thumbMaxHeight) = getThumbnailDimensions($post, $index);
					$post['thumb' . $index] = $fileName . 's.jpg';
					shell_exec('ffmpegthumbnailer -t 1 -s ' . max($thumbMaxWidth, $thumbMaxHeight) .
						' -i ' . $fileLocation . ' -o thumb/' . $post['thumb' . $index]);
					$thumbInfo = getimagesize('thumb/' . $post['thumb' . $index]);
					$post['thumb' . $index . '_width'] = $thumbInfo[0];
					$post['thumb' . $index . '_height'] = $thumbInfo[1];
					if ($post['thumb' . $index . '_width'] <= 0 || $videoWidth > 32766 ||
						$post['thumb' . $index . '_height'] <= 0 || $videoHeight > 32766
					) {
						@unlink($fileLocation);
						@unlink('thumb/' . $post['thumb' . $index]);
						fancyDie('Sorry, your video ' . $fileIdxTxt . ' appears to be corrupt.');
					}
					if (ATOM_VIDEO_OVERLAY) {
						addVideoOverlay('thumb/' . $post['thumb' . $index]);
					}
				}
				$duration = (int)shell_exec('mediainfo --Inform="General;%Duration%" ' . $fileLocation);
				if ($duration > 0) {
					$mins = floor(round($duration / 1000) / 60);
					$secs = str_pad(floor(round($duration / 1000) % 60), 2, '0', STR_PAD_LEFT);
					$post['file' . $index . '_original'] = $mins . ':' . $secs .
						($post['file' . $index . '_original'] != '' ?
							(', ' . $post['file' . $index . '_original']) : '');
				}
			}

			// Get image info
			elseif (in_array($fileMime, [
				'image/avif',
				'image/gif',
				'image/jpeg',
				'image/pjpeg',
				'image/png',
				'image/webp'
			])) {
				$fileInfo = @getimagesize($fileLocation);
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
			}

			// Get default image thumbnail
			elseif (in_array($fileMime, [
				'image/avif',
				'image/gif',
				'image/jpeg',
				'image/pjpeg',
				'image/png',
				'image/webp'
			])) {
				$post['thumb' . $index] = $fileName . 's.' . $atom_uploads[$fileMime][0];
				list($thumbMaxWidth, $thumbMaxHeight) = getThumbnailDimensions($post, $index);
				if (!createThumbnail(
					$fileLocation,
					'thumb/' . $post['thumb' . $index],
					$thumbMaxWidth,
					$thumbMaxHeight
				)) {
					@unlink($fileLocation);
					fancyDie('Could not create thumbnail for ' . $fileIdxTxt . '.');
				}
			}

			// Get thumbnail info
			if ($post['thumb' . $index] != '') {
				$thumbInfo = @getimagesize('thumb/' . $post['thumb' . $index]);
				$post['thumb' . $index . '_width'] = $thumbInfo[0];
				$post['thumb' . $index . '_height'] = $thumbInfo[1];
			}

			$filesCount++;
		}
	}

	/* --------[ No file upload ]-------- */

	if ($post['file0'] == '') {
		$allowed = '';
		if (!empty($atom_uploads) && ($isStaffPost || !in_array('file', $hideFields))) {
			$allowed = 'file';
		}
		if (!empty($atom_embeds) && ($isStaffPost || !in_array('embed', $hideFields))) {
			if ($allowed != '') {
				$allowed .= ' or ';
			}
			$allowed .= 'embed URL';
		}
		if (isOp($post) && $allowed != '' && !ATOM_NOFILEOK) {
			fancyDie('A ' . $allowed . ' is required to start a thread.');
		}
		if (!$isStaffPost && str_replace('<br>', '', $post['message']) == '') {
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
		if (ATOM_POSTING_REDIRECT || strtolower($post['email']) == 'noko') {
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

/* ==[ Banned request ]==================================================================================== */

function checkForBans($ip, $ban, $validPasscode, $isCloseWarning, $isJson) {
	$directBan = $ban['ip_from'] == $ban['ip_to'];
	// Range bans do not affect passcode users
	if (!$directBan && $validPasscode) {
		return;
	}
	$message = '';
	$reason = $ban['reason'] == '' ? '' : '<br><br>Reason: ' . $ban['reason'];
	if ($ban['expire'] == 1) {
		if ($isCloseWarning) {
			deleteBan($ban['id']);
			$message = 'Your IP address ' . $ip . ' has been issued a warning:<br><br>' . $ban['reason'] .
				'<br><br>Please make sure you have read and understood the rules.<br>
				This warning has been automatically removed, you may continue posting now.';
		} else {
			$message = 'Your IP address ' . $ip . ' has been issued a warning.<br>To continue posting,' .
				' please read <a href="/' . ATOM_BOARD . '/imgboard.php?banned">this page</a>.' . $reason;
		}
	} else if ($ban['expire'] == 0 || $ban['expire'] > time()) {
		$message = 'Your IP address ' . $ip . ' has been banned from posting on this image board. ' . (
			$ban['expire'] > 0 ? '<br>This ban will expire ' . date('d.m.Y D H:i:s', $ban['expire']) :
				'<br>This ban is permanent and will not expire.' .
				(!$directBan ? '<br>This is a range ban (affects a whole subnet).' : '') .
				(ATOM_PASSCODES_ENABLED && !$directBan ? '<br><br><a href="/' . ATOM_BOARD .
					'/imgboard.php?passcode">Passcode users</a> are not affected by subnet bans.' : '')
		) . $reason;
	} else {
		clearExpiredBans();
	}
	if ($message) {
		if ($isJson) {
			die('{ "result": "error", "message": "' . $message . '" }');
		} else {
			fancyDie($message);
		}
	}
}

function bannedRequest() {
	$ip = $_SERVER['REMOTE_ADDR'];
	$ban = banByIP($ip);
	if ($ban) {
		checkForBans($ip, $ban, checkPasscode(false)[0], true, false);
	} else {
		fancyDie('Your IP address ' . $ip . ' is not banned at this time.');
	}
}

/* ==[ Deletion request ]================================================================================== */

function deletionRequest() {
	global $loginStatus;
	if (!isset($_POST['delete'])) {
		fancyDie('Tick the box next to a post and click "Delete" to delete it.');
	}
	$post = getPost($_POST['delete']);
	if (!$post) {
		fancyDie('Sorry, an invalid post identifier was sent.<br>' .
			'Please go back, refresh the page, and try again.');
	}
	if ($loginStatus != 'disabled' && $_POST['password'] == '') {
		// Redirect to post moderation page
		die('<meta http-equiv="refresh" content="0;url=' . basename($_SERVER['PHP_SELF']) .
			'?manage&moderate=' . $_POST['delete'] . '">');
	} elseif ($post['password'] == '' || md5(md5($_POST['password'])) != $post['password']) {
		fancyDie('Invalid password.');
	}
	$id = $post['id'];
	deletePost($id);
	rebuildThread(getThreadId($post));
	fancyDie('Post №' . $id . ' has been deleted.');
}

/* ==[ Post report request ]=============================================================================== */

function reportRequest() {
	global $loginStatus;
	$isJson = isset($_GET['json']) && $_GET['json'] == '1';
	$ip = $_SERVER['REMOTE_ADDR'];
	$ban = banByIP($ip);
	if ($ban) {
		checkForBans($ip, $ban, checkPasscode(false)[0], false, $isJson);
	}

	// Check for dirty ip
	if (defined('ATOM_IPLOOKUPS_KEY') && ATOM_IPLOOKUPS_KEY && isDirtyIP($ip)) {
		$message = 'Your IP address ' . $ip .
			' is not allowed to report due to abuse (proxy, Tor, VPN, VPS).';
		if ($isJson) {
			die('{ "result": "error", "message": "' . $message . '" }');
		} else {
			fancyDie($message);
		}
	}

	if (isset($_GET['addreport'])) {
		if (!checkPasscode(false)[0]) {
			checkCaptcha();
		}
		$id = $_POST['id'];
		$report = insertReport($id, ATOM_BOARD, $ip, $_POST['reason']);
		if ($report) {
			if ($report == 'exists') {
				if ($isJson) {
					die('{ "result": "alreadysent" }');
				} else {
					fancyDie('You have already sent a report to post №' . $id . '!');
				}
			}
			if ($isJson) {
				die('{ "result": "ok" }');
			} else {
				fancyDie('Report to post №' . $id . ' successfully sent.');
			}
		}
		if ($isJson) {
			die('{ "result": "error" }');
		} else {
			fancyDie('An error occurred while sending the report.');
		}
	} else if ($loginStatus != 'disabled') {
		if (isset($_GET['deletereports'])) {
			$id = $_GET['id'];
			deleteReports($id);
			fancyDie('Post №' . $id . ' approved. All reports are closed.');
		} else if (isset($_GET['deleteallreports'])) {
			$reports = getAllReports();
			$reportsCount = count($reports);
			if ($reportsCount) {
				foreach ($reports as $report) {
					deleteReports($report['postnum']);
				}
				fancyDie('All reports on board /' . ATOM_BOARD . ' are closed.');
			}
		}
	}
}

/* ==[ Passcode requests ]================================================================================= */

function checkPasscode($showMessages) {
	if (!ATOM_PASSCODES_ENABLED || empty($_SESSION['passcode'])) {
		return [0];
	}
	$pass = passByID($_SESSION['passcode']);
	if (!$pass || isPassExpired($pass)) {
		clearPass();
		if ($showMessages) {
			fancyDie('Your passcode has expired. Please issue a new passcode to continue.');
		}
	}
	$passBlocked = isPassBlocked($pass);
	if ($passBlocked && $showMessages) {
		fancyDie('Your passcode has been blocked till ' . date('d.m.y D H:i:s', $pass['blocked_till']) .
			'. Reason: ' . $passBlocked . '. Please log in again after the block expires.');
	}
	$ip = $_SERVER['REMOTE_ADDR'];
	$checkTill = $pass['last_used'] + ATOM_PASSCODES_USE_LIMIT;
	if ($checkTill > time() && $pass['last_used_ip'] != $ip && $showMessages) {
		fancyDie('Your passcode has been used recently by another ip. Please wait till ' .
			date('d.m.y D H:i:s', $checkTill));
	}
	usePass($pass['id'], $ip); // Update passcode info (last used ip)
	return $pass['number'] ? [$pass['number'], str_contains($pass['meta'], '[donator]')] : [0];
}

function passcodeRequest() {
	// Check passcode entered in passcode login form
	if (isset($_POST['passcode'])) {
		$passId = $_POST['passcode'];
		$pass = passByID($passId);
		if (!$pass) {
			die(managePage('<div align="center"><b>Could not log in the provided passcode:</b><br>' .
				'<br>This passcode is not found in database.</div>'));
		}
		$blocked = isPassBlocked($pass);
		if (isPassExpired($pass)) {
			clearPass();
			die(managePage('<div align="center"><b>Could not log in the provided passcode:</b><br>' .
				'<br>This passcode has expired on ' . date('d.m.Y D H:i:s', $pass['expires']) . '.</div>'));
		} else if ($blocked) {
			clearPass();
			die(managePage('<div align="center"><b>Could not log in the provided passcode:</b><br>' .
				'<br>This passcode has been blocked till ' . date('d.m.y D H:i:s', $pass['blocked_till']) .
				'<br>Reason: ' . $blocked . '</div>'));
		}
		setcookie('passcode', '1', $pass['expires'], '/');
		$_SESSION['passcode'] = $passId;
		die(managePage(manageInfo('<b>You have logged in. You may post without entering the captcha.</b>' .
			'<br>This passcode will expire on ' . date('d.m.Y D H:i:s', $pass['expires']))));
	}

	// Check passcode status by imgboard.php?passcode&check
	if (isset($_GET['check'])) {
		if (isset($_SESSION['passcode'])) {
			$pass = passByID($_SESSION['passcode']);
			if ($pass && !isPassExpired($pass) && !isPassBlocked($pass)) {
				die('OK');
			}
		}
		http_response_code(403);
		die('INVALID');
	}

	// Logout from passcode
	if (isset($_GET['logout'])) {
		clearPass();
		die(managePage(manageInfo('You have been logged out.')));
	}

	// Check if passcode already has effect now
	if (isset($_SESSION['passcode'])) {
		$pass = passByID($_SESSION['passcode']);
		if ($pass && !isPassExpired($pass) && !isPassBlocked($pass)) {
			die(managePage(buildPasscodeLoginForm('valid', $pass)));
		}
	}

	// Show passcode login form
	die(managePage(buildPasscodeLoginForm('login'), 'passcode'));
}

/* ==[ Like request ]====================================================================================== */

function likeRequest() {
	$postNum = $_GET['like'];
	$result = toggleLike($postNum, $_SERVER['REMOTE_ADDR']);
	$post = getPost($postNum);
	$post['likes'] = $result;
	rebuildThread(getThreadId($post));
	die('{
		"status": "ok",
		"message": "' . (
			$result[0] ? 'Post №' . $postNum . ' has been liked!' :
			'The like to post №' . $postNum . ' has been cancelled!'
		) . '",
		"likes": ' . $result[1] . ' }');
}

/* ==[ Main ]============================================================================================== */

// Settings initialization
if (!file_exists('settings.php')) {
	fancyDie('Please copy the file settings.default.php to settings.php');
}
require 'settings.php';
if (ATOM_GEOIP == 'geoip2') {
	require 'vendor/autoload.php';
}
if (ATOM_TRIPSEED == '') {
	fancyDie('settings.php: ATOM_TRIPSEED must be configured.');
}
if (ATOM_CAPTCHA == 'recaptcha' && (ATOM_RECAPTCHA_SITE == '' || ATOM_RECAPTCHA_SECRET == '')) {
	fancyDie('settings.php: ATOM_RECAPTCHA_SITE and ATOM_RECAPTCHA_SECRET must be configured.');
}

// Check if directories are writable by the script
foreach (['res', 'src', 'thumb'] as $dir) {
	if (!is_writable($dir)) {
		fancyDie('Directory "' . $dir . '" can not be written to.<br>Please modify its permissions.');
	}
}

// Dynamic connection of PHP scripts
$incPath = __DIR__ . '/inc/';
$includes = [$incPath . 'defines.php', $incPath . 'functions.php', $incPath . 'html.php'];
if (in_array(ATOM_DBMODE, ['mysqli', 'pdo'])) {
	$includes[] = $incPath . 'database_' . ATOM_DBMODE . '.php';
} else {
	fancyDie('settings.php: Unknown database mode in ATOM_DBMODE specified.');
}
if (defined('ATOM_UNIQUENAME') && ATOM_UNIQUENAME) {
	$namesDir = $incPath . 'usernames/' . ATOM_UNIQUENAME . '/';
	$includes[] = $namesDir . 'firstnames.php';
	$includes[] = $namesDir . 'lastnames.php';
}
foreach ($includes as $file) {
	if (!file_exists($file)) {
		fancyDie('Critical file missing: ' . basename($file));
	}
	require_once $file;
}
if (ATOM_TIMEZONE != '') {
	date_default_timezone_set(ATOM_TIMEZONE);
}

// Check for login status [admin/moderator/janitor/disabled]
$loginStatus = checkLogin();

// Requests processing
if (isset($_GET['manage'])) {
	managementRequest();
}
if (isset($_GET['delete'])) { // Must be before postingRequest()
	deletionRequest();
}
if (array_intersect_key($_POST,
	array_flip(['name', 'email', 'subject', 'message', 'file', 'embed', 'password']))
) {
	postingRequest();
}
if (isset($_GET['banned'])) {
	bannedRequest();
}
if (isset($_GET['report'])) {
	reportRequest();
}
if (ATOM_PASSCODES_ENABLED && isset($_GET['passcode'])) {
	passcodeRequest();
}
if (isset($_GET['like'])) {
	likeRequest();
}
if (isset($_GET['ban_reasons'])) {
	header('Content-type: application/json');
	echo json_encode($atom_ban_reasons, JSON_UNESCAPED_UNICODE);
}

// Initialization of empty board 
if (!file_exists(ATOM_INDEX) || getThreadsCount() == 0) {
	rebuildIndexPages();
}
header('Location: ' . ATOM_INDEX, true, 307);
exit();
