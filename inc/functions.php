<?php
if (!defined('TINYIB_BOARD')) {
	die('');
}

// if you wannt to add more file_* entities you also have to change:
// postsByHex()
// insertPost()
if (TINYIB_DBMODE == 'pdo' && TINYIB_DBDRIVER == 'pgsql') {
	$posts_sql = 'CREATE TABLE "' . TINYIB_DBPOSTS . '" (
		"id" bigserial NOT NULL,
		"parent" integer NOT NULL,
		"timestamp" integer NOT NULL,
		"bumped" integer NOT NULL,
		"ip" varchar(39) NOT NULL,
		"name" varchar(75) NOT NULL,
		"tripcode" varchar(10) NOT NULL,
		"email" varchar(75) NOT NULL,
		"nameblock" varchar(255) NOT NULL,
		"subject" varchar(75) NOT NULL,
		"message" text NOT NULL,
		"password" varchar(255) NOT NULL,
		"file0" text NOT NULL,
		"file0_hex" varchar(75) NOT NULL,
		"file0_original" varchar(255) NOT NULL,
		"file0_size" integer NOT NULL default \'0\',
		"file0_size_formatted" varchar(75) NOT NULL,
		"image0_width" smallint NOT NULL default \'0\',
		"image0_height" smallint NOT NULL default \'0\',
		"thumb0" varchar(255) NOT NULL,
		"thumb0_width" smallint NOT NULL default \'0\',
		"thumb0_height" smallint NOT NULL default \'0\',
		"file1" text NOT NULL,
		"file1_hex" varchar(75) NOT NULL,
		"file1_original" varchar(255) NOT NULL,
		"file1_size" integer NOT NULL default \'0\',
		"file1_size_formatted" varchar(75) NOT NULL,
		"image1_width" smallint NOT NULL default \'0\',
		"image1_height" smallint NOT NULL default \'0\',
		"thumb1" varchar(255) NOT NULL,
		"thumb1_width" smallint NOT NULL default \'0\',
		"thumb1_height" smallint NOT NULL default \'0\',
		"file2" text NOT NULL,
		"file2_hex" varchar(75) NOT NULL,
		"file2_original" varchar(255) NOT NULL,
		"file2_size" integer NOT NULL default \'0\',
		"file2_size_formatted" varchar(75) NOT NULL,
		"image2_width" smallint NOT NULL default \'0\',
		"image2_height" smallint NOT NULL default \'0\',
		"thumb2" varchar(255) NOT NULL,
		"thumb2_width" smallint NOT NULL default \'0\',
		"thumb2_height" smallint NOT NULL default \'0\',
		"file3" text NOT NULL,
		"file3_hex" varchar(75) NOT NULL,
		"file3_original" varchar(255) NOT NULL,
		"file3_size" integer NOT NULL default \'0\',
		"file3_size_formatted" varchar(75) NOT NULL,
		"image3_width" smallint NOT NULL default \'0\',
		"image3_height" smallint NOT NULL default \'0\',
		"thumb3" varchar(255) NOT NULL,
		"thumb3_width" smallint NOT NULL default \'0\',
		"thumb3_height" smallint NOT NULL default \'0\',
		"stickied" smallint NOT NULL default \'0\',
		"moderated" smallint NOT NULL default \'1\',
		"likes" smallint NOT NULL default \'0\',
		PRIMARY KEY ("id")
	);
	CREATE INDEX ON "' . TINYIB_DBPOSTS . '"("parent");
	CREATE INDEX ON "' . TINYIB_DBPOSTS . '"("bumped");
	CREATE INDEX ON "' . TINYIB_DBPOSTS . '"("stickied");
	CREATE INDEX ON "' . TINYIB_DBPOSTS . '"("moderated");';

	$bans_sql = 'CREATE TABLE "' . TINYIB_DBBANS . '" (
		"id" bigserial NOT NULL,
		"ip" varchar(39) NOT NULL,
		"timestamp" integer NOT NULL,
		"expire" integer NOT NULL,
		"reason" text NOT NULL,
		PRIMARY KEY ("id")
	);
	CREATE INDEX ON "' . TINYIB_DBBANS . '"("ip");';

	$likes_sql = 'CREATE TABLE "' . TINYIB_DBLIKES . '" (
		"id" bigserial NOT NULL,
		"ip" varchar(39) NOT NULL,
		"board" varchar(16) NOT NULL,
		"postnum" integer NOT NULL,
		"islike" smallint NOT NULL default \'1\',
		PRIMARY KEY ("id")
	);
	CREATE INDEX ON "' . TINYIB_DBLIKES . '"("ip");';

	$modlog_sql = 'CREATE TABLE "' . TINYIB_DBMODLOG . '" (
		"id" bigserial NOT NULL,
		"timestamp" integer NOT NULL,
		"boardname" varchar(255) NOT NULL,
		"username" varchar(75) NOT NULL,
		"action" text NOT NULL,
		"color" varchar(75) NOT NULL,
		"private" smallint NOT NULL default \'1\',
	);
	CREATE INDEX ON "' . TINYIB_DBMODLOG . '"("boardname");';

} else {
	$posts_sql = "CREATE TABLE `" . TINYIB_DBPOSTS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`parent` mediumint(7) unsigned NOT NULL,
		`timestamp` int(20) NOT NULL,
		`bumped` int(20) NOT NULL,
		`ip` varchar(39) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`name` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`tripcode` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`email` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`nameblock` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`subject` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`message` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`password` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file0` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file0_hex` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file0_original` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file0_size` int(20) unsigned NOT NULL default '0',
		`file0_size_formatted` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`image0_width` smallint(5) unsigned NOT NULL default '0',
		`image0_height` smallint(5) unsigned NOT NULL default '0',
		`thumb0` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`thumb0_width` smallint(5) unsigned NOT NULL default '0',
		`thumb0_height` smallint(5) unsigned NOT NULL default '0',
		`file1` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file1_hex` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file1_original` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file1_size` int(20) unsigned NOT NULL default '0',
		`file1_size_formatted` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`image1_width` smallint(5) unsigned NOT NULL default '0',
		`image1_height` smallint(5) unsigned NOT NULL default '0',
		`thumb1` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`thumb1_width` smallint(5) unsigned NOT NULL default '0',
		`thumb1_height` smallint(5) unsigned NOT NULL default '0',
		`file2` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file2_hex` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file2_original` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file2_size` int(20) unsigned NOT NULL default '0',
		`file2_size_formatted` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`image2_width` smallint(5) unsigned NOT NULL default '0',
		`image2_height` smallint(5) unsigned NOT NULL default '0',
		`thumb2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`thumb2_width` smallint(5) unsigned NOT NULL default '0',
		`thumb2_height` smallint(5) unsigned NOT NULL default '0',
		`file3` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file3_hex` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file3_original` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`file3_size` int(20) unsigned NOT NULL default '0',
		`file3_size_formatted` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`image3_width` smallint(5) unsigned NOT NULL default '0',
		`image3_height` smallint(5) unsigned NOT NULL default '0',
		`thumb3` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`thumb3_width` smallint(5) unsigned NOT NULL default '0',
		`thumb3_height` smallint(5) unsigned NOT NULL default '0',
		`stickied` tinyint(1) NOT NULL default '0',
		`moderated` tinyint(1) NOT NULL default '1',
		`likes` smallint(5) NOT NULL default '0',
		PRIMARY KEY (`id`),
		KEY `parent` (`parent`),
		KEY `bumped` (`bumped`),
		KEY `stickied` (`stickied`),
		KEY `moderated` (`moderated`)
	)";

	$bans_sql = "CREATE TABLE `" . TINYIB_DBBANS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`ip` varchar(39) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`timestamp` int(20) NOT NULL,
		`expire` int(20) NOT NULL,
		`reason` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		PRIMARY KEY (`id`),
		KEY `ip` (`ip`)
	)";

	$likes_sql = "CREATE TABLE `" . TINYIB_DBLIKES . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`ip` varchar(39) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`board` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`postnum` mediumint(7) unsigned NOT NULL,
		`islike` tinyint(1) NOT NULL default '1',
		PRIMARY KEY (`id`)
	)";

	$modlog_sql = "CREATE TABLE `" . TINYIB_DBMODLOG . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`timestamp` int(20) NOT NULL,
		`boardname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`username` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`action` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`color` varchar(75) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		`private` tinyint(1) NOT NULL default '1',
		PRIMARY KEY (`id`)
	)";
}

function cleanString($string) {
	$search = array("&", "<", ">");
	$replace = array("&amp;", "&lt;", "&gt;");
	return str_replace($search, $replace, $string);
}

function plural($singular, $count, $plural = 's') {
	if ($plural == 's') {
		$plural = $singular . $plural;
	}
	return ($count == 1 ? $singular : $plural);
}

function threadUpdated($id) {
	rebuildThread($id);
	rebuildIndexes();
}

function newPost($parent = TINYIB_NEWTHREAD) {
	return array('parent' => $parent,
		'timestamp' => '0',
		'bumped' => '0',
		'ip' => '',
		'name' => '',
		'tripcode' => '',
		'email' => '',
		'nameblock' => '',
		'subject' => '',
		'message' => '',
		'password' => '',
		'file0' => '',
		'file0_hex' => '',
		'file0_original' => '',
		'file0_size' => '0',
		'file0_size_formatted' => '',
		'image0_width' => '0',
		'image0_height' => '0',
		'thumb0' => '',
		'thumb0_width' => '0',
		'thumb0_height' => '0',
		'file1' => '',
		'file1_hex' => '',
		'file1_original' => '',
		'file1_size' => '0',
		'file1_size_formatted' => '',
		'image1_width' => '0',
		'image1_height' => '0',
		'thumb1' => '',
		'thumb1_width' => '0',
		'thumb1_height' => '0',
		'file2' => '',
		'file2_hex' => '',
		'file2_original' => '',
		'file2_size' => '0',
		'file2_size_formatted' => '',
		'image2_width' => '0',
		'image2_height' => '0',
		'thumb2' => '',
		'thumb2_width' => '0',
		'thumb2_height' => '0',
		'file3' => '',
		'file3_hex' => '',
		'file3_original' => '',
		'file3_size' => '0',
		'file3_size_formatted' => '',
		'image3_width' => '0',
		'image3_height' => '0',
		'thumb3' => '',
		'thumb3_width' => '0',
		'thumb3_height' => '0',
		'stickied' => '0',
		'moderated' => '1');
}

function convertBytes($number) {
	$len = strlen($number);
	if ($len < 4) {
		return sprintf("%dB", $number);
	} elseif ($len <= 6) {
		return sprintf("%0.2fKB", $number / 1024);
	} elseif ($len <= 9) {
		return sprintf("%0.2fMB", $number / 1024 / 1024);
	}
	return sprintf("%0.2fGB", $number / 1024 / 1024 / 1024);
}

function nameAndTripcode($name) {
	if (preg_match("/(#|!)(.*)/", $name, $regs)) {
		$cap = $regs[2];
		$cap_full = '#' . $regs[2];
		if (function_exists('mb_convert_encoding')) {
			$recoded_cap = mb_convert_encoding($cap, 'SJIS', 'UTF-8');
			if ($recoded_cap != '') {
				$cap = $recoded_cap;
			}
		}
		if (strpos($name, '#') === false) {
			$cap_delimiter = '!';
		} elseif (strpos($name, '!') === false) {
			$cap_delimiter = '#';
		} else {
			$cap_delimiter = strpos($name, '#') < strpos($name, '!') ? '#' : '!';
		}
		if (preg_match("/(.*)(" . $cap_delimiter . ")(.*)/", $cap, $regs_secure)) {
			$cap = $regs_secure[1];
			$cap_secure = $regs_secure[3];
			$is_secure_trip = true;
		} else {
			$is_secure_trip = false;
		}
		$tripcode = "";
		if ($cap != "") { // Copied from Futabally
			$cap = strtr($cap, "&amp;", "&");
			$cap = strtr($cap, "&#44;", ", ");
			$salt = substr($cap . "H.", 1, 2);
			$salt = preg_replace("/[^\.-z]/", ".", $salt);
			$salt = strtr($salt, ":;<=>?@[\\]^_`", "ABCDEFGabcdef");
			$tripcode = substr(crypt($cap, $salt), -10);
		}
		if ($is_secure_trip) {
			if ($cap != "") {
				$tripcode .= "!";
			}
			$tripcode .= "!" . substr(md5($cap_secure . TINYIB_TRIPSEED), 2, 10);
		}
		return array(preg_replace("/(" . $cap_delimiter . ")(.*)/", "", $name), $tripcode);
	}
	return array($name, "");
}

