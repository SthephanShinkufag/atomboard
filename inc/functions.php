<?php
declare(strict_types=1);
if (!defined('ATOM_BOARD')) {
	die('');
}

/* ==[ Queries for creatsng new tables ]=================================================================== */

if (ATOM_DBMODE === 'pdo' && ATOM_DBDRIVER === 'pgsql') {
	$postsQuery = 'CREATE TABLE ' . ATOM_DBPOSTS . ' (
		id bigserial PRIMARY KEY,
		parent integer NOT NULL,
		timestamp integer NOT NULL,
		bumped integer NOT NULL,
		ip varchar(39) NOT NULL,
		name varchar(75) NOT NULL,
		tripcode varchar(10) NOT NULL,
		email varchar(75) NOT NULL,
		nameblock text NOT NULL,
		subject varchar(100) NOT NULL,
		message text NOT NULL,
		password varchar(255) NOT NULL,
		file0 text NOT NULL,
		file0_hex varchar(75) NOT NULL,
		file0_original varchar(255) NOT NULL,
		file0_size integer NOT NULL DEFAULT 0,
		file0_size_formatted varchar(75) NOT NULL,
		image0_width smallint NOT NULL DEFAULT 0,
		image0_height smallint NOT NULL DEFAULT 0,
		thumb0 varchar(255) NOT NULL,
		thumb0_width smallint NOT NULL DEFAULT 0,
		thumb0_height smallint NOT NULL DEFAULT 0,
		file1 text NOT NULL,
		file1_hex varchar(75) NOT NULL,
		file1_original varchar(255) NOT NULL,
		file1_size integer NOT NULL DEFAULT 0,
		file1_size_formatted varchar(75) NOT NULL,
		image1_width smallint NOT NULL DEFAULT 0,
		image1_height smallint NOT NULL DEFAULT 0,
		thumb1 varchar(255) NOT NULL,
		thumb1_width smallint NOT NULL DEFAULT 0,
		thumb1_height smallint NOT NULL DEFAULT 0,
		file2 text NOT NULL,
		file2_hex varchar(75) NOT NULL,
		file2_original varchar(255) NOT NULL,
		file2_size integer NOT NULL DEFAULT 0,
		file2_size_formatted varchar(75) NOT NULL,
		image2_width smallint NOT NULL DEFAULT 0,
		image2_height smallint NOT NULL DEFAULT 0,
		thumb2 varchar(255) NOT NULL,
		thumb2_width smallint NOT NULL DEFAULT 0,
		thumb2_height smallint NOT NULL DEFAULT 0,
		file3 text NOT NULL,
		file3_hex varchar(75) NOT NULL,
		file3_original varchar(255) NOT NULL,
		file3_size integer NOT NULL DEFAULT 0,
		file3_size_formatted varchar(75) NOT NULL,
		image3_width smallint NOT NULL DEFAULT 0,
		image3_height smallint NOT NULL DEFAULT 0,
		thumb3 varchar(255) NOT NULL,
		thumb3_width smallint NOT NULL DEFAULT 0,
		thumb3_height smallint NOT NULL DEFAULT 0,
		likes smallint NOT NULL DEFAULT 0,
		moderated smallint NOT NULL DEFAULT 1,
		stickied smallint NOT NULL DEFAULT 0,
		locked smallint NOT NULL DEFAULT 0,
		endless smallint NOT NULL DEFAULT 0,
		pass integer NOT NULL DEFAULT 0
	);
	CREATE INDEX ' . ATOM_DBPOSTS . '_parent_sort_idx ON ' . ATOM_DBPOSTS . ' (parent, stickied DESC, bumped DESC);
	CREATE INDEX ' . ATOM_DBPOSTS . '_parent_id_idx ON ' . ATOM_DBPOSTS . ' (parent, id ASC);
	CREATE INDEX ' . ATOM_DBPOSTS . '_ip_time_idx ON ' . ATOM_DBPOSTS . ' (ip, timestamp DESC);
	CREATE INDEX ' . ATOM_DBPOSTS . '_mod_time_idx ON ' . ATOM_DBPOSTS . ' (moderated, timestamp DESC);
	CREATE INDEX ' . ATOM_DBPOSTS . '_f0_hex_idx ON ' . ATOM_DBPOSTS . ' (file0_hex);
	CREATE INDEX ' . ATOM_DBPOSTS . '_f1_hex_idx ON ' . ATOM_DBPOSTS . ' (file1_hex);
	CREATE INDEX ' . ATOM_DBPOSTS . '_f2_hex_idx ON ' . ATOM_DBPOSTS . ' (file2_hex);
	CREATE INDEX ' . ATOM_DBPOSTS . '_f3_hex_idx ON ' . ATOM_DBPOSTS . ' (file3_hex);';

	$staffQuery = 'CREATE TABLE ' . ATOM_DBSTAFF . ' (
		id bigserial NOT NULL PRIMARY KEY,
		username varchar(50) UNIQUE NOT NULL,
		password_hash varchar(255) NOT NULL,
		role varchar(20) NOT NULL,
		last_login integer NOT NULL DEFAULT 0
	);
	CREATE INDEX ' . ATOM_DBSTAFF . '_role_username_idx ON ' . ATOM_DBSTAFF . '(role, username);';

	$bansQuery = 'CREATE TABLE ' . ATOM_DBBANS . ' (
		id bigserial NOT NULL PRIMARY KEY,
		ip_from bigint NOT NULL,
		ip_to bigint NOT NULL,
		timestamp integer NOT NULL,
		expire integer NOT NULL,
		reason text NOT NULL
	);
	CREATE INDEX ' . ATOM_DBBANS . '_ip_range_idx ON ' . ATOM_DBBANS . '(ip_from, ip_to);
	CREATE INDEX ' . ATOM_DBBANS . '_time_idx ON ' . ATOM_DBBANS . '(timestamp DESC);
	CREATE INDEX ' . ATOM_DBBANS . '_expire_idx ON ' . ATOM_DBBANS . '(expire);';

	$ipLookupsQuery = 'CREATE TABLE ' . ATOM_DBIPLOOKUPS . ' (
		ip varchar(39) NOT NULL PRIMARY KEY,
		abuser smallint NOT NULL DEFAULT 0,
		vps smallint NOT NULL DEFAULT 0,
		proxy smallint NOT NULL DEFAULT 0,
		tor smallint NOT NULL DEFAULT 0,
		vpn smallint NOT NULL DEFAULT 0,
		last_updated integer NOT NULL DEFAULT 0
	);';

	$reportsQuery = 'CREATE TABLE ' . ATOM_DBREPORTS . ' (
		id bigserial NOT NULL PRIMARY KEY,
		ip varchar(39) NOT NULL,
		board varchar(16) NOT NULL,
		postnum integer NOT NULL,
		timestamp integer NOT NULL,
		reason text NOT NULL
	);
	CREATE INDEX ' . ATOM_DBREPORTS . '_board_pnum_time_idx ON ' . ATOM_DBREPORTS . '(board, postnum DESC, timestamp DESC);
	CREATE INDEX ' . ATOM_DBREPORTS . '_ip_board_pnum_idx ON ' . ATOM_DBREPORTS . '(ip, board, postnum);
	CREATE INDEX ' . ATOM_DBREPORTS . '_pnum_time_idx ON ' . ATOM_DBREPORTS . '(postnum, timestamp DESC);';

	$passQuery = 'CREATE TABLE ' . ATOM_DBPASS . ' (
		number bigserial NOT NULL PRIMARY KEY,
		id varchar(64) UNIQUE NOT NULL,
		issued integer NOT NULL,
		expires integer NOT NULL,
		blocked_till integer NOT NULL DEFAULT 0,
		blocked_reason text,
		meta text NOT NULL,
		meta_admin text NOT NULL,
		last_used integer NOT NULL DEFAULT 0,
		last_used_ip varchar(64)
	);
	CREATE INDEX ' . ATOM_DBPASS . '_num_idx ON ' . ATOM_DBPASS . '(number);
	CREATE INDEX ' . ATOM_DBPASS . '_expires_idx ON ' . ATOM_DBPASS . '(expires);';

	$likesQuery = 'CREATE TABLE ' . ATOM_DBLIKES . ' (
		id bigserial NOT NULL PRIMARY KEY,
		ip varchar(39) NOT NULL,
		board varchar(16) NOT NULL,
		postnum integer NOT NULL,
		islike smallint NOT NULL DEFAULT 1
	);
	CREATE INDEX ' . ATOM_DBLIKES . '_ip_board_pnum_idx ON ' . ATOM_DBLIKES . '(ip, board, postnum);
	CREATE INDEX ' . ATOM_DBLIKES . '_board_pnum_idx ON ' . ATOM_DBLIKES . '(board, postnum);';

	$modlogQuery = 'CREATE TABLE ' . ATOM_DBMODLOG . ' (
		id bigserial NOT NULL PRIMARY KEY,
		timestamp integer NOT NULL,
		boardname varchar(255) NOT NULL,
		username varchar(75) NOT NULL,
		action text NOT NULL,
		color varchar(75) NOT NULL,
		private smallint NOT NULL DEFAULT 1
	);
	CREATE INDEX ' . ATOM_DBMODLOG . '_board_public_idx ON ' . ATOM_DBMODLOG . '(boardname, private, timestamp DESC);
	CREATE INDEX ' . ATOM_DBMODLOG . '_board_time_idx ON ' . ATOM_DBMODLOG . '(boardname, timestamp DESC);';

} else {
	$postsQuery = "CREATE TABLE IF NOT EXISTS `" . ATOM_DBPOSTS . "` (
		`id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`parent` mediumint(7) unsigned NOT NULL,
		`timestamp` int(20) NOT NULL,
		`bumped` int(20) NOT NULL,
		`ip` varchar(39) NOT NULL,
		`name` varchar(75) NOT NULL,
		`tripcode` varchar(10) NOT NULL,
		`email` varchar(75) NOT NULL,
		`nameblock` text NOT NULL,
		`subject` varchar(100) NOT NULL,
		`message` text NOT NULL,
		`password` varchar(255) NOT NULL,
		`file0` text NOT NULL,
		`file0_hex` varchar(75) NOT NULL,
		`file0_original` varchar(255) NOT NULL,
		`file0_size` int(20) unsigned NOT NULL DEFAULT 0,
		`file0_size_formatted` varchar(75) NOT NULL,
		`image0_width` smallint(5) unsigned NOT NULL DEFAULT 0,
		`image0_height` smallint(5) unsigned NOT NULL DEFAULT 0,
		`thumb0` varchar(255) NOT NULL,
		`thumb0_width` smallint(5) unsigned NOT NULL DEFAULT 0,
		`thumb0_height` smallint(5) unsigned NOT NULL DEFAULT 0,
		`file1` text NOT NULL,
		`file1_hex` varchar(75) NOT NULL,
		`file1_original` varchar(255) NOT NULL,
		`file1_size` int(20) unsigned NOT NULL DEFAULT 0,
		`file1_size_formatted` varchar(75) NOT NULL,
		`image1_width` smallint(5) unsigned NOT NULL DEFAULT 0,
		`image1_height` smallint(5) unsigned NOT NULL DEFAULT 0,
		`thumb1` varchar(255) NOT NULL,
		`thumb1_width` smallint(5) unsigned NOT NULL DEFAULT 0,
		`thumb1_height` smallint(5) unsigned NOT NULL DEFAULT 0,
		`file2` text NOT NULL,
		`file2_hex` varchar(75) NOT NULL,
		`file2_original` varchar(255) NOT NULL,
		`file2_size` int(20) unsigned NOT NULL DEFAULT 0,
		`file2_size_formatted` varchar(75) NOT NULL,
		`image2_width` smallint(5) unsigned NOT NULL DEFAULT 0,
		`image2_height` smallint(5) unsigned NOT NULL DEFAULT 0,
		`thumb2` varchar(255) NOT NULL,
		`thumb2_width` smallint(5) unsigned NOT NULL DEFAULT 0,
		`thumb2_height` smallint(5) unsigned NOT NULL DEFAULT 0,
		`file3` text NOT NULL,
		`file3_hex` varchar(75) NOT NULL,
		`file3_original` varchar(255) NOT NULL,
		`file3_size` int(20) unsigned NOT NULL DEFAULT 0,
		`file3_size_formatted` varchar(75) NOT NULL,
		`image3_width` smallint(5) unsigned NOT NULL DEFAULT 0,
		`image3_height` smallint(5) unsigned NOT NULL DEFAULT 0,
		`thumb3` varchar(255) NOT NULL,
		`thumb3_width` smallint(5) unsigned NOT NULL DEFAULT 0,
		`thumb3_height` smallint(5) unsigned NOT NULL DEFAULT 0,
		`likes` smallint(5) NOT NULL DEFAULT 0,
		`moderated` tinyint(1) NOT NULL DEFAULT 1,
		`stickied` tinyint(1) NOT NULL DEFAULT 0,
		`locked` tinyint(1) NOT NULL DEFAULT 0,
		`endless` tinyint(1) NOT NULL DEFAULT 0,
		`pass` mediumint(7) unsigned NOT NULL DEFAULT 0,
		INDEX `parent_sort_idx` (`parent`, `stickied` DESC, `bumped` DESC),
		INDEX `parent_id_idx` (`parent`, `id` ASC),
		INDEX `ip_time_idx` (`ip`, `timestamp` DESC),
		INDEX `mod_time_idx` (`moderated`, `timestamp` DESC),
		INDEX `f0_hex_idx` (`file0_hex`),
		INDEX `f1_hex_idx` (`file1_hex`),
		INDEX `f2_hex_idx` (`file2_hex`),
		INDEX `f3_hex_idx` (`file3_hex`)
	) ENGINE=InnoDB;";

	$staffQuery = "CREATE TABLE IF NOT EXISTS `" . ATOM_DBSTAFF . "` (
		`id` INT AUTO_INCREMENT PRIMARY KEY,
		`username` VARCHAR(50) UNIQUE NOT NULL,
		`password_hash` VARCHAR(255) NOT NULL,
		`role` ENUM('admin', 'moderator', 'janitor') NOT NULL,
		`last_login` int(20) NOT NULL DEFAULT 0,
		INDEX `role_username_idx` (`role`, `username`)
	) ENGINE=InnoDB;";

	$bansQuery = "CREATE TABLE IF NOT EXISTS `" . ATOM_DBBANS . "` (
		`id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`ip_from` bigint(20) NOT NULL,
		`ip_to` bigint(20) NOT NULL,
		`timestamp` int(20) NOT NULL,
		`expire` int(20) NOT NULL,
		`reason` text NOT NULL,
		INDEX `ip_range_idx` (`ip_from`, `ip_to`),
		INDEX `time_idx` (`timestamp` DESC),
		INDEX `expire_idx` (`expire`)
	) ENGINE=InnoDB;";

	$ipLookupsQuery = "CREATE TABLE IF NOT EXISTS `" . ATOM_DBIPLOOKUPS . "` (
		`ip` varchar(39) NOT NULL PRIMARY KEY,
		`abuser` tinyint(1) NOT NULL DEFAULT 0,
		`vps` tinyint(1) NOT NULL DEFAULT 0,
		`proxy` tinyint(1) NOT NULL DEFAULT 0,
		`tor` tinyint(1) NOT NULL DEFAULT 0,
		`vpn` tinyint(1) NOT NULL DEFAULT 0,
		`last_updated` int(11) NOT NULL DEFAULT 0
	) ENGINE=InnoDB;";

	$reportsQuery = "CREATE TABLE IF NOT EXISTS `" . ATOM_DBREPORTS . "` (
		`id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`ip` varchar(39) NOT NULL,
		`board` varchar(16) NOT NULL,
		`postnum` mediumint(7) unsigned NOT NULL,
		`timestamp` int(20) NOT NULL,
		`reason` text NOT NULL,
		INDEX `board_pnum_time_idx` (`board`, `postnum` DESC, `timestamp` DESC),
		INDEX `ip_board_pnum_idx` (`ip`, `board`, `postnum`),
		INDEX `pnum_time_idx` (`postnum`, `timestamp` DESC)
	) ENGINE=InnoDB;";

	$passQuery = "CREATE TABLE IF NOT EXISTS `" . ATOM_DBPASS . "` (
		`number` mediumint(7) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`id` varchar(64) NOT NULL,
		`issued` int(20) NOT NULL,
		`expires` int(20) NOT NULL,
		`blocked_till` int(20) NOT NULL DEFAULT 0,
		`blocked_reason` text,
		`meta` text NOT NULL,
		`meta_admin` text NOT NULL,
		`last_used` int(20) NOT NULL DEFAULT 0,
		`last_used_ip` varchar(64),
		INDEX `num_idx` (`number`),
		INDEX `expires_idx` (`expires`)
	) ENGINE=InnoDB;";

	$likesQuery = "CREATE TABLE IF NOT EXISTS `" . ATOM_DBLIKES . "` (
		`id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`ip` varchar(39) NOT NULL,
		`board` varchar(16) NOT NULL,
		`postnum` mediumint(7) unsigned NOT NULL,
		`islike` tinyint(1) NOT NULL DEFAULT 1,
		INDEX `ip_board_pnum_idx` (`ip`, `board`, `postnum`),
		INDEX `board_pnum_idx` (`board`, `postnum`)
	) ENGINE=InnoDB;";

	$modlogQuery = "CREATE TABLE IF NOT EXISTS `" . ATOM_DBMODLOG . "` (
		`id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`timestamp` int(20) NOT NULL,
		`boardname` varchar(255) NOT NULL,
		`username` varchar(75) NOT NULL,
		`action` text NOT NULL,
		`color` varchar(75) NOT NULL,
		`private` tinyint(1) NOT NULL DEFAULT 1,
		INDEX `board_public_idx` (`boardname`, `private`, `timestamp` DESC),
		INDEX `board_time_idx` (`boardname`, `timestamp` DESC)
	) ENGINE=InnoDB;";
}

/* ==[ Strings ]=========================================================================================== */

function escapeHTML(string $string): string {
	return str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $string);
}

function plural(string $singular, int $count, string $plural = 's'): string {
	if ($plural === 's') {
		$plural = $singular . $plural;
	}
	return $count === 1 ? $singular : $plural;
}

function strallpos(string $haystack, string $needle, int $offset = 0): array {
	$result = [];
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

function hslToHex(float $h, float $s, float $l): string {
	$h /= 360;
	$q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
	$p = 2 * $l - $q;
	$f = function(float $t) use ($p, $q): float {
		if ($t < 0) $t += 1;
		if ($t > 1) $t -= 1;
		if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
		if ($t < 1/2) return $q;
		if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
		return $p;
	};
	return sprintf('#%02x%02x%02x', 
		(int)round($f($h + 1/3) * 255), 
		(int)round($f($h) * 255), 
		(int)round($f($h - 1/3) * 255));
}

/* ==[ Posts ]============================================================================================= */

function newPost(int $parent): array {
	return [
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
		'endless' => '0'];
}

function isOp(array $post): bool {
	return (int)$post['parent'] === 0;
}

function deleteAllPosts(string $ip, ?int $parentId): string {
	$deletedPosts = '';
	$updThreads = [];
	$posts = getPostsByIP($ip);
	$count = 0;
	foreach ($posts as $post) {
		$id = (int)$post['id'];
		$thrId = (int)$post['parent'];
		if (!isset($parentId) || $thrId === $parentId) {
			deletePost($id);
			$deletedPosts .= ($count ? ', ' : '') . $id;
			if (!isOp($post) && !in_array($thrId, $updThreads)) {
				$updThreads[] = $thrId;
			}
			$count++;
		}
	}
	foreach ($updThreads as $updThreadId) {
		rebuildThreadPage($updThreadId);
	}
	modLog('Deleted all posts from IP ' . $ip . ': №' . $deletedPosts . '.');
	rebuildIndexPages();
	return $deletedPosts;
}

/* ==[ Images/video files ]================================================================================ */

function deletePostImageFiles(array $post, array $imgList = []): void {
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT)) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = (int)trim(basename($index));
			if (!isEmbed($post['file' . $index . '_hex']) && $post['file' . $index] !== '') {
				@unlink('src/' . $post['file' . $index]);
			}
			$thumbName = $post['thumb' . $index];
			if ($thumbName !== '' && $thumbName !== 'spoiler.png') {
				@unlink('thumb/' . $thumbName);
			}
		}
		return;
	}
	for ($index = 0; $index < ATOM_FILES_COUNT; $index++) {
		if (!isEmbed($post['file' . $index . '_hex']) && $post['file' . $index] !== '') {
			@unlink('src/' . $post['file' . $index]);
		}
		$thumbName = $post['thumb' . $index];
		if ($thumbName !== '' && $thumbName !== 'spoiler.png') {
			@unlink('thumb/' . $thumbName);
		}
	}
}

function deletePostThumbFiles(array $post, array $imgList): void {
	if ($imgList && (count($imgList) <= ATOM_FILES_COUNT)) {
		foreach ($imgList as $arrayIndex => $index) {
			$index = (int)trim(basename($index));
			$thumbName = $post['thumb' . $index];
			if ($thumbName !== '' && $thumbName !== 'spoiler.png') {
				@unlink('thumb/' . $thumbName);
			}
		}
	}
}

function getThumbnailDimensions(array $post, int $imgIdx = 0): array {
	if (isOp($post)) {
		$maxW = ATOM_FILE_MAXWOP;
		$maxH = ATOM_FILE_MAXHOP;
	} else {
		$maxW = ATOM_FILE_MAXW;
		$maxH = ATOM_FILE_MAXH;
	}
	return (
		$post['image' . $imgIdx . '_width'] > $maxW ||
		$post['image' . $imgIdx . '_height'] > $maxH
	) ? [$maxW, $maxH] :
		[$post['image' . $imgIdx . '_width'], $post['image' . $imgIdx . '_height']];
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

function createThumbnail(string $fileLocation, string $thumbLocation, int $newW, int $newH): bool {
	if (ATOM_FILE_THUMBDRIVER === 'gd') {
		$system = array_reverse(explode('.', $thumbLocation));
		if (preg_match('/jpg|jpeg/', $system[0])) {
			$srcImg = imagecreatefromjpeg($fileLocation);
		} elseif (preg_match('/png/', $system[0])) {
			$srcImg = @imagecreatefrompng($fileLocation);
		} elseif (preg_match('/gif/', $system[0])) {
			$srcImg = imagecreatefromgif($fileLocation);
		} elseif (preg_match('/avif/', $system[0])) {
			$srcImg = imagecreatefromavif($fileLocation);
		} elseif (preg_match('/webp/', $system[0])) {
			$srcImg = imagecreatefromwebp($fileLocation);
		} else {
			return false;
		}
		if (!$srcImg) {
			return false;
		}
		$oldX = imageSX($srcImg);
		$oldY = imageSY($srcImg);
		$percent = $oldX > $oldY ? $newW / $oldX : $newH / $oldY;
		$thumbW = (int)round($oldX * $percent);
		$thumbH = (int)round($oldY * $percent);
		$dstImg = imagecreatetruecolor($thumbW, $thumbH);
		if (preg_match('/png/', $system[0]) && imagepng($srcImg, $thumbLocation)) {
			imagealphablending($dstImg, false);
			imagesavealpha($dstImg, true);
			$color = imagecolorallocatealpha($dstImg, 0, 0, 0, 0);
			imagefilledrectangle($dstImg, 0, 0, $thumbW, $thumbH, $color);
			imagecolortransparent($dstImg, $color);
			imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $thumbW, $thumbH, $oldX, $oldY);
		} else {
			fastimagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $thumbW, $thumbH, $oldX, $oldY);
		}
		if (preg_match('/png/', $system[0])) {
			if (!imagepng($dstImg, $thumbLocation)) {
				return false;
			}
		} elseif (preg_match('/jpg|jpeg/', $system[0])) {
			if (!imagejpeg($dstImg, $thumbLocation, 70)) {
				return false;
			}
		} elseif (preg_match('/gif/', $system[0])) {
			if (!imagegif ($dstImg, $thumbLocation)) {
				return false;
			}
		} elseif (preg_match('/avif/', $system[0])) {
			if (!imageavif($dstImg, $thumbLocation, 70)) {
				return false;
			}
		} elseif (preg_match('/webp/', $system[0])) {
			if (!imagewebp($dstImg, $thumbLocation, 70)) {
				return false;
			}
		}
		imagedestroy($dstImg);
		imagedestroy($srcImg);
	} else {
		// Imagemagick
		$discard = '';
		$exitStatus = 1;
		$extension = pathinfo($thumbLocation, PATHINFO_EXTENSION);
		if ($extension === 'gif' || $extension === 'webp') {
			if (ATOM_FILE_ANIM_GIF) {
				exec("convert " . $fileLocation . " -auto-orient -thumbnail '" . $newW . "x" . $newH .
					"' -coalesce -layers OptimizeFrame -depth 4 -type palettealpha " . $thumbLocation,
					$discard, $exitStatus);
			} else {
				exec("convert '" . $fileLocation . "[0]' -auto-orient -thumbnail '" . $newW . "x" . $newH .
					"' -layers OptimizeFrame -depth 4 -type palettealpha " . $thumbLocation,
					$discard, $exitStatus);
			}
		} else {
			exec("convert " . $fileLocation . " -auto-orient -thumbnail '" . $newW . "x" . $newH .
				"' -layers OptimizeFrame -depth 8 " . $thumbLocation, $discard, $exitStatus);
		}
		if ($exitStatus !== 0) {
			return false;
		}
	}
	return true;
}

function addVideoOverlay(string $thumbLocation): void {
	if (!file_exists('icons/video_overlay.png')) {
		return;
	}
	if (ATOM_FILE_THUMBDRIVER === 'imagemagick') {
		$discard = '';
		$exitStatus = 1;
		exec('convert ' . $thumbLocation .
			' icons/video_overlay.png -gravity center -composite -quality 75 ' .
			$thumbLocation, $discard, $exitStatus);
		return;
	}
	// GD
	$thumbnail = substr($thumbLocation, -4) === '.jpg' ? imagecreatefromjpeg($thumbLocation) :
		imagecreatefrompng($thumbLocation);
	list($width, $height, $type, $attr) = getimagesize($thumbLocation);
	$overlay_play = imagecreatefrompng('icons/video_overlay.png');
	imagealphablending($overlay_play, false);
	imagesavealpha($overlay_play, true);
	list($overlayWidth, $overlayHeight, $overlayType, $overlayAttr) = getimagesize('icons/video_overlay.png');
	if (substr($thumbLocation, -4) === '.png') {
		imagecolortransparent($thumbnail, imagecolorallocatealpha($thumbnail, 0, 0, 0, 127));
		imagealphablending($thumbnail, true);
		imagesavealpha($thumbnail, true);
	}
	imagecopy($thumbnail, $overlay_play, (int)($width / 2) - (int)($overlayWidth / 2),
		(int)($height / 2) - (int)($overlayHeight / 2), 0, 0, $overlayWidth, $overlayHeight);
	if (substr($thumbLocation, -4) === '.jpg') {
		imagejpeg($thumbnail, $thumbLocation);
	} else {
		imagepng($thumbnail, $thumbLocation);
	}
}

function isEmbed(string $fileHex): bool {
	global $atom_embeds;
	return in_array($fileHex, array_keys($atom_embeds));
}

function getEmbed(string $url): array {
	global $atom_embeds;
	if (sizeof($atom_embeds) !== 0) {
		foreach ($atom_embeds as $service => $service_url) {
			if (strpos(strtolower($url), strtolower($service)) !== false) {
				$service_url = str_ireplace('ATOM_EMBED', urlencode($url), $service_url);
				$result = json_decode(url_get_contents($service_url), true);
				if (!empty($result)) {
					return [$service, $result];
				}
			}
		}
	}
	return ['', []];
}

/* ==[ Threads ]=========================================================================================== */

function updateThreadPosts(int $thrId, array $post): void {
	if (isOp($post)) {
		return;
	}
	if (ATOM_THREAD_LIMIT === 0 || getThreadPostsCount($thrId) <= ATOM_THREAD_LIMIT) {
		if (strtolower($post['email']) !== 'sage') {
			bumpThread($thrId);
		}
	} elseif (ATOM_THREAD_LIMIT !== 0) {
		// Delete old posts in endless threads
		$postOP = getPost($thrId);
		if ($postOP && (int)$postOP['endless'] === 1) {
			$posts = getThreadPosts($thrId, false);
			$overLimit = count($posts) - ATOM_THREAD_LIMIT + 1;
			if ($overLimit > 0) {
				for ($i = 1; $i < $overLimit; $i++) {
					deletePost($posts[$i]['id']);
				}
			}
		}
	}
}

function getThreadId(array $post): int {
	return isOp($post) ? (int)$post['id'] : (int)$post['parent'];
}

/* ==[ Administration and moderation ]===================================================================== */

function checkLogin(): string {
	if (isset($_POST['manage_password'])) {
		$passw = $_POST['manage_password'];
		if (empty(getAllStaffMembers())) {
			if ($passw === ATOM_ADMINPASS && ATOM_ADMINPASS !== '') {
				$_SESSION['atom_user'] = 'TemporaryAdmin';
				$_SESSION['atom_role'] = 'admin';
			}
		} else {
			$staff = getStaffMember($_POST['manage_user'] ?? '');
			if ($staff && password_verify($passw, $staff['password_hash'])) {
				$userName = $staff['username'];
				$_SESSION['atom_user'] = $userName;
				$_SESSION['atom_role'] = $staff['role'];
				updateStaffLogin($userName);
				 // Generate CSRF token if not already present
				if (empty($_SESSION['atom_token'])) {
					$_SESSION['atom_token'] = bin2hex(random_bytes(32));
				}
				modLog(ucfirst($staff['role']) . ' login', '1', 'BlueViolet');
			}
		}
	}
	$loginStatus = $_SESSION['atom_role'] ?? 'disabled';
	if ($loginStatus === 'disabled') {
		setcookie('atom_access', '', time() - 3600, '/' . ATOM_BOARD . '/');
		unset($_COOKIE['atom_access']);
	} else {
		setcookie('atom_access', '1', time() + 2592000, '/' . ATOM_BOARD . '/'); // 30 days
	}
	return $loginStatus;
}

function isStaffPost(): bool {
	return isset($_POST['staffpost']) && checkLogin() !== 'disabled';
}

function deleteSession(): void {
	session_unset();
	session_destroy();
	setcookie('atom_access', '', time() - 3600, '/' . ATOM_BOARD . '/');
	unset($_COOKIE['atom_access']);
}

/* ==[ File reading/writing ]============================================================================== */

function url_get_contents(string $url): string|false {
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

function writePage(string $filename, string $contents): void {
	$tempfile = tempnam('res/', ATOM_BOARD . 'tmp'); // Create a temporary file
	$fp = fopen($tempfile, 'w');
	fwrite($fp, $contents);
	fclose($fp);
	// If not able to use the rename function, try the alternate method
	if (!@rename($tempfile, $filename)) {
		copy($tempfile, $filename);
		unlink($tempfile);
	}
	chmod($filename, 0664);
}

/* ==[ Passcodes ]========================================================================================= */

function isPassExpired(array $pass): bool {
	return time() > $pass['expires'];
}

function isPassBlocked(array $pass): string|false {
	if ($pass['blocked_till'] > time()) {
		return $pass['blocked_reason'];
	} else {
		return false;
	}
}

function clearPass(): void {
	$_SESSION['passcode'] = '';
	setcookie('passcode', '', -1, '/');
}

/* ==[ IP ]================================================================================================ */

function cidr2ip(string $cidr): array {
	$parts = explode('/', $cidr);
	$ip = $parts[0];
	$start = ip2long($ip);
	if ($start === false) {
		return [0, 0];
	}
	if (!isset($parts[1])) {
		return [$start, $start];
	}
	$nm = (int)$parts[1];
	$nm = max(0, min(32, $nm));
	// Calculating the range
	$mask = ~((1 << (32 - $nm)) - 1);
	$start &= $mask;
	$end = $start + (pow(2, 32 - $nm) - 1);
	return [(int)$start, (int)$end];
}

function ip2cidr(int $ipFrom, int $ipTo): string {
	if ($ipTo === $ipFrom) {
		return long2ip($ipFrom);
	}
	$range = $ipTo - $ipFrom + 1;
	if ((($range - 1) & $range) !== 0) {
		// Not a power of two
		return long2ip($ipFrom) . '/???';
	}
	$b = 32 - log($range, 2);
	return long2ip($ipFrom) . '/' . $b;
}

function getCountryCode(string $ip, ?\GeoIp2\Database\Reader $geoipReader): string {
	$countryCode = '';
	if ($ip !== '') {
		if (ATOM_GEOIP === 'geoip2') {
			if (!$geoipReader) {
				try {
					$geoipReader = new \GeoIp2\Database\Reader('/usr/share/GeoIP/GeoLite2-Country.mmdb');
				} catch (\Exception $e) {
					return 'ANON';
				}
			}
			try {
				$record = $geoipReader->country($ip);
				$countryCode = (string)$record->country->isoCode;
			} catch (\GeoIp2\Exception\AddressNotFoundException $e) {
				$countryCode = 'ANON';
			}
		} else if (ATOM_GEOIP === 'geoip' && function_exists('geoip_country_code_by_name')) {
			$countryCode = (string)geoip_country_code_by_name($ip);
		}
	}
	return $countryCode ?: 'ANON';
}

// Check for dirty IP using external service - ipregistry.co
function isDirtyIP(string $ip): bool {
	$ipLookup = lookupByIP($ip);
	if ($ipLookup) {
		$ipLookupAbuser = $ipLookup['abuser'];
		$ipLookupVps = $ipLookup['vps'];
		$ipLookupProxy = $ipLookup['proxy'];
		$ipLookupTor = $ipLookup['tor'];
		$ipLookupVpn = $ipLookup['vpn'];
	} else {
		try {
			$json = json_decode(url_get_contents(
				'https://api.ipregistry.co/' . $ip . '?key=' . ATOM_IPLOOKUPS_KEY));
			$ipLookupSecurity = $json->security;
			$ipLookupAbuser = (int)($ipLookupSecurity->is_abuser ||
				$ipLookupSecurity->is_threat || $ipLookupSecurity->is_attacker);
			$ipLookupVps = (int)($ipLookupSecurity->is_cloud_provider);
			$ipLookupProxy = (int)($ipLookupSecurity->is_proxy);
			$ipLookupTor = (int)($ipLookupSecurity->is_tor || $ipLookupSecurity->is_tor_exit);
			$ipLookupVpn = (int)($ipLookupSecurity->is_vpn);
			storeLookupResult($ip, $ipLookupAbuser, $ipLookupVps, $ipLookupProxy, $ipLookupTor, $ipLookupVpn);
		} catch (Exception $e) {
			$ipLookupAbuser = false;
			$ipLookupVps = false;
			$ipLookupProxy = false;
			$ipLookupTor = false;
			$ipLookupVpn = false;
		}
	}
	return ATOM_IPLOOKUPS_BLOCK_ABUSER && $ipLookupAbuser ||
		ATOM_IPLOOKUPS_BLOCK_VPS && $ipLookupVps ||
		ATOM_IPLOOKUPS_BLOCK_PROXY && $ipLookupProxy ||
		ATOM_IPLOOKUPS_BLOCK_TOR && $ipLookupTor ||
		ATOM_IPLOOKUPS_BLOCK_VPN && $ipLookupVpn;
}

function checkIP(string $ip, bool $isPasscode, bool $isJson): void {
	// Check for dirty IP
	if (defined('ATOM_IPLOOKUPS_KEY') && ATOM_IPLOOKUPS_KEY && !$isPasscode && isDirtyIP($ip)) {
		$message = 'Error: Your IP ' . $ip . ' is not allowed due to abuse (proxy, Tor, VPN, VPS).';
		if ($isJson) {
			jsonDie('error', $message);
		} else {
			fancyDie($message);
		}
	}

	// Check for ban
	$ban = banByIP($ip);
	if ($ban) {
		checkForBans($ip, $ban, $isPasscode, $isJson);
	}
}

/* ==[ Captcha ]=========================================================================================== */

function checkCaptcha(): void {
	$captchaError = '';
	$isJson = isset($_GET['json']) && $_GET['json'] === '1';

	// Check for recaptcha
	if (ATOM_CAPTCHA === 'recaptcha') {
		require_once 'inc/recaptcha/autoload.php';
		$captcha = $_POST['g-recaptcha-response'] ?? '';
		$recaptcha = new \ReCaptcha\ReCaptcha(ATOM_RECAPTCHA_SECRET);
		$response = $recaptcha->verify($captcha, $_SERVER['REMOTE_ADDR']);
		if (!$response->isSuccess()) {
			$captchaError = 'Captcha error: ';
			$errCodes = $response->getErrorCodes();
			$errReason = $errCodes[0] ?? '';
			if ($errReason === 'missing-input-response') {
				$captchaError .= ' Please click the checkbox labeled "I\'m not a robot".';
			} else {
				$captchaError .= implode(';<br>', $errCodes);
			}
		}
	}

	// Check for simple captcha
	elseif (ATOM_CAPTCHA) {
		$captcha = strtolower(trim($_POST['captcha'] ?? ''));
		$captchaError = $captcha === '' ? 'Captcha error: The captcha text was not entered.' :
			($captcha !== strtolower(trim($_SESSION['atom_captcha'] ?? '')) ?
				'Captcha error: Incorrect captcha text entered, please try again.<br>' .
				'Click the image to retrieve a new captcha.' : '');
		unset($_SESSION['atom_captcha']);
	}

	// Output error if captcha check failed
	if ($captchaError) {
		if ($isJson) {
			jsonDie('error', $captchaError);
		} else {
			fancyDie($captchaError);
		}
	}
}
