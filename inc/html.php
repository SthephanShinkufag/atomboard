<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

function pageHeader() {
	return '<!DOCTYPE html>

<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta http-equiv="cache-control" content="max-age=0">
	<meta http-equiv="cache-control" content="no-store, no-cache, must-revalidate">
	<meta http-equiv="expires" content="0">
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
	<meta http-equiv="pragma" content="no-cache">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>' . TINYIB_BOARDDESC . '</title>
	<link rel="shortcut icon" href="/' . TINYIB_BOARD . '/favicon.png">
	<link rel="stylesheet" type="text/css" href="/' . TINYIB_BOARD . '/css/global.css">
	<script src="/' . TINYIB_BOARD . '/js/tinyib.js"></script>
	' . (TINYIB_CAPTCHA === 'recaptcha' ?
		'<script src="https://www.google.com/recaptcha/api.js" async defer></script>' : '') . '
	' . TINYIB_HTML_HEAD . '
</head>
';
}

function pageWrapper($returnHref) {
	return '
	<div class="aside aside-left">
		<div class="aside-top">
			<nav>' .
				TINYIB_HTML_LEFTSIDE . '
			</nav>
		</div>
		<div class="aside-bottom">' . ($returnHref ? '
			<a class="aside-btn" id="aside-btn-return" href="/' . TINYIB_BOARD . '/" title="Return">
				<svg><use xlink:href="#symbol-arrow-left"/></svg>
			</a>' : '') . '
		</div>
	</div>
	<div class="wrapper">';
}

function pageFooter() {
	return '
		<div class="footer">
			- <a href="https://github.com/SthephanShinkufag/TinyIB" target="_top">TinyIB</a> - forked by <a href="mailto:sthephan.shi@gmail.com">SthephanShi</a> - forked again by <a href="https://github.com/nolifer1337/TinyIB" target="_top">nolifer</a>
		</div>
	</div>
	<div class="aside aside-right">
		<div class="aside-top">
			<a class="aside-btn" id="aside-btn-totop" href="#" title="To top" onclick="window.scroll(0, 0); return false;">
				<svg><use xlink:href="#symbol-arrow-left"/></svg>
			</a>
		</div>
		<div class="aside-bottom">
			<a class="aside-btn" id="aside-btn-tobottom" href="#" title="To bottom" onclick="window.scroll(0, document.body.scrollHeight); return false;">
				<svg><use xlink:href="#symbol-arrow-left"/></svg>
			</a>
		</div>
	</div>
	<div id="svg-icons" style="height: 0; width: 0; overflow: hidden;">
		<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
			<symbol viewBox="0 0 1024 1024" id="symbol-arrow-left">
				<path d="M958.1 333.6H541.8v-177c0-16.9-5.5-31.2-16.4-43.1-11.9-11.9-26.3-17.9-43.1-17.9h-59.5L6.4 512l416.4 416.4h59.5c16.9 0 31.2-5.5 43.1-16.4 10.9-11.9 16.4-26.3 16.4-43.1V690.4h416.4c16.9 0 31.2-5.4 43.1-16.4 10.9-11.9 16.4-26.3 16.4-43.1V394.5c0-16.9-5.5-31.2-16.4-43.1-12-11.9-26.4-17.9-43.2-17.8z"/>
			</symbol>
			<symbol viewBox="0 0 1024 1024" id="symbol-high">
				<path d="M259.2 575.2c0-16.9-6.3-31.6-19-44.3-12.6-12.6-27.4-18.9-44.2-18.9H69.6c-17.9 0-33.2 6.3-45.8 18.9-11.6 12.7-17.4 27.9-17.4 45.9v377.6c0 17.9 5.8 33.2 17.4 45.8 12.6 11.6 27.9 17.4 45.8 17.4H196c16.8 0 31.6-5.8 44.2-17.3 12.6-12.7 19-27.9 19-45.8V575.2zm360.2-297.1c-12.6-12.6-27.4-18.9-44.2-18.9H448.8c-17.9 0-33.2 6.3-45.9 18.9-11.6 12.6-17.4 27.9-17.4 45.8v630.4c0 17.9 5.8 33.2 17.4 45.8 12.6 11.6 27.9 17.4 45.8 17.4h126.4c16.9 0 31.6-5.8 44.3-17.4 12.6-12.7 19-27.9 19-45.8v-632c0-16.8-6.3-31.5-19-44.2zM782.2 25.3c-11.6 12.6-17.4 27.9-17.4 45.8v883.2c0 17.9 5.8 33.2 17.4 45.8 12.6 11.6 27.9 17.4 45.8 17.4h126.4c16.9 0 31.6-5.8 44.3-17.4 12.6-12.7 18.9-27.9 18.9-45.8V69.6c0-16.9-6.3-31.6-18.9-44.3C986 12.7 971.2 6.4 954.4 6.4H828c-17.9 0-33.2 6.3-45.8 18.9z"/>
			</symbol>
			<symbol viewBox="0 0 1024 1024" id="symbol-home">
				<path d="M386.2 1017V637.8H639V1017h189.6c16.9 0 31.6-5.8 44.3-17.4 12.6-12.7 18.9-27.9 18.9-45.8v-316h126.4V511.4L512.6 5.8 7 511.4v126.4h126.4v316c0 17.9 5.8 33.2 17.4 45.8 12.6 11.6 27.9 17.4 45.8 17.4h189.6z"/>
			</symbol>
			<symbol viewBox="0 0 1024 1024" id="symbol-github">
				<path d="M512 6.4c279.2 0 505.6 232.1 505.6 518.4 0 229-144.7 423.1-345.5 491.8-25.7 5.1-34.8-11-34.8-24.9 0-17 .6-72.8.6-142.2 0-48.4-16.2-79.9-34.4-96 112.7-12.8 230.9-56.6 230.9-255.8 0-56.6-19.6-102.8-52-139.1 5.2-13.2 22.6-65.8-5-137.2 0 0-42.3-14-138.9 53.1-40.4-11.5-83.6-17.2-126.6-17.4-43 .2-86.2 6-126.6 17.4-96.6-67.1-139-53.1-139-53.1-27.5 71.4-10.2 124.1-4.9 137.2-32.4 36.2-52.1 82.5-52.1 139.1 0 198.7 118 243.1 230.3 256.2-14.5 12.9-27.5 35.9-32.1 69.3-28.8 13.3-102.1 36.2-147.2-43 0 0-26.7-49.8-77.4-53.5 0 0-49.4-.7-3.5 31.5 0 0 33.2 15.9 56.1 75.8 0 0 29.7 100.8 170.3 69.5.3 43.3.7 75.9.7 88.2 0 13.7-9.2 29.8-34.5 25C151.2 948.2 6.4 753.9 6.4 524.8 6.4 238.5 232.7 6.4 512 6.4z"/>
			</symbol>
			<symbol viewBox="0 0 16 16" id="symbol-like">
				<path d="M14.8 1.6l-.3-.3C13-.5 10.4-.4 8.9 1.4l-.9 1-.9-1C5.6-.4 3-.4 1.5 1.4l-.3.3C-.4 3.5-.4 6.3 1.1 8.1l1 1.1L8 16l5.9-6.8 1-1.2c1.5-1.8 1.5-4.6-.1-6.4z"/>
			</symbol>
			<symbol viewBox="0 0 512 512" id="symbol-music">
				<path d="M511.99 32.01c0-21.71-21.1-37.01-41.6-30.51L150.4 96c-13.3 4.2-22.4 16.5-22.4 30.5v261.42c-10.05-2.38-20.72-3.92-32-3.92-53.02 0-96 28.65-96 64s42.98 64 96 64 96-28.65 96-64V214.31l256-75.02v184.63c-10.05-2.38-20.72-3.92-32-3.92-53.02 0-96 28.65-96 64s42.98 64 96 64 96-28.65 96-64l-.01-351.99z"/>
			</symbol>
			<symbol viewBox="0 0 512 512" id="symbol-list">
				<path d="M149.333 216v80c0 13.255-10.745 24-24 24H24c-13.255 0-24-10.745-24-24v-80c0-13.255 10.745-24 24-24h101.333c13.255 0 24 10.745 24 24zM0 376v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H24c-13.255 0-24 10.745-24 24zM125.333 32H24C10.745 32 0 42.745 0 56v80c0 13.255 10.745 24 24 24h101.333c13.255 0 24-10.745 24-24V56c0-13.255-10.745-24-24-24zm80 448H488c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24zm-24-424v80c0 13.255 10.745 24 24 24H488c13.255 0 24-10.745 24-24V56c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24zm24 264H488c13.255 0 24-10.745 24-24v-80c0-13.255-10.745-24-24-24H205.333c-13.255 0-24 10.745-24 24v80c0 13.255 10.745 24 24 24z"/>
			</symbol>
		</svg>
	</div>
</body>
</html>';
}