function nameBlock($name, $tripcode, $email, $ip, $parent, $timestamp, $rawposttext) {
	$posterUID = '';
	if (TINYIB_POSTERUID) {
		$hash = substr(md5($ip . intval($parent) . TINYIB_TRIPSEED), 0, 8);
		$hashint = hexdec('0x' . $hash);
		$red = $hashint >> 24 & 255;
		$green = $hashint >> 16 & 255;
		$blue = $hashint >> 8 & 255;
		$isBlack = 0.299 * $red + 0.587 * $green + 0.114 * $blue > 125;
		$posterUID = ' <span class="posteruid" style="background-color: rgb(' . $red . ', ' . $green . ', ' .
			$blue . '); color: ' . ($isBlack ? 'black' : 'white') . ';">' . $hash . '</span>';
	}
	$output = '<span class="postername' .
		(checkAccess() != 'disabled' && $name != '' ? ' postername-admin' : '') . '">';
	$output .= $name == '' && $tripcode == '' ? TINYIB_POSTERNAME : $name;
	if ($tripcode != '') {
		$output .= '</span><span class="postertrip">!' . $tripcode;
	}
	$output .= '</span>' . $posterUID;
	$lowEmail = strtolower($email);
	if ($email != '' && $lowEmail != 'noko') {
		$output = '<a href="mailto:' . $email . '"' .
			($lowEmail == 'sage' ? ' class="sage"' : '') . '>' . $output . '</a>';
	}
	return $output . $rawposttext . ' ' . date('d.m.y D H:i:s', $timestamp);
}

