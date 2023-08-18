<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

/* ==[ Queries for creatsng new tables ]=================================================================== */

if (ATOM_DBMODE == 'pdo' && ATOM_DBDRIVER == 'pgsql') {
	$postsQuery = 'CREATE TABLE "' . ATOM_DBPOSTS . '" (
		"id" bigserial NOT NULL,
		"parent" integer NOT NULL,
		"timestamp" integer NOT NULL,
		"bumped" integer NOT NULL,
		"ip" varchar(39) NOT NULL,
		"name" varchar(75) NOT NULL,
		"tripcode" varchar(10) NOT NULL,
		"email" varchar(75) NOT NULL,
		"nameblock" text NOT NULL,
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
		"likes" smallint NOT NULL default \'0\',
		"moderated" smallint NOT NULL default \'1\',
		"stickied" smallint NOT NULL default \'0\',
		"locked" smallint NOT NULL default \'0\',
		"endless" smallint NOT NULL default \'0\',
		PRIMARY KEY ("id")
	);
	CREATE INDEX ON "' . ATOM_DBPOSTS . '"("parent");
	CREATE INDEX ON "' . ATOM_DBPOSTS . '"("bumped");
	CREATE INDEX ON "' . ATOM_DBPOSTS . '"("stickied");
	CREATE INDEX ON "' . ATOM_DBPOSTS . '"("moderated");';

	$bansQuery = 'CREATE TABLE "' . ATOM_DBBANS . '" (
		"id" bigserial NOT NULL,
		"ip" varchar(39) NOT NULL,
		"timestamp" integer NOT NULL,
		"expire" integer NOT NULL,
		"reason" text NOT NULL,
		PRIMARY KEY ("id")
	);
	CREATE INDEX ON "' . ATOM_DBBANS . '"("ip");';

	$likesQuery = 'CREATE TABLE "' . ATOM_DBLIKES . '" (
		"id" bigserial NOT NULL,
		"ip" varchar(39) NOT NULL,
		"board" varchar(16) NOT NULL,
		"postnum" integer NOT NULL,
		"islike" smallint NOT NULL default \'1\',
		PRIMARY KEY ("id")
	);
	CREATE INDEX ON "' . ATOM_DBLIKES . '"("ip");';

	$modlogQuery = 'CREATE TABLE "' . ATOM_DBMODLOG . '" (
		"id" bigserial NOT NULL,
		"timestamp" integer NOT NULL,
		"boardname" varchar(255) NOT NULL,
		"username" varchar(75) NOT NULL,
		"action" text NOT NULL,
		"color" varchar(75) NOT NULL,
		"private" smallint NOT NULL default \'1\',
	);
	CREATE INDEX ON "' . ATOM_DBMODLOG . '"("boardname");';

} else {
	$postsQuery = "CREATE TABLE `" . ATOM_DBPOSTS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`parent` mediumint(7) unsigned NOT NULL,
		`timestamp` int(20) NOT NULL,
		`bumped` int(20) NOT NULL,
		`ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`name` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`tripcode` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`email` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`nameblock` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`subject` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file0` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file0_hex` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file0_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file0_size` int(20) unsigned NOT NULL default '0',
		`file0_size_formatted` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`image0_width` smallint(5) unsigned NOT NULL default '0',
		`image0_height` smallint(5) unsigned NOT NULL default '0',
		`thumb0` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`thumb0_width` smallint(5) unsigned NOT NULL default '0',
		`thumb0_height` smallint(5) unsigned NOT NULL default '0',
		`file1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file1_hex` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file1_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file1_size` int(20) unsigned NOT NULL default '0',
		`file1_size_formatted` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`image1_width` smallint(5) unsigned NOT NULL default '0',
		`image1_height` smallint(5) unsigned NOT NULL default '0',
		`thumb1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`thumb1_width` smallint(5) unsigned NOT NULL default '0',
		`thumb1_height` smallint(5) unsigned NOT NULL default '0',
		`file2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file2_hex` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file2_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file2_size` int(20) unsigned NOT NULL default '0',
		`file2_size_formatted` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`image2_width` smallint(5) unsigned NOT NULL default '0',
		`image2_height` smallint(5) unsigned NOT NULL default '0',
		`thumb2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`thumb2_width` smallint(5) unsigned NOT NULL default '0',
		`thumb2_height` smallint(5) unsigned NOT NULL default '0',
		`file3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file3_hex` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file3_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`file3_size` int(20) unsigned NOT NULL default '0',
		`file3_size_formatted` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`image3_width` smallint(5) unsigned NOT NULL default '0',
		`image3_height` smallint(5) unsigned NOT NULL default '0',
		`thumb3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`thumb3_width` smallint(5) unsigned NOT NULL default '0',
		`thumb3_height` smallint(5) unsigned NOT NULL default '0',
		`likes` smallint(5) NOT NULL default '0',
		`moderated` tinyint(1) NOT NULL default '1',
		`stickied` tinyint(1) NOT NULL default '0',
		`locked` tinyint(1) NOT NULL default '0',
		`endless` tinyint(1) NOT NULL default '0',
		PRIMARY KEY (`id`),
		KEY `parent` (`parent`),
		KEY `bumped` (`bumped`),
		KEY `moderated` (`moderated`),
		KEY `stickied` (`stickied`)
	)";

	$bansQuery = "CREATE TABLE `" . ATOM_DBBANS . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`timestamp` int(20) NOT NULL,
		`expire` int(20) NOT NULL,
		`reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		PRIMARY KEY (`id`),
		KEY `ip` (`ip`)
	)";

	$likesQuery = "CREATE TABLE `" . ATOM_DBLIKES . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`board` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`postnum` mediumint(7) unsigned NOT NULL,
		`islike` tinyint(1) NOT NULL default '1',
		PRIMARY KEY (`id`)
	)";

	$modlogQuery = "CREATE TABLE `" . ATOM_DBMODLOG . "` (
		`id` mediumint(7) unsigned NOT NULL auto_increment,
		`timestamp` int(20) NOT NULL,
		`boardname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`username` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`action` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`color` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
		`private` tinyint(1) NOT NULL default '1',
		PRIMARY KEY (`id`)
	)";
}

/* ==[ Strings ]=========================================================================================== */

function escapeHTML($string) {
	return str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $string);
}

function plural($singular, $count, $plural = 's') {
	if ($plural == 's') {
		$plural = $singular . $plural;
	}
	return ($count == 1 ? $singular : $plural);
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

/* ==[ Posts ]============================================================================================= */

function newPost($parent) {
	return array(
		'parent' => $parent,
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
		'likes' => '0',
		'moderated' => '1',
		'stickied' => '0',
		'locked' => '0',
		'endless' => '0');
}

function isOp($post) {
	return $post['parent'] == ATOM_NEWTHREAD;
}

/* ==[ Images/video files ]================================================================================ */

function deletePostImagesFiles($post, $imgList = array()) {
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT)) {
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
		return;
	}
	for ($index = 0; $index < ATOM_FILES_COUNT; $index++) {
		if (!isEmbed($post['file' . $index . '_hex']) && $post['file' . $index] != '') {
			@unlink('src/' . $post['file' . $index]);
		}
		$thumbName = $post['thumb' . $index];
		if ($thumbName != '' && $thumbName != 'spoiler.png') {
			@unlink('thumb/' . $thumbName);
		}
	}
}

function deletePostImagesFilesThumbFiles($post, $imgList) {
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT)) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = intval(trim(basename($index)));
			$thumbName = $post['thumb' . $index];
			if ($thumbName != '' && $thumbName != 'spoiler.png') {
				@unlink('thumb/' . $thumbName);
			}
		}
	}
}

function getThumbnailDimensions($post, $imgIndex = 0) {
	if ($post['parent'] == ATOM_NEWTHREAD) {
		$max_width = ATOM_FILE_MAXWOP;
		$max_height = ATOM_FILE_MAXHOP;
	} else {
		$max_width = ATOM_FILE_MAXW;
		$max_height = ATOM_FILE_MAXH;
	}
	return (
		$post['image' . $imgIndex . '_width'] > $max_width ||
		$post['image' . $imgIndex . '_height'] > $max_height
	) ? array($max_width, $max_height) :
		array($post['image' . $imgIndex . '_width'], $post['image' . $imgIndex . '_height']);
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

function createThumbnail($file_location, $thumb_location, $new_w, $new_h) {
	if (ATOM_FILE_THUMBDRIVER == 'gd') {
		$system = explode(".", $thumb_location);
		$system = array_reverse($system);
		if (preg_match("/jpg|jpeg/", $system[0])) {
			$src_img = imagecreatefromjpeg($file_location);
		} elseif (preg_match("/png/", $system[0])) {
			$src_img = imagecreatefrompng($file_location);
		} elseif (preg_match("/gif/", $system[0])) {
			$src_img = imagecreatefromgif ($file_location);
		} elseif (preg_match("/webp/", $system[0])) {
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
		} elseif (preg_match("/jpg|jpeg/", $system[0])) {
			if (!imagejpeg($dst_img, $thumb_location, 70)) {
				return false;
			}
		} elseif (preg_match("/gif/", $system[0])) {
			if (!imagegif ($dst_img, $thumb_location)) {
				return false;
			}
		} elseif (preg_match("/webp/", $system[0])) {
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
			if (ATOM_FILE_ANIM_GIF) {
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

function addVideoOverlay($thumb_location) {
	if (!file_exists('icons/video_overlay.png')) {
		return;
	}
	if (ATOM_FILE_THUMBDRIVER == 'imagemagick') {
		$discard = '';
		$exit_status = 1;
		exec('convert ' . $thumb_location .
			' icons/video_overlay.png -gravity center -composite -quality 75 ' .
			$thumb_location, $discard, $exit_status);
		return;
	}
	// gd
	$thumbnail = substr($thumb_location, -4) == '.jpg' ? imagecreatefromjpeg($thumb_location) :
		imagecreatefrompng($thumb_location);
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
		intval($width / 2) - intval($overlay_width / 2),
		intval($height / 2) - intval($overlay_height / 2),
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
}

function isEmbed($file_hex) {
	global $atom_embeds;
	return in_array($file_hex, array_keys($atom_embeds));
}

function getEmbed($url) {
	global $atom_embeds;
	if (sizeof($atom_embeds) != 0) {
		foreach ($atom_embeds as $service => $service_url) {
			if (strpos(strtolower($url), strtolower($service)) !== false) {
				$service_url = str_ireplace("ATOM_EMBED", urlencode($url), $service_url);
				$result = json_decode(url_get_contents($service_url), true);
				if (!empty($result)) {
					return array($service, $result);
				}
			}
		}
	}
	return array('', array());
}

/* ==[ Threads ]=========================================================================================== */

// Delete old posts in endless threads
function trimThreadPostsCount($id) {
	$postOP = getPost($id);
	if($postOP && intval($postOP['endless']) == 1) {
		$posts = getThreadPosts($id, false);
		$overLimit = count($posts) - ATOM_THREAD_LIMIT + 1;
		if ($overLimit > 0) {
			for ($i = 1; $i < $overLimit; $i++) {
				deletePost($posts[$i]['id']);
			}
		}
	}
}

function getThreadId($post) {
	return $post['parent'] == ATOM_NEWTHREAD ? $post['id'] : $post['parent'];
}

/* ==[ Posting ]=========================================================================================== */

function checkAccessRights() {
	global $atom_moderators, $atom_janitors;
	if (isset($_POST['managepassword'])) {
		$providedPassword = substr($_POST['managepassword'], 0, 256);
		if ($providedPassword != '' && $providedPassword === ATOM_ADMINPASS) {
			$_SESSION['atomboard'] = ATOM_ADMINPASS;
			$_SESSION['atom_user'] = 'Admin';
		} elseif ($providedPassword != '' &&
			count($atom_moderators) != 0 &&
			$modname = array_search($providedPassword, $atom_moderators, true)
		) {
			$_SESSION['atomboard'] = $atom_moderators[$modname];
			$_SESSION['atom_user'] = $modname;
			modLog('Moderator login', '1', 'BlueViolet');
		} elseif ($providedPassword != '' &&
			count($atom_janitors) != 0 &&
			$modname = array_search($providedPassword, $atom_janitors, true)
		) {
			$_SESSION['atomboard'] = $atom_janitors[$modname];
			$_SESSION['atom_user'] = $modname;
			modLog('Janitor login', '1', 'BlueViolet');
		} else {
			// Uncomment if you want a lot of "failed login" records in modLog
			// modLog('Failed login attempt', '1', 'Orange');
		}
	}
	$access = 'disabled';
	if (isset($_SESSION['atomboard'])) {
		if ($_SESSION['atomboard'] === ATOM_ADMINPASS) {
			$access = 'admin';
		} elseif (count($atom_moderators) != 0 &&
			array_search($_SESSION['atomboard'], $atom_moderators, true)
		) {
			$access = 'moderator';
		} elseif (count($atom_janitors) != 0 &&
			array_search($_SESSION['atomboard'], $atom_janitors, true)
		) {
			$access = 'janitor';
		}
	}
	if($access == 'disabled') {
		setcookie('atom_access', '', time() - 3600, '/' . ATOM_BOARD . '/');
		unset($_COOKIE['atom_access']);
	} else {
		setcookie('atom_access', '1', time() + 2592000, '/' . ATOM_BOARD . '/');
	}
	return $access;
}

function isstaffPost() {
	return isset($_POST['staffPost']) && checkAccessRights() != 'disabled';
}

/* ==[ File reading/writing ]============================================================================== */

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

function writePage($filename, $contents) {
	$tempfile = tempnam('res/', ATOM_BOARD . 'tmp'); /* Create the temporary file */
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
