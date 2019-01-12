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
	$hideFields = $post['parent'] == TINYIB_NEWTHREAD ? $tinyib_hidefieldsop : $tinyib_hidefields;

	$post['ip'] = $_SERVER['REMOTE_ADDR'];
	if ($rawPost || !in_array('name', $hideFields)) {
		list($post['name'], $post['tripcode']) = nameAndTripcode($_POST['name']);
		$post['name'] = cleanString(substr($post['name'], 0, 75));
	}
	if ($rawPost || !in_array('email', $hideFields)) {
		$post['email'] = cleanString(str_replace('"', '&quot;', substr($_POST['email'], 0, 75)));
	}
	if ($rawPost || !in_array('subject', $hideFields)) {
		$post['subject'] = cleanString(substr($_POST['subject'], 0, 75));
	}
	if ($rawPost || !in_array('message', $hideFields)) {
		$post['message'] = $_POST['message'];
		if ($rawPost) {
			// Treat message as raw HTML
			$rawPostText = $isAdmin ?
				' <span style="color: red;">## Admin</span>' : ' <span style="color: purple;">## Mod</span>';
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
		$post['password'] = ($_POST['password'] != '') ? md5(md5($_POST['password'])) : '';
	}
	$post['nameblock'] = nameBlock($post['name'], $post['tripcode'], $post['email'], time(), $rawPostText);

	if (isset($_POST['embed']) &&
		trim($_POST['embed']) != '' &&
		($rawPost || !in_array('embed', $hideFields))
	) {
		if (isset($_FILES['file']) && $_FILES['file']['name'] != '') {
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

		$post['file_hex'] = $service;
		$tempFile = time() . substr(microtime(), 2, 3);
		$fileLocation = 'thumb/' . $tempFile;
		file_put_contents($fileLocation, url_get_contents($embed['thumbnail_url']));

		$fileInfo = getimagesize($fileLocation);
		$fileMime = mime_content_type($fileLocation);
		$post['image_width'] = $fileInfo[0];
		$post['image_height'] = $fileInfo[1];

		if ($fileMime == 'image/jpeg') {
			$post['thumb'] = $tempFile . '.jpg';
		} else if ($fileMime == 'image/gif') {
			$post['thumb'] = $tempFile . '.gif';
		} else if ($fileMime == 'image/png') {
			$post['thumb'] = $tempFile . '.png';
		} else {
			fancyDie('Error while processing audio/video.');
		}
		$thumbLocation = 'thumb/' . $post['thumb'];

		list($thumbMaxWidth, $thumbMaxHeight) = thumbnailDimensions($post);

		if (!createThumbnail($fileLocation, $thumbLocation, $thumbMaxWidth, $thumbMaxHeight)) {
			fancyDie('Could not create thumbnail.');
		}

		addVideoOverlay($thumbLocation);

		$thumbInfo = getimagesize($thumbLocation);
		$post['thumb_width'] = $thumbInfo[0];
		$post['thumb_height'] = $thumbInfo[1];

		$post['file_original'] = cleanString($embed['title']);
		$post['file'] = str_ireplace(array('src="https://', 'src="http://'), 'src="//', $embed['html']);
	} else if (isset($_FILES['file']) && ($rawPost || !in_array('file', $hideFields))) {
		if ($_FILES['file']['name'] != '') {
			validateFileUpload();

			if (!is_file($_FILES['file']['tmp_name']) || !is_readable($_FILES['file']['tmp_name'])) {
				fancyDie('File transfer failure.<br>Please retry the submission.');
			}

			if ((TINYIB_MAXKB > 0) && (filesize($_FILES['file']['tmp_name']) > (TINYIB_MAXKB * 1024))) {
				fancyDie('That file is larger than ' . TINYIB_MAXKBDESC . '.');
			}

			$post['file_original'] =
				trim(htmlentities(substr($_FILES['file']['name'], 0, 50), ENT_QUOTES, 'UTF-8'));
			$post['file_hex'] = md5_file($_FILES['file']['tmp_name']);
			$post['file_size'] = $_FILES['file']['size'];
			$post['file_size_formatted'] = convertBytes($post['file_size']);

			if (TINYIB_FILE_ALLOW_DUPLICATE === false) {
				checkDuplicateFile($post['file_hex']);
			}

			$fileMimeSplit = explode(' ', trim(mime_content_type($_FILES['file']['tmp_name'])));
			if (count($fileMimeSplit) > 0) {
				$fileMime = strtolower(array_pop($fileMimeSplit));
			} else {
				if (!@getimagesize($_FILES['file']['tmp_name'])) {
					fancyDie('Failed to read the MIME type and size of the uploaded file.<br>' .
						'Please retry the submission.');
				}

				$fileInfo = getimagesize($_FILES['file']['tmp_name']);
				$fileMime = mime_content_type($_FILES['file']['tmp_name']);
			}

			if (empty($fileMime) || !isset($tinyib_uploads[$fileMime])) {
				fancyDie(supportedFileTypes());
			}

			$fileName = time() . substr(microtime(), 2, 3);
			$post['file'] = $fileName . '.' . $tinyib_uploads[$fileMime][0];

			$fileLocation = 'src/' . $post['file'];
			if (!move_uploaded_file($_FILES['file']['tmp_name'], $fileLocation)) {
				fancyDie('Could not copy uploaded file.');
			}

			if ($_FILES['file']['size'] != filesize($fileLocation)) {
				@unlink($fileLocation);
				fancyDie('File transfer failure.<br>Please go back and try again.');
			}

			if ($fileMime == 'audio/webm' || $fileMime == 'video/webm' || $fileMime == 'video/mp4') {
				$post['image_width'] = max(0, intval(shell_exec(
					'mediainfo --Inform="Video;%Width%" ' . $fileLocation)));
				$post['image_height'] = max(0, intval(shell_exec(
					'mediainfo --Inform="Video;%Height%" ' . $fileLocation)));

				if ($post['image_width'] > 0 && $post['image_height'] > 0) {
					list($thumbMaxWidth, $thumbMaxHeight) = thumbnailDimensions($post);
					$post['thumb'] = $fileName . 's.jpg';
					shell_exec('ffmpegthumbnailer -s ' . max($thumbMaxWidth, $thumbMaxHeight) .
						' -i ' . $fileLocation . ' -o thumb/' . $post['thumb']);

					$thumbInfo = getimagesize('thumb/' . $post['thumb']);
					$post['thumb_width'] = $thumbInfo[0];
					$post['thumb_height'] = $thumbInfo[1];

					if ($post['thumb_width'] <= 0 || $post['thumb_height'] <= 0) {
						@unlink($fileLocation);
						@unlink('thumb/' . $post['thumb']);
						fancyDie('Sorry, your video appears to be corrupt.');
					}

					addVideoOverlay('thumb/' . $post['thumb']);
				}

				$duration = intval(shell_exec('mediainfo --Inform="General;%Duration%" ' . $fileLocation));
				if ($duration > 0) {
					$mins = floor(round($duration / 1000) / 60);
					$secs = str_pad(floor(round($duration / 1000) % 60), 2, '0', STR_PAD_LEFT);

					$post['file_original'] = $mins . ':' . $secs .
						($post['file_original'] != '' ? (', ' . $post['file_original']) : '');
				}
			} else if (in_array($fileMime,
				array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif', 'application/x-shockwave-flash'))
			) {
				$fileInfo = getimagesize($fileLocation);

				$post['image_width'] = $fileInfo[0];
				$post['image_height'] = $fileInfo[1];
			}

			if (isset($tinyib_uploads[$fileMime][1])) {
				$thumbFileSplit = explode('.', $tinyib_uploads[$fileMime][1]);
				$post['thumb'] = $fileName . 's.' . array_pop($thumbFileSplit);
				if (!copy($tinyib_uploads[$fileMime][1], 'thumb/' . $post['thumb'])) {
					@unlink($fileLocation);
					fancyDie('Could not create thumbnail.');
				}
				if ($fileMime == 'application/x-shockwave-flash') {
					addVideoOverlay('thumb/' . $post['thumb']);
				}
			} else if (in_array($fileMime, array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'))) {
				$post['thumb'] = $fileName . 's.' . $tinyib_uploads[$fileMime][0];
				list($thumbMaxWidth, $thumbMaxHeight) = thumbnailDimensions($post);

				if (!createThumbnail(
					$fileLocation,
					'thumb/' . $post['thumb'],
					$thumbMaxWidth,
					$thumbMaxHeight)
				) {
					@unlink($fileLocation);
					fancyDie('Could not create thumbnail.');
				}
			}

			if ($post['thumb'] != '') {
				$thumbInfo = getimagesize('thumb/' . $post['thumb']);
				$post['thumb_width'] = $thumbInfo[0];
				$post['thumb_height'] = $thumbInfo[1];
			}
		}
	}

	if ($post['file'] == '') { // No file uploaded
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

	if (!$loggedIn && (($post['file'] != '' && TINYIB_REQMOD == 'files') || TINYIB_REQMOD == 'all')) {
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
			if ($post['parent'] == TINYIB_NEWTHREAD) {
				threadUpdated($post['id']);
			} else {
				threadUpdated($post['parent']);
			}
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
						$ban['expire'] = ($_POST['expire'] > 0) ? (time() + $_POST['expire']) : 0;
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
			} else if (isset($_GET['update'])) {
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
				if (TINYIB_DBMIGRATE) {
					if (isset($_GET['go'])) {
						if (TINYIB_DBMODE == 'flatfile') {
							if (function_exists('mysqli_connect')) {
								$link = @mysqli_connect(TINYIB_DBHOST, TINYIB_DBUSERNAME, TINYIB_DBPASSWORD);
								if (!$link) {
									fancyDie('Could not connect to database: ' . (
										(is_object($link)) ? mysqli_error($link) :
										(($link_error = mysqli_connect_error()) ?
											$link_error : '(unknown error)')
									));
								}
								$dbSelected = @mysqli_query($link, 'USE ' . constant('TINYIB_DBNAME'));
								if (!$dbSelected) {
									fancyDie('Could not select database: ' . (
										(is_object($link)) ? mysqli_error($link) :
										(($link_error = mysqli_connect_error()) ?
											$link_error : '(unknown error)')
									));
								}
								mysqli_set_charset($link, 'utf8');
								if (mysqli_num_rows(mysqli_query($link,
									"SHOW TABLES LIKE '" . TINYIB_DBPOSTS . "'")) == 0
								) {
									if (mysqli_num_rows(mysqli_query($link,
										"SHOW TABLES LIKE '" . TINYIB_DBBANS . "'")) == 0
									) {
										mysqli_query($link, $posts_sql);
										mysqli_query($link, $bans_sql);

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
														`file`,
														`file_hex`,
														`file_original`,
														`file_size`,
														`file_size_formatted`,
														`image_width`,
														`image_height`,
														`thumb`,
														`thumb_width`,
														`thumb_height`,
														`stickied`,
														`likes`
													) VALUES (' .
														$post['id'] . ', ' .
														$post['parent'] . ', ' .
														time() . ', ' .
														time() . ", '" .
														$_SERVER['REMOTE_ADDR'] . "', '" .
														mysqli_real_escape_string($link,
															$post['name']) . "', '" .
														mysqli_real_escape_string($link,
															$post['tripcode']) . "', '" .
														mysqli_real_escape_string($link,
															$post['email']) . "', '" .
														mysqli_real_escape_string($link,
															$post['nameblock']) . "', '" .
														mysqli_real_escape_string($link,
															$post['subject']) . "', '" .
														mysqli_real_escape_string($link,
															$post['message']) . "', '" .
														mysqli_real_escape_string($link,
															$post['password']) . "', '" .
														$post['file'] . "', '" .
														$post['file_hex'] . "', '" .
														mysqli_real_escape_string($link,
															$post['file_original']) . "', " .
														$post['file_size'] . ", '" .
														$post['file_size_formatted'] . "', " .
														$post['image_width'] . ", " .
														$post['image_height'] . ", '" .
														$post['thumb'] . "', " .
														$post['thumb_width'] . ', ' .
														$post['thumb_height'] . ', ' .
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
												'<p>Unable to update the <code>AUTO_INCREMENT</code>' .
												' value for table <code>' . TINYIB_DBPOSTS . '</code>,' .
												' please set it to ' . ($maxId + 1) . '.</p>';
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
													mysqli_real_escape_string($link,
														$ban['timestamp']) . "', '" .
													mysqli_real_escape_string($link,
														$ban['expire']) . "', '" .
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

										$text .= '<p><b>Database migration complete!</b></p>' .
											'<p>Set <code>TINYIB_DBMODE</code> to <code>mysqli</code> and' .
											' <code>TINYIB_DBMIGRATE</code> to <code>false</code> in your' .
											' settings.php file,<br>Then click <b>[Rebuild All]</b> above' .
											' and ensure everything looks the way it should.</p>';
									} else {
										fancyDie('Bans table (' . TINYIB_DBBANS . ') already exists!<br>' .
											'Please DROP this table and try again.');
									}
								} else {
									fancyDie('Posts table (' . TINYIB_DBPOSTS . ') already exists!<br>' .
										'Please DROP this table and try again.');
								}
							} else {
								fancyDie('Please install the ' .
									'<a href="http://php.net/manual/en/book.mysqli.php">' .
									'MySQLi extension</a> and try again.');
							}
						} else {
							fancyDie('settings.php: Set TINYIB_DBMODE to "flatfile" and enter your MySQL' .
								' settings before migrating.');
						}
					} else {
						$text .= '<p>
			This tool currently only supports migration from a flat file database to MySQL.<br>
			Your original database will not be deleted.<br>
			If the migration fails, disable the tool and your board will be unaffected.<br>
			See the <a href="https://github.com/SthephanShinkufag/TinyIB#migrating" target="_blank">README</a>
			<small>(<a href="README.md" target="_blank">alternate link</a>)</small> for instructions.<br><br>
			<a href="?manage&dbmigrate&go"><b>Start the migration</b></a>
		</p>';
					}
				} else {
					fancyDie('settings.php: Set TINYIB_DBMIGRATE to true to use this feature.');
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
} elseif (!file_exists(TINYIB_INDEX) || countThreads() == 0) {
	rebuildIndexes();
}

if ($redirect) {
	echo '<meta http-equiv="refresh" content="' . (isset($slowRedirect) ? '3' : '0') .
		';url=' . (is_string($redirect) ? $redirect : TINYIB_INDEX) . '">';
}