function writePage($filename, $contents) {
	$tempfile = tempnam('res/', TINYIB_BOARD . 'tmp'); /* Create the temporary file */
	$fp = fopen($tempfile, 'w');
	fwrite($fp, $contents);
	fclose($fp);
	/* If we aren't able to use the rename function, try the alternate method */
	if (!@rename($tempfile, $filename)) {
		copy($tempfile, $filename);
		unlink($tempfile);
	}
	chmod($filename, 0664); /* it was created 0600 */
}

function _finishWordBreak($matches) {
	return '<a' . $matches[1] . 'href="' . str_replace(TINYIB_WORDBREAK_IDENTIFIER, '', $matches[2]) . '"' .
		$matches[3] . '>' . str_replace(TINYIB_WORDBREAK_IDENTIFIER, '<br>', $matches[4]) . '</a>';
}

function finishWordBreak($message) {
	return str_replace(
		TINYIB_WORDBREAK_IDENTIFIER,
		'<br>',
		preg_replace_callback('/<a(.*?)href="([^"]*?)"(.*?)>(.*?)<\/a>/', '_finishWordBreak', $message)
	);
}

function deletePostImages($post, $imgList = array()) {
	if (($imgList) && (count($imgList) <= TINYIB_MAXIMUM_FILES)) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			if (!isEmbed($post['file' . $index . '_hex']) && $post['file' . $index] != '') {
				@unlink('src/' . $post['file' . $index]);
			}
			$thumbName = $post['thumb' . $index];
			if ($thumbName != '' && $thumbName != 'spoiler.png') {
				@unlink('thumb/' . $thumbName);
			}
		}
	} else {
		for ($index = 0; $index < TINYIB_MAXIMUM_FILES; $index++) {
			if (!isEmbed($post['file' . $index . '_hex']) && $post['file' . $index] != '') {
				@unlink('src/' . $post['file' . $index]);
			}
			$thumbName = $post['thumb' . $index];
			if ($thumbName != '' && $thumbName != 'spoiler.png') {
				@unlink('thumb/' . $thumbName);
			}
		}
	}
}

