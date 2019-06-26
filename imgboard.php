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
if (get_magic_quotes_gpc()) {
	foreach ($_GET as $key => $val) {
		$_GET[$key] = stripslashes($val);
	}
	foreach ($_POST as $key => $val) {
		$_POST[$key] = stripslashes($val);
	}
}
if (get_magic_quotes_runtime()) {
	set_magic_quotes_runtime(0);
}

function manageInfo($text) {
	return '<div class="manageinfo">' . $text . '</div>';
}

function fancyDie($message) {
	die('<head>
	<link rel="stylesheet" type="text/css" href="/' . TINYIB_BOARD . '/css/global.css">
</head>
<body align="center">
	<br>
	<div class="reply" style="display: inline-block; font-size: 1.25em;">' . $message . '</div>
	<br><br>
	- <a href="./">Click here to go back</a> -
</body>');
}

if (!file_exists('settings.php')) {
	fancyDie('Please copy the file settings.default.php to settings.php');
}
require 'settings.php';
if (TINYIB_TRIPSEED == '' || TINYIB_ADMINPASS == '') {
	fancyDie('settings.php: TINYIB_TRIPSEED and TINYIB_ADMINPASS must be configured.');
}
if (TINYIB_CAPTCHA === 'recaptcha' && (TINYIB_RECAPTCHA_SITE == '' || TINYIB_RECAPTCHA_SECRET == '')) {
	fancyDie('settings.php: TINYIB_RECAPTCHA_SITE and TINYIB_RECAPTCHA_SECRET must be configured.');
}

// Check directories are writable by the script
$writedirs = array('res', 'src', 'thumb');
if (TINYIB_DBMODE == 'flatfile') {
	$writedirs[] = 'inc/flatfile';
}
foreach ($writedirs as $dir) {
	if (!is_writable($dir)) {
		fancyDie('Directory "' . $dir . '" can not be written to.<br>Please modify its permissions.');
	}
}

$includes = array('inc/defines.php', 'inc/functions.php', 'inc/html.php');
if (in_array(TINYIB_DBMODE, array('flatfile', 'mysql', 'mysqli', 'sqlite', 'sqlite3', 'pdo'))) {
	$includes[] = 'inc/database_' . TINYIB_DBMODE . '.php';
} else {
	fancyDie('settings.php: Unknown database mode in TINYIB_DBMODE specified.');
}
foreach ($includes as $include) {
	include $include;
}
if (TINYIB_TIMEZONE != '') {
	date_default_timezone_set(TINYIB_TIMEZONE);
}
$redirect = true;

// Check if the request is to make a post
if (!isset($_GET['delete']) && !isset($_GET['manage']) && (
	isset($_POST['name']) ||
	isset($_POST['email']) ||
	isset($_POST['subject']) ||
	isset($_POST['message']) ||
	isset($_POST['file']) ||
	isset($_POST['embed']) ||
	isset($_POST['password']))
) {
	if (TINYIB_DBMIGRATE) {
		fancyDie('Posting is currently disabled.<br>Please try again in a few moments.');
	}
	list($loggedIn, $isAdmin) = manageCheckLogIn();
	$rawPost = isRawPost();
	$rawPostText = '';
	if (!$loggedIn) {
		checkCAPTCHA();
		checkBanned();
		checkMessageSize();
		checkFlood();
	}
	$post = newPost(setParent());
	if ($post['parent'] != TINYIB_NEWTHREAD && !$loggedIn) {
		$parentPost = postByID($post['parent']);
		if ($parentPost['email'] == TINYIB_LOCKTHR_COOKIE) {
			fancyDie('Posting in this thread is currently disabled.<br>Thread is locked.');
		}
	}
	$hideFields = $post['parent'] == TINYIB_NEWTHREAD ? $tinyib_hidefieldsop : $tinyib_hidefields;
	$post['ip'] = $_SERVER['REMOTE_ADDR'];
	if ($rawPost || !in_array('name', $hideFields)) {
		list($post['name'], $post['tripcode']) = nameAndTripcode($_POST['name']);
		$post['name'] = cleanString(substr($post['name'], 0, 75));
	}
	if ($rawPost || !in_array('email', $hideFields)) {
		$providedEmail = cleanString(str_replace('"', '&quot;', substr($_POST['email'], 0, 75)));
		$post['email'] = $providedEmail == TINYIB_LOCKTHR_COOKIE ? '' : $providedEmail;
	}
	if ($rawPost || !in_array('subject', $hideFields)) {
		$post['subject'] = cleanString(substr($_POST['subject'], 0, 75));
	}
	if ($rawPost || !in_array('message', $hideFields)) {
		$post['message'] = $_POST['message'];
		if ($rawPost) {
			// Treat message as raw HTML
			$rawPostText = $isAdmin ? ' <span style="color: red;">## Admin</span>' :
				' <span style="color: purple;">## Mod</span>';
		} else {
			$msg = cleanString(rtrim($post['message']));
			if (TINYIB_WORDBREAK > 0) {
				$msg = preg_replace(
					'/([^\s]{' . TINYIB_WORDBREAK . '})(?=[^\s])/',
					'$1' . TINYIB_WORDBREAK_IDENTIFIER,
					$msg);
			}
			// MARKUP
			// [code]Block code[/code]
			$msg = preg_replace_callback('/\[code\]\r?\n?([\s\S]*?)\r?\n?\[\/code\]/i',
				function($matches)
			{
				$m = $matches[1];
				$m = str_replace("\r\n", '@!@TINYIB_LINE_END@!@', $m);
				$m = str_replace("\r", '@!@TINYIB_LINE_END@!@', $m);
				$m = str_replace("\n", '@!@TINYIB_LINE_END@!@', $m);
				$m = str_replace('`', '&#96;', $m);
				$m = str_replace('<', '&lt;', $m);
				$m = preg_replace('/>|&gt;/', '@!@TINYIB_GT@!@', $m);
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
				$m = preg_replace('/>|&gt;/', '@!@TINYIB_GT@!@', $m);
				$m = str_replace('[', '&#91;', $m);
				$m = str_replace(']', '&#93;', $m);
				$m = str_replace('*', '&#42;', $m);
				$m = str_replace('__', '&#95;&#95;', $m);
				$m = str_replace('~~', '&#126;&#126;', $m);
				$m = str_replace('%%', '&#37;&#37;', $m);
				return '<code>' . $m . '</code>';
			}, $msg);
			// Post links
			$msg = preg_replace_callback('/&gt;&gt;([0-9]+)/', function($matches) {
				$post = postByID($matches[1]);
				if ($post) {
					return '<a href="/' . TINYIB_BOARD . '/res/' .
						($post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent']) .
						'.html#' . $matches[1] . '">' . $matches[0] . '</a>';
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
			$msg = str_replace('@!@TINYIB_GT@!@', '&gt;', $msg);
			$msg = str_replace('@!@TINYIB_LINE_END@!@', "\r\n", $msg);
			if (TINYIB_WORDBREAK > 0) {
				$msg = finishWordBreak($msg);
			}
			$post['message'] = $msg;
		}
	}
	if ($rawPost || !in_array('password', $hideFields)) {
		$post['password'] = $_POST['password'] != '' ? md5(md5($_POST['password'])) : '';
	}
	$post['nameblock'] = nameBlock($post['name'], $post['tripcode'], $post['email'], time(), $rawPostText);

	// Embed URL uploaded
	if (isset($_POST['embed']) &&
		trim($_POST['embed']) != '' &&
		($rawPost || !in_array('embed', $hideFields))
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
				(implode('/', array_keys($tinyib_embeds))) . ' URLs are supported.');
		}
		$post['file0_hex'] = $service;
		$tempFile = time() . substr(microtime(), 2, 3) . '-0';
		$fileLocation = 'thumb/' . $tempFile;
		file_put_contents($fileLocation, url_get_contents($embed['thumbnail_url']));
		$fileInfo = getimagesize($fileLocation);
		$fileMime = mime_content_type($fileLocation);
		$post['image0_width'] = $fileInfo[0];
		$post['image0_height'] = $fileInfo[1];
		if ($fileMime == 'image/jpeg') {
			$post['thumb0'] = $tempFile . '.jpg';
		} elseif ($fileMime == 'image/gif') {
			$post['thumb0'] = $tempFile . '.gif';
		} elseif ($fileMime == 'image/png') {
			$post['thumb0'] = $tempFile . '.png';
		} else {
			fancyDie('Error while processing audio/video.');
		}
		$thumbLocation = 'thumb/' . $post['thumb0'];
		list($thumbMaxWidth, $thumbMaxHeight) = thumbnailDimensions($post, '0');
		if (!createThumbnail($fileLocation, $thumbLocation, $thumbMaxWidth, $thumbMaxHeight)) {
			@unlink($fileLocation);
			fancyDie('Could not create thumbnail.');
		} else {
			@unlink($fileLocation);
		}
		if (TINYIB_VIDEO_OVERLAY) {
			addVideoOverlay($thumbLocation);
		}
		$thumbInfo = getimagesize($thumbLocation);
		$post['thumb0_width'] = $thumbInfo[0];
		$post['thumb0_height'] = $thumbInfo[1];
		$post['file0_original'] = cleanString($embed['title']);
		$post['file0'] = str_ireplace(array('src="https://', 'src="http://'), 'src="//', $embed['html']);

	// Images uploaded
	} elseif (isset($_FILES['file']) &&
		($rawPost || !in_array('file', $hideFields)) &&
		$_FILES['file']['tmp_name'][0]
	) {
		$filesCount = 0;
		$sizeOfCurrentFileInBytes = 0;
		$sizeOfAllFilesInBytes = 0;
		foreach ($_FILES["file"]["error"] as $index => $error) {
			if (!$_FILES['file']['tmp_name'][$index] || $filesCount >= TINYIB_MAXIMUM_FILES) {
				continue;
			}
			validateFileUpload($error);
			if (!is_file($_FILES['file']['tmp_name'][$index]) ||
				!is_readable($_FILES['file']['tmp_name'][$index])
			) {
				fancyDie('File transfer failure.<br>Please retry the submission.');
			}
			$sizeOfCurrentFileInBytes = filesize($_FILES['file']['tmp_name'][$index]);
			$sizeOfAllFilesInBytes += $sizeOfCurrentFileInBytes;
			if ((TINYIB_MAXKB > 0) && ($sizeOfCurrentFileInBytes > (TINYIB_MAXKB * 1024))) {
				fancyDie('That file is larger than ' . TINYIB_MAXKBDESC . '.');
			}
			if ((TINYIB_MAXKB > 0) && ($sizeOfAllFilesInBytes > (TINYIB_MAXKB * 1024))) {
				// silently drop all remained files if comulative size is getting more than TINYIB_MAXKB.
				// or uncomment fancyDie to get error message and lost post.
				// fancyDie('Size of all files is larger than ' . TINYIB_MAXKBDESC . '.');
				continue;
			}
			$post['file' . $index . '_original'] = trim(
				htmlentities(substr(basename($_FILES['file']['name'][$index]), 0, 50), ENT_QUOTES, 'UTF-8'));
			$post['file' . $index . '_hex'] = md5_file($_FILES['file']['tmp_name'][$index]);
			$post['file' . $index . '_size'] = $_FILES['file']['size'][$index];
			$post['file' . $index . '_size_formatted'] = convertBytes($post['file' . $index . '_size']);
			if (TINYIB_FILE_ALLOW_DUPLICATE === false) {
				checkDuplicateFile($post['file' . $index . '_hex']);
			}
			$fileMimeSplit = explode(' ', trim(mime_content_type($_FILES['file']['tmp_name'][$index])));
			if (count($fileMimeSplit) > 0) {
				$fileMime = strtolower(array_pop($fileMimeSplit));
			} else {
				if (!@getimagesize($_FILES['file']['tmp_name'][$index])) {
					fancyDie('Failed to read the MIME type and size of the uploaded file.<br>' .
						'Please retry the submission.');
				}
				$fileInfo = getimagesize($_FILES['file']['tmp_name'][$index]);
				$fileMime = mime_content_type($_FILES['file']['tmp_name'][$index]);
			}
			if (empty($fileMime) || !isset($tinyib_uploads[$fileMime])) {
				fancyDie(supportedFileTypes());
			}
			$fileName = time() . substr(microtime(), 2, 3) . '-' . $index;
			$post['file' . $index] = $fileName . '.' . $tinyib_uploads[$fileMime][0];
			$fileLocation = 'src/' . $post['file' . $index];
			if (!move_uploaded_file($_FILES['file']['tmp_name'][$index], $fileLocation)) {
				fancyDie('Could not copy uploaded file.');
			}
			if ($_FILES['file']['size'][$index] != filesize($fileLocation)) {
				@unlink($fileLocation);
				fancyDie('File transfer failure.<br>Please go back and try again.');
			}
			if ($fileMime == 'audio/webm' || $fileMime == 'video/webm' || $fileMime == 'video/mp4') {
				$post['image' . $index . '_width'] = max(0, intval(shell_exec(
					'mediainfo --Inform="Video;%Width%" ' . $fileLocation)));
				$post['image' . $index . '_height'] = max(0, intval(shell_exec(
					'mediainfo --Inform="Video;%Height%" ' . $fileLocation)));
				if ($post['image' . $index . '_width'] > 0 && $post['image' . $index . '_height'] > 0) {
					list($thumbMaxWidth, $thumbMaxHeight) = thumbnailDimensions($post, $index);
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
						fancyDie('Sorry, your video appears to be corrupt.');
					}
					if (TINYIB_VIDEO_OVERLAY) {
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
			} elseif (in_array($fileMime,
				array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif', 'application/x-shockwave-flash'))
			) {
				$fileInfo = getimagesize($fileLocation);
				$post['image' . $index . '_width'] = $fileInfo[0];
				$post['image' . $index . '_height'] = $fileInfo[1];
			}
			if (isset($tinyib_uploads[$fileMime][1])) {
				$thumbFileSplit = explode('.', $tinyib_uploads[$fileMime][1]);
				$post['thumb' . $index] = $fileName . 's.' . array_pop($thumbFileSplit);
				if (!copy($tinyib_uploads[$fileMime][1], 'thumb/' . $post['thumb' . $index])) {
					@unlink($fileLocation);
					fancyDie('Could not create thumbnail.');
				}
				if ($fileMime == 'application/x-shockwave-flash') {
					(TINYIB_VIDEO_OVERLAY)?addVideoOverlay('thumb/' . $post['thumb' . $index]):'';
				}
			} elseif (in_array($fileMime, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'))) {
				$post['thumb' . $index] = $fileName . 's.' . $tinyib_uploads[$fileMime][0];
				list($thumbMaxWidth, $thumbMaxHeight) = thumbnailDimensions($post, $index);
				if (!createThumbnail(
					$fileLocation,
					'thumb/' . $post['thumb' . $index],
					$thumbMaxWidth,
					$thumbMaxHeight)
				) {
					@unlink($fileLocation);
					fancyDie('Could not create thumbnail.');
				}
			}
			if ($post['thumb' . $index] != '') {
				$thumbInfo = getimagesize('thumb/' . $post['thumb' . $index]);
				$post['thumb' . $index . '_width'] = $thumbInfo[0];
				$post['thumb' . $index . '_height'] = $thumbInfo[1];
			}
			$filesCount++;
		}
	}

	// No file uploaded
	if ($post['file0'] == '') {
		$allowed = '';
		if (!empty($tinyib_uploads) && ($rawPost || !in_array('file', $hideFields))) {
			$allowed = 'file';
		}
		if (!empty($tinyib_embeds) && ($rawPost || !in_array('embed', $hideFields))) {
			if ($allowed != '') {
				$allowed .= ' or ';
			}
			$allowed .= 'embed URL';
		}
		if ($post['parent'] == TINYIB_NEWTHREAD && $allowed != '' && !TINYIB_NOFILEOK) {
			fancyDie('A ' . $allowed . ' is required to start a thread.');
		}
		if (!$rawPost && str_replace('<br>', '', $post['message']) == '') {
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

	if (!$loggedIn && (($post['file0'] != '' && TINYIB_REQMOD == 'files') || TINYIB_REQMOD == 'all')) {
		$post['moderated'] = '0';
		echo 'Your ' . ($post['parent'] == TINYIB_NEWTHREAD ? 'thread' : 'post') .
			' will be shown <b>once it has been approved</b>.<br>';
		$slowRedirect = true;
	}

	$post['likes'] = 0;
	$post['id'] = insertPost($post);

	if ($post['moderated'] == '1') {
		if (TINYIB_ALWAYSNOKO || strtolower($post['email']) == 'noko') {
			$redirect = 'res/' . ($post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent']) .
				'.html#' . $post['id'];
		}
		trimThreads();
		if ($post['parent'] != TINYIB_NEWTHREAD) {
			rebuildThread($post['parent']);
			if (strtolower($post['email']) != 'sage') {
				if (TINYIB_MAXREPLIES == 0 || numRepliesToThreadByID($post['parent']) <= TINYIB_MAXREPLIES) {
					bumpThreadByID($post['parent']);
				}
			}
		} else {
			rebuildThread($post['id']);
		}
		rebuildIndexes();
	}

// Check if the request is to delete a post and/or its associated image
} elseif (isset($_GET['delete']) && !isset($_GET['manage'])) {
	if (!isset($_POST['delete'])) {
		fancyDie('Tick the box next to a post and click "Delete" to delete it.');
	}
	if (TINYIB_DBMIGRATE) {
		fancyDie('Post deletion is currently disabled.<br>Please try again in a few moments.');
	}
	$post = postByID($_POST['delete']);
	if ($post) {
		list($loggedIn, $isAdmin) = manageCheckLogIn();
		if ($loggedIn && $_POST['password'] == '') {
			// Redirect to post moderation page
			echo '<meta http-equiv="refresh" content="0;url=' . basename($_SERVER['PHP_SELF']) .
				'?manage&moderate=' . $_POST['delete'] . '">';
		} elseif ($post['password'] != '' && md5(md5($_POST['password'])) == $post['password']) {
			deletePostByID($post['id']);
			threadUpdated($post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent']);
			fancyDie('Post deleted.');
		} else {
			fancyDie('Invalid password.');
		}
	} else {
		fancyDie('Sorry, an invalid post identifier was sent.<br>' .
			'Please go back, refresh the page, and try again.');
	}
	$redirect = false;

// Check if the request is to access the management area
} elseif (isset($_GET['manage'])) {
	$text = '';
	$onload = '';
	$navbar = '&nbsp;';
	$redirect = false;
	$loggedIn = false;
	$isAdmin = false;
	$returnlink = basename($_SERVER['PHP_SELF']);
	list($loggedIn, $isAdmin) = manageCheckLogIn();
	if ($loggedIn) {
		if ($isAdmin) {
			if (isset($_GET['rebuildall'])) {
				$allthreads = allThreads();
				foreach ($allthreads as $thread) {
					rebuildThread($thread['id']);
				}
				rebuildIndexes();
				$text .= manageInfo('Rebuilt board.');
			} elseif (isset($_GET['bans'])) {
				clearExpiredBans();
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
						deleteBanByID($_GET['lift']);
						$text .= manageInfo('Ban record lifted for ' . $ban['ip']);
					}
				}
				$onload = manageOnLoad('bans');
				$text .= manageBanForm() . manageBansTable();
			} elseif (isset($_GET['update'])) {
				if (is_dir('.git')) {
					$gitOutput = shell_exec('git pull 2>&1');
					$text .=
		'<blockquote class="reply" style="padding: 7px;font-size: 1.25em;">
			<pre style="margin: 0;padding: 0;">Attempting update...' . "\n\n" . $git_output . '</pre>
		</blockquote>
		<p><b>Note:</b> If TinyIB updates and you have made custom modifications,
			<a href="https://github.com/SthephanShinkufag/TinyIB/commits/master" target="_blank">
				review the changes</a> which have been merged into your installation.<br>
			Ensure that your modifications do not interfere with any new/modified files.<br>
			See the <a href="https://github.com/SthephanShinkufag/TinyIB#readme">README</a>
			for more information.
		</p>';
				} else {
					$text .=
		'<p><b>TinyIB was not installed via Git!</b></p>
		<p>If you installed TinyIB without Git, you must
			<a href="https://github.com/SthephanShinkufag/TinyIB/#updating">update manually</a><br>
			If you did install with Git, ensure the script has read and write access to the
			<b>.git</b> folder.
		</p>';
				}
			} elseif (isset($_GET['dbmigrate'])) {
				if (!TINYIB_DBMIGRATE) {
					fancyDie('settings.php: Set TINYIB_DBMIGRATE to true to use this feature.');
				} elseif (!isset($_GET['go'])) {
					$text .= '<p>
			This tool currently only supports migration from a flat file database to MySQL.<br>
			Your original database will not be deleted.<br>
			If the migration fails, disable the tool and your board will be unaffected.<br>
			See the <a href="https://github.com/SthephanShinkufag/TinyIB#migrating" target="_blank">README</a>
			<small>(<a href="README.md" target="_blank">alternate link</a>)</small> for instructions.<br><br>
			<a href="?manage&dbmigrate&go"><b>Start the migration</b></a>
		</p>';
				} elseif (TINYIB_DBMODE != 'flatfile') {
					fancyDie('settings.php: Set TINYIB_DBMODE to "flatfile" and enter your MySQL' .
						' settings before migrating.');
				} elseif (!function_exists('mysqli_connect')) {
					fancyDie('Please install the <a href="http://php.net/manual/en/book.mysqli.php">' .
						'MySQLi extension</a> and try again.');
				} else {
					$link = @mysqli_connect(TINYIB_DBHOST, TINYIB_DBUSERNAME, TINYIB_DBPASSWORD);
					if (!$link) {
						fancyDie('Could not connect to database: ' . (
							is_object($link) ? mysqli_error($link) :
							(($link_error = mysqli_connect_error()) ? $link_error : '(unknown error)')
						));
					}
					$dbSelected = @mysqli_query($link, 'USE ' . constant('TINYIB_DBNAME'));
					if (!$dbSelected) {
						fancyDie('Could not select database: ' . (
							is_object($link) ? mysqli_error($link) :
							(($link_error = mysqli_connect_error()) ? $link_error : '(unknown error)')
						));
					}
					mysqli_set_charset($link, 'utf8');
					if (mysqli_num_rows(mysqli_query($link,
						"SHOW TABLES LIKE '" . TINYIB_DBPOSTS . "'")) != 0
					) {
						fancyDie('Posts table (' . TINYIB_DBPOSTS . ') already exists!<br>' .
							'Please DROP this table and try again.');
					} elseif (mysqli_num_rows(mysqli_query($link,
						"SHOW TABLES LIKE '" . TINYIB_DBBANS . "'")) != 0
					) {
						fancyDie('Bans table (' . TINYIB_DBBANS . ') already exists!<br>' .
							'Please DROP this table and try again.');
					} elseif (mysqli_num_rows(mysqli_query($link,
						"SHOW TABLES LIKE '" . TINYIB_DBLIKES . "'")) != 0
					) {
						fancyDie('Likes table (' . TINYIB_DBLIKES . ') already exists!<br>' .
							'Please DROP this table and try again.');
					} else {
						mysqli_query($link, $posts_sql);
						mysqli_query($link, $bans_sql);
						mysqli_query($link, $likes_sql);
						$maxId = 0;
						$threads = allThreads();
						foreach ($threads as $thread) {
							$posts = postsInThreadByID($thread['id']);
							foreach ($posts as $post) {
								mysqli_query($link,
									'INSERT INTO `' . TINYIB_DBPOSTS . '` (
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
							'ALTER TABLE `' . TINYIB_DBPOSTS .
							'` AUTO_INCREMENT = ' . ($maxId + 1))
						) {
							$text .= '<p><b>Warning!</b></p>' .
								'<p>Unable to update the <code>AUTO_INCREMENT</code> value for table <code>' .
								TINYIB_DBPOSTS . '</code>,' . ' please set it to ' . ($maxId + 1) . '.</p>';
						}
						$maxId = 0;
						$bans = allBans();
						foreach ($bans as $ban) {
							$maxId = max($maxId, $ban['id']);
							mysqli_query($link,
								'INSERT INTO `' . TINYIB_DBBANS . "` (
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
							'ALTER TABLE `' . TINYIB_DBBANS .
							'` AUTO_INCREMENT = ' . ($maxId + 1))
						) {
							$text .= '<p><b>Warning!</b></p>' .
								'<p>Unable to update the <code>AUTO_INCREMENT</code>' .
								' value for table <code>' . TINYIB_DBBANS . '</code>,' .
								' please set it to ' . ($maxId + 1) . '.</p>';
						}
						$maxId = 0;
						$likes = allLikes();
						foreach ($likes as $like) {
							$maxId = max($maxId, $like['id']);
							mysqli_query($link,
								'INSERT INTO `' . TINYIB_DBLIKES . "` (
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
							'ALTER TABLE `' . TINYIB_DBLIKES .
							'` AUTO_INCREMENT = ' . ($maxId + 1))
						) {
							$text .= '<p><b>Warning!</b></p>' .
								'<p>Unable to update the <code>AUTO_INCREMENT</code>' .
								' value for table <code>' . TINYIB_DBLIKES . '</code>,' .
								' please set it to ' . ($maxId + 1) . '.</p>';
						}
						$text .= '<p><b>Database migration complete!</b></p>' .
							'<p>Set <code>TINYIB_DBMODE</code> to <code>mysqli</code> and' .
							' <code>TINYIB_DBMIGRATE</code> to <code>false</code> in your' .
							' settings.php file,<br>Then click <b>[Rebuild All]</b> above' .
							' and ensure everything looks the way it should.</p>';
					}
				}
			}
		}
		if (isset($_GET['delete'])) {
			$post = postByID($_GET['delete']);
			if ($post) {
				deletePostByID($post['id']);
				rebuildIndexes();
				if ($post['parent'] != TINYIB_NEWTHREAD) {
					rebuildThread($post['parent']);
				}
				$text .= manageInfo('Post No.' . $post['id'] . ' deleted.');
			} else {
				fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
			}
		} elseif (isset($_GET['delete-img']) && isset($_GET["delete-img-mod"])) {
			$post = postByID($_GET['delete-img']);
			if ($post) {
				deleteImagesByImageID($post, $_GET["delete-img-mod"]);
				threadUpdated($post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent']);
				$text .= manageInfo('Selected images from post No.' . $post['id'] . ' deleted.');
			} else {
				fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
			}
		} elseif (isset($_GET['editpost']) &&  isset($_POST['message'])) {
			$post = postByID($_GET['editpost']);
			$newMessage = $_POST['message'] . '<br><br><span style="color: purple;">Message edited: ' .
				(date('d.m.y D H:i:s', time())) . '</span>';
			if ($post) {
				editMessageInPostById($post['id'], $newMessage);
				threadUpdated($post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent']);
				$text .= manageInfo('Message in post No.' . $post['id'] . ' changed.');
			} else {
				fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
			}
		} elseif (isset($_GET['approve'])) {
			if ($_GET['approve'] > 0) {
				$post = postByID($_GET['approve']);
				if ($post) {
					approvePostByID($post['id']);
					$threadId = $post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent'];
					if (strtolower($post['email']) != 'sage' &&
						(TINYIB_MAXREPLIES == 0 || numRepliesToThreadByID($threadId) <= TINYIB_MAXREPLIES)
					) {
						bumpThreadByID($threadId);
					}
					threadUpdated($threadId);
					$text .= manageInfo('Post No.' . $post['id'] . ' approved.');
				} else {
					fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
				}
			}
		} elseif (isset($_GET['moderate'])) {
			if ($_GET['moderate'] > 0) {
				$post = postByID($_GET['moderate']);
				if ($post) {
					$text .= manageModeratePost($post);
				} else {
					fancyDie('Sorry, there doesn\'t appear to be a post with that ID.');
				}
			} else {
				$onload = manageOnLoad('moderate');
				$text .= manageModeratePostForm();
			}
		} elseif (isset($_GET['locked']) && isset($_GET['setlocked'])) {
			if ($_GET['locked'] > 0) {
				$post = postByID($_GET['locked']);
				if ($post && $post['parent'] == TINYIB_NEWTHREAD) {
					lockThreadByID($post['id'], (intval($_GET['setlocked'])));
					threadUpdated($post['id']);
					$text .= manageInfo('Thread No.' . $post['id'] . ' ' .
						($_GET['setlocked'] == 1 ? 'locked' : 'un-locked') . '.');
				} else {
					fancyDie('Sorry, there doesn\'t appear to be a thread with that ID.');
				}
			} else {
				fancyDie('Form data was lost. Please go back and try again.');
			}
		} elseif (isset($_GET['sticky']) && isset($_GET['setsticky'])) {
			if ($_GET['sticky'] > 0) {
				$post = postByID($_GET['sticky']);
				if ($post && $post['parent'] == TINYIB_NEWTHREAD) {
					stickyThreadByID($post['id'], (intval($_GET['setsticky'])));
					threadUpdated($post['id']);
					$text .= manageInfo('Thread No.' . $post['id'] . ' ' .
						(intval($_GET['setsticky']) == 1 ? 'stickied' : 'un-stickied') . '.');
				} else {
					fancyDie('Sorry, there doesn\'t appear to be a thread with that ID.');
				}
			} else {
				fancyDie('Form data was lost. Please go back and try again.');
			}
		} elseif (isset($_GET['rawpost'])) {
			$onload = manageOnLoad('rawpost');
			$text .= buildPostForm(0, true);
		} elseif (isset($_GET['logout'])) {
			$_SESSION['tinyib'] = '';
			session_destroy();
			die('<meta http-equiv="refresh" content="0;url=' . $returnlink . '?manage">');
		}
		if ($text == '') {
			$text = manageStatus();
		}
	} else {
		$onload = manageOnLoad('login');
		$text .= manageLogInForm();
	}
	echo managePage($text, $onload);

} elseif (isset($_GET['like'])) {
	$postNum = $_GET['like'];
	$result = likePostByID($postNum, $_SERVER['REMOTE_ADDR']);
	$post = postByID($postNum);
	$post['likes'] = $result;
	threadUpdated($post['parent'] == TINYIB_NEWTHREAD ? $post['id'] : $post['parent']);
	echo '{
		"status": "ok",
		"message": "' . (
			$result[0] ? 'Post №' . $postNum . ' succesfully liked!' :
			'The like to post №' . $postNum . ' is cancelled!'
		) . '",
		"likes": ' . $result[1] . ' }';
	$redirect = false;

} elseif (!file_exists(TINYIB_INDEX) || countThreads() == 0) {
	rebuildIndexes();
}

if ($redirect) {
	echo '<meta http-equiv="refresh" content="' . (isset($slowRedirect) ? '3' : '0') .
		'; url=' . (is_string($redirect) ? $redirect : TINYIB_INDEX) . '">';
}
