<?php
declare(strict_types=1);
if (!defined('ATOM_BOARD')) {
	die('');
}

/* ==[ Common elements ]=================================================================================== */

function getCountryIcon(string $ip, ?\GeoIp2\Database\Reader $geoipReader): string {
	$countryCode = getCountryCode(filter_var($ip, FILTER_VALIDATE_IP), $geoipReader);
	return '<img class="poster-country" title="' . $countryCode . '" src="/' . ATOM_BOARD .
		'/icons/flag-icons/' . $countryCode . '.png" alt="' . $countryCode . ' flag">';
}

function getIpUserInfoLink(string $ip): string {
	return '<a href="/' . ATOM_BOARD . '/imgboard.php?manage=&ipinfo=' . $ip .
		'" target="_blank" title="View user IP info">' . $ip . '</a>';
}

/* ==[ Page elements ]===================================================================================== */

function pageHeader(): string {
	return '<!DOCTYPE html>

<html data-theme="' . ATOM_THEME . '">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>' . ATOM_BOARD_DESCRIPTION . '</title>
	<link rel="shortcut icon" href="/' . ATOM_BOARD . '/icons/favicon.png">
	<link rel="stylesheet" type="text/css" href="/' . ATOM_BOARD . '/css/atomboard.css?2026040900">
	<script src="/' . ATOM_BOARD . '/js/atomboard.js?2026040900"></script>
	<script src="/' . ATOM_BOARD .
		'/js/extension/Dollchan_Extension_Tools.user.js?2026040900" async defer></script>' .
	(ATOM_CAPTCHA === 'recaptcha' ? '
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>' : '') . '
</head>
';
}

function pageWrapper(string $description, bool $needReturn): string {
	return '
	<nav id="navigation-top" class="navigation" aria-label="Top menu">' . ATOM_HTML_NAVIGATION . '
		<a class="navigation-link" href="/' . ATOM_BOARD . '/catalog.html" title="Go to catalog">Catalog</a>
		<a class="navigation-link" href="/' . ATOM_BOARD .
			'/' . basename($_SERVER['PHP_SELF']) . '?passcode">Passcode</a>
		<a class="navigation-link" href="/' . ATOM_BOARD .
			'/' . basename($_SERVER['PHP_SELF']) . '?manage">Manage</a>
		<select class="select-style navigation-link" onchange="setThemeStyle(this);">
			<option value="Dark">Dark</option>
			<option value="Light">Light</option>
		</select>
	</nav>
	<main class="wrapper">
		<h1 class="page-title">' . $description . '</h1>
		<hr>
		<div id="panel-top" class="panel">' .
			($needReturn ? '
			<a class="link-button" href="/' . ATOM_BOARD . '/" title="Return to board">Return</a>' : '') . '
			<button class="link-button" title="Navigate to bottom"' .
				' onclick="window.scroll(0, document.body.scrollHeight); return false;">To bottom</button>
		</div>
		';
}

function pageFooter(bool $needReturn): string {
	return '
		<div id="panel-bottom" class="panel">' .
			($needReturn ? '
			<a class="link-button" href="/' . ATOM_BOARD . '/" title="Return to board">Return</a>' : '') . '
			<button class="link-button" title="Navigate to top"' .
				' onclick="window.scroll(0, 0); return false;">To top</button>
		</div>
		<hr>
		<footer>
			<p>
				We are not responsible for the content posted on this site.
				Any information posted here is the responsibility of the user who uploaded it.<br>
				The content on the site is intended for persons over 18 years of age.
			</p>
			<p>- <a href="https://github.com/SthephanShinkufag/atomboard">atomboard</a> -</p>
		</footer>
	</main>
	<nav id="navigation-bottom" class="navigation" aria-label="Bottom menu"> ' . ATOM_HTML_NAVIGATION . '
		<a class="navigation-link" href="/' . ATOM_BOARD . '/catalog.html" title="Go to catalog">Catalog</a>
		<a class="navigation-link" href="/' . ATOM_BOARD .
			'/' . basename($_SERVER['PHP_SELF']) . '?passcode">Passcode</a>
		<a class="navigation-link" href="/' . ATOM_BOARD .
			'/' . basename($_SERVER['PHP_SELF']) . '?manage">Manage</a>
		<select class="select-style navigation-link" onchange="setThemeStyle(this);">
			<option value="Snow" selected>Snow</option>
			<option value="Dark">Dark</option>
			<option value="Light">Light</option>
		</select>
	</nav>
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

function getCaptcha(): string {
	return 	ATOM_CAPTCHA ? '
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
					</tr><tr id="invalidcaptchablock" style="display: none">
						<td class="postblock"></td>
						<td>
							Your pass code seems to be not valid. <a href="/' . ATOM_BOARD .
								'/imgboard.php?passcode" target="_blank">Log In Again?</a>
						</td>
					</tr>' : '';
}

function supportedFileTypes(): string {
	global $atom_uploads;
	if (empty($atom_uploads)) {
		return '';
	}
	$typesAllowed = array_map('strtoupper', array_unique(array_column($atom_uploads, 0)));
	$typesLast = array_pop($typesAllowed);
	$typesFormatted = $typesAllowed ? implode(', ', $typesAllowed) . ' and ' . $typesLast : $typesLast;
	return 'Supported file type' . (count($atom_uploads) !== 1 ? 's are ' : ' is ') . $typesFormatted . '.';
}

function buildPostForm(int $parent, bool $isStaffPost = false): string {
	global $atom_hidefieldsop, $atom_hidefields, $atom_uploads, $atom_embeds;
	$isInThread = $parent !== 0;
	$hideFields = $isInThread ? $atom_hidefields : $atom_hidefieldsop;
	$postformExtra = ['name' => '', 'email' => '', 'subject' => '', 'footer' => ''];
	$inputSubmit = '<input type="submit" value="' .
		($isStaffPost ? 'New post' : ($isInThread ? 'Reply' : 'New thread')) . '" accesskey="z">';
	if ($isStaffPost || !in_array('subject', $hideFields)) {
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
	if (!empty($atom_uploads) && ($isStaffPost || !in_array('file', $hideFields))) {
		if (ATOM_FILE_MAXKB > 0) {
			$maxFileSize = ATOM_PASSCODES_ENABLED ? max(ATOM_FILE_MAXKB, ATOM_FILE_MAXKB_PASS) :
				ATOM_FILE_MAXKB;
			$maxFileSizeInputHtml = '<input type="hidden" name="MAX_FILE_SIZE" value="' .
				strval($maxFileSize * 1024) . '">';
			$maxFileSizeRulesHtml = '<li>Limit: ' . ATOM_FILES_COUNT . ' ' .
				plural('file', ATOM_FILES_COUNT) . ', ' . ATOM_FILE_MAXKBDESC . ' per file' .
				(ATOM_PASSCODES_ENABLED ? ' (' . ATOM_FILE_MAXKBDESC_PASS . ' for <a href="/' . ATOM_BOARD .
					'/imgboard.php?passcode">Passcode users</a>)' : '') . '.</li>';
		}
		$fileTypesHtml = '<li>' . supportedFileTypes() . '</li>';
		$fileInputHtml = '<tr>
						<td class="postblock"></td>
						<td><input type="file" name="file[]" size="35" accesskey="f" multiple></td>
					</tr>';
	}
	if (!empty($atom_embeds) && ($isStaffPost || !in_array('embed', $hideFields))) {
		$embedInputHtml = '<tr>
						<td class="postblock"></td>
						<td><input type="text" class="postform-input" name="embed"' .
							' placeholder="YouTube URL" accesskey="x" autocomplete="off"></td>
					</tr>';
	}
	$reqModHtml = '';
	if (ATOM_REQMOD === 'files' || ATOM_REQMOD === 'all') {
		$reqModHtml = '<li>All posts' . (ATOM_REQMOD === 'files' ? ' with a file attached' : '') .
			' will be moderated before being shown.</li>';
	}
	$thumbnailsHtml = '';
	if (isset($atom_uploads['image/jpeg']) ||
		isset($atom_uploads['image/pjpeg']) ||
		isset($atom_uploads['image/png']) ||
		isset($atom_uploads['image/gif']) ||
		isset($atom_uploads['image/avif']) ||
		isset($atom_uploads['image/webp'])
	) {
		$thumbnailsHtml = '<li>Images greater than ' . ATOM_FILE_MAXWOP . 'x' . ATOM_FILE_MAXHOP . (
			ATOM_FILE_MAXW === ATOM_FILE_MAXWOP && ATOM_FILE_MAXH === ATOM_FILE_MAXHOP ? '' :
				' (new thread) or ' . ATOM_FILE_MAXW . 'x' . ATOM_FILE_MAXH . ' (reply)'
			) . ' will be thumbnailed.</li>';
	}
	$uniquePostersCount = getUniquePostersCount();
	$uniquePosters = $uniquePostersCount > 0 ?
		'<li>' . $uniquePostersCount . ' unique users on the board.</li>' : '';

	// Build postform
	return '<div class="postarea">
			<form name="postform" id="postform" method="post" action="/' . ATOM_BOARD .
				'/imgboard.php" enctype="multipart/form-data">
				' . $maxFileSizeInputHtml .
			(!$isStaffPost ? '
				<input type="hidden" name="parent" value="' . $parent . '">' : '') . '
				<table class="postform-table reply"><tbody>' . (
					$isStaffPost ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="checkbox" name="staffpost" checked style="margin: 0 auto;">
							<span style="font: 12px sans-serif;">Write message as raw HTML</span>
						</td>
					</tr>
					<tr>
						<td class="postblock"></td>
						<td><input type="text" class="postform-input" name="parent" placeholder="' .
							'Reply to (0 = new thread)" maxlength="75" accesskey="t"></td>
					</tr>' : ''
				) . (
					$isStaffPost || !in_array('name', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="name" placeholder="Name"' .
								' maxlength="75" accesskey="n"> ' .
							$postformExtra['name'] . '
						</td>
					</tr>' : ''
				) . (
					$isStaffPost || !in_array('email', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="email" placeholder="Mail"' .
								' maxlength="75" accesskey="e"> ' .
							$postformExtra['email'] . '
						</td>
					</tr>' : ''
				) . (
					$isStaffPost || !in_array('subject', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td style="display: flex;">
							<input type="text" class="postform-input" name="subject" placeholder="Subject"' .
								' maxlength="75" accesskey="s" style="width: 100%" autocomplete="off"> ' .
							$postformExtra['subject'] . '
						</td>
					</tr>' : ''
				) . (
					$isStaffPost || !in_array('message', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<textarea id="message" name="message" placeholder="Message' .
								($isInThread ? ' - reply in thread' : '') . '" accesskey="m"></textarea>
						</td>
					</tr>' : ''
				) . getCaptcha() . '
					' . $fileInputHtml . '
					' . $embedInputHtml .
				(
					$isStaffPost || !in_array('password', $hideFields) ? '
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
					$postformExtra['footer'] !== '' ? '
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

function buildPost(array $post, bool $isInThread = false, string $mode = ''): string {
	$isEditPost = $mode === 'edit';
	$showIP = $mode === 'ip';
	if (!isset($post['omitted'])) {
		$post['omitted'] = 0;
	}

	// Post files
	$postId = (int)$post['id'];
	$thrId = getThreadId($post);
	$isOp = isOp($post);
	$fileHtml = '';
	for ($i = 0; $i < ATOM_FILES_COUNT; $i++) {
		$fileHex = $post['file' . $i . '_hex'];
		if (!$fileHex) {
			continue;
		}
		$fileWidth = (int)$post['image' . $i . '_width'];
		$fileHeight = (int)$post['image' . $i . '_height'];
		$hasSize = $fileWidth > 0 && $fileHeight > 0;
		$fileName = $post['file' . $i];
		$fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
		$isImage = in_array($fileExt, ['jpg', 'png', 'gif', 'avif', 'webp']);
		$isVideo = in_array($fileExt, ['webm', 'mp4', 'mov']);
		$isEmbed = isEmbed($fileHex);
		$fileUrl = $isEmbed ? '#' : '/' . ATOM_BOARD . '/src/' . $fileName;
		$origName = $post['file' . $i . '_original'];
		$linkHtml = 'href="' . $fileUrl . '" target="_blank" onclick="return expandFile(event, this, \'' . 
			($isEmbed ? 'embed' : ($isVideo ? 'video' : ($isImage ? 'image' : 'file'))) . '\');"' .
			($origName !== '' ? ' download="' . $origName . '"' : '');
		$fileInfoHtml = '<a class="file-fullname" ' . $linkHtml . '><span class="file-name">';
		if ($isEmbed) {
			$fileInfoHtml .= $origName . '</span></a>,&nbsp;' . $fileHex;
		} elseif ($fileName !== '') {
			$fileInfoHtml .= pathinfo($origName !== '' ? $origName : $fileName, PATHINFO_FILENAME) .
				'</span>.<span class="file-extension">' . $fileExt . '</span></a><br>(' .
				$post['file' . $i . '_size_formatted'] .
				($hasSize ? ',&nbsp;' . $fileWidth . 'x' . $fileHeight : '') . ')';
		} else {
			continue;
		}
		$fileHtml .= '
						<figure class="post-file">
							<figcaption class="file-info">' .
								($isEditPost ? '
								<input type="checkbox" name="delete-file-mod[]" value="' . $i . '">' : '') . '
								' . $fileInfoHtml . '
							</figcaption>
							<div class="file-wrap"' .
								($isEmbed ? ' data-embed="' . rawurlencode($fileName) . '"' :
								($hasSize ? ' data-width="' . $fileWidth . '" data-height="' .
									$fileHeight . '"' : '')) . '>' .
								($post['thumb' . $i] !== '' ? '
								<a ' . $linkHtml . '>
									<img class="file-thumb' . ($isVideo ? ' file-thumb-video' : '') .
										'" src="/' . ATOM_BOARD . '/thumb/' . $post['thumb' . $i] .
										'" width="' . $post['thumb' . $i . '_width'] .
										'" height="' . $post['thumb' . $i . '_height'] . '" alt="Thumbnail">
								</a>' :
								($isVideo /* If a video has no thumbnail (ffmpeg error) */ ? '
								<a ' . $linkHtml . '>
									<video src="' . $fileUrl . '" class="file-thumb file-thumb-video"></video>
								</a>' : '')) . '
							</div>
						</figure>';
	}

	// Truncate messages on board index pages for readability
	$message = $post['message'];
	if (!$isInThread && !$isEditPost) {
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
				['quiet' => true, 'show-body-only' => true],
				'utf8'
			) . '
						<div class="abbrev">
							Post too long. <a href="/' . ATOM_BOARD . '/res/' . $thrId . '.html#' . $postId .
							'">Click to view</a>.
						</div>';
		}
	}

	// Start post building
	$ip = $post['ip'];
	$omitted = $post['omitted'];
	$likes = $post['likes'];
	$replyBtn = $isOp && !$isInThread ? '<a class="link-button" href="res/' . $postId .
		'.html" title="Reply to thread №' . $postId . '">Reply</a>' : '';
	$reflink = '<a href="/' . ATOM_BOARD . '/res/' . $thrId . '.html#' . $postId . '"';
	$messageHtml = '';
	if ($isEditPost) {
		$token = $_SESSION['atom_token'] ?? '';
		if ($fileHtml !== '') {
			$messageHtml .= '
						<form class="post-files-edit" method="get" action="?">
							<input type="hidden" name="token" value="' . $token . '">
							<input type="hidden" name="manage" value="">
							<input type="hidden" name="delete-files" value="' . $postId . '">' .
							$fileHtml . '
							<div style="margin-left: 20px;">
								<small>Select the checkboxes and action to remove images or make spoilers:
								</small><br>
								<select name="action">
									<option value="delete" selected>Delete files</option>
									<option value="hide">Make spoilers</option>
								</select>
								<input type="submit" value="Apply to selected">
							</div>
						</form>';
		}
		$messageHtml .= '
						<form class="post-message-edit" method="post" action="?manage&editpost=' . $postId .
							'" enctype="multipart/form-data">
							<input type="hidden" name="token" value="' . $token . '">
							<textarea id="message" name="message">' .
								htmlspecialchars($message) .
							'</textarea>
							<input type="submit" value="Save message">
						</form>';
	} else {
		$messageHtml = $fileHtml . '
						<blockquote class="post-message">' .$message . '</blockquote>';
	}
	return '
				<article class="post ' . ($isOp ? 'op' : 'reply') . '" id="post' . $postId . '">
					<header class="post-meta">
						<input type="checkbox" name="delete" value="' . $postId . '">' .
						($post['subject'] !== '' ? '
						<span class="post-subject">' . $post['subject'] . '</span>' : '') . '
						' . (ATOM_GEOIP ? getCountryIcon($ip, null) : '') .
						($showIP || $isEditPost ? '&nbsp;' . getIpUserInfoLink($ip) : '') . '
						' . $post['nameblock'] . '
						<span class="post-id">
							' . $reflink . ' title="Click to link to post" aria-label="Link to post">№</a>
							' . $reflink . ' title="Click to reply to post" aria-label="Reply to post">' .
							$postId . '</a>
						</span>
						<span class="post-buttons">' .
							(ATOM_LIKES ? '
							<span class="like-container">
								<span class="like-icon' . ($likes ? ' like-enabled' : ' like-disabled') .
									'" onclick="sendLike(this, \'' . ATOM_BOARD . '\', ' . $postId . ');">
									<svg><use xlink:href="#symbol-like"></use></svg>
								</span><span class="like-counter">' . ($likes ? $likes : '') . '</span>
							</span>' : '') .
							($post['stickied'] === 1 ? '
							<img src="/' . ATOM_BOARD . '/icons/sticky.png"' .
								' title="Thread is stickied to top" width="16" height="16">' : '') .
							($post['locked'] === 1 ? '
							<img src="/' . ATOM_BOARD . '/icons/locked.png"' .
								' title="Thread is locked for posting" width="11" height="16">' : '') .
							($post['endless'] === 1 ? '
							<img src="/' . ATOM_BOARD . '/icons/endless.png"' .
								' title="Thread is endless" width="16" height="16">' : '') . '
							' . $replyBtn . '
						</span>
					</header>
					<div class="post-body">' .
						$messageHtml . '
					</div>
				</article>' . ($isOp && !$isInThread && $omitted > 0 ? '
				<div class="omittedposts">
					' . $omitted . ' ' . plural('post', $omitted) .
					' omitted. Click ' . $replyBtn . ' to view.
				</div>' : '');
}

/* ==[ Page ]============================================================================================== */

function buildPage(string $htmlPosts, int $parent, int $pages = 0, int $thisPage = 0): string {
	// Build page links: [Previous] [0] [1] [2] [Next]
	$pagelinks = '';
	$isInThread = $parent !== 0;
	if (!$isInThread) {
		$pages = max($pages, 0);
		$pagelinks = ($thisPage === 0 ?
			'<span class="pagelist-previous">[Previous]</span>' :
			'<span class="pagelist-previous">[<a href="' .
				($thisPage === 1 ? 'index' : $thisPage - 1) . '.html">Previous</a>]</span>') . '
			<span class="pagelist-links">';
		for ($i = 0; $i <= $pages; $i++) {
			$pagelinks .= $thisPage === $i ? '[' . $i . '] ' :
				'[<a href="' . ($i === 0 ? "index" : $i) . '.html">' . $i . '</a>] ';
		}
		$pagelinks .= '</span>' . ($pages <= $thisPage ? '
			<span class="pagelist-next">[Next]</span>' : '
			<span class="pagelist-next">[<a href="' . ($thisPage + 1) . '.html">Next</a>]</span>');
	}
	// Build page's body
	return pageHeader() . '<body class="tinyib atomboard de-runned">' .
		pageWrapper(ATOM_BOARD_DESCRIPTION, $isInThread) .
		(ATOM_HTML_INFO_TOP ? ATOM_HTML_INFO_TOP . '
		<hr>
		' : '') .
		buildPostForm($parent) . '
		<hr>
		<form id="delform" method="post" action="/' . ATOM_BOARD . '/imgboard.php?delete">
			<input type="hidden" name="board" value="' . ATOM_BOARD . '">' .
			$htmlPosts . '
			<menu class="userdelete">
				Delete Post <input type="password" name="password" id="deletepostpassword" size="8"' .
					' placeholder="Password">&nbsp;<input name="deletepost" value="Delete" type="submit">
			</menu>
		</form>
		<nav class="pagelist" aria-label="Pages">
			' . $pagelinks . '
		</nav>' .
		(ATOM_HTML_INFO_BOTTOM ? '
		<hr>
		' . ATOM_HTML_INFO_BOTTOM : '') .
		pageFooter($isInThread);
}

/* ==[ Rebuilding ]======================================================================================== */

function rebuildThreadPage(int $thrId): void {
	$htmlPosts = '
			<section class="thread" id="thread' . $thrId . '">';
	$posts = getThreadPosts($thrId);
	foreach ($posts as $post) {
		$htmlPosts .= buildPost($post, true);
	}
	$htmlPosts .= '
			</section>
			<hr>';
	writePage('res/' . $thrId . '.html', buildPage($htmlPosts, $thrId));
}

function rebuildIndexPages(): void {
	$page = 0;
	$i = 0;
	$htmlPosts = '';
	$threads = getThreads();
	$pages = (int)ceil(count($threads) / ATOM_THREADSPERPAGE) - 1;
	foreach ($threads as $thread) {
		$thrId = (int)$thread['id'];
		$replies = getThreadPosts($thrId);
		$thread['omitted'] = max(0, count($replies) - ATOM_PREVIEWREPLIES - 1);
		// Build replies for preview
		$htmlReplies = [];
		for ($j = count($replies) - 1; $j > $thread['omitted']; $j--) {
			$htmlReplies[] = buildPost($replies[$j]);
		}
		$htmlPosts .= '
			<section class="thread" id="thread' . $thrId . '">' .
				buildPost($thread) . implode('', array_reverse($htmlReplies)) . '
			</section>
			<hr>';
		if (++$i >= ATOM_THREADSPERPAGE) {
			$file = $page === 0 ? ATOM_INDEX : $page . '.html';
			writePage($file, buildPage($htmlPosts, 0, $pages, $page));
			$page++;
			$i = 0;
			$htmlPosts = '';
		}
	}
	if ($page === 0 || $htmlPosts !== '') {
		$file = $page === 0 ? ATOM_INDEX : $page . '.html';
		writePage($file, buildPage($htmlPosts, 0, $pages, $page));
	}
	// Create catalog
	writePage('catalog.html', makeCatalogPage());
}

function rebuildThread(int $thrId): void {
	rebuildThreadPage($thrId);
	rebuildIndexPages();
}

/* ==[ Manage ]============================================================================================ */

function manageInfo(string $text): string {
	return '<div class="manage-info">' . $text . '</div>
		';
}

function manageError(string $text): string {
	return '<div class="manage-error">' . $text . '</div>
		';
}

function manageDie(string $text, string $action = ''): void {
	global $loginStatus;
	$onload = '';
	switch ($action) {
	case 'bans': $onload = ' onload="document.form_bans.ip.focus();"'; break;
	case 'ipinfo': $onload = ' onload="document.form_ipinfo.ipinfo.focus();"'; break;
	case 'login': $onload = ' onload="document.form_login_staff.manage_user.focus();"'; break;
	case 'moderate': $onload = ' onload="document.form_moderate_post.moderate.focus();"'; break;
	case 'passcode': $onload = ' onload="document.form_passcode_login.passcode.focus();"'; break;
	case 'passcode_manage': $onload = ' onload="document.form_passcode_manage.block_reason.focus();"'; break;
	case 'passcode_new': $onload = ' onload="document.form_passcode_new.meta.focus();"'; break;
	case 'staffpost': $onload = ' onload="document.postform.parent.focus();"'; break;
	}
	$isAdmin = $loginStatus === 'admin';
	die(pageHeader() . '<body' . $onload . '>' .
		pageWrapper(ATOM_BOARD_DESCRIPTION, true) . (
			$loginStatus === 'disabled' ? '' : '<hr>
		<div class="panel-adminbar">
			<a class="link-button" href="?manage">Status</a>' .
			($isAdmin || $loginStatus === 'moderator' ? '
			<a class="link-button" href="?manage&bans">Bans</a>
			<a class="link-button" href="?manage&passcodes=new">Passcodes</a>' : '') . '
			<a class="link-button" href="?manage&modlog">ModLog</a>
			<a class="link-button" href="?manage&ipinfo=manage">IP info</a>
			<a class="link-button" href="?manage&moderate">Manage post</a>
			<a class="link-button" href="?manage&staffpost">Raw post</a>' .
			($isAdmin ? '
			<a class="link-button" href="?manage&rebuildall">Rebuild All</a>
			<a class="link-button" href="?manage&staff">Staff</a>' : '') . '
			<a class="link-button" href="?manage&account">Account</a>
			<a class="link-button" href="?manage&logout">Log Out</a>
		</div>
		') . '<hr>
		' . $text . '
		<hr>' . pageFooter(true));
}

function makeManageLoginForm(): string {
	return '<h2>Login</h2>
		<form name="form_login_staff" method="post" action="?manage">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Username:</div>
					<input type="text" name="manage_user" required>
				</div>
				<div class="form-row">
					<div class="form-row-label">Password:</div>
					<input type="password" name="manage_password" required>
				</div>
				<input class="link-button" type="submit" value="Log In">
			</div>
		</form>';
}

function makePasscodeLoginForm(string $action, ?array $pass = null): string {
	if ($action === 'login') {
		return '<h2>Enter your passcode</h2>
		<form name="form_passcode_login" method="post" action="?passcode">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Passcode:</div>
					<input type="text" name="passcode" style="width: 400px;" required>
				</div>
				<input class="link-button" type="submit" value="Use Passcode">
			</div>
		</form>';
	} else if ($action === 'valid') {
		return '<center><b>You are using a valid passcode.</b><br>' .
			'<br>Issued: ' . date('d.m.Y D H:i:s', $pass['issued']) .
			'<br>Expires: ' . date('d.m.Y D H:i:s', $pass['expires']) .
			'<br><a href="/' . ATOM_BOARD . '/imgboard.php?passcode&logout">Log Out.</a></center>';
	}
	return '';
}

function makeAdminCreateForm(): string {
	return '<h2>Initial Setup: Create admin account</h2>
		<form method="post" action="?manage">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Admin username:</div>
					<input type="text" name="new_admin_user" required>
				</div>
				<div class="form-row">
					<div class="form-row-label">Admin password:</div>
					<input type="password" name="new_admin_pass" required>
				</div>
				<input class="link-button" type="submit" value="Create account">
			</div>
			<div class="form-notes">
				Don\' forget to erase ATOM_ADMINPASS with empty string in settings.php<br>
				after you create the administrator account.
			</div>
		</form>';
}

function formatTimestamp(int $time): string {
	if ($time === 0) {
		return 'Never';
	}
	$diff = time() - $time;
	if ($diff < 60) {
		return 'Just now';
	}
	if ($diff < 3600) {
		return (int)floor($diff / 60) . 'm ago';
	}
	if ($diff < 86400) {
		return (int)floor($diff / 3600) . 'h ago';
	}
	return date('d.m.Y H:i', $time);
}

function makeStaffManager(string $token): string {
	$html = '<h2>Add new staff member</h2>
		<form method="post" action="?manage&staff">
			<input type="hidden" name="token" value="' . $token . '">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Username:</div>
					<input type="text" name="add_user" required autocomplete="off">
				</div>
				<div class="form-row">
					<div class="form-row-label">Password:</div>
					<input type="password" name="add_pass" required>
				</div>
				<div class="form-row">
					<div class="form-row-label">Role:</div>
					<select name="add_role">
						<option value="moderator">Moderator</option>
						<option value="janitor">Janitor</option>
					</select>
				</div>
				<input class="link-button" type="submit" value="Create account">
			</div>
		</form>
		<hr>
		<h2>Staff Management</h2>
		<table class="table"><thead>
			<tr>
				<th>Username</th>
				<th>Role</th>
				<th>Last Login</th>
				<th>Action</th>
			</tr></thead><tbody>';
	$staffList = getAllStaffMembers();
	foreach ($staffList as $person) {
		$lastSeen = formatTimestamp((int)$person['last_login']);
		$username = htmlspecialchars($person['username']);
		$html .= '
			<tr>
				<td>' . $username . '</td>
				<td>' . ucfirst($person['role']) . '</td>
				<td title="' . date('Y-m-d H:i:s', $person['last_login']) . '">' .
					$lastSeen . '</td>
				<td>' . (
					$person['role'] === 'admin' ? '---' :
					'<a href="?manage&staff&delete_staff=' . $person['id'] .
						'" onclick="return confirm(\'Delete ' . $username . '?\')">Delete</a>') . '</td>
			</tr>';
	}
	$html .= '
		</tbody></table>';
	return $html;
}

function makeChangePasswForm(string $token): string {
	return '<h2>Account settings (' . htmlspecialchars($_SESSION['atom_user']) . ')</h2>
		<form method="post" action="?manage&account">
			<input type="hidden" name="token" value="' . $token . '">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Old password:</div>
					<input type="password" name="old_pass" required>
				</div>
				<div class="form-row">
					<div class="form-row-label">New password:</div>
					<input type="password" name="new_pass" required>
				</div>
				<div class="form-row">
					<div class="form-row-label">Confirm password:</div>
					<input type="password" name="confirm_pass" required>
				</div>
				<input class="link-button" type="submit" value="Update Password">
			</div>
		</form>';
}

function makeBansTable(array $bans): string {
	$geoipReader = ATOM_GEOIP === 'geoip2' ?
		new GeoIp2\Database\Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb') : null;
	$bansHtml = '
		<table class="table"><thead>
			<tr>
				<th>IP address</th>
				<th>Set at</th>
				<th>Expires</th>
				<th>Reason provided</th>
				<th>&nbsp;</th>
			</tr>
		</thead><tbody>';
	foreach ($bans as $ban) {
		$expire = (int)$ban['expire'];
		if ($expire === 1) {
			$expireText = 'Warning';
		} else if ($expire > 0) {
			$expireText = date('d.m.Y D H:i:s', $expire);
		} else {
			$expireText = 'Does not expire';
		}
		$ipFrom = (int)$ban['ip_from'];
		$ipTo = (int)$ban['ip_to'];
		$bansHtml .= '
			<tr>
				<td style="white-space: nowrap;">' .
					(ATOM_GEOIP ? getCountryIcon(long2ip($ipFrom), $geoipReader) . '&nbsp;' : '') .
					getIpUserInfoLink(ip2cidr($ipFrom, $ipTo)) . '</td>
				<td>' . date('d.m.Y D H:i:s', $ban['timestamp']) . '</td>
				<td>' . $expireText . '</td><td>' . ($ban['reason'] !== '' ?
					htmlentities($ban['reason'], ENT_QUOTES, 'UTF-8') : '&nbsp;') . '</td>
				<td><a href="?manage&bans&lift=' . $ban['id'] . '">lift</a></td>
			</tr>';
	}
	return $bansHtml . '
		</tbody></table>';
}

function makeIpField(string $ip, string $formName, string $fieldName): string {
	return '
				<div class="form-row">
					<div class="form-row-label">IP address (CIDR format):</div>
					<input type="text" name="' . $fieldName . '" value="' . $ip .
						'" placeholder="0.0.0.0" required>
					<div>
						<button class="link-button" onclick="var el = document.' . $formName . '.' .
						$fieldName . '; el.value = el.value.split(\'/\')[0] + \'/24\'; return false;">
							subnet /24</button>
						<button class="link-button" onclick="var el = document.' . $formName . '.' .
						$fieldName . '; el.value = el.value.split(\'/\')[0] + \'/16\'; return false;">
							subnet /16</button>
					</div>
				</div>';
}

function makeBansManager(string $token): string {
	global $atom_ban_reasons;
	$banReasons = '';
	if (!empty($atom_ban_reasons)) {
		$banReasonsLen = count($atom_ban_reasons);
		for ($i = 0; $i < $banReasonsLen; $i++) {
			$banReasons .= '
						<option value="' . $atom_ban_reasons[$i] . '">' .
							$atom_ban_reasons[$i] . '</option>';
		}
	}
	$bans = getAllBans();
	$bansCount = count($bans);
	$bansHtml = '<h2>Ban an IP address</h2>
		<form name="form_bans" method="post" action="?manage&bans">
			<input type="hidden" name="token" value="' . $token . '">
			<div class="form-container">' .
				makeIpField($_GET['bans'], 'form_bans', 'ip') . '
				<div class="form-row">
					<div class="form-row-label">Expire (sec):</div>
					<input type="text" name="expire" value="0">
					<div>
						<button class="link-button" onclick="document.form_bans.expire.value = ' .
							'\'3600\'; return false;">1hr</button>
						<button class="link-button" onclick="document.form_bans.expire.value = ' .
							'\'86400\'; return false;">1d</button>
						<button class="link-button" onclick="document.form_bans.expire.value = ' .
							'\'172800\'; return false;">2d</button>
						<button class="link-button" onclick="document.form_bans.expire.value = ' .
							'\'604800\'; return false;">1w</button>
						<button class="link-button" onclick="document.form_bans.expire.value = ' .
							'\'1209600\'; return false;">2w</button>
						<button class="link-button" onclick="document.form_bans.expire.value = ' .
							'\'2592000\'; return false;">30d</button>
						<button class="link-button" onclick="document.form_bans.expire.value = ' .
							'\'0\'; return false;">never</button>
						<button class="link-button" onclick="document.form_bans.expire.value = ' .
							'\'1\'; return false;">warning</button>
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Reason (optional):</div>
					<input type="text" name="reason">' .
					($banReasons ? '
					<select onchange="var el = document.form_bans.reason; el.value = this.value;' .
						' el.style.display = el.value ? \'none\' : \'\'">
						<option value="">- Select ban reason -</option>' .
						$banReasons . '
					</select>' : '') . '
				</div>
				<div class="form-row">
					<div class="form-row-label">Thread number (empty to delete all posts / threads):</div>
					<input type="text" name="thrid" value="' . ($_GET['thrid'] ?? '') . '">
				</div>
				<input class="link-button" type="submit"' .
					' name="ban_delall" value="Ban + DelAll" style="width: 100%;"' .
					' onclick="return confirm(\'Are you sure to ban and delete all posts?\')">
				<input class="link-button" type="submit" name="ban" value="Ban" style="width: 100%;">
			</div>
		</form>
		<hr>
		<h2>Current bans</h2>
		<center>Total bans: ' . $bansCount . '</center>';
	if ($bansCount > 0) {
		$bansHtml .= makeBansTable($bans);
	}
	return $bansHtml;
}

function makePasscodesManager(string $token): string {
	global $loginStatus;
	$isAdmin = $loginStatus === 'admin';
	$passHtml = '';
	if ($isAdmin) {
		$passHtml .= '<h2>Issue a new passcode</h2>
		<form name="form_passcode_new" method="post" action="?manage&issuepasscode">
			<input type="hidden" name="token" value="' . $token . '">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Passcode duration (sec):</div>
					<input type="text" name="expires" value="31536000" required>
					<div>
						<button class="link-button" onclick="document.form_passcode_new.expires.value = ' .
							'\'2592000\'; return false;">30d</button>
						<button class="link-button" onclick="document.form_passcode_new.expires.value = ' .
							'\'15780000\'; return false;">6m</button>
						<button class="link-button" onclick="document.form_passcode_new.expires.value = ' .
							'\'31536000\'; return false;">1y</button>
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Meta (optional info):</div>
					<input type="text" name="meta">
				</div>
				<div class="form-row">
					<div class="form-row-label">Meta for Admin (optional):</div>
					<input type="text" name="meta_admin">
				</div>
				<input class="link-button" type="submit" value="Submit">
			</div>
		</form>
		<hr>
		';
	}
	$passNum = (int)($_GET['passcode'] ?? 0);
	if ($passNum > 0) {
		$editPass = passByNum($passNum);
	}
	$passHtml .= '<h2>Manage the passcode</h2>
		<form name="form_passcode_manage" method="post" action="?manage&managepasscode">
			<input type="hidden" name="token" value="' . $token . '">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Passcode number:</div>
					<input type="text" name="id" value="' . ($passNum ?: '') . '" required>
				</div>
				<div class="form-row">
					<div class="form-row-label">Meta (related info):</div>
					<input type="text" name="meta" value="' .
						($editPass['meta'] ?? '') . '" size="50">
				</div>' .
				($isAdmin ? '
				<div class="form-row">
					<div class="form-row-label">Expires:</div>
					<input type="datetime-local" name="expires" value="' .
						(isset($editPass['expires']) ? date('Y-m-d\TH:i', $editPass['expires']) : '') .
						'" required>
				</div>' : '') . '
				<div class="form-row">
					<div class="form-row-label">Block till:</div>
					<input type="datetime-local" name="block_till" value="' .
						(isset($editPass['blocked_till']) && $editPass['blocked_till'] ?
							date('Y-m-d\TH:i', $editPass['blocked_till']) : '') . '">
					<div>
						<button class="link-button" onclick="this.parentNode.previousElementSibling.value = ' .
							'new Date(Date.now() + 36E5 - (new Date).getTimezoneOffset() * 6E4).toISOString().slice(0, 16); return false;">1hr</button>
						<button class="link-button" onclick="this.parentNode.previousElementSibling.value = ' .
							'new Date(Date.now() + 864E5 - (new Date).getTimezoneOffset() * 6E4).toISOString().slice(0, 16); return false;">1d</button>
						<button class="link-button" onclick="this.parentNode.previousElementSibling.value = ' .
							'new Date(Date.now() + 1728E5 - (new Date).getTimezoneOffset() * 6E4).toISOString().slice(0, 16); return false;">2d</button>
						<button class="link-button" onclick="this.parentNode.previousElementSibling.value = ' .
							'new Date(Date.now() + 6048E5 - (new Date).getTimezoneOffset() * 6E4).toISOString().slice(0, 16); return false;">1w</button>
						<button class="link-button" onclick="this.parentNode.previousElementSibling.value = ' .
							'new Date(Date.now() + 12096E5 - (new Date).getTimezoneOffset() * 6E4).toISOString().slice(0, 16); return false;">2w</button>
						<button class="link-button" onclick="this.parentNode.previousElementSibling.value = ' .
							'new Date(Date.now() + 2592E6 - (new Date).getTimezoneOffset() * 6E4).toISOString().slice(0, 16); return false;">30d</button>
						<button class="link-button" onclick="this.parentNode.previousElementSibling.value = \'\'; return false;">unblock</button>
					</div>
				</div>
				<div class="form-row">
					<div class="form-row-label">Block reason:</div>
					<input type="text" name="block_reason" value="' .
						($editPass['blocked_reason'] ?? '') . '" size="50">
				</div>
				<input class="link-button" type="submit" value="Submit">
			</div>
		</form>';
	$passcodes = getAllPasscodes();
	$passCount = count($passcodes);
	if ($passCount > 0) {
		$passHtml .= '
		<hr>
		<h2>Issued passcodes</h2>
		<center>Total passcodes: ' . $passCount . '</center>
		<table class="table"><thead>
			<tr>
				<th>№</th>' .
				($isAdmin ? '
				<th>ID (admin)</th>
				<th>Meta (admin)</th>' : '') . '
				<th>Meta</th>
				<th>Set at</th>
				<th>Expires</th>
				<th>Blocked till</th>
				<th>Blocked reason</th>
				<th>Last used</th>
				<th>Last used IP</th>
			</tr></thead><tbody>';
		$passcodes = getAllPasscodes();
		$geoipReader = ATOM_GEOIP === 'geoip2' ?
			new GeoIp2\Database\Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb') : null;
		foreach ($passcodes as $pass) {
			$ip = $pass['last_used_ip'];
			$countryIcon = '';
			$passcodeNum = $pass['number'];
			$blockedTill = $pass['blocked_till'];
			$nowTime = time();
			$passHtml .= '
			<tr' . ($nowTime > $pass['expires'] ? ' class="passcode-expired"' :
				($nowTime < $blockedTill ? ' class="passcode-blocked"' : '')) . '>
				<td><a target="_blank" href="/' . ATOM_BOARD . '/imgboard.php?manage=&passcode=' .
					$passcodeNum . '&passcodes=manage" title="Manage passcode №' . $passcodeNum . '">' .
					$passcodeNum . '</a></td>' .
				($isAdmin ? '
				<td style="width: 200px; word-break: break-all;">' . $pass['id'] . '</td>
				<td>' . $pass['meta_admin'] . '</td>' : '') . '
				<td>' . str_replace('[donator]', '<img class="poster-achievement" height="18"' .
					' title="Donator" src="/' . ATOM_BOARD . '/icons/donator.png">', $pass['meta']) . '</td>
				<td>' . date('d.m.Y H:i:s', $pass['issued']) . '</td>
				<td>' . date('d.m.Y H:i:s', $pass['expires']) . '</td>
				<td>' . ($blockedTill ? date('d.m.Y H:i:s', $blockedTill) : '') . '</td>
				<td>' . $pass['blocked_reason'] . '</td>
				<td>' . ($pass['last_used'] ? date('d.m.Y H:i:s', $pass['last_used']) : '') . '</td>
				<td style="white-space: nowrap;">' . ($ip ?
					(ATOM_GEOIP ? getCountryIcon($ip, $geoipReader) . '&nbsp;' : '') .
					getIpUserInfoLink($ip) : '') . '</td>
			</tr>';
		}
		$passHtml .= '
		</tbody></table>';
	} else {
		$passHtml .= '
		<center>No passcodes issued yet.</center>';
	}
	return $passHtml;
}

function makeUserInfoForm(string $ip = ''): string {
	return '<h2>View user IP info</h2>
		<form name="form_ipinfo" method="get" action="?">
			<input type="hidden" name="manage" value="">
			<div class="form-container">' .
				makeIpField($ip, 'form_ipinfo', 'ipinfo') . '
				<input class="link-button" type="submit" value="Submit">
			</div>
		</form>';
}

function makeUserInfoManager(string $token, string $ip, array $posts): string {
	global $loginStatus;
	$isMod = $loginStatus === 'admin' || $loginStatus === 'moderator';
	$postsHtml = '';
	foreach ($posts as $post) {
		$postsHtml .= '
			<tr><th class="panel-adminbar">' . makePostManageButtons($token, $post) . '
			</th></tr>
			<tr><td>' . buildPost($post, false, 'ip') . '
			</td></tr>';
	}
	$ban = banByIP($ip);
	$banHtml = $ban ? makeBansTable([$ban]) : '';
	$ipLookupHtml = '';
	if (ATOM_IPLOOKUPS_KEY) {
		$ipLookup = lookupByIP($ip);
		if ($ipLookup) {
			$red = ' style="background: #ff000060;">1';
			$ipLookupHtml = '<table class="table" style="width: auto; margin: 0 auto;">
		<thead><tr><th>Type</th><th>Status</th></tr></thead>
		<tbody>
			<tr><td>Abuser</td><td' . ($ipLookup['abuser'] ? $red : '>0') . '</td></tr>
			<tr><td>VPS</td><td' . ($ipLookup['vps'] ? $red : '>0') . '</td></tr>
			<tr><td>Proxy</td><td' . ($ipLookup['proxy'] ? $red : '>0') . '</td></tr>
			<tr><td>TOR</td><td' . ($ipLookup['tor'] ? $red : '>0') . '</td></tr>
			<tr><td>VPN</td><td' . ($ipLookup['vpn'] ? $red : '>0') . '</td></tr>
		</tbody></table>';
		} else {
			$ipLookupHtml = '<center>This IP has not yet been verified.</center>';
		}
	}
	return 	makeUserInfoForm($ip) . '
		<hr>
		<h2>Moderating IP ' . $ip . '</h2>
		<div class="form-container">' .
			getIpModBtns($token, $loginStatus, $ip) . '
		</div>
		<hr>
		<h2>Bans and warnings</h2>' .
		($ban ? '
		<table class="table"><thead>' .
			$banHtml . '
		</tbody></table>' : '
		<center>This IP has no bans or warnings.</center>') .
		(ATOM_IPLOOKUPS_KEY ? '
		<hr>
		<h2>IP Lookup</h2>
		' . $ipLookupHtml : '') . '
		<hr>
		<h2>User posts and threads</h2>' .
		($postsHtml ? '
		<table class="table-posts"><tbody>' .
			$postsHtml . '
		</tbody></table>' : '
		<center>No posts or threads from this IP on this board.</center>');
}

function makeReportPostForm(array $post): string {
	$isOp = isOp($post);
	return '<h2>Report a ' . ($isOp ? 'thread' : 'post') . ' to moderators</h2>
		<form name="form_report_post" method="post" action="?report&addreport">
			<input type="hidden" name="id" value="' . $post['id'] . '">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Reason:</div>
					<input type="text" name="reason" required>
				</div>
				<table><tbody>' .
					getCaptcha() . '
				</tbody></table>
				<input class="link-button" type="submit" value="Send a report">
			</div>
		</form>
		<hr>
		<h2>' . ($isOp ? 'OP-post' : 'Post') . ' view</h2>' .
		buildPost($post);
}

function makePostModForm(): string {
	return '<h2>Moderate a post</h2>
		<form name="form_moderate_post" method="get" action="?">
			<input type="hidden" name="manage" value="">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">Post ID:</div>
					<input type="text" name="moderate" required>
				</div>
				<input class="link-button" type="submit" value="Submit">
			</div>
			<div class="form-notes">
				Tip: while browsing the imageboard, you can moderate a post if you\'re logged in.<br>
				Check the box next to a post and click "Delete" at the bottom of the page,
				with a blank password.
			</div>
		</form>';
}

function getPostReports(array $reports, ?\GeoIp2\Database\Reader $geoipReader): string {
	$reportsHtml = '';
	foreach ($reports as $report) {
		$ip = $report['ip'];
		$reportsHtml .= '
				<article class="reply report">
					&nbsp;' . (ATOM_GEOIP ? getCountryIcon($ip, $geoipReader) . '&nbsp;' : '') .
					getIpUserInfoLink($ip) . '
					(' . date('d.m.y D H:i:s', $report['timestamp']) . ')
					<br>
					<blockquote class="post-message">' . $report['reason'] . '</blockquote>
				</article>';
	}
	return $reportsHtml;
}

function getPostModBtn(string $token, string $label, string $description, array $params,
	string $icon = '', string $confirm = ''
): string {
	$hiddenInputs = '<input type="hidden" name="token" value="' . $token . '">';
	foreach ($params as $name => $value) {
		$hiddenInputs .= '
					<input type="hidden" name="' . htmlspecialchars($name) .
					'" value="' . htmlspecialchars((string)$value) . '">';
	}
	$iconHtml = $icon ? '<img src="/' . ATOM_BOARD . '/icons/' . $icon .
		'" width="16" height="16" style="vertical-align: -3px;"> ' : '';
	return '
			<div class="mod-row">
				<form method="post" action="?manage"' .
					($confirm ? ' onclick="return confirm(\'' . addslashes($confirm) . '\')"' : '') . '>
					' . $hiddenInputs . '
					<button type="submit" class="mod-button link-button">' . $iconHtml . $label . '</button>
				</form>
				<div class="mod-description">' . $description . '</div>
			</div>';
}

function getIpModBtns(string $token, string $loginStatus, string $ip, ?int $thrId = null): string {
	$modButtons = getPostModBtn($token,
		'Delete all',
		'Delete all posts and threads in /' . ATOM_BOARD . ' from IP ' . $ip,
		['delall' => $ip],
		'',
		'Are you sure to delete ALL POSTS AND THERADS in /' . ATOM_BOARD . ' from IP ' . $ip . '?');
	$isBanned = banByIP($ip);
	$isMod = $loginStatus === 'admin' || $loginStatus === 'moderator';
	$modButtons .= '
			<div class="mod-row">
				<form method="get" action="?">
					<input type="hidden" name="manage" value="">
					<input type="hidden" name="bans" value="' . $ip . '">' .
					(isset($thrId) ? '
					<input type="hidden" name="thrid" value="' . $thrId . '">' : '') . '
					<button type="submit" class="mod-button link-button"' .
						($isBanned || !$isMod ? ' disabled' : '') . '>' . 
						($isBanned ? 'Already banned!' : 'Ban user') . '</button>
				</form>
				<div class="mod-description">' . ($isBanned ? 'Ban record exists for IP ' . $ip :
					($isMod ? 'Ban IP ' . $ip : 'Janitors can\'t ban')) . '</div>
			</div>';
	return $modButtons;
}

function makePostModManager(string $token, array $post): string {
	global $loginStatus;
	$postId = (int)$post['id'];
	$isOp = isOp($post);

	// Post modreation buttons
	$modButtons = '';
	if ($isOp) {
		$isStickied = $post['stickied'] === 1;
		$modButtons .= getPostModBtn($token,
			($isStickied ? 'Unsticky' : 'Sticky') . ' thread',
			$isStickied ? 'Return to normal state' : 'Keep at the top of the board',
			['stick' => $postId, 'setsticky' => ($isStickied ? 0 : 1)],
			'sticky.png');
		$isLocked = $post['locked'] === 1;
		$lockedValue = $isLocked ? 'Unlock' : 'Lock';
		$modButtons .= getPostModBtn($token,
			$lockedValue . ' thread',
			$lockedValue . ' for posting',
			['lock' => $postId, 'setlocked' => ($isLocked ? 0 : 1)],
			'locked.png');
		$isEndless = $post['endless'] === 1;
		$modButtons .= getPostModBtn($token,
			'Make ' . ($isEndless ? 'non-endless' : 'endless'),
			($isEndless ? 'Disable' : 'Enable') . ' endless mode for this thread',
			['endless' => $postId, 'setendless' => ($isEndless ? 0 : 1)],
			'endless.png');
	}
	$modButtons .= getPostModBtn($token,
		'Delete ' . ($isOp ? 'thread' : 'post'),
		$isOp ? 'This will delete the entire thread' : 'This will delete the post',
		['delete' => $postId]);
	$ip = $post['ip'];
	$thrId = ((int)$post['parent']) ?: $postId;
	$modButtons .= getPostModBtn($token,
		'DelAll in thread',
		'Delete all posts from IP ' . $ip . ' in thread <a href="/' . ATOM_BOARD . '/res/' . $thrId .
			'.html#' . $postId . '" target="_blank">№' . $thrId . '</a>',
		['delall' => $ip, 'thrid' => $thrId],
		'',
		'Are you sure to delete all posts from IP ' . $ip . ' in thread №' . $thrId . '?');
	$modButtons .= getIpModBtns($token, $loginStatus, $ip, $thrId);
	$passcodeNum = ATOM_PASSCODES_ENABLED ? $post['pass'] : 0;
	if ($passcodeNum) {
		$modButtons .= '
			<div class="mod-row">
				<a class="mod-button link-button" target="_blank" href="/' . ATOM_BOARD .
					'/imgboard.php?manage=&passcode=' . $passcodeNum . '&passcodes=manage">Manage passcode</a>
				<div class="mod-description">Manage passcode №' . $passcodeNum .'</div>
			</div>';
	}
	$reports = reportsByPostID($postId);
	$reportsCount = count($reports);
	if ($reportsCount) {
		$modButtons .= getPostModBtn($token,
			'Close reports',
			'Delete all related reports',
			['deletereports' => $postId]);
	}

	// Likes table
	$geoipReader = ATOM_GEOIP === 'geoip2' ?
		new GeoIp2\Database\Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb') : null;
	$likes = likesByPostID($postId);
	$likesHtml = '';
	foreach ($likes as $like) {
		$likeIP = $like['ip'];
		$likesHtml .= '
			<tr><td>' . (ATOM_GEOIP ? getCountryIcon($likeIP, $geoipReader) . '&nbsp;' : '') .
				getIpUserInfoLink($likeIP) . '</td></tr>';
	}

	return '<h2>Moderating ' . ($isOp ? 'thread' : 'post') . ' №' . $postId . '</h2>
		<div class="form-container">' .
			$modButtons . '
		</div>' .
		($reportsCount ? '
		<hr>
		<h2>Reports</h2>' .
		getPostReports($reports, $geoipReader) : '') . '
		<hr>
		<h2>' . ($isOp ? 'OP-post' : 'Post') . ' view</h2>' .
		buildPost($post, false, 'edit') .
		($likesHtml ? '
		<hr>
		<h2>Likes received: ' . count($likes) . '</h2>
		<table class="table"><thead>
			<tr><th>IP</th></tr>
		</thead><tbody>' .
			$likesHtml . '
		</tbody></table>' : '');
}

function getPostManageBtn(string $token, string $label, array $params,
	string $title = '', string $confirm = ''
): string {
	$inputs = '<input type="hidden" name="token" value="' . $token . '">';
	foreach ($params as $name => $value) {
		$inputs .= '
					<input type="hidden" name="' . $name . '" value="' . $value . '">';
	}
	return '
				<form method="post" action="?manage"' .
					($confirm ? ' onclick="return confirm(\'' . addslashes($confirm) . '\')"' : '') . '>
					' . $inputs . '
					<button type="submit" class="link-button" title="' . htmlspecialchars($title) . '">' .
						$label . '</button>
				</form>';
};

function makePostManageButtons(string $token, array $post): string {
	global $loginStatus;
	$postId = (int)$post['id'];
	$thrId = ((int)$post['parent']) ?: $postId;
	$ip = $post['ip'];
	$isOp = isOp($post);
	$result = '
				<a class="link-button" target="_blank" href="/' . ATOM_BOARD .
					'/imgboard.php?manage&moderate=' . $postId . '" title="Advanced options">Manage ' .
					($isOp ? 'thread' : 'post') . '</a>';
	$result .= getPostManageBtn($token,
		$isOp ? 'Delete thread' : 'Delete post',
		['delete' => $postId],
		$isOp ? 'Delete entire thread' : 'Delete post');
	$result .= getPostManageBtn($token,
		'DelAll in thread',
		['delall' => $ip, 'thrid' => $thrId],
		'Delete all posts from IP ' . $ip . ' in thread №' . $thrId, 
		'Are you sure to delete all posts from IP ' . $ip . ' in thread №' . $thrId . '?');
	$result .= getPostManageBtn($token, 'Delete all', ['delall' => $ip],
		'Delete all posts and threads in /' . ATOM_BOARD . ' from IP ' . $ip, 
		'Are you sure to delete ALL POSTS AND THERADS in /' . ATOM_BOARD . ' from IP ' . $ip . '?');
	if ($loginStatus === 'admin' || $loginStatus === 'moderator') {
		$isBanned = banByIP($ip);
		$result .= '
				<a class="link-button" target="_blank" href="/' . ATOM_BOARD .
					'/imgboard.php?manage=&bans=' . $ip . '&thrid=' . $thrId . '" title="' . 
					($isBanned ? 'Already banned!' : 'Ban IP ' . $ip) .'">' .
					($isBanned ? 'Banned!' : 'Ban user') . '</a>';
	}
	$passcodeNum = ATOM_PASSCODES_ENABLED ? $post['pass'] : 0;
	if ($passcodeNum) {
		$result .= '
				<a class="link-button" target="_blank" href="/' . ATOM_BOARD .
					'/imgboard.php?manage=&passcode=' . $passcodeNum .
					'&passcodes=manage" title="Manage passcode №' . $passcodeNum .'">Manage passcode</a>';
	}
	return $result;
}

function makeStatusManager(string $token): string {
	global $loginStatus;

	// Build reports table
	$reports = getAllReports();
	$reportsCount = count($reports);
	$reportsHtml = '';
	if ($reportsCount) {
		$geoipReader = ATOM_GEOIP === 'geoip2' ?
			new GeoIp2\Database\Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb') : null;
		$reportsByPost = [];
		foreach ($reports as $report) {
			$postId = (int)$report['postnum'];
			if (!isset($reportsByPost[$postId])) {
				$reportsByPost[$postId] = [];
			}
			$reportsByPost[$postId][] = $report;
		}
		foreach (array_keys($reportsByPost) as $postId) {
			$post = getPost($postId);
			if (!$post) {
				continue;
			}
			$reportsHtml .= '
			<tr><th class="panel-adminbar">' .
				getPostManageBtn($token,
					'Close reports',
					['deletereports' => $postId],
					'Delete all related reports') .
				makePostManageButtons($token, $post) . '
			</th></tr>
			<tr><td>' .
				buildPost($post, false, 'ip') .
				getPostReports($reportsByPost[$postId], $geoipReader) . '
			</td></tr>';
		}
	}

	// Build posts requiring premoderation
	$reqModPostHtml = '';
	if (ATOM_REQMOD === 'files' || ATOM_REQMOD === 'all') {
		$reqModPosts = getLatestPosts(false, 20);
		foreach ($reqModPosts as $post) {
			$reqModPostHtml .= '
			<tr><th class="panel-adminbar">' .
				getPostManageBtn($token,
					'Approve',
					['approve' => $post['id']],
					'Allow to be published') .
				makePostManageButtons($token, $post) . '
			</th></tr>
			<tr><td>' . buildPost($post, false, 'ip') . '
			</td></tr>';
		}
	}

	// Build recent posts table
	$postsHtml = '';
	$recentCount = 100;
	$posts = getLatestPosts(true, $recentCount);
	foreach ($posts as $post) {
		$postsHtml .= '
			<tr><th class="panel-adminbar">' . makePostManageButtons($token, $post) . '
			</th></tr>
			<tr><td>' . buildPost($post, false, 'ip') . '
			</td></tr>';
	}

	// Build status page
	$threads = getThreadsCount();
	$bans = count(getAllBans());
	$uniquePostersCount = getUniquePostersCount();
	$uniquePosters = $uniquePostersCount > 0 ? $uniquePostersCount . ' unique users' : '';
	return '<h2>Status</h2>
		<center>' . $threads . ' ' . plural('thread', $threads) . ', ' . $bans . ' ' . plural('ban', $bans) .
			', ' . $reportsCount . ' ' . plural('report', $reportsCount) . ', ' . $uniquePosters .
		'</center>' .
		($reportsCount ? '
		<hr>
		<h2>Reports</h2>
		<div class="form-container">' .
			getPostModBtn($token,
				'Close all',
				'Clear all reports on the board',
				['deleteallreports' => ''],
				'',
				'Are you sure to close ALL REPORTS on /' . ATOM_BOARD . '?') . '
		</div>
		<table class="table-posts"><tbody>' .
			$reportsHtml . '
		</tbody></table>' : '') .
		((ATOM_REQMOD === 'files' || ATOM_REQMOD === 'all') && $reqModPostHtml !== '' ? '
		<hr>
		<h2>Pending posts</h2>
		<table class="table-posts"><tbody>' .
			$reqModPostHtml . '
		</tbody></table>' : '') . '
		<hr>
		<h2>Recent ' . $recentCount . ' posts</h2>
		<table class="table-posts"><tbody>' .
			$postsHtml . '
		</tbody></table>';
}

/* ==[ Catalog ]=========================================================================================== */

function makeCatalogPage(): string {
	$catalogHtml = '';
	$thumb = 'icons/noimage.png';
	$thumbWidth = ATOM_FILE_MAXW;
	$thumbHeight = ATOM_FILE_MAXH;
	$OPposts = getThreads();
	foreach ($OPposts as $post) {
		$postId = (int)$post['id'];
		$numOfReplies = getThreadPostsCount($postId);
		$OPpostMessage = '';
		if (function_exists('mb_substr') && extension_loaded('mbstring')) {
			$OPpostMessage = tidy_repair_string(
				mb_substr($post['message'], 0, 160, 'UTF-8'),
				['quiet' => true, 'show-body-only' => true],
				'utf8');
		} else {
			$OPpostMessage = tidy_repair_string(
				substr($post['message'], 0, 160),
				['quiet' => true, 'show-body-only' => true],
				'utf8');
		}
		$opSubject = $post['subject'];
		$opUserName = $post['name'] ?: ATOM_POSTERNAME;
		if ($post['thumb0'] !== '' && $post['thumb0_width'] > 0 && $post['thumb0_height'] > 0) {
			$thumb = 'thumb/' . $post['thumb0'];
			$thumbWidth = $post['thumb0_width'];
			$thumbHeight = $post['thumb0_height'];
		} else {
			$thumb = 'icons/noimage.png';
			$thumbWidth = ATOM_FILE_MAXW;
			$thumbHeight = ATOM_FILE_MAXH;
		}
		$catalogHtml .= '
			<div class="catalog-block">
				<a href="res/' . $postId . '.html">
					<img src="' . $thumb . '" width="' . $thumbWidth . '" height="' . $thumbHeight . '" />
				</a>
				<br>
				<center>' .
					($opSubject ? '
					<span class="post-subject">' . $opSubject . '</span>
					<br>' : '') . '
					<span class="poster-name">' . $opUserName . '</span>
					<span>replies: ' . $numOfReplies . '</span>
					<br>
				</center>
				<blockquote class="post-message" style="text-align: left">' . $OPpostMessage . '</blockquote>
				<br>
			</div>';
	}
	return pageHeader() . '<body>' .
		pageWrapper(ATOM_BOARD_DESCRIPTION . ' / Catalog', true) .
		'<center>' .
			$catalogHtml . '
		</center>' .
		pageFooter(true);
}

/* ==[ Modlog ]============================================================================================ */

function makeModLogManager(string $token, string $startDate, string $endDate): string {
	$html = '<h2>Moderation period</h2>
		<form method="post" action="?manage&modlog">
			<input type="hidden" name="token" value="' . $token . '">
			<div class="form-container">
				<div class="form-row">
					<div class="form-row-label">From:</div>
					<input type="date" name="from" value="' . $startDate . '">
				</div>
				<div class="form-row">
					<div class="form-row-label">To:</div>
					<input type="date" name="to" value="' . $endDate . '">
				</div>
				<input class="link-button" type="submit" value="Show records">
			</div>
		</form>
		<hr>
		';
	$records = getModLogRecords((int)strtotime($startDate), (int)strtotime($endDate));
	if (empty($records)) {
		return $html . '<div class="notice">No moderation records found for the selected period.</div>';
	}
	$html .= '<h2>Modlog</h2>
		<center>Records shown: ' . count($records) . '</center>
		<table class="table"><thead>
			<tr>
				<th>Date / Time</th>
				<th>User</th>
				<th>Action</th>
			</tr>
		</thead>
		<tbody>';
	foreach ($records as $record) {
		$style = '';
		if (!empty($record['color']) && $record['color'] !== 'Black') {
			$style = ' style="color: ' . htmlspecialchars($record['color']) . '"';
		}
		$html .= '
			<tr' . $style . '>
				<td>' . date('d.m.y D H:i:s', $record['timestamp']) . '</td>
				<td>' . htmlspecialchars($record['username']) . '</td>
				<td>' . htmlspecialchars($record['action']) . '</td>
			</tr>';
	}
	return $html . '
		</tbody></table>';
}