function deletePostImagesThumb($post, $imgList) {
	if ($imgList && (count($imgList) <= TINYIB_MAXIMUM_FILES)) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			$thumbName = $post['thumb' . $index];
			if ($thumbName != '' && $thumbName != 'spoiler.png') {
				@unlink('thumb/' . $thumbName);
			}
		}
	}
}

function checkCAPTCHA() {
	if (TINYIB_CAPTCHA === 'recaptcha') {
		require_once 'inc/recaptcha/autoload.php';
		$captcha = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
		$failed_captcha = true;
		$recaptcha = new \ReCaptcha\ReCaptcha(TINYIB_RECAPTCHA_SECRET);
		$resp = $recaptcha->verify($captcha, $_SERVER['REMOTE_ADDR']);
		if ($resp->isSuccess()) {
			$failed_captcha = false;
		}
		if ($failed_captcha) {
			$captcha_error = 'Failed CAPTCHA.';
			$errCodes = $resp->getErrorCodes();
			$errReason = '';
			if (count($errCodes) == 1) {
				$errCodes = $errCodes;
				$errReason = $errCodes[0];
			}
			if ($errReason == 'missing-input-response') {
				$captcha_error .= ' Please click the checkbox labeled "I\'m not a robot".';
			} else {
				$captcha_error .= ' Reason:';
				foreach ($errCodes as $error) {
					$captcha_error .= '<br>' . $error;
				}
			}
			fancyDie($captcha_error);
		}
	} else if (TINYIB_CAPTCHA) { // Simple CAPTCHA
		$captcha = isset($_POST['captcha']) ? strtolower(trim($_POST['captcha'])) : '';
		$captcha_solution = isset($_SESSION['atomboardcaptcha']) ?
			strtolower(trim($_SESSION['atomboardcaptcha'])) : '';
		if ($captcha == '') {
			fancyDie('Please enter the CAPTCHA text.');
		} else if ($captcha != $captcha_solution) {
			fancyDie('Incorrect CAPTCHA text entered, please try again.<br>' .
				'Click the image to retrieve a new CAPTCHA.');
		}
	}
}

