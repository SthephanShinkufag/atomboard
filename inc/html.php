<?php
if (!defined('ATOM_BOARD')) {
	die('');
}
if (ATOM_GEOIP == 'geoip2') {
	require 'vendor/autoload.php';
}
use GeoIp2\Database\Reader;

/* ==[ Page elements ]===================================================================================== */

function pageHeader() {
	return '<!DOCTYPE html>

<html data-theme="' . ATOM_THEME . '">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta http-equiv="cache-control" content="max-age=0">
	<meta http-equiv="cache-control" content="no-store, no-cache, must-revalidate">
	<meta http-equiv="expires" content="0">
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
	<meta http-equiv="pragma" content="no-cache">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>' . ATOM_BOARD_DESCRIPTION . '</title>
	<link rel="shortcut icon" href="/' . ATOM_BOARD . '/icons/favicon.png">
	<link rel="stylesheet" type="text/css" href="/' . ATOM_BOARD . '/css/atomboard.css?2023091900">
	<script src="/' . ATOM_BOARD . '/js/atomboard.js?2023091900"></script>' .
	(ATOM_CAPTCHA === 'recaptcha' ? '
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>' : '') . '
</head>
';
}

function pageWrapper($description, $needReturn) {
	return '
	<div class="wrapper">
		<div id="navigation-top" class="navigation">' . ATOM_HTML_NAVIGATION . '
			<a class="navigation-link" href="/' . ATOM_BOARD .
				'/catalog.html" title="Go to catalog">Catalog</a>
			<a class="navigation-link" href="/' . ATOM_BOARD .
				'/' . basename($_SERVER['PHP_SELF']) . '?passcode">Passcode</a>
			<a class="navigation-link" href="/' . ATOM_BOARD .
				'/' . basename($_SERVER['PHP_SELF']) . '?manage">Manage</a>
			<select class="select-style navigation-link" onchange="setThemeStyle(this);">
				<option value="Dark" selected>Dark</option>
				<option value="Light">Light</option>
			</select>
		</div>
		<div class="description">' . $description . '</div>
		<hr>
		<div id="panel-top" class="panel">' .
			($needReturn ? '
			<a class="link-button" href="/' . ATOM_BOARD . '/"' .
				' title="Return to board">Return</a>' : '') . '
			<a class="link-button" href="#" title="Navigate to bottom"' .
				' onclick="window.scroll(0, document.body.scrollHeight); return false;">To bottom</a>
		</div>
		';
}

function pageFooter($needReturn) {
	return '
		<div id="panel-bottom" class="panel">' .
			($needReturn ? '
			<a class="link-button" href="/' . ATOM_BOARD . '/"' .
				' title="Return to board">Return</a>' : '') . '
			<a class="link-button" href="#" title="Navigate to top"' .
				' onclick="window.scroll(0, 0); return false;">To top</a>
		</div>
		<hr>
		<div class="footer">
			<div>
				We are not responsible for the content posted on this site.
				Any information posted here is the responsibility of the user who uploaded it.<br>
				The content on the site is intended for persons over 18 years of age.
			</div>
			- <a href="https://github.com/SthephanShinkufag/atomboard">atomboard</a> -
		</div>
		<div id="navigation-bottom" class="navigation"> ' . ATOM_HTML_NAVIGATION . '
			<a class="navigation-link" href="/' . ATOM_BOARD .
				'/catalog.html" title="Go to catalog">Catalog</a>
			<a class="navigation-link" href="/' . ATOM_BOARD .
				'/' . basename($_SERVER['PHP_SELF']) . '?passcode">Passcode</a>
			<a class="navigation-link" href="/' . ATOM_BOARD .
				'/' . basename($_SERVER['PHP_SELF']) . '?manage">Manage</a>
			<select class="select-style navigation-link" onchange="setThemeStyle(this);">
				<option value="Dark" selected>Dark</option>
				<option value="Light">Light</option>
			</select>
		</div>
	</div>
	<div id="svg-icons" style="height: 0; width: 0; overflow: hidden;">
		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			<symbol viewBox="0 0 16 16" id="symbol-like">
				<path d="M14.8 1.6l-.3-.3C13-.5 10.4-.4 8.9 1.4l-.9 1-.9-1C5.6-.4 3-.4 1.5 1.4l-.3.3C-.4' .
					' 3.5-.4 6.3 1.1 8.1l1 1.1L8 16l5.9-6.8 1-1.2c1.5-1.8 1.5-4.6-.1-6.4z"/>
			</symbol>
		</svg>
	</div>
</body>
</html>';
}

/* ==[ Postform ]========================================================================================== */

function supportedFileTypes() {
	global $atom_uploads;
	if (empty($atom_uploads)) {
		return '';
	}
	$typesAllowed = array_map('strtoupper', array_unique(array_column($atom_uploads, 0)));
	$typesLast = array_pop($typesAllowed);
	$typesFormatted = $typesAllowed ? implode(', ', $typesAllowed) . ' and ' . $typesLast : $typesLast;
	return 'Supported file type' . (count($atom_uploads) != 1 ? 's are ' : ' is ') . $typesFormatted . '.';
}

function buildPostForm($parent, $isstaffPost = false) {
	global $atom_hidefieldsop, $atom_hidefields, $atom_uploads, $atom_embeds;
	$isOnPage = $parent == ATOM_NEWTHREAD;
	$hideFields = $isOnPage ? $atom_hidefieldsop : $atom_hidefields;
	$postformExtra = array('name' => '', 'email' => '', 'subject' => '', 'footer' => '');
	$inputSubmit = '<input type="submit" value="' .
		($isstaffPost ? 'New post' : ($isOnPage ? 'New thread' : 'Reply')) . '" accesskey="z">';
	if ($isstaffPost || !in_array('subject', $hideFields)) {
		$postformExtra['subject'] = $inputSubmit;
	} elseif (!in_array('email', $hideFields)) {
		$postformExtra['email'] = $inputSubmit;
	} elseif (!in_array('name', $hideFields)) {
		$postformExtra['name'] = $inputSubmit;
	} else {
		$postformExtra['footer'] = $inputSubmit;
	}

	// Build board rules
	$maxFileSizeInputHtml = '';
	$maxFileSizeRulesHtml = '';
	$fileTypesHtml = '';
	$fileInputHtml = '';
	$embedInputHtml = '';
	if (!empty($atom_uploads) && ($isstaffPost || !in_array('file', $hideFields))) {
		if (ATOM_FILE_MAXKB > 0) {
			$maxFileSizeInputHtml = '<input type="hidden" name="MAX_FILE_SIZE" value="' .
				strval(ATOM_FILE_MAXKB * 1024) . '">';
			$maxFileSizeRulesHtml = '<li>Maximum number of files is ' . ATOM_FILES_COUNT . ', ' .
				ATOM_FILE_MAXKBDESC . ' total.</li>';
		}
		$fileTypesHtml = '<li>' . supportedFileTypes() . '</li>';
		$fileInputHtml = '<tr>
						<td class="postblock"></td>
						<td><input type="file" name="file[]" size="35" accesskey="f" multiple></td>
					</tr>';
	}
	if (!empty($atom_embeds) && ($isstaffPost || !in_array('embed', $hideFields))) {
		$embedInputHtml = '<tr>
						<td class="postblock"></td>
						<td><input type="text" class="postform-input" name="embed"' .
							' placeholder="YouTube URL" accesskey="x" autocomplete="off"></td>
					</tr>';
	}
	$reqModHtml = '';
	if (ATOM_REQMOD == 'files' || ATOM_REQMOD == 'all') {
		$reqModHtml = '<li>All posts' . (ATOM_REQMOD == 'files' ? ' with a file attached' : '') .
			' will be moderated before being shown.</li>';
	}
	$thumbnailsHtml = '';
	if (isset($atom_uploads['image/jpeg']) ||
		isset($atom_uploads['image/pjpeg']) ||
		isset($atom_uploads['image/png']) ||
		isset($atom_uploads['image/gif']) ||
		isset($atom_uploads['image/webp'])
	) {
		$thumbnailsHtml = '<li>Images greater than ' . ATOM_FILE_MAXWOP . 'x' . ATOM_FILE_MAXHOP . (
			ATOM_FILE_MAXW == ATOM_FILE_MAXWOP && ATOM_FILE_MAXH == ATOM_FILE_MAXHOP ? '' :
				' (new thread) or ' . ATOM_FILE_MAXW . 'x' . ATOM_FILE_MAXH . ' (reply)'
			) . ' will be thumbnailed.</li>';
	}
	$uniquePostersCount = getUniquePostersCount();
	$uniquePosters = $uniquePostersCount > 0 ?
		'<li>Currently ' . $uniquePostersCount . ' unique users.</li>' : '';

	// Build postform
	return '<div class="postarea">
			<form name="postform" id="postform" action="/' . ATOM_BOARD .
				'/imgboard.php" method="post" enctype="multipart/form-data">
				' . $maxFileSizeInputHtml .
			(!$isstaffPost ? '
				<input type="hidden" name="parent" value="' . $parent . '">' : '') . '
				<table class="postform-table reply"><tbody>' . (
					$isstaffPost ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="checkbox" name="staffPost" checked style="margin: 0 auto;">
							<span style="font: 12px sans-serif;">Write message as raw HTML</span>
						</td>
					</tr>
					<tr>
						<td class="postblock"></td>
						<td><input type="text" class="postform-input" name="parent" placeholder="' .
							'Reply to (0 = new thread)" maxlength="75" accesskey="t"></td>
					</tr>' : ''
				) . (
					$isstaffPost || !in_array('name', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="name" placeholder="Name"' .
								' maxlength="75" accesskey="n"> ' .
							$postformExtra['name'] . '
						</td>
					</tr>' : ''
				) . (
					$isstaffPost || !in_array('email', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="email" placeholder="Mail"' .
								' maxlength="75" accesskey="e"> ' .
							$postformExtra['email'] . '
						</td>
					</tr>' : ''
				) . (
					$isstaffPost || !in_array('subject', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td style="display: flex;">
							<input type="text" class="postform-input" name="subject" placeholder="Subject"' .
								' maxlength="75" accesskey="s" style="width: 100%" autocomplete="off"> ' .
							$postformExtra['subject'] . '
						</td>
					</tr>' : ''
				) . (
					$isstaffPost || !in_array('message', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td id="markup-buttons">
							<button class="markup-button" id="markup-bold" title="Bold">B</button>
							<button class="markup-button" id="markup-italic" title="Italic">I</button>
							<button class="markup-button" id="markup-underline" title="Underline">U</button>
							<button class="markup-button" id="markup-strike" title="Strike">S</button>
							<button class="markup-button" id="markup-spoiler" title="Spoiler">%</button>
							<button class="markup-button" id="markup-code" title="Code">C</button>
							<button class="markup-button" id="markup-quote" title="' .
								'Select the text, then click to insert a quote">&gt;</button>
						</td>
					</tr>
					<tr>
						<td class="postblock"></td>
						<td>
							<textarea id="message" name="message" placeholder="Message' .
								($isOnPage ? '' : ' - reply in thread') . '" accesskey="m"></textarea>
						</td>
					</tr>' : ''
				) . (
					ATOM_CAPTCHA ? '
					<tr id="captchablock">
						<td class="postblock"></td>
						<td>' . (ATOM_CAPTCHA === 'recaptcha' ? '
							<div style="min-height: 80px;">
								<div id="g-recaptcha" class="g-recaptcha" data-sitekey="' .
									ATOM_RECAPTCHA_SITE . '"></div>
								<noscript><div>
									<div style="width: 302px; height: 422px; position: relative;">
										<div style="width: 302px; height: 422px; position: absolute;">
											<iframe src="https://www.google.com/recaptcha/api/fallback?k=' .
												ATOM_RECAPTCHA_SITE . '" frameborder="0" scrolling="no"' .
												' style="width: 302px; height:422px; border-style: none;">
											</iframe>
										</div>
									</div>
									<div style="width: 300px; height: 60px; border-style: none; bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px; background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
										<textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid #c1c1c1; margin: 10px 25px; padding: 0px; resize: none;"></textarea>
									</div>
								</div></noscript>
							</div>
						' : '
							<input type="text" class="postform-input" name="captcha" id="captcha"' .
								' placeholder="Captcha" accesskey="c" autocomplete="off">
							<img id="captchaimage" src="/' . ATOM_BOARD . '/inc/captcha.php"' .
								' width="175" height="55" alt="CAPTCHA" onclick="reloadCaptcha();">
						') . '</td>
					</tr><tr id="validcaptchablock" style="display: none">
						<td class="postblock"></td>
						<td>
							No captcha: you are a passcode user. <a href="/' . ATOM_BOARD .
								'/imgboard.php?passcode&logout">Log Out.</a>
						</td>
					</tr>' : ''
				) . '
					' . $fileInputHtml . '
					' . $embedInputHtml .
				(
					$isstaffPost || !in_array('password', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td><input type="password" name="password" id="newpostpassword" size="8"' .
							' accesskey="p">&nbsp;Deletion password</td>
					</tr>' : ''
				) . '
					<tr>
						<td colspan="2" class="rules">
							<ul>
								' . $reqModHtml . '
								' . $fileTypesHtml . '
								' . $maxFileSizeRulesHtml . '
								' . $thumbnailsHtml . '
								' . $uniquePosters . '
							</ul>
						</td>
					</tr>' .
				(
					$postformExtra['footer'] != '' ? '
					<tr>
						<td>&nbsp;</td>
						<td>' .
							$postformExtra['footer'] . '
						</td>
					</tr>
					' : ''
				) . '
				</tbody></table>
			</form>
		</div>';
}

/* ==[ Post ]============================================================================================== */

function buildPostBacklinks($id, $thrId) {
	if (!ATOM_BACKLINKS) {
		return '';
	}
	$str = '';
	$match = '&gt;&gt;' . $id . '<';
	$posts = getThreadPosts($thrId);
	foreach ($posts as $reply) {
		if (strpos($reply['message'], $match) === false) {
			continue;
		}
		$post = getPost($reply['id']);
		$str .= ($str != '' ? ', ' : '') . (!$post ? '&gt;&gt;' . $reply['id'] :
			'<a class="' . (isOp($post) ? 'refop' : 'refreply') . '" href="/' . ATOM_BOARD . '/res/' .
				getThreadId($post) . '.html#' . $reply['id'] . '">&gt;&gt;' . $reply['id'] . '</a>');
	}
	return $str != '' ? '
				<div class="backlinks">' . $str . '</div>' : '';
}

function checkGeoIP($ip) {
	$countryCode = 'ANON';
	$validIP = filter_var($ip, FILTER_VALIDATE_IP);
	if ($validIP) {
		if (ATOM_GEOIP == 'geoip2') {
			$reader = new Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb');
			try {
				$record = $reader->country($validIP);
				$countryCode = $record->country->isoCode;
			} catch (\GeoIp2\Exception\AddressNotFoundException $e) {
				$countryCode = 'ANON';
			}
		} else if (ATOM_GEOIP == 'geoip') {
			$countryCode = geoip_country_code_by_name($validIP);
		}
	}
	return $countryCode ? $countryCode : 'ANON';
}

function buildPost($post, $res, $isModPanel = false) {
	if (!isset($post['omitted'])) {
		$post['omitted'] = 0;
	}

	// Build post file
	$id = $post['id'];
	$thrId = getThreadId($post);
	$isOp = isOp($post);
	$filehtml = '';
	$hasImages = false;
	$imagesCount = 0;
	for ($index = 0; $index < ATOM_FILES_COUNT; $index++) {
		if (!$post['file' . $index . '_hex']) {
			continue;
		}
		$hasImages = true;
		$imagesCount++;
		$fWidth = $post['image' . $index . '_width'];
		$fHeight = $post['image' . $index . '_height'];
		$fName = $post['file' . $index];
		$isEmbed = isEmbed($post['file' . $index . '_hex']);
		$fExt = substr(strrchr($fName, '.'), 1);
		$isVideo = !!($fExt == 'webm' || $fExt == 'mp4' || $fExt == 'mov');
		$directLink = $isEmbed ? '#' : '/' . ATOM_BOARD . '/src/' . $fName;
		$expandClick = ' onclick="return expandFile(event, ' . $id . $index . ');"';
		$expandHtml = '';
		if ($isEmbed) {
			$expandHtml = rawurlencode($fName);
		} elseif ($isVideo) {
			$expandHtml = rawurlencode('<video ' .
				($fWidth > 0 && $fHeight > 0 ? 'width="' . $fWidth . '" height="' . $fHeight . '"' :
					'width="500"') .
				'style="position: static; pointer-events: inherit; display: inline; height: auto;' .
				' max-width: 100%; max-height: 100%;" controls autoplay loop><source src="' .
				$directLink . '"></source></video>');
		} elseif (in_array($fExt, array('jpg', 'png', 'gif', 'webp'))) {
			$expandHtml = rawurlencode('<a href="' . $directLink . '"' . $expandClick . '><img src="/' .
				ATOM_BOARD . '/src/' . $fName . '" width="' . $fWidth .
				'" style="max-width: 100%; height: auto;"></a>');
		}
		$origName = $post['file' . $index . '_original'];
		$hasOrigName = $origName != '';
		$thumblink = '<a href="' . $directLink . '" target="_blank"' .
			($isEmbed || in_array($fExt, array('jpg', 'png', 'gif', 'webp', 'webm', 'mp4', 'mov')) ?
				$expandClick : '') .
			($hasOrigName ? ' download="' . $origName . '" title="Click to expand/collapse">' : '>');
		$filesize = '';
		if ($isEmbed) {
			$filesize = '<a href="' . $directLink . '"' . $expandClick . '>' . $origName .
				'</a>,&nbsp;' . $post['file' . $index . '_hex'];
		} elseif ($fName != '') {
			$filesize = $thumblink . ($hasOrigName ? $origName : $fName) . '</a><br>(' .
				$post['file' . $index . '_size_formatted'] .
				($fWidth > 0 && $fHeight > 0 ? ',&nbsp;' . $fWidth . 'x' . $fHeight : '') . ')';
		}
		if ($filesize == '') {
			continue;
		}
		$filehtml .= '
				<div class="image-container">
					<span class="filesize">' .
					($isModPanel ? '
						<input type="checkbox" name="delete-img-mod[]" value="' . $index . '">' : '') . '
						' . $filesize . '
					</span>
					<div id="thumbfile' . $id . $index . '">' .
						($post['thumb' . $index] != '' /* If a video has a thumbnail */ ? '
						' . $thumblink . '
							<img src="/' . ATOM_BOARD . '/thumb/' . $post['thumb' . $index] .
							'" alt="' . $id . $index . '" class="thumb' . ($isVideo ? ' thumb-video' : '') .
							'" id="thumbnail' . $id . $index.
							'" width="' . $post['thumb' . $index . '_width'] .
							'" height="' . $post['thumb' . $index . '_height'] . '">
						</a>' :
						($isVideo /* If a video has no thumbnail */ ? '
						' . $thumblink . '
							<video src="' . $directLink . '" alt="' . $id . $index .
							'" class="thumb thumb-video" id="thumbnail' . $id . $index. '"></video>
						</a>' : '')) . '
					</div>' . ($expandHtml == '' ? '' : '
					<div id="expand' . $id . $index . '" style="display: none;">
						' . $expandHtml . '
					</div>
					<div id="file' . $id . $index . '" class="thumb" style="display: none;"></div>
				</div>');
	}

	// Truncate messages on board index pages for readability
	$message = $post['message'];
	if (!$res && !$isModPanel) {
		$truncLen = 0;
		if (ATOM_TRUNC_LINES > 0 && substr_count($message, '<br>') > ATOM_TRUNC_LINES) {
			$brOffsets = strallpos($message, '<br>');
			$truncLen = $brOffsets[ATOM_TRUNC_LINES - 1];
		} elseif (ATOM_TRUNC_SIZE > 0 && strlen($message) > ATOM_TRUNC_SIZE) {
			$truncLen = ATOM_TRUNC_SIZE;
		}
		if ($truncLen) {
			$message = tidy_repair_string(
				substr($message, 0, $truncLen),
				array('quiet' => true, 'show-body-only' => true),
				'utf8'
			) . '
					<div class="abbrev">
						Post too long. <a href="/' . ATOM_BOARD . '/res/' . $thrId . '.html#' . $id .
						'">Click to view</a>.
					</div>';
		}
	}

	// Country flags
	$countryCode = checkGeoIP($post['ip'],
		ATOM_GEOIP == 'geoip2' ? new Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb') : NULL);

	// Start post building
	$omitted = $post['omitted'];
	$likes = $post['likes'];
	$replyBtn = ($isOp && $res == ATOM_INDEXPAGE ? '&nbsp;<a class="link-button" href="res/' .
		$id . '.html" title="Reply to thread №' . $id . '">Reply</a>' : '');

	return PHP_EOL . ($isOp ? '
			<div class="oppost" id="op' . $id . '">' : '
			<table border="0"><tbody><tr><td class="reply" id="reply' . $id . '">') . '
				<a id="' . $id . '"></a>
				<label>
					<input type="checkbox" name="delete" value="' . $id . '">' .
					($post['subject'] != '' ? '
					<span class="filetitle">' . $post['subject'] . '</span>' : '') .
					(ATOM_GEOIP ? '
						<img class="poster-country" title="' . $countryCode . '" src="/' . ATOM_BOARD .
							'/icons/flag-icons/' . $countryCode . '.png">' : '') .
					'</span> ' .
					$post['nameblock'] . '
				</label>
				<span class="reflink">' . ($res == ATOM_RESPAGE ? '
					<a href="' . $thrId . '.html#' . $id . '" onclick="highlightPost(' . $id .
						');" title="Click to link to this post">№</a>' .
					'<a href="' . $thrId . '.html#q' . $id . '" onclick="quotePost(' . $id .
						');" title="Click to reply to this post">' .
						$id . '</a>' : '
					<a href="/' . ATOM_BOARD . '/res/' . $thrId . '.html#' . $id .
						'" title="Click to link to this post">№</a>' .
					'<a href="/' . ATOM_BOARD . '/res/' . $thrId . '.html#q' . $id .
						'" title="Click to reply to this post">' . $id . '</a>') .
					($post['stickied'] == 1 ? '
					<img src="/' . ATOM_BOARD . '/icons/sticky.png"' .
						' title="Thread is stickied to top" width="16" height="16">' : '') .
					($post['locked'] == 1 ? '
					<img src="/' . ATOM_BOARD . '/icons/locked.png"' .
						' title="Thread is locked for posting" width="11" height="16">' : '') .
					($post['endless'] == 1 ? '
					<img src="/' . ATOM_BOARD . '/icons/endless.png"' .
						' title="Thread is endless" width="16" height="16">' : '') .
					(ATOM_LIKES ? '
					<span class="like-container">
						<span class="like-icon' . ($likes ? ' like-enabled' : ' like-disabled') .
							'" onclick="sendLike(this, ' . $id . ');">
							<svg><use xlink:href="#symbol-like"></use></svg>
						</span>
						<span class="like-counter">' . ($likes ? $likes : '') . '</span>
					</span>' : '') . '
				</span><a class="link-button button-manage" target="_blank" href="/' . ATOM_BOARD .
					'/imgboard.php?manage=&moderate=' . $id . '" title="Manage post №' . $id . '">!</a>' .
				$replyBtn . '
				<br>' .
				($isModPanel && $hasImages ? '
				<form method="get" action="?">
					<input type="hidden" name="manage" value="">
					<input type="hidden" name="delete-img" value="' . $id . '">
					<select name="action" class="managebutton" style="margin-left: 20px;">
						<option value="delete" selected>Delete images</option>
						<option value="hide">Hide thumbnails</option>
					</select>
					<input type="submit" value="Apply to selected" class="managebutton">
					<br>' : '') .
					($imagesCount > 1 ? '<div class="images-container">' . $filehtml . '</div>' : $filehtml) .
					($isModPanel && $hasImages ? '
				</form>' : '') . '
				<div class="message">' .
					($isModPanel ? '
					<form method="post" action="?manage&editpost=' . $id . '" enctype="multipart/form-data">
						<textarea id="message" name="message">' . htmlspecialchars($message) . '</textarea>
						<br>
						<input type="submit" value="Edit" class="managebutton">
					</form>' : $message) . '
				</div>' .
				buildPostBacklinks($id, $thrId) . '
			' . (!$isOp ? '</td></tr></tbody></table>' : '</div>' .
			($res == ATOM_INDEXPAGE && $omitted > 0 ? '
			<div class="omittedposts">' . $omitted . ' ' .
				plural('post', $omitted) . ' omitted. Click' . $replyBtn . ' to view.
			</div>' : ''));
}

/* ==[ Page ]============================================================================================== */

function buildPage($htmlPosts, $parent, $pages = 0, $thispage = 0) {
	// Build page links: [Previous] [0] [1] [2] [Next]
	$isInThread = $parent != ATOM_NEWTHREAD;
	$pagelinks = '';
	if (!$isInThread) {
		$pages = max($pages, 0);
		$pagelinks = ($thispage == 0 ?
					'<td>Previous</td>' :
					'<td>
						<form method="get" action="' . ($thispage == 1 ? 'index' : $thispage - 1) . '.html">
							<input value="Previous" type="submit">
						</form>
					</td>') . '
					<td>';
		for ($i = 0; $i <= $pages; $i++) {
			if ($thispage == $i) {
				$pagelinks .= '&#91;' . $i . '&#93; ';
			} else {
				$pagelinks .= '&#91;<a href="' . ($i == 0 ? "index" : $i) . '.html">' . $i . '</a>&#93; ';
			}
		}
		$pagelinks .= '</td>' . ($pages <= $thispage ? '
					<td>Next</td>' : '
					<td>
						<form method="get" action="' . ($thispage + 1) . '.html">
							<input value="Next" type="submit">
						</form>
					</td>');
	}
	// Build page's body
	return pageHeader() . '<body class="tinyib atomboard">' .
		pageWrapper(ATOM_BOARD_DESCRIPTION, $isInThread) .
		(ATOM_HTML_INFO_TOP ? ATOM_HTML_INFO_TOP . '
		<hr>
		' : '') .
		buildPostForm($parent) . '
		<hr>
		<form id="delform" action="/' . ATOM_BOARD . '/imgboard.php?delete" method="post">
			<input type="hidden" name="board" value="' . ATOM_BOARD . '">' .
			$htmlPosts . '
			<div class="userdelete">
				Delete Post <input type="password" name="password" id="deletepostpassword" size="8"' .
					' placeholder="Password">&nbsp;<input name="deletepost" value="Delete" type="submit">
			</div>
		</form>
		<table>
			<tbody>
				<tr>
					' . $pagelinks . '
				</tr>
			</tbody>
		</table>' .
		(ATOM_HTML_INFO_BOTTOM ? '
		<hr>
		' . ATOM_HTML_INFO_BOTTOM : '') .
		pageFooter($isInThread);
}

/* ==[ Rebuilding ]======================================================================================== */

function rebuildThreadPage($id) {
	$htmlPosts = '';
	$posts = getThreadPosts($id);
	foreach ($posts as $post) {
		$htmlPosts .= buildPost($post, ATOM_RESPAGE);
	}
	$htmlPosts .= '
			<hr>';
	writePage('res/' . $id . '.html', buildPage($htmlPosts, $id));
}

function rebuildIndexPages() {
	global $atom_janitors;
	$page = 0;
	$i = 0;
	$htmlPosts = '';
	$threads = getThreads();
	$pages = ceil(count($threads) / ATOM_THREADSPERPAGE) - 1;
	foreach ($threads as $thread) {
		$replies = getThreadPosts($thread['id']);
		$thread['omitted'] = max(0, count($replies) - ATOM_PREVIEWREPLIES - 1);
		// Build replies for preview
		$htmlReplies = array();
		for ($j = count($replies) - 1; $j > $thread['omitted']; $j--) {
			$htmlReplies[] = buildPost($replies[$j], ATOM_INDEXPAGE);
		}
		$htmlPosts .= buildPost($thread, ATOM_INDEXPAGE) . implode('', array_reverse($htmlReplies)) .
			PHP_EOL . '
			<hr>';
		if (++$i >= ATOM_THREADSPERPAGE) {
			$file = $page == 0 ? ATOM_INDEX : $page . '.html';
			writePage($file, buildPage($htmlPosts, 0, $pages, $page));
			$page++;
			$i = 0;
			$htmlPosts = '';
		}
	}
	if ($page == 0 || $htmlPosts != '') {
		$file = $page == 0 ? ATOM_INDEX : $page . '.html';
		writePage($file, buildPage($htmlPosts, 0, $pages, $page));
	}
	// Create catalog
	writePage('catalog.html', buildCatalogPage());
	// Create janitor log
	if (count($atom_janitors) != 0) {
		writePage('janitorlog.html', buildModLogPage());
	}
}

function rebuildThread($id) {
	rebuildThreadPage($id);
	rebuildIndexPages();
}

/* ==[ Manage ]============================================================================================ */

function manageInfo($text) {
	return '<div class="manageinfo">' . $text . '</div>';
}

function managePage($text, $action = '') {
	global $access, $atom_janitors;
	$onload = '';
	switch ($action) {
	case 'bans': $onload = ' onload="document.form_bans.ip.focus();"'; break;
	case 'login': $onload = ' onload="document.form_login_staff.managepassword.focus();"'; break;
	case 'moderate': $onload = ' onload="document.form_moderate_post.moderate.focus();"'; break;
	case 'passcode': $onload = ' onload="document.form_login_passcode.passcode.focus();"'; break;
	case 'passcode_block': $onload = ' onload="document.form_passcode_manage.block_reason.focus();"'; break;
	case 'passcode_manage': $onload = ' onload="document.form_passcode_new.meta.focus();"'; break;
	case 'staffPost': $onload = ' onload="document.postform.parent.focus();"'; break;
	}
	return pageHeader() . '<body' . $onload . '>' .
		pageWrapper(ATOM_BOARD_DESCRIPTION, true) .
		'<hr>
		<div id="panel-adminbar" class="panel">' . (
			$access == 'disabled' ? '' : '
			<a class="link-button" href="?manage">Status</a>' .
			($access == 'admin' || $access == 'moderator' ? '
			<a class="link-button" href="?manage&bans">Bans</a>
			<a class="link-button" href="?manage&passcodes=manage">Passcodes</a>
			<a class="link-button" href="?manage&modlog">ModLog</a>' : '') .
			(count($atom_janitors) != 0 && $access == 'janitor' ? '
			<a class="link-button" href="/' . ATOM_BOARD . '/janitorlog.html">JanitorLog</a>' : '') . '
			<a class="link-button" href="?manage&moderate">Moderate Post</a>
			<a class="link-button" href="?manage&staffPost">Raw Post</a>' .
			($access == 'admin' ? '
			<a class="link-button" href="?manage&rebuildall">Rebuild All</a>' : '') .
			($access == 'admin' && ATOM_DBMIGRATE ? '
			<a class="link-button" href="?manage&dbmigrate"><b>Migrate Database</b></a>' : '') . '
			<a class="link-button" href="?manage&logout">Log Out</a>
		') . '</div>
		' . $text . '
		<hr>' .
	pageFooter(true);
}

function manageLoginForm() {
	return '<form id="form_login_staff" name="form_login_staff" method="post" action="?manage">
			<fieldset>
				<legend align="center">Enter an administrator or moderator password</legend>
				<div class="login">
					<input type="password" id="managepassword" name="managepassword"><br>
					<input type="submit" value="Log In" class="managebutton">
				</div>
			</fieldset>
		</form>
		<br>';
}

function passLoginForm($action = '') {
	if ($action == 'login') {
		return '<form id="form_login_passcode" name="form_login_passcode" method="post" action="?passcode">
			<fieldset>
				<legend align="center">Enter passcode</legend>
				<div class="login">
					<input type="text" id="passcode" name="passcode"' .
						'style="width: 400px; padding: 4px; margin: 4px;" ><br>
					<input type="submit" value="Use Passcode" class="managebutton">
				</div>
			</fieldset>
		</form>
		<br>';
	}
	if ($action == 'valid') {
		return '<div align="center">You are already using a valid passcode. <a href="/' . ATOM_BOARD .
			'/imgboard.php?passcode&logout">Log Out.</a></div><br>';
	}
}

function manageBanForm() {
	return '<form id="form_bans" name="form_bans" method="post" action="?manage&bans">
			<fieldset>
				<legend>Ban an IP-address</legend>
				<table><tbody>
					<tr>
						<td><label for="ip">IP-address:</label></td>
						<td><input type="text" name="ip" id="ip" value="' . $_GET['bans'] . '">&nbsp;
						<small>[
							<a href="#" onclick="document.form_bans.ip.value+=\'/24\'; return false;">
								subnet /24</a> |
							<a href="#" onclick="document.form_bans.ip.value+=\'/16\'; return false;">
								subnet /16</a>
						] CIDR format is supported</small>
						</td>
					</tr>
					<tr>
						<td><label for="expire">Expire (sec):</label></td>
						<td>
							<input type="text" name="expire" id="expire" value="0">
							<small>[
								<a href="#" onclick="document.form_bans.expire.value=' .
									'\'3600\'; return false;">1hr</a> |
								<a href="#" onclick="document.form_bans.expire.value=' .
									'\'86400\'; return false;">1d</a> |
								<a href="#" onclick="document.form_bans.expire.value=' .
									'\'172800\'; return false;">2d</a> |
								<a href="#" onclick="document.form_bans.expire.value=' .
									'\'604800\'; return false;">1w</a> |
								<a href="#" onclick="document.form_bans.expire.value=' .
									'\'1209600\'; return false;">2w</a> |
								<a href="#" onclick="document.form_bans.expire.value=' .
									'\'2592000\'; return false;">30d</a> |
								<a href="#" onclick="document.form_bans.expire.value=' .
									'\'0\'; return false;">never</a> |
								<a href="#" onclick="document.form_bans.expire.value=' .
									'\'1\'; return false;">warning</a>
							]</small>
						</td>
					</tr>
					<tr>
						<td><label for="reason">Reason:</label></td>
						<td><input type="text" name="reason" id="reason">&nbsp;<small>optional</small></td>
					</tr>
					<tr>
						<td><input type="submit" value="Submit" class="managebutton"></td>
						<td></td>
					</tr>
				</tbody></table>
			</fieldset>
		</form>
		<br>';
}

function manageBansTable() {
	$text = '';
	$getAllBans = getAllBans();
	if (count($getAllBans) > 0) {
		if (ATOM_GEOIP == 'geoip2') {
			$geoipReader = new Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb');
		}
		$text .= '
		<table class="table"><tbody>
			<tr>
				<th>IP-address</th>
				<th>Set at</th>
				<th>Expires</th>
				<th>Reason provided</th>
				<th>&nbsp;</th>
			</tr>';
		foreach ($getAllBans as $ban) {
		    if ($ban['expire'] == 1) {
		        $expire = 'Warning';
		    } else {
		        if ($ban['expire'] > 0) {
		            $expire = date('d.m.Y D H:i:s', $ban['expire']);
		        } else {
			        $expire = 'Does not expire';
		        }
		    }
			$reason = $ban['reason'] == '' ? '&nbsp;' : htmlentities($ban['reason'], ENT_QUOTES, 'UTF-8');
			$countryCode = checkGeoIP(long2ip($ban['ip_from']), $geoipReader);
			$text .= '
			<tr>
				<td>' . (ATOM_GEOIP ? '<img class="poster-country" title="' . $countryCode . '" src="/' .
					ATOM_BOARD . '/icons/flag-icons/' . $countryCode . '.png"> ' : '') .
					ip2cidr($ban['ip_from'], $ban['ip_to']) . '</td>
				<td>' . date('d.m.Y D H:i:s', $ban['timestamp']) . '</td>
				<td>' . $expire . '</td><td>' . $reason . '</td>
				<td><a href="?manage&bans&lift=' . $ban['id'] . '">lift</a></td>
			</tr>';
		}
		$text .= '
		</tbody></table>';
	}
	return $text;
}


function buildPasscodesForm() {
	global $access;
	$isAdmin = $access == 'admin';
	$passHtml = '';
	if($isAdmin) {
		$passHtml .= '<form id="form_passcode_new" name="form_passcode_new" method="post"' .
			' action="?manage&issuepasscode">
			<fieldset>
				<legend>Issue a new passcode</legend>
				<table><tbody>
					<tr>
						<td><label for="expires">Passcode duration (sec):</label></td>
						<td>
							<input type="text" name="expires" id="expires" value="31536000">
							<small>[
								<a href="#" onclick="document.form_passcode_new.expires.value=' .
									'\'2592000\'; return false;">30d</a> |
								<a href="#" onclick="document.form_passcode_new.expires.value=' .
									'\'15780000\'; return false;">6m</a> |
								<a href="#" onclick="document.form_passcode_new.expires.value=' .
									'\'31536000\'; return false;">1y</a>
							]</small>
						</td>
					</tr>
					<tr>
						<td><label for="meta">Meta (related info):</label></td>
						<td><input type="text" name="meta" id="meta">&nbsp;<small>optional</small></td>
					</tr>
					<tr>
						<td><input type="submit" value="Submit" class="managebutton"></td>
						<td></td>
					</tr>
				</tbody></table>
			</fieldset>
		</form>
		<br>
		';
	}
	$passHtml .= '<form id="form_passcode_manage" name="form_passcode_manage" method="post"' .
			' action="?manage&managepasscode">
			<fieldset>
				<legend>Manage passcode</legend>
				<table><tbody>
					<tr>
						<td><label for="id">Passcode number:</label></td>
						<td>
							<input type="text" name="id" id="id" value="' .
								(isset($_GET['passcode']) ? $_GET['passcode'] : '') . '">
						</td>
					</tr>
					<tr>
						<td><label for="expires">Block duration (sec):</label></td>
						<td>
							<input type="text" name="block_till" id="block_till" value="604800">
							<small>[
								<a href="#" onclick="document.form_passcode_manage.block_till.value=' .
									'\'3600\'; return false;">1hr</a> |
								<a href="#" onclick="document.form_passcode_manage.block_till.value=' .
									'\'86400\'; return false;">1d</a> |
								<a href="#" onclick="document.form_passcode_manage.block_till.value=' .
									'\'172800\'; return false;">2d</a> |
								<a href="#" onclick="document.form_passcode_manage.block_till.value=' .
									'\'604800\'; return false;">1w</a> |
								<a href="#" onclick="document.form_passcode_manage.block_till.value=' .
									'\'1209600\'; return false;">2w</a> |
								<a href="#" onclick="document.form_passcode_manage.block_till.value=' .
									'\'2592000\'; return false;">30d</a> |
								<a href="#" onclick="document.form_passcode_manage.block_till.value=' .
									'\'0\'; return false;">unblock</a>
							]</small>
						</td>
					</tr>
					<tr>
						<td><label for="block_reason">Block reason:</label></td>
						<td><input type="text" name="block_reason" id="block_reason"></small></td>
					</tr>
					<tr>
						<td><input type="submit" value="Submit" class="managebutton"></td>
						<td></td>
					</tr>
				</tbody></table>
			</fieldset>
		</form>
		<br>
		<table class="table"><tbody>
			<tr>
				<th>Number</th>' .
				($isAdmin ? '
				<th>ID</th>' : '') . '
				<th>Meta</th>
				<th>Set at</th>
				<th>Expires</th>
				<th>Blocked till</th>
				<th>Blocked reason</th>
				<th>Last used</th>
				<th>Last used IP</th>
			</tr>';
	$passcodes = getAllPasscodes();
	foreach ($passcodes as $pass) {
		$passIp = $pass['last_used_ip'];
		$countryIcon = '';
		if($passIp) {
			$countryCode = checkGeoIP($passIp,
				ATOM_GEOIP == 'geoip2' ? new Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb') : NULL);
			$countryIcon = '<img class="poster-country" title="' . $countryCode . '" src="/' . ATOM_BOARD .
				'/icons/flag-icons/' . $countryCode . '.png">';
		}
		$passHtml .= '
			<tr>
				<td>' . $pass['number'] . '</td>' .
				($isAdmin ? '
				<td>' . $pass['id'] . '</td>' : '') . '
				<td>' . $pass['meta'] . '</td>
				<td>' . date('d.m.Y H:i:s', $pass['issued']) . '</td>
				<td>' . date('d.m.Y H:i:s', $pass['expires']) . '</td>
				<td>' . ($pass['blocked_till'] ?
					date('d.m.Y H:i:s', $pass['blocked_till']) : '') . '</td>
				<td>' . $pass['blocked_reason'] . '</td>
				<td>' . ($pass['last_used'] ?
					date('d.m.Y H:i:s', $pass['last_used']) : '') . '</td>
				<td style="white-space: pre;">' .
					($countryIcon ? $countryIcon . '&nbsp;' : '') . $passIp . '</td>
			</tr>';
	}
	$passHtml .= '
		</tbody></table>';
	return $passHtml;
}

function manageModeratePostForm() {
	return '<form id="form_moderate_post" name="form_moderate_post" method="get" action="?">
			<input type="hidden" name="manage" value="">
			<fieldset>
				<legend>Moderate a post</legend>
				<div valign="top">
					<label for="moderate">Post ID:</label>
					<input type="text" name="moderate" id="moderate">
					<input type="submit" value="Submit" class="managebutton">
				</div>
				<br>
				<small>
					<b>Tip:</b>
					While browsing the image board, you can easily moderate a post if you are logged in:<br>
					Tick the box next to a post and click "Delete"
					at the bottom of the page with a blank password.
				</small>
				<br>
			</fieldset>
		</form>
		<br>';
}

function manageModeratePost($post) {
	global $access;
	$id = $post['id'];
	$ip = $post['ip'];
	$passcodeNum = $post['pass'];
	$isOp = isOp($post);
	$ban = banByIP($ip);
	$isMod = $access == 'admin' || $access == 'moderator';
	$postOrThread = $isOp ? 'Thread' : 'Post';
	$stickyHtml = '';
	$lockedHtml = '';
	$endlessHtml = '';
	if ($isOp) {
		$isStickied = $post['stickied'] == 1;
		$stickyHtml = '
					<tr>
						<td align="right" width="50%;">
							<form method="get" action="?">
								<input type="hidden" name="manage" value="">
								<input type="hidden" name="sticky" value="' . $id . '">
								<input type="hidden" name="setsticky" value="' . ($isStickied ? 0 : 1) . '">
								<button type="submit" class="managebutton-action">
									<img src="/' . ATOM_BOARD .
									'/icons/sticky.png" width="16" height="16" style="vertical-align: -3px;">
									' . ($isStickied ? 'Unsticky' : 'Sticky') . ' thread
								</button>
							</form>
						</td>
						<td><small>' . ($isStickied ? 'Return this thread to a normal state.' :
							'Keep this thread at the top of the board.') . '</small></td>
					</tr>';
		$isLocked = $post['locked'] == 1;
		$lockedValue = $isLocked ? 'Unlock' : 'Lock';
		$lockedHtml = '
					<tr>
						<td align="right" width="50%;">
							<form method="get" action="?">
								<input type="hidden" name="manage" value="">
								<input type="hidden" name="locked" value="' . $id . '">
								<input type="hidden" name="setlocked" value="' . ($isLocked ? 0 : 1) . '">
								<button type="submit" class="managebutton-action">
									<img src="/' . ATOM_BOARD .
									'/icons/locked.png" width="11" height="16" style="vertical-align: -3px;">
									' . $lockedValue . ' thread
								</button>
							</form>
						</td>
						<td><small>' . $lockedValue . ' this thread for posting.</small></td>
					</tr>';
		$isEndless = $post['endless'] == 1;
		$endlessHtml = '
					<tr>
						<td align="right" width="50%;">
							<form method="get" action="?">
								<input type="hidden" name="manage" value="">
								<input type="hidden" name="endless" value="' . $id . '">
								<input type="hidden" name="setendless" value="' . ($isEndless ? 0 : 1) . '">
								<button type="submit" class="managebutton-action">
									<img src="/' . ATOM_BOARD .
									'/icons/endless.png" width="16" height="16" style="vertical-align: -3px;">
									Make ' . ($isEndless ? 'non-endless' : 'endless') . ' thread
								</button>
							</form>
						</td>
						<td><small>' . ($isEndless ? 'Disable' : 'Enable') .
							' endless mode for this thread.</small></td>
					</tr>';
		$postHtml = '';
		$posts = getThreadPosts($id, false);
		foreach ($posts as $postTemp) {
			$postHtml .= buildPost($postTemp, ATOM_INDEXPAGE, true);
		}
	} else {
		$postHtml = buildPost($post, ATOM_INDEXPAGE, true);
	}
	return '<fieldset>
			<legend>Moderating №' . $id . '</legend>
			<fieldset>
				<legend>Action</legend>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">' .
					$stickyHtml .
					$lockedHtml .
					$endlessHtml . '
					<tr>
						<td align="right" width="50%;">
							<form method="get" action="?">
								<input type="hidden" name="manage" value="">
								<input type="hidden" name="delete" value="' . $id . '">
								<input type="submit" class="managebutton-action" value="Delete ' .
									$postOrThread . '">
							</form>
						</td>
						<td><small>' . ($isOp ? 'This will delete the entire thread below.' :
							'This will delete the post below.'
						) . '</small></td>
					</tr>
					<tr>
						<td align="right" width="50%;">
							<form method="get" action="?">
								<input type="hidden" name="manage" value="">
								<input type="hidden" name="delall" value="' . $ip . '">
								<input type="submit" class="managebutton-action" value="Delete all" onclick' .
									'="return confirm(\'Are you sure to delete all from ' . $ip . '?\')">
							</form>
						</td>
						<td><small>This will delete all posts and threads from ip ' . $ip . '</small></td>
					</tr>
					<tr>
						<td align="right" width="50%;">
							<form method="get" action="?">
								<input type="hidden" name="manage" value="">
								<input type="hidden" name="bans" value="' . $ip . '">
								<input type="submit" class="managebutton-action" value="Ban Poster"' .
									($ban || !$isMod ? ' disabled' : '') . '>
							</form>
						</td>
						<td><small>' . (
							$ban ? ' A ban record already exists for ' . $ip : (
							$isMod ? 'IP address: ' . $ip : 'Only an administrator may ban an IP address.'
							)) . '</small></td>
					</tr>
					' . ($passcodeNum ? '<tr>
						<td align="right" width="50%;">
							<form method="get" action="?">
								<input type="hidden" name="manage" value="">
								<input type="hidden" name="passcode" value="' . $passcodeNum . '">
								<input type="hidden" name="passcodes" value="block">
								<input type="submit" class="managebutton-action" value="Manage passcode">
							</form>
						</td>
						<td><small>' . ($passcodeNum ? ('Passcode number: ' . $passcodeNum): '') .
							'</small></td>
					</tr>' : '') .'
				</table>
			</fieldset>
			<fieldset>
				<legend>' . $postOrThread . '</legend>' .
				$postHtml . '
			</fieldset>
		</fieldset>
		<br>';
}

function manageStatus() {
	global $access;
	$threads = getThreadsCount();
	$bans = count(getAllBans());
	if (ATOM_REQMOD == 'files' || ATOM_REQMOD == 'all') {
		$reqModPostHtml = '';
		$reqModPosts = getLatestPosts(false, 20);
		foreach ($reqModPosts as $post) {
			$id = $post['id'];
			$reqModPostHtml .= ($reqModPostHtml != '' ? '
				<tr><td colspan="2"><hr></td></tr>' : '') . '
				<tr>
					<td>' . buildPost($post, ATOM_INDEXPAGE) . PHP_EOL . '
					</td>
					<td valign="top" align="right">
						<table border="0">
							<tr>
								<td align="right">
									<form method="get" action="?">
										<input type="hidden" name="manage" value="">
										<input type="hidden" name="approve" value="' . $id . '">
										<input type="submit" value="Approve" class="managebutton">
									</form>
								</td>
							</tr>
							<tr>
								<td align="right">
									<form method="get" action="?">
										<input type="hidden" name="manage" value="">
										<input type="hidden" name="moderate" value="' . $id . '">
										<input type="submit" value="More Info" class="managebutton">
									</form>
								</td>
							</tr>
							<tr>
								<td align="right">
									<form method="get" action="?">
										<input type="hidden" name="manage" value="">
										<input type="hidden" name="delete" value="' . $id . '">
										<input type="submit" value="Delete" class="managebutton">
									</form>
								</td>
							</tr>
						</table>
					</td>
				</tr>';
		}
	}
	$postHtml = '';
	$recentCount = 0;
	$posts = getLatestPosts(true, 100);
	foreach ($posts as $post) {
		$recentCount++;
		$postHtml .= ($postHtml != '' ? '
					<tr><td colspan="2"><hr></td></tr>' : '') . '
					<tr>
						<td>' . buildPost($post, ATOM_INDEXPAGE) . PHP_EOL . '
						</td>
						<td valign="top" align="right">
							<form method="get" action="?">
								<input type="hidden" name="manage" value="">
								<input type="hidden" name="moderate" value="' . $post['id'] . '">
								<input type="submit" value="Moderate" class="managebutton">
							</form>
						</td>
					</tr>';
	}
	$uniquePostersCount = getUniquePostersCount();
	$uniquePosters = $uniquePostersCount > 0 ? $uniquePostersCount . ' unique users' : '';
	return ($access == 'admin' && ATOM_DBMODE == 'mysql' && function_exists('mysqli_connect') ?
		'<fieldset>
			<legend>Notice</legend>
			<p><b>ATOM_DBMODE</b> is currently <b>mysql</b> in <b>settings.php</b>, but
				<a href="http://www.php.net/manual/en/book.mysqli.php">MySQLi</a> is installed.
				Please change it to <b>mysqli</b>. This will not affect your data.</p>
		</fieldset>' : '') .
		'<fieldset>
			<legend>Status</legend>
			<fieldset>
				<legend>Info</legend>
				<table border="0" cellspacing="0" cellpadding="0" width="100%"><tbody><tr>
					<td>' . $threads . ' ' . plural('thread', $threads) . ', ' .
							$bans . ' ' . plural('ban', $bans) . ', ' . $uniquePosters . '</td>' .
					($access == 'admin' ? '
					<td valign="top" align="right">
						<form method="get" action="?">
							<input type="hidden" name="manage">
							<input type="hidden" name="update">
							<input type="submit" value="Update atomboard" class="managebutton">
						</form>
					</td>' : '') . '
				</tr></tbody></table>
			</fieldset>' .
			((ATOM_REQMOD == 'files' || ATOM_REQMOD == 'all') && $reqModPostHtml != '' ? '
			<fieldset>
				<legend>Pending posts</legend>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					' . $reqModPostHtml . '
				</table>
			</fieldset>' : '') . '
			<fieldset>
				<legend>Recent ' . $recentCount . ' posts</legend>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">' .
					$postHtml . '
				</table>
			</fieldset>
		</fieldset>
		<br>';
}

/* ==[ Catalog ]=========================================================================================== */

function buildCatalogPage() {
	$catalogHTML = '';
	$thumb = 'icons/noimage.png';
	$thumbWidth = ATOM_FILE_MAXW;
	$thumbHeight = ATOM_FILE_MAXH;
	$OPposts = getThreads();
	foreach ($OPposts as $post) {
		$id = $post['id'];
		$numOfReplies = getThreadPostsCount($id);
		$OPpostMessage = '';
		if (function_exists('mb_substr') && extension_loaded('mbstring')) {
			$OPpostMessage = tidy_repair_string(
				mb_substr($post['message'], 0, 160, 'UTF-8'),
				array('quiet' => true, 'show-body-only' => true),
				'utf8');
		} else {
			$OPpostMessage = tidy_repair_string(
				substr($post['message'], 0, 160),
				array('quiet' => true, 'show-body-only' => true),
				'utf8');
		}
		$OPpostSubject = $post['subject'];
		$OPuserName = $post['name'] != '' ? $post['name'] : ATOM_POSTERNAME;
		if ($post['thumb0'] != '' && $post['thumb0_width'] > 0 && $post['thumb0_height'] > 0) {
			$thumb = 'thumb/' . $post['thumb0'];
			$thumbWidth = $post['thumb0_width'];
			$thumbHeight = $post['thumb0_height'];
		} else {
			$thumb = 'icons/noimage.png';
			$thumbWidth = ATOM_FILE_MAXW;
			$thumbHeight = ATOM_FILE_MAXH;
		}
		$catalogHTML .= '
			<div class="catalog-block">
				<a href="res/' . $id . '.html">
					<img src="' . $thumb . '" width="' . $thumbWidth . '" height="' . $thumbHeight . '" />
				</a>
				<br>
				<center>' .
					($OPpostSubject ? '
					<span class="filetitle">' . $OPpostSubject . '</span>
					<br>' : '') . '
					<span class="postername">' . $OPuserName . '</span>
					<span>replies: ' . $numOfReplies . '</span>
					<br>
				</center>
				<div class="message" style="text-align: left">' . $OPpostMessage . '</div>
				<br>
			</div>';
	}
	return pageHeader() . '<body>' .
		pageWrapper(ATOM_BOARD_DESCRIPTION . ' / Catalog', true) .
		'<center>' .
			$catalogHTML . '
		</center>' .
		pageFooter(true);
}

/* ==[ Modlog ]============================================================================================ */

function generateModLogForm() {
	$periodStartDate = isset($_POST['from']) ? $_POST['from'] : date("Y-m-d", strtotime("-2 day"));
	$periodEndDate = isset($_POST['to']) ? $_POST['to'] : date("Y-m-d", strtotime("+1 day"));
	return '<form method="post" action="?manage&modlog">
			<fieldset>
				<legend>Select moderation period</legend>
				<span>From:</span>
				<input name="from" type="date" value="' . $periodStartDate . '">
				<span>To: </span>
				<input name="to" type="date" value="' . $periodEndDate . '">&nbsp;
				<input type="submit" value="Show records" class="managebutton">
			</fieldset>
		</form>
		<br>';
}

function generateModLogTable($isModerators = false, $fromtime = '0', $totime = '0') {
	$periodEndDate = '0';
	$periodStartDate = '0';
	if ($isModerators && $fromtime !== '0' && $totime !== '0') {
		$periodEndDate = max($fromtime, $totime);
		$periodStartDate = min($fromtime, $totime);
	}
	$text = '';
	$records = getModLogRecords($isModerators ? '1' : '0', $periodEndDate, $periodStartDate);
	$recordsCount = count($records);
	if ($recordsCount > 0) {
		$text .= ($isModerators ? 'Total Records: ' . $recordsCount : '') . '
		<table class="table"><tbody>
			<tr>
				<th>Date / Time:</th>' .
				($isModerators ? '
				<th>User:</th>' : '') . '
				<th>Action:</th>
			</tr>';
		foreach ($records as $record) {
			$action = $record['action'];
			$text .= '
			<tr' . ($isModerators ? ' style="color: ' .
				($record['color'] != 'Black' ? $record['color'] : '') . '"' : '') . '>
				<td>' . date('d.m.y D H:i:s', $record['timestamp']) . '</td>' .
				($isModerators ? '
				<td>' . $record['username'] . '</td>' : '') . '
				<td>' . $record['action'] . '</td>
			</tr>';
		}
		$text .= '
		</tbody></table><br>';
	}
	return $text;
}

function buildModLogPage() {
	return pageHeader() . '<body>' .
		pageWrapper(ATOM_BOARD_DESCRIPTION . ' / Modlog', true) .
		'<center>
			' . generateModLogTable() . '
		</center>' .
		pageFooter(true);
}
