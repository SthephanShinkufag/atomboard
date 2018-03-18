<?php
# TinyIB, forked version by SthephanShi: https://github.com/SthephanShinkufag/TinyIB
# Original repo: https://gitlab.com/tslocum/TinyIB
# See README for instructions on configuring, moderating and upgrading your board.
# Set TINYIB_DBMODE to a PDO-related mode if it's available.
# By default it's set to 'flatfile', which can be very slow.

/* ==[ Board description and customization ]=============================================================== */
// Unique identifier for this board using only letters and numbers
define('TINYIB_BOARD', 'b');
// Displayed at the top of every page
define('TINYIB_BOARDDESC', 'TinyIB board');
// Logo HTML
define('TINYIB_LOGO', '');
// Index-file name
define('TINYIB_INDEX', 'index.html');
// Timezone (see https://secure.php.net/manual/en/timezones.php - e.g. 'America/Los_Angeles')
define('TINYIB_TIMEZONE', 'UTC');
// <Head> HTML - specify your code to be added to the end of the <HEAD>
define('TINYIB_HTML_HEAD', '');
// Sidebar HTML - specify your navigation buttons to be added at the left side of the page
define('TINYIB_HTML_LEFTSIDE', '
				<a class="aside-btn" id="aside-btn-home" href="/" title="Home">
					<svg><use xlink:href="#symbol-home"/></svg>
				</a>
				<a class="aside-btn aside-btn-board" href="/' . TINYIB_BOARD . '/" title="' .
					TINYIB_BOARDDESC . '">' . TINYIB_BOARD .'</a>
				<a class="aside-btn aside-btn-board" href="/test/" title="Test board">test</a>');

/* ==[ Administrator/moderator credentials ]=============================================================== */
// Administrators have full access to the board
define('TINYIB_ADMINPASS', '');
// Moderators only have access to delete (and moderate if TINYIB_REQMOD is set) posts ['' to disable]
define('TINYIB_MODPASS', '');
// Require moderation before displaying posts:
// files / all (see README for instructions, only MySQL is supported)  ['' to disable]
define('TINYIB_REQMOD', '');

/* ==[ Database ]========================================================================================== */
// Recommended database modes from best to worst: 'pdo', 'mysqli', 'mysql', 'sqlite', 'flatfile'
// 'flatfile' is only useful if you need portability or lack any kind of database
// Mode
define('TINYIB_DBMODE', 'flatfile');
// Enable database migration tool (see README for instructions)
define('TINYIB_DBMIGRATE', false);
// Bans table name in database (use the same bans table across boards for global bans)
define('TINYIB_DBBANS', 'bans');
// Posts table name in database
define('TINYIB_DBPOSTS', TINYIB_BOARD . '_posts');

/* ==[ Database configuration - MySQL / pgSQL ]============================================================ */
// The following only apply when TINYIB_DBMODE is set to mysql,
// mysqli or pdo with default (blank) TINYIB_DBDSN
// Hostname
define('TINYIB_DBHOST', 'localhost');
// Port (set to 0 if you are using a UNIX socket as the host)
define('TINYIB_DBPORT', 3306);
// Username
define('TINYIB_DBUSERNAME', '');
// Password
define('TINYIB_DBPASSWORD', '');
// Database
define('TINYIB_DBNAME', '');
// Database configuration - PDO
// The following only apply when TINYIB_DBMODE is set to 'pdo' (see README for instructions)
// PDO driver to use: 'mysql', 'pgsql', 'sqlite'
define('TINYIB_DBDRIVER', 'mysql');
// Enter a custom DSN to override all of the connection/driver settings above  (see README for instructions)
// When changing this, you should still set TINYIB_DBDRIVER appropriately.
// If you're using PDO with a MySQL or pgSQL database, you should leave this blank.
define('TINYIB_DBDSN', '');

/* ==[ Posts and threads ]================================================================================= */
// Default poster names
define('TINYIB_POSTERNAME', 'Anonymous');
// Tripcode seed - Must not change once set!
// Enter some random text (used when generating secure tripcodes)
define('TINYIB_TRIPSEED', '');
// Amount of threads shown per index page
define('TINYIB_THREADSPERPAGE', 10);
// Amount of replies previewed on index pages
define('TINYIB_PREVIEWREPLIES', 5);
// Amount of text lines to truncate posts on index pages [0 to disable]
define('TINYIB_TRUNC_LINES', 10);
// Text size in bytes to truncate posts on index pages [0 to disable]
define('TINYIB_TRUNC_SIZE', 1536);
// Words longer than this many characters will be broken apart [0 to disable]
define('TINYIB_WORDBREAK', 100);
// Post likes system
define('TINYIB_LIKES', false);

/* ==[ Post control ]====================================================================================== */
// Delay (in seconds) between posts from the same IP address to help control flooding [0 to disable]
define('TINYIB_DELAY', 30);
// Oldest threads are discarded when the thread count passes this limit [0 to disable]
define('TINYIB_MAXTHREADS', 100);
// Maximum replies before a thread stops bumping [0 to disable]
define('TINYIB_MAXREPLIES', 0);

/* ==[ Reply form and posting ]============================================================================= */
// Redirect to thread after posting
define('TINYIB_ALWAYSNOKO', true);
// Fields to hide when creating a new thread
// e.g. array('name', 'email', 'subject', 'message', 'file', 'embed', 'password')
$tinyib_hidefieldsop = array();
// Fields to hide when replying
$tinyib_hidefields = array();

/* ==[ Upload types ]====================================================================================== */
// Empty array to disable
// Format: MIME type => (extension, optional thumbnail)
// WebM upload requires mediainfo and ffmpegthumbnailer (see README for instructions)
$tinyib_uploads = array(
	'image/jpeg'  => array('jpg'),
	'image/pjpeg' => array('jpg'),
	'image/png'   => array('png'),
	'image/gif'   => array('gif'),
	'video/webm'  => array('webm'),
	'audio/webm'  => array('webm'),
	'video/mp4'   => array('mp4')
//	'application/x-shockwave-flash' => array('swf', 'swf_thumbnail.png')
);

/* ==[ oEmbed APIs ]======================================================================================= */
// Empty array to disable
$tinyib_embeds = array(
	'SoundCloud' => 'http://soundcloud.com/oembed?format=json&url=TINYIBEMBED',
	'Vimeo'      => 'http://vimeo.com/api/oembed.json?url=TINYIBEMBED',
	'YouTube'    => 'http://www.youtube.com/oembed?url=TINYIBEMBED&format=json'
);

/* ==[ File control ]====================================================================================== */
// Maximum file size in kilobytes [0 to disable]
define('TINYIB_MAXKB', 2048);
// Human-readable representation of the maximum file size
define('TINYIB_MAXKBDESC', '2 MB');
// Thumbnail method to use: 'gd', 'imagemagick' (see README for instructions)
define('TINYIB_THUMBNAIL', 'gd');
// Allow the creation of new threads without uploading a file
define('TINYIB_NOFILEOK', false);
// Allow duplicate files
define('TINYIB_FILE_ALLOW_DUPLICATE', false);
// Animate gif thumbnails
// The following only apply when TINYIB_THUMBNAIL is set to 'imagemagick'
define('TINYIB_FILE_ANIM_GIF_THUMB', true);
// Thumbnail size - new thread
define('TINYIB_MAXWOP', 230); // Width
define('TINYIB_MAXHOP', 230); // Height
// Thumbnail size - reply
define('TINYIB_MAXW', 230); // Width
define('TINYIB_MAXH', 230); // Height

/* ==[ Captcha ]=========================================================================================== */
// Reduce spam by requiring users to pass a CAPTCHA when posting: 'simple', 'recaptcha'
// (click Rebuild All in the management panel after enabling) ['' to disable]
define('TINYIB_CAPTCHA', 'simple');
// The following only apply when TINYIB_CAPTCHA is set to recaptcha
// For API keys visit https://www.google.com/recaptcha
define('TINYIB_RECAPTCHA_SITE', '');   // Site key
define('TINYIB_RECAPTCHA_SECRET', ''); // Secret key