function checkBanned() {
	$ban = banByIP($_SERVER['REMOTE_ADDR']);
	if ($ban) {
		if ($ban['expire'] == 0 || $ban['expire'] > time()) {
			$expire = $ban['expire'] > 0 ?
				('<br>This ban will expire ' . date('y.m.d D H:i:s', $ban['expire'])) :
				'<br>This ban is permanent and will not expire.';
			$reason = $ban['reason'] == '' ? '' : '<br>Reason: ' . $ban['reason'];
			fancyDie('Your IP address ' . $ban['ip'] . ' has been banned from posting on this image board. ' .
				$expire . $reason);
		} else {
			clearExpiredBans();
		}
	}
}

function checkFlood() {
	if (TINYIB_DELAY > 0) {
		$lastpost = lastPostByIP();
		if ($lastpost) {
			if ((time() - $lastpost['timestamp']) < TINYIB_DELAY) {
				fancyDie('Please wait a moment before posting again.<br>' .
					'You will be able to make another post in ' .
					(TINYIB_DELAY - (time() - $lastpost['timestamp'])) .
					' ' . plural("second", (TINYIB_DELAY - (time() - $lastpost['timestamp']))) . '.');
			}
		}
	}
}

function checkMessageSize() {
	if (strlen($_POST["message"]) > 8000) {
		fancyDie("Please shorten your message, or post it in multiple parts.<br>Your message is " .
			strlen($_POST["message"]) . " characters long, and the maximum allowed is 8000.");
	}
}