function supportedFileTypes() {
	global $tinyib_uploads;
	if (empty($tinyib_uploads)) {
		return '';
	}
	$typesAllowed = array_map('strtoupper', array_unique(array_column($tinyib_uploads, 0)));
	$typesLast = array_pop($typesAllowed);
	$typesFormatted = $typesAllowed ? implode(', ', $typesAllowed) . ' and ' . $typesLast : $typesLast;
	return 'Supported file type' . (count($tinyib_uploads) != 1 ? 's are ' : ' is ') . $typesFormatted . '.';
}

function buildPostForm($parent, $isRawPost = false) {
	global $tinyib_hidefieldsop, $tinyib_hidefields, $tinyib_uploads, $tinyib_embeds;
	$isOnPage = $parent == TINYIB_NEWTHREAD;
	$hideFields = $isOnPage ? $tinyib_hidefieldsop : $tinyib_hidefields;

	$postformExtra = array('name' => '', 'email' => '', 'subject' => '', 'footer' => '');
	$inputSubmit = '<input type="submit" value="Submit" accesskey="z">';
	if ($isRawPost || !in_array('subject', $hideFields)) {
		$postformExtra['subject'] = $inputSubmit;
	} else if (!in_array('email', $hideFields)) {
		$postformExtra['email'] = $inputSubmit;
	} else if (!in_array('name', $hideFields)) {
		$postformExtra['name'] = $inputSubmit;
	} else if (!in_array('email', $hideFields)) {
		$postformExtra['email'] = $inputSubmit;
	} else {
		$postformExtra['footer'] = $inputSubmit;
	}

	// Build board rules
	$maxFileSizeInputHtml = '';
	$maxFileSizeRulesHtml = '';
	$fileTypesHtml = '';
	$fileInputHtml = '';
	$embedInputHtml = '';

	if (!empty($tinyib_uploads) && ($isRawPost || !in_array('file', $hideFields))) {
		if (TINYIB_MAXKB > 0) {
			$maxFileSizeInputHtml = '<input type="hidden" name="MAX_FILE_SIZE" value="' .
				strval(TINYIB_MAXKB * 1024) . '">';
			$maxFileSizeRulesHtml = '<li>Maximum number of files is '.MAXIMUM_FILES.', '.TINYIB_MAXKBDESC.' total.</li>';
		}
		$fileTypesHtml = '<li>' . supportedFileTypes() . '</li>';
		$fileInputHtml = '<tr>
						<td class="postblock"></td>
						<td>
							<input type="file" name="file[]" size="35" accesskey="f" multiple>
						</td>
					</tr>';
	}
	if (!empty($tinyib_embeds) && ($isRawPost || !in_array('embed', $hideFields))) {
		$embedInputHtml = '<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="embed" placeholder="YouTube URL" accesskey="x" autocomplete="off">
						</td>
					</tr>';
	}
	$reqModHtml = '';
	if (TINYIB_REQMOD == 'files' || TINYIB_REQMOD == 'all') {
		$reqModHtml = '<li>All posts' . (TINYIB_REQMOD == 'files' ? ' with a file attached' : '') .
			' will be moderated before being shown.</li>';
	}
	$thumbnailsHtml = '';
	if (isset($tinyib_uploads['image/jpeg']) ||
	   isset($tinyib_uploads['image/pjpeg']) ||
	   isset($tinyib_uploads['image/png']) ||
	   isset($tinyib_uploads['image/gif'])) {
		$thumbnailsHtml = '<li>Images greater than ' . TINYIB_MAXWOP . 'x' . TINYIB_MAXHOP . (
			TINYIB_MAXW == TINYIB_MAXWOP && TINYIB_MAXH == TINYIB_MAXHOP ? '' :
				' (new thread) or ' . TINYIB_MAXW . 'x' . TINYIB_MAXH . ' (reply)'
			) . ' will be thumbnailed.</li>';
	}
	$uniquePostsHtml = '';
	$uniquePosts = uniquePosts();
	if ($uniquePosts > 0) {
		$uniquePostsHtml = '<li>Currently ' . $uniquePosts . ' unique users.</li>';
	}

	// Build postform
	return '<div class="postarea">
			<form name="postform" id="postform" action="/' . TINYIB_BOARD .
				'/imgboard.php" method="post" enctype="multipart/form-data">
			' . $maxFileSizeInputHtml . '
			<table class="postform-table reply">
				<tbody>' . (
					($isRawPost)? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="checkbox" name="rawpost" checked style="margin: 0 auto;"> '.'<span style="font: 10px sans-serif;">Add <span style="color: red;">## Admin</span> or <span style="color: purple;">## Mod</span> mark</span>' . '
						</td>
					</tr>

					<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="parent" placeholder="Reply to (0 = new thread)" maxlength="75" accesskey="t">
						</td>
					</tr>

						':'<input type="hidden" name="parent" value="' . $parent . '">'
				) . (
					$isRawPost || !in_array('name', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="name" placeholder="Name" maxlength="75" accesskey="n"> ' .
							$postformExtra['name'] . '
						</td>
					</tr>' : ''
				) . (
					$isRawPost || !in_array('email', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="email" placeholder="Mail" maxlength="75" accesskey="e"> ' .
							$postformExtra['email'] . '
						</td>
					</tr>' : ''
				) . (
					$isRawPost || !in_array('subject', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="text" class="postform-input" name="subject" placeholder="Subject" maxlength="75" accesskey="s" autocomplete="off"> ' .
							$postformExtra['subject'] . '
						</td>
					</tr>' : ''
				) . (
					$isRawPost || !in_array('message', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<textarea id="message" name="message" placeholder="Message' .
								($isOnPage ? '' : ' - reply in thread') . '" accesskey="m"></textarea>
						</td>
					</tr>
					<tr>
						<td class="postblock"></td>
						<td id="markup-buttons">
							<button class="markup-button" id="markup-bold" title="Bold">B</button>
							<button class="markup-button" id="markup-italic" title="Italic">I</button>
							<button class="markup-button" id="markup-underline" title="Underline">U</button>
							<button class="markup-button" id="markup-strike" title="Strike">S</button>
							<button class="markup-button" id="markup-spoiler" title="Spoiler">%</button>
							<button class="markup-button" id="markup-code" title="Code">C</button>
							<button class="markup-button" id="markup-quote" title="Select the text, then click to insert a quote">&gt;</button>
						</td>
					</tr>' : ''
				) . (
					TINYIB_CAPTCHA ? '
					<tr>
						<td class="postblock"></td>
						<td>' . (TINYIB_CAPTCHA === 'recaptcha' ? '
							<div style="min-height: 80px;">
								<div id="g-recaptcha" class="g-recaptcha" data-sitekey="' . TINYIB_RECAPTCHA_SITE . '"></div>
								<noscript>
									<div>
										<div style="width: 302px; height: 422px; position: relative;">
											<div style="width: 302px; height: 422px; position: absolute;">
												<iframe src="https://www.google.com/recaptcha/api/fallback?k=' . TINYIB_RECAPTCHA_SITE . '" frameborder="0" scrolling="no" style="width: 302px; height:422px; border-style: none;"></iframe>
											</div>
										</div>
										<div style="width: 300px; height: 60px; border-style: none;bottom: 12px; left: 25px; margin: 0px; padding: 0px; right: 25px;background: #f9f9f9; border: 1px solid #c1c1c1; border-radius: 3px;">
											<textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 40px; border: 1px solid #c1c1c1; margin: 10px 25px; padding: 0px; resize: none;"></textarea>
										</div>
									</div>
								</noscript>
							</div>
						' : '
							<input type="text" class="postform-input" name="captcha" id="captcha" placeholder="Captcha" accesskey="c" autocomplete="off">
							<img id="captchaimage" src="/' . TINYIB_BOARD . '/inc/captcha.php" width="175" height="55" alt="CAPTCHA" onclick="reloadCaptcha();">
						') . '</td>
					</tr>' : ''
				) . '
					' . $fileInputHtml . '
					' . $embedInputHtml .
				(
					$isRawPost || !in_array('password', $hideFields) ? '
					<tr>
						<td class="postblock"></td>
						<td>
							<input type="password" name="password" id="newpostpassword" size="8" accesskey="p">&nbsp;Deletion password
						</td>
					</tr>' : ''
				) . '
					<tr>
						<td colspan="2" class="rules">
							<ul>
								' . $reqModHtml . '
								' . $fileTypesHtml . '
								' . $maxFileSizeRulesHtml . '
								' . $thumbnailsHtml . '
								' . $uniquePostsHtml . '
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
				</tbody>
			</table>
			</form>
		</div>';
}


function buildPost($post, $res, $forModForm='', $postID='') {
	$isOp = $post['parent'] == TINYIB_NEWTHREAD;
	$id = $post['id'];
	$thrId = $isOp ? $id : $post['parent'];
	if (!isset($post['omitted'])) {
		$post['omitted'] = 0;
	}
	$filehtml = '';
	// Build post file

// cikl-start
$postHaveImages=false;
for($index=0; $index<MAXIMUM_FILES; $index++){
if($post['file'.$index.'_hex']){

	$postHaveImages=true;
	$fWidth = $post['image'.$index.'_width'];
	$fHeight = $post['image'.$index.'_height'];
	$fName = $post['file'.$index];

	$isEmbed = isEmbed($post['file'.$index.'_hex']);
	$isVideoFile = (substr($fName, -5) == '.webm' || substr($fName, -4) == '.mp4')?true:false;
	$isPictureFile = in_array(substr($fName, -4), array('.jpg', '.png', '.gif'));

	$directLink = $isEmbed ? '#' : '/' . TINYIB_BOARD . '/src/' . $fName;
	$expandClick = ' onclick="return expandFile(event, ' . $id.$index . ');"';

if($isEmbed){
$expandHtml = rawurlencode($fName);
}
else if($isVideoFile){
	if($fWidth > 0 && $fHeight > 0){
	$hwParams='width="' . $fWidth . '" height="' . $fHeight . '"';
	}
	else{
	$hwParams='width="500"';
	}
$expandHtml = rawurlencode('<video ' . $hwParams . 'style="position: static; pointer-events: inherit; display: inline; max-width: 100%; max-height: 100%;" controls autoplay loop><source src="' . $directLink . '"></source></video>');
}
else if($isPictureFile){
$expandHtml = rawurlencode('<a href="' . $directLink . '"' . $expandClick . '><img src="/' . TINYIB_BOARD . '/src/' . $fName .'" width="' . $fWidth . '" style="max-width: 100%; height: auto;"></a>');
}

	$origName = $post['file'.$index.'_original'];
	$hasOrigName = ($origName != '');

if ($isEmbed || in_array(substr($fName, -4), array('.jpg', '.png', '.gif', 'webm', '.mp4')) ){
$thumblink = '<a href="' . $directLink . '" target="_blank"' . $expandClick;
}
else{
$thumblink = '<a href="' . $directLink . '" target="_blank"';
}
if ($hasOrigName){
$thumblink.=' download="' . $origName . '">';
}
else{
$thumblink.='>';
}

if ($isEmbed){
$filesize = '<a href="' . $directLink . '"' . $expandClick . '>' . $origName .'</a>,&nbsp;' . $post['file'.$index.'_hex'];
}
else{
	if ($fName != ''){
	$filesize = $thumblink . ($hasOrigName ? $origName : $fName) .'</a>'. 
	'<br />('.$post['file'.$index.'_size_formatted'].($fWidth > 0 && $fHeight > 0 ? ',&nbsp;' . $fWidth . 'x' . $fHeight : '').')';
	}
	else{
	$filesize = '';
	}
}

if ($filesize == ''){
$filehtml = '';
}
else{
$filehtml .= '<div class="inlineblock"> <span class="filesize">'. (($forModForm && $postID==$post['id'])?'<input type="checkbox" name="delete-img[]" value="'.$index.'">':'') . $filesize . '</span><div id="thumbfile'.$id.$index.'">'; 

 if ($post['thumb'.$index.'_width'] > 0 && $post['thumb'.$index.'_height'] > 0){
 //if file have thumbnail
 $filehtml .= '' . $thumblink . '<img src="/' . TINYIB_BOARD . '/thumb/' . $post['thumb'.$index] .'"' . ($isVideoFile?' style="border: 1px dashed #5d5d5d;" ':'') . 'alt="' .$id.$index .'" class="thumb" id="thumbnail' . $id.$index. '" width="' .$post['thumb'.$index.'_width'] . '" height="' . $post['thumb'.$index.'_height'] . '"></a>';
 }
 else if($isVideoFile){
 //if file have no thumbnail but it is webm or mp4 file
 $filehtml .= '' . $thumblink . '<video src="' . $directLink . '" alt="' .$id.$index .'" class="thumb" id="thumbnail' . $id.$index. '"></a>';
 }
 else {
 $filehtml .= '';
 }

$filehtml .= '</div>' .($expandHtml == '' ? '' : '<div id="expand' . $id.$index . '" style="display: none;">' . $expandHtml . '</div><div id="file' . $id.$index . '" class="thumb" style="display: none;"></div> </div>');
}

 }
}
// cikl-stop

	// Truncate messages on board index pages for readability
	$message = $post['message'];
	if (!$res) {
		$truncLen = 0;
		if (TINYIB_TRUNC_LINES > 0 && substr_count($message, '<br>') > TINYIB_TRUNC_LINES) {
			$brOffsets = strallpos($message, '<br>');
			$truncLen = $brOffsets[TINYIB_TRUNC_LINES - 1];
		} elseif (TINYIB_TRUNC_SIZE > 0 && strlen($message) > TINYIB_TRUNC_SIZE) {
			$truncLen = TINYIB_TRUNC_SIZE;
		}

		if ($truncLen) {
			$message = tidy_repair_string(substr($message, 0, $truncLen),array('quiet' => true, 'show-body-only' => true),'utf8') . '
					<div class="abbrev">
						Post too long. <a href="/' . TINYIB_BOARD . '/res/' . $thrId . '.html#' . $id .
						'">Click to view</a>.
					</div>';
		}
	}
	// Start post building
	$omitted = $post['omitted'];
	$likes = $post['likes'];

	return PHP_EOL . ($isOp ? '
			<div class="oppost" id="op' . $id . '">' : '
			<table border="0"><tbody><tr><td class="reply" id="reply' . $id . '">') . '
				<a id="' . $id . '"></a>
				<label>
					<input type="checkbox" name="delete" value="' . $id . '">' .
					($post['subject'] != '' ? '
					<span class="filetitle">' . $post['subject'] . '</span>' : '') . '
					' . $post['nameblock'] . '
				</label>
				<span class="reflink">' . ($res == TINYIB_RESPAGE ? '
					<a href="' . $thrId . '.html#' . $id . '">No.</a>' .
					'<a href="' . $thrId . '.html#q' . $id . '" onclick="quotePost(' .$id . ');">' .
						$id . '</a>' : '
					<a href="/' . TINYIB_BOARD . '/res/' . $thrId . '.html#' . $id . '">No.</a>' .
					'<a href="/' . TINYIB_BOARD . '/res/' . $thrId . '.html#q' . $id . '">' . $id . '</a>') .
					($post['stickied'] == 1 ? '
					<img src="/' . TINYIB_BOARD .
						'/sticky.png" title="Thread is sticked" width="16" height="16">' : '') .
					($post['email'] == LOCKED_THREAD_COOKIE ? '
					<img src="/' . TINYIB_BOARD .
						'/locked.png" title="Thread is locked" width="11" height="16">' : '') .
				(
					TINYIB_LIKES ? '
					<span class="like-container">
						<span class="like-icon' . ($likes ? ' like-enabled' : ' like-disabled') .
							'" onclick="sendLike(this, ' . $id . ');">
							<svg><use xlink:href="#symbol-like"></use></svg>
						</span>
						<span class="like-counter">' . ($likes ? $likes:'') . '</span>
					</span>' : ''
				)  .'
				</span>'. ($isOp && $res == TINYIB_INDEXPAGE ? '
				&nbsp;<a class="gotothread" href="res/' . $id . '.html">Reply</a>' : '') . '<br />' .
				( ($forModForm && $postID==$post['id'] && $postHaveImages)?'<form method="get" action="?"><input type="hidden" name="manage" value=""><input type="hidden" name="deleteimages" value="'.$postID.'"><input type="submit" value="Delete/Hide Selected Images" class="managebutton"> Action: <select name="action" class="managebutton"><option value="delete" selected>Delete Image</option><option value="hide">Hide Preview</option></select><br /><br />':'').

				$filehtml . 

				( ($forModForm && $postID==$post['id'] && $postHaveImages)?'</form>':'' ) .
				'
				<div class="message">' .
				( ($forModForm && $postID==$post['id'])?'<form method="post" action="?manage&editpost='.$postID.'" enctype="multipart/form-data"><textarea id="message" name="message">':'') .

				$message .

				( ($forModForm && $postID==$post['id'])?'</textarea><br /><input type="submit" value="Edit" class="managebutton"></form>':'') .
				'</div>
			' . (!$isOp ? '</td></tr></tbody></table>' : '</div>' .
			($res == TINYIB_INDEXPAGE && $omitted > 0 ? '
			<div class="omittedposts">' . $omitted . ' ' .
				plural('post', $omitted) . ' omitted. Click Reply to view.
			</div>' : '')) . PHP_EOL;
}

function buildPage($htmlPosts, $parent, $pages = 0, $thispage = 0) {
	// Build page links: [Previous] [0] [1] [2] [Next]
	$isOnPage = $parent == TINYIB_NEWTHREAD;
	$pagelinks = '';
	if ($isOnPage) {
		$pages = max($pages, 0);
		$pagelinks = ($thispage == 0 ?
					'<td>Previous</td>' :
					'<td>
						<form method="get" action="' . ($thispage == 1 ? 'index' : $thispage - 1) . '.html">
							<input value="Previous" type="submit">
						</form>
					</td>') . '
					<td>';
		for($i = 0; $i <= $pages; $i++) {
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
	return pageHeader() . '<body>' . pageWrapper(!$isOnPage) . '
		<div class="logo">
			' . TINYIB_LOGO . TINYIB_BOARDDESC . '
		</div>
		' .buildPostForm($parent) .( ($isOnPage)?'<br /><div style="text-align: center"><a href="/'.TINYIB_BOARD.'/catalog.html">Catalog</div>':''). '
		<hr>
		<form id="delform" action="/' . TINYIB_BOARD . '/imgboard.php?delete" method="post">
			<input type="hidden" name="board" value="' . TINYIB_BOARD . '">' .
			$htmlPosts . '
			<table class="userdelete">
				<tbody>
					<tr>
						<td>Delete Post <input type="password" name="password" id="deletepostpassword" size="8" placeholder="Password">&nbsp;<input name="deletepost" value="Delete" type="submit"></td>
						<td>
							<a href="/' . TINYIB_BOARD . '/' . basename($_SERVER['PHP_SELF']) .
							'?manage" style="text-decoration: underline;">Manage</a>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<table>
			<tbody>
				<tr>
					' . $pagelinks . '
				</tr>
			</tbody>
		</table>
		<br>' .
		pageFooter();
}

function rebuildIndexes() {
	$page = 0;
	$i = 0;
	$htmlPosts = '';
	$threads = allThreads();
	$pages = ceil(count($threads) / TINYIB_THREADSPERPAGE) - 1;
	foreach ($threads as $thread) {
		$replies = postsInThreadByID($thread['id']);
		$thread['omitted'] = max(0, count($replies) - TINYIB_PREVIEWREPLIES - 1);
		// Build replies for preview
		$htmlReplies = array();
		for($j = count($replies) - 1; $j > $thread['omitted']; $j--) {
			$htmlReplies[] = buildPost($replies[$j], TINYIB_INDEXPAGE);
		}
		$htmlPosts .= buildPost($thread, TINYIB_INDEXPAGE) . implode('', array_reverse($htmlReplies)) . '
			<hr>';
		if (++$i >= TINYIB_THREADSPERPAGE) {
			$file = ($page == 0) ? TINYIB_INDEX : $page . '.html';
			writePage($file, buildPage($htmlPosts, 0, $pages, $page));
			$page++;
			$i = 0;
			$htmlPosts = '';
		}
	}
	if ($page == 0 || $htmlPosts != '') {
		$file = ($page == 0) ? TINYIB_INDEX : $page . '.html';
		writePage($file, buildPage($htmlPosts, 0, $pages, $page));
	}
createCatalog();
}

function rebuildThread($id) {
	$htmlPosts = '';
	$posts = postsInThreadByID($id);
	foreach ($posts as $post) {
		$htmlPosts .= buildPost($post, TINYIB_RESPAGE);
	}
	$htmlPosts .= '
			<hr>';
	writePage('res/' . $id . '.html', buildPage($htmlPosts, $id));
}

function adminBar() {
	global $loggedIn, $isAdmin;
	return !$loggedIn ? '' : '
			[<a href="?manage">Status</a>]
			[' . ($isAdmin ? '<a href="?manage&bans">Bans</a>]
			[' : '') . '<a href="?manage&moderate">Moderate Post</a>]
			[<a href="?manage&rawpost">Raw Post</a>]
			[' . ($isAdmin ? '<a href="?manage&rebuildall">Rebuild All</a>]
			[' : '') . ($isAdmin && TINYIB_DBMIGRATE ?
				'<a href="?manage&dbmigrate"><b>Migrate Database</b></a>] [' : '') .
				'<a href="?manage&logout">Log Out</a>]
		';
}

function managePage($text, $onload = '') {
	global $returnlink;
	return pageHeader() . '<body' . $onload . '>' . pageWrapper($returnlink) . '
		<div class="adminbar">' . adminBar() . '</div>
		<div class="logo">
			' . TINYIB_LOGO . TINYIB_BOARDDESC . '
		</div>
		<hr width="90%">
		' . $text . '
		<hr>' .
	pageFooter();
}

function manageOnLoad($page) {
	switch ($page) {
	case 'login':    return ' onload="document.tinyib.managepassword.focus();"';
	case 'moderate': return ' onload="document.tinyib.moderate.focus();"';
	case 'rawpost':  return ' onload="document.postform.parent.focus();"';
	case 'bans':     return ' onload="document.tinyib.ip.focus();"';
	}
}

function manageLogInForm() {
	return '<form id="tinyib" name="tinyib" method="post" action="?manage">
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

function manageBanForm() {
	return '<form id="tinyib" name="tinyib" method="post" action="?manage&bans">
		<fieldset>
			<legend>Ban an IP-address</legend>
			<label for="ip">IP-address:</label>
			<input type="text" name="ip" id="ip" value="' . $_GET['bans'] . '">
			<input type="submit" value="Submit" class="managebutton"><br>
			<label for="expire">Expire (sec):</label>
			<input type="text" name="expire" id="expire" value="0">&nbsp;&nbsp;
			<small>[
				<a href="#" onclick="document.tinyib.expire.value=\'3600\'; return false;">1hr</a> |
				<a href="#" onclick="document.tinyib.expire.value=\'86400\'; return false;">1d</a> |
				<a href="#" onclick="document.tinyib.expire.value=\'172800\'; return false;">2d</a> |
				<a href="#" onclick="document.tinyib.expire.value=\'604800\'; return false;">1w</a> |
				<a href="#" onclick="document.tinyib.expire.value=\'1209600\'; return false;">2w</a> |
				<a href="#" onclick="document.tinyib.expire.value=\'2592000\'; return false;">30d</a> |
				<a href="#" onclick="document.tinyib.expire.value=\'0\'; return false;">never</a>
			]</small><br>
			<label for="reason">Reason:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
			<input type="text" name="reason" id="reason">&nbsp;&nbsp;<small>optional</small>
		</fieldset>
		</form><br>';
}

function manageBansTable() {
	$text = '';
	$allbans = allBans();
	if (count($allbans) > 0) {
		$text .= '
		<table id="ban-table"><tbody>
			<tr>
				<th>IP-address</th>
				<th>Set at</th>
				<th>Expires</th>
				<th>Reason provided</th>
				<th>&nbsp;</th>
			</tr>';
		foreach ($allbans as $ban) {
			$expire = ($ban['expire'] > 0) ? date('y.m.d D H:i:s', $ban['expire']) : 'Does not expire';
			$reason = ($ban['reason'] == '') ? '&nbsp;' : htmlentities($ban['reason'], ENT_QUOTES, 'UTF-8');
			$text .= '<tr>
				<td>' . $ban['ip'] . '</td>
				<td>' . date('y.m.d D H:i:s', $ban['timestamp']) . '</td>
				<td>' . $expire . '</td><td>' . $reason . '</td>
				<td><a href="?manage&bans&lift=' . $ban['id'] . '">lift</a></td>
			</tr>';
		}
		$text .= '
		</tbody></table>';
	}
	return $text;
}

function manageModeratePostForm() {
	return '<form id="tinyib" name="tinyib" method="get" action="?">
			<input type="hidden" name="manage" value="">
			<fieldset>
				<legend>Moderate a post</legend>
				<div valign="top">
					<label for="moderate">Post ID:</label>
					<input type="text" name="moderate" id="moderate">
					<input type="submit" value="Submit" class="managebutton">
				</div><br>
				<small>
					<b>Tip:</b> While browsing the image board, you can easily moderate a post if you are logged in:<br>
					Tick the box next to a post and click "Delete" at the bottom of the page with a blank password.
				</small><br>
			</fieldset>
		</form><br>';
}

function manageModeratePost($post) {
	global $isAdmin;
	$ip = $post['ip'];
	$ban = banByIP($ip);
	$banDisabled = (!$ban && $isAdmin) ? '' : ' disabled';
	$banInfo = (!$ban) ?
		((!$isAdmin) ? 'Only an administrator may ban an IP address.' : ('IP address: ' . $ip)) :
		(' A ban record already exists for ' . $ip);
	$isOp = $post['parent'] == TINYIB_NEWTHREAD;
	$deleteInfo = $isOp ? 'This will delete the entire thread below.' : 'This will delete the post below.';
	$postOrThread = $isOp ? 'Thread' : 'Post';
	$stickyHtml = '';
	$lockedHtml = '';
	if ($isOp) {
		$stickySet = $post['stickied'] == 1 ? '0' : '1';
		$stickyUnsticky = $post['stickied'] == 1 ? 'Un-sticky' : 'Sticky';
		$stickyUnstickyHelp = $post['stickied'] == 1 ? 'Return this thread to a normal state.' :
			'Keep this thread at the top of the board.';
		$stickyHtml = <<<H
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td align="right" width="50%;">
		<form method="get" action="?">
		<input type="hidden" name="manage" value="">
		<input type="hidden" name="sticky" value="${post['id']}">
		<input type="hidden" name="setsticky" value="$stickySet">
		<input type="submit" value="$stickyUnsticky Thread" class="managebutton" style="width: 50%;">
		</form>
	</td><td><small>$stickyUnstickyHelp</small></td></tr>
H;
		$lockedSet = $post['email'] == LOCKED_THREAD_COOKIE ? '0' : '1';
		$lockUnlock = $post['email'] == LOCKED_THREAD_COOKIE ? 'Un-lock' : 'Lock';
		$lockedUnlockedHelp = $post['email'] == LOCKED_THREAD_COOKIE ? 'Unlock this thread.' :
			'Lock this thread.';
		$lockedHtml = <<<H
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td align="right" width="50%;">
		<form method="get" action="?">
		<input type="hidden" name="manage" value="">
		<input type="hidden" name="locked" value="${post['id']}">
		<input type="hidden" name="setlocked" value="$lockedSet">
		<input type="submit" value="$lockUnlock Thread" class="managebutton" style="width: 50%;">
		</form>
	</td><td><small>$lockedUnlockedHelp</small></td></tr>
H;
		$postHtml = '';
		$posts = postsInThreadByID($post['id']);
		foreach ($posts as $postTemp) {
			$postHtml .= buildPost($postTemp, TINYIB_INDEXPAGE, 'forModForm', $post['id']);
		}
	} else {
		$postHtml = buildPost($post, TINYIB_INDEXPAGE, 'forModForm', $post['id']);
	}
	return <<<H
	<fieldset>
	<legend>Moderating No.${post['id']}</legend>

	<fieldset>
	<legend>Action</legend>

	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr><td align="right" width="50%;">

	<form method="get" action="?">
	<input type="hidden" name="manage" value="">
	<input type="hidden" name="delete" value="${post['id']}">
	<input type="submit" value="Delete $postOrThread" class="managebutton" style="width: 50%;">
	</form>

	</td><td><small>$deleteInfo</small></td></tr>
	<tr><td align="right" width="50%;">

	<form method="get" action="?">
	<input type="hidden" name="manage" value="">
	<input type="hidden" name="bans" value="${post['ip']}">
	<input type="submit" value="Ban Poster" class="managebutton" style="width: 50%;"$banDisabled>
	</form>

	</td><td><small>$banInfo</small></td></tr>

	$stickyHtml

	$lockedHtml

	</table>

	</fieldset>

	<fieldset>
	<legend>$postOrThread</legend>
	$postHtml
	</fieldset>

	</fieldset>
	<br>
H;
}

function manageStatus() {
	global $isAdmin;
	$threads = countThreads();
	$bans = count(allBans());
	if (TINYIB_REQMOD == 'files' || TINYIB_REQMOD == 'all') {
		$reqModPostHtml = '';
		$reqModPosts = latestPosts(false);
		foreach ($reqModPosts as $post) {
			$id = $post['id'];
			$reqModPostHtml .= ($reqModPostHtml != '' ? '
				<tr><td colspan="2"><hr></td></tr>' : '') . '
				<tr>
					<td>' . buildPost($post, TINYIB_INDEXPAGE) . '</td>
					<td valign="top" align="right">
						<table border="0">
							<tr>
								<td>
									<form method="get" action="?">
										<input type="hidden" name="manage" value="">
										<input type="hidden" name="approve" value="' . $id . '">
										<input type="submit" value="Approve" class="managebutton">
									</form>
								</td>
								<td>
									<form method="get" action="?">
										<input type="hidden" name="manage" value="">
										<input type="hidden" name="moderate" value="' . $id . '">
										<input type="submit" value="More Info" class="managebutton">
									</form>
								</td>
							</tr>
							<tr>
								<td align="right" colspan="2">
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
	$posts = latestPosts(true);
	foreach ($posts as $post) {
		$postHtml .= ($postHtml != '' ? '
					<tr><td colspan="2"><hr></td></tr>' : '') . '
					<tr>
						<td>' . buildPost($post, TINYIB_INDEXPAGE) . '</td>
						<td valign="top" align="right">
							<form method="get" action="?"><input type="hidden" name="manage" value="">
								<input type="hidden" name="moderate" value="' . $post['id'] . '">
								<input type="submit" value="Moderate" class="managebutton">
							</form>
						</td>
					</tr>';
	}
	return ($isAdmin && TINYIB_DBMODE == 'mysql' && function_exists('mysqli_connect') ?
		'<fieldset>
			<legend>Notice</legend>
			<p><b>TINYIB_DBMODE</b> is currently <b>mysql</b> in <b>settings.php</b>, but
				<a href="http://www.php.net/manual/en/book.mysqli.php">MySQLi</a> is installed.
				Please change it to <b>mysqli</b>. This will not affect your data.</p>
		</fieldset>' : '') . '
		<fieldset>
			<legend>Status</legend>
			<fieldset>
				<legend>Info</legend>
				<table border="0" cellspacing="0" cellpadding="0" width="100%"><tbody><tr>
					<td>
						' . $threads . ' ' . plural('thread', $threads) . ', ' .
							$bans . ' ' . plural('ban', $bans) . '
					</td>' . ($isAdmin ? '
					<td valign="top" align="right">
						<form method="get" action="?">
							<input type="hidden" name="manage">
							<input type="hidden" name="update">
							<input type="submit" value="Update TinyIB" class="managebutton">
						</form>
					</td>' : '') . '
				</tr></tbody></table>
			</fieldset>' .
			((TINYIB_REQMOD == 'files' || TINYIB_REQMOD == 'all') &&  $reqModPostHtml != '' ? '
			<fieldset>
				<legend>Pending posts</legend>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					' . $reqModPostHtml . '
				</table>
			</fieldset>' : '') . '
			<fieldset>
				<legend>Recent posts</legend>
				<table border="0" cellspacing="0" cellpadding="0" width="100%">
					' . $postHtml . '
				</table>
			</fieldset>
		</fieldset>
		<br>';
}

function buildCatalogPage(){
$catalogHTML = '';
$numOfReplies = 0;
$OPuserName = '';
$OPpostSubject = '';
$OPpostMessage = '';
$OPpostID = '';

$thumb = 'noimage.png';
$thumb_width = TINYIB_MAXW;
$thumb_height = TINYIB_MAXH;

$OPposts = allThreads();

	foreach ($OPposts as $post) {
	$numOfReplies = numRepliesToThreadByID($post['id']);
	$OPpostMessage = tidy_repair_string(substr($post['message'], 0, 160),array('quiet' => true, 'show-body-only' => true),'utf8');
	$OPpostSubject = $post['subject'];
	
	if($post['name'] == ''){
	$OPuserName = TINYIB_POSTERNAME;
	} else {
	$OPuserName = $post['name'];
	}
	
	$OPpostID = $post['id'];

	  if($post['thumb0'] != '' && $post['thumb0_width'] > 0 && $post['thumb0_height'] > 0){
	  $thumb = 'thumb/'.$post['thumb0'];
	  $thumb_width = $post['thumb0_width'];
	  $thumb_height = $post['thumb0_height'];
	  } else {
	  $thumb = 'noimage.png';
	  $thumb_width = TINYIB_MAXW;
	  $thumb_height = TINYIB_MAXH;
	  }

	$catalogHTML .= '<div class="catalogblock">
	<a href="res/'.$OPpostID.'.html"><img src="'.$thumb.'" width="'.$thumb_width.'" height="'.$thumb_height.'" /></a><br />
	<center>'.
	(($OPpostSubject)?'<span class="filetitle">'.$OPpostSubject.'</span><br />':'').
	'<span class="postername">'.$OPuserName.' (R: '.$numOfReplies.')</span><br />
	</center>
	<div class="message" style="text-align: left">'.$OPpostMessage.'</div><br />
	</div>';

	}

	return pageHeader() . '<body>' . pageWrapper('back') . '
		<div class="logo">
			' . TINYIB_LOGO . TINYIB_BOARDDESC . ' / Catalog
		</div>
		<hr />
		<br /> <div style="text-align: center">'.
		$catalogHTML . '</div>'.
		pageFooter();
}

function createCatalog(){
writePage('catalog.html', buildCatalogPage());
}