function checkAccess() {
	global $atomboard_moderators, $atomboard_janitors;
	if (isset($_POST['managepassword'])) {
		$providedPassword = substr($_POST['managepassword'], 0, 256);
		if ($providedPassword != '' && $providedPassword === TINYIB_ADMINPASS) {
			$_SESSION['atomboard'] = TINYIB_ADMINPASS;
			$_SESSION['atomboard_user'] = 'Admin';
		} elseif ($providedPassword != '' &&
			count($atomboard_moderators) != 0 &&
			$modname = array_search($providedPassword, $atomboard_moderators, true)
		) {
			$_SESSION['atomboard'] = $atomboard_moderators[$modname];
			$_SESSION['atomboard_user'] = $modname;
			modLog('Moderator login', '1', 'BlueViolet');
		} elseif ($providedPassword != '' &&
			count($atomboard_janitors) != 0 &&
			$modname = array_search($providedPassword, $atomboard_janitors, true)
		) {
			$_SESSION['atomboard'] = $atomboard_janitors[$modname];
			$_SESSION['atomboard_user'] = $modname;
			modLog('Janitor login', '1', 'BlueViolet');
		} else {
			// uncomment if you want a lot of "failed login" records in modLog
			// modLog('Failed login attempt', '1', 'Orange');
		}
	}
	$access = 'disabled';
	if (isset($_SESSION['atomboard'])) {
		if ($_SESSION['atomboard'] === TINYIB_ADMINPASS) {
			$access = 'admin';
		} elseif (count($atomboard_moderators) != 0 &&
			array_search($_SESSION['atomboard'], $atomboard_moderators, true)
		) {
			$access = 'moderator';
		} elseif (count($atomboard_janitors) != 0 &&
			array_search($_SESSION['atomboard'], $atomboard_janitors, true)
		) {
			$access = 'janitor';
		}
	}
	return $access;
}

function setParent() {
	if (isset($_POST["parent"])) {
		if ($_POST["parent"] != TINYIB_NEWTHREAD) {
			if (!threadExistsByID($_POST['parent'])) {
				fancyDie("Invalid parent thread ID supplied, unable to create post.");
			}
			return $_POST["parent"];
		}
	}
	return TINYIB_NEWTHREAD;
}

function isRawPost() {
	return isset($_POST['rawpost']) && checkAccess() != 'disabled';
}

function validateFileUpload($error) {
	switch ($error) {
	case UPLOAD_ERR_OK:
		break;
	case UPLOAD_ERR_FORM_SIZE:
		fancyDie("That file is larger than " . TINYIB_MAXKBDESC . ".");
		break;
	case UPLOAD_ERR_INI_SIZE:
		fancyDie("The uploaded file exceeds the upload_max_filesize directive (" .
			ini_get('upload_max_filesize') . ") in php.ini.");
		break;
	case UPLOAD_ERR_PARTIAL:
		fancyDie("The uploaded file was only partially uploaded.");
		break;
	case UPLOAD_ERR_NO_FILE:
		fancyDie("No file was uploaded.");
		break;
	case UPLOAD_ERR_NO_TMP_DIR:
		fancyDie("Missing a temporary folder.");
		break;
	case UPLOAD_ERR_CANT_WRITE:
		fancyDie("Failed to write file to disk.");
		break;
	case UPLOAD_ERR_EXTENSION:
		fancyDie("Unable to save the uploaded file. Extension error");
		break;
	default:
		fancyDie("Unable to save the uploaded file.");
	}
}

function checkDuplicateFile($hex) {
	$hexmatches = postsByHex($hex);
	if (count($hexmatches) > 0) {
		foreach ($hexmatches as $hexmatch) {
			fancyDie("Duplicate file uploaded.<br>That file has already been posted <a href=\"res/" .
				($hexmatch["parent"] == TINYIB_NEWTHREAD ? $hexmatch["id"] : $hexmatch["parent"]) .
				".html#" . $hexmatch["id"] . "\">here</a>.");
		}
	}
}

function thumbnailDimensions($post, $imgIndex = 0) {
	if ($post['parent'] == TINYIB_NEWTHREAD) {
		$max_width = TINYIB_MAXWOP;
		$max_height = TINYIB_MAXHOP;
	} else {
		$max_width = TINYIB_MAXW;
		$max_height = TINYIB_MAXH;
	}
	return (
		$post['image' . $imgIndex . '_width'] > $max_width ||
		$post['image' . $imgIndex . '_height'] > $max_height
	) ? array($max_width, $max_height) :
		array($post['image' . $imgIndex . '_width'], $post['image' . $imgIndex . '_height']);
}

function createThumbnail($file_location, $thumb_location, $new_w, $new_h) {
	if (TINYIB_THUMBNAIL == 'gd') {
		$system = explode(".", $thumb_location);
		$system = array_reverse($system);
		if (preg_match("/jpg|jpeg/", $system[0])) {
			$src_img = imagecreatefromjpeg($file_location);
		} else if (preg_match("/png/", $system[0])) {
			$src_img = imagecreatefrompng($file_location);
		} else if (preg_match("/gif/", $system[0])) {
			$src_img = imagecreatefromgif ($file_location);
		} else if (preg_match("/webp/", $system[0])) {
			$src_img = imagecreatefromwebp ($file_location);
		} else {
			return false;
		}
		if (!$src_img) {
			fancyDie("Unable to read uploaded file during thumbnailing.<br>A common cause' .
				' for this is an incorrect extension when the file is actually of a different type.");
		}
		$old_x = imageSX($src_img);
		$old_y = imageSY($src_img);
		$percent = $old_x > $old_y ? $new_w / $old_x : $new_h / $old_y;
		$thumb_w = round($old_x * $percent);
		$thumb_h = round($old_y * $percent);
		$dst_img = imagecreatetruecolor($thumb_w, $thumb_h);
		if (preg_match("/png/", $system[0]) && imagepng($src_img, $thumb_location)) {
			imagealphablending($dst_img, false);
			imagesavealpha($dst_img, true);
			$color = imagecolorallocatealpha($dst_img, 0, 0, 0, 0);
			imagefilledrectangle($dst_img, 0, 0, $thumb_w, $thumb_h, $color);
			imagecolortransparent($dst_img, $color);
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
		} else {
			fastimagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
		}
		if (preg_match("/png/", $system[0])) {
			if (!imagepng($dst_img, $thumb_location)) {
				return false;
			}
		} else if (preg_match("/jpg|jpeg/", $system[0])) {
			if (!imagejpeg($dst_img, $thumb_location, 70)) {
				return false;
			}
		} else if (preg_match("/gif/", $system[0])) {
			if (!imagegif ($dst_img, $thumb_location)) {
				return false;
			}
		} else if (preg_match("/webp/", $system[0])) {
			if (!imagewebp($dst_img, $thumb_location, 70)) {
				return false;
			}
		}
		imagedestroy($dst_img);
		imagedestroy($src_img);
	} else { // imagemagick
		$discard = '';
		$exit_status = 1;
		$extension = pathinfo($thumb_location, PATHINFO_EXTENSION);
		if ($extension === 'gif') {
			if (TINYIB_FILE_ANIM_GIF_THUMB) {
				exec("convert $file_location -auto-orient -thumbnail '" . $new_w . "x" . $new_h .
					"' -coalesce -layers OptimizeFrame -depth 4 $thumb_location", $discard, $exit_status);
			} else {
				exec("convert ${file_location}[0] -auto-orient -thumbnail '" . $new_w . "x" . $new_h .
					"' -layers OptimizeFrame -depth 8 $thumb_location", $discard, $exit_status);
			}
		} else {
			exec("convert $file_location -auto-orient -thumbnail '" . $new_w . "x" . $new_h .
				"' -layers OptimizeFrame -depth 8 $thumb_location", $discard, $exit_status);
		}
		if ($exit_status != 0) {
			return false;
		}
	}
	return true;
}

function fastimagecopyresampled(
	&$dst_image,
	&$src_image,
	$dst_x,
	$dst_y,
	$src_x,
	$src_y,
	$dst_w,
	$dst_h,
	$src_w,
	$src_h,
	$quality = 3
) {
	// Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable.
	if (empty($src_image) || empty($dst_image)) {
		return false;
	}
	if ($quality <= 1) {
		$temp = imagecreatetruecolor($dst_w + 1, $dst_h + 1);
		imagecopyresized(
			$temp,
			$src_image,
			$dst_x,
			$dst_y,
			$src_x,
			$src_y,
			$dst_w + 1,
			$dst_h + 1,
			$src_w,
			$src_h
		);
		imagecopyresized($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
		imagedestroy($temp);
	} elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
		$tmp_w = $dst_w * $quality;
		$tmp_h = $dst_h * $quality;
		$temp = imagecreatetruecolor($tmp_w + 1, $tmp_h + 1);
		imagecopyresized(
			$temp,
			$src_image,
			$dst_x * $quality,
			$dst_y * $quality,
			$src_x,
			$src_y,
			$tmp_w + 1,
			$tmp_h + 1,
			$src_w,
			$src_h
		);
		imagecopyresampled($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
		imagedestroy($temp);
	} else {
		imagecopyresampled(
			$dst_image,
			$src_image,
			$dst_x,
			$dst_y,
			$src_x,
			$src_y,
			$dst_w,
			$dst_h,
			$src_w,
			$src_h
		);
	}
	return true;
}

function addVideoOverlay($thumb_location) {
	if (!file_exists('icons/video_overlay.png')) {
		return;
	}
	if (TINYIB_THUMBNAIL == 'gd') {
		if (substr($thumb_location, -4) == ".jpg") {
			$thumbnail = imagecreatefromjpeg($thumb_location);
		} else {
			$thumbnail = imagecreatefrompng($thumb_location);
		}
		list($width, $height, $type, $attr) = getimagesize($thumb_location);
		$overlay_play = imagecreatefrompng('icons/video_overlay.png');
		imagealphablending($overlay_play, false);
		imagesavealpha($overlay_play, true);
		list(
			$overlay_width,
			$overlay_height,
			$overlay_type,
			$overlay_attr
		) = getimagesize('icons/video_overlay.png');
		if (substr($thumb_location, -4) == ".png") {
			imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
			imagealphablending($thumbnail, true);
			imagesavealpha($thumbnail, true);
		}
		imagecopy(
			$thumbnail,
			$overlay_play,
			($width / 2) - ($overlay_width / 2),
			($height / 2) - ($overlay_height / 2),
			0,
			0,
			$overlay_width,
			$overlay_height
		);
		if (substr($thumb_location, -4) == ".jpg") {
			imagejpeg($thumbnail, $thumb_location);
		} else {
			imagepng($thumbnail, $thumb_location);
		}
	} else { // imagemagick
		$discard = '';
		$exit_status = 1;
		exec('convert ' . $thumb_location .
			' icons/video_overlay.png -gravity center -composite -quality 75 ' .
			$thumb_location, $discard, $exit_status);
	}
}

function strallpos($haystack, $needle, $offset = 0) {
	$result = array();
	for ($i = $offset; $i < strlen($haystack); $i++) {
		$pos = strpos($haystack, $needle, $i);
		if ($pos !== False) {
			$offset = $pos;
			if ($offset >= $i) {
				$i = $offset;
				$result[] = $offset;
			}
		}
	}
	return $result;
}

function url_get_contents($url) {
	if (!function_exists('curl_init')) {
		return file_get_contents($url);
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function isEmbed($file_hex) {
	global $atomboard_embeds;
	return in_array($file_hex, array_keys($atomboard_embeds));
}

function getEmbed($url) {
	global $atomboard_embeds;
	if (sizeof($atomboard_embeds) != 0) {
		foreach ($atomboard_embeds as $service => $service_url) {
			if (strpos(strtolower($url), strtolower($service)) !== false) {
				$service_url = str_ireplace("TINYIBEMBED", urlencode($url), $service_url);
				$result = json_decode(url_get_contents($service_url), true);
				if (!empty($result)) {
					return array($service, $result);
				}
			}
		}
	}
	return array('', array());
}
