<?php
# See README for instructions on configuring, moderating and upgrading your board.
# Set ATOM_DBMODE to a PDO-related mode if it's available.
# By default it's set to 'flatfile', which can be very slow.

/* ==[ Board description and customization ]=============================================================== */
// Unique identifier for this board using only letters and numbers
define('ATOM_BOARD', '');
// Description displayed at the top of pages
define('ATOM_BOARD_DESCRIPTION', '');
// Name of the index page
define('ATOM_INDEX', 'index.html');
// Timezone (see https://secure.php.net/manual/en/timezones.php - e.g. 'America/Los_Angeles')
define('ATOM_TIMEZONE', 'UTC');
// Default theme style. Values: 'Dark', 'Light'
define('ATOM_THEME', 'Dark');
// Specify your code at the top of pages
define('ATOM_HTML_INFO_TOP', '');
// Specify your code at the bottom of pages
define('ATOM_HTML_INFO_BOTTOM', '');
// Specify your navigation links to be added at the top and the bottom of the page
define('ATOM_HTML_NAVIGATION', '
			<a class="navigation-link" href="/" title="Main page">Home</a>
			<span class="navigation-separator"></span>
			<a class="navigation-link" href="/' . ATOM_BOARD . '/" title="' . ATOM_BOARD_DESCRIPTION . '">' .
				ATOM_BOARD . '</a>');

/* ==[ Administration staff ]============================================================================== */
// Administrator password. Administrator has full access to the board
define('ATOM_ADMINPASS', '');
// Moderators have access to ban posters and delete posts
$atom_moderators = array(
	// 'Mod1' => 'Password1',
	// 'Mod2' => 'Password2',
	// 'Mod3' => 'Password3'
);
// Janitors only have access to delete posts (and moderate if ATOM_REQMOD is set)
// If the array is not empty, the janitorlog.html will be generated
$atom_janitors = array(
	// 'Janitor1' => 'Password1',
	// 'Janitor2' => 'Password2',
	// 'Janitor3' => 'Password3'
);
// Require moderation before displaying posts
// Values: 'files', 'all' (see README for instructions, only MySQL is supported), '' to disable
define('ATOM_REQMOD', '');

/* ==[ Database ]========================================================================================== */
// Recommended database modes from best to worst: 'pdo', 'mysqli', 'mysql', 'sqlite3', 'sqlite', 'flatfile'
// 'flatfile' is only useful if you need portability or lack any kind of database
define('ATOM_DBMODE', 'flatfile');
// Posts table name in database
define('ATOM_DBPOSTS', ATOM_BOARD . '_posts');
// Modlog table name in database (use the same modlog table across boards for global modlog)
// define('ATOM_DBMODLOG', 'modlog');
define('ATOM_DBMODLOG', ATOM_BOARD . '_modlog');
// Bans table name in database
define('ATOM_DBBANS', 'bans');
// Database for dirty IP lookups
define('ATOM_DBIPLOOKUPS', 'iplookups');
// Reports table name in database
define('ATOM_DBREPORTS', 'reports');
// Passcodes table name in database
define('ATOM_DBPASS', 'pass');
// Likes table name in database
define('ATOM_DBLIKES', 'likes');
// Enable database migration tool (see README for instructions)
define('ATOM_DBMIGRATE', false);

/* ==[ Database configuration - MySQL / pgSQL ]============================================================ */
// The following only apply when ATOM_DBMODE is set to 'mysql', 'mysqli' or 'pdo' with ATOM_DBDSN = ''
// Hostname
define('ATOM_DBHOST', 'localhost');
// Port (set to 0 if you are using a UNIX socket as the host)
define('ATOM_DBPORT', 3306);
// Username
define('ATOM_DBUSERNAME', '');
// Password
define('ATOM_DBPASSWORD', '');
// Database name
define('ATOM_DBNAME', '');

/* ==[ Database configuration - PDO ]====================================================================== */
// The following only apply when ATOM_DBMODE is set to 'pdo' (see README for instructions)
// PDO driver to use: 'mysql', 'pgsql', 'sqlite'
define('ATOM_DBDRIVER', 'mysql');
// Enter a custom DSN to override all of the connection/driver settings above (see README for instructions)
// When changing this, you should still set ATOM_DBDRIVER appropriately
// If you're using PDO with a MySQL or pgSQL database, you should leave this blank
define('ATOM_DBDSN', '');

/* ==[ Passcodes ]========================================================================================= */
// Enable passcode system
define('ATOM_PASSCODES_ENABLED', false);
// Number of seconds a single passcode can be used by single IP
// Second posting from different IP will be denied
define('ATOM_PASSCODES_USE_LIMIT', 900);

/* ==[ Dirty IP Lookups ]================================================================================== */
// IP lookups, using ipregistry.co. Set ATOM_IPLOOKUPS_KEY to '' to disable, othwerise provide a key
define('ATOM_IPLOOKUPS_KEY', '');
// Block abusive IPs
define('ATOM_IPLOOKUPS_BLOCK_ABUSER', true);
// Block IPs under cloud providers
define('ATOM_IPLOOKUPS_BLOCK_VPS', true);
// Block IPs under proxy
define('ATOM_IPLOOKUPS_BLOCK_PROXY', true);
// Block IPs under TOR network
define('ATOM_IPLOOKUPS_BLOCK_TOR', true);
// Block IPs under VPN
define('ATOM_IPLOOKUPS_BLOCK_VPN', true);

/* ==[ Ban options ]======================================================================================= */
// Ban reason templates. Empty array() to disable
$atom_ban_reasons = array(
	// 'Spamming',
	// 'Abusive post',
	// 'Breaking the rules'
);
// List of countries from which it is prohibited to post. ATOM_GEOIP must be set.
// See ISO-3166 alpha2 at http://www.geonames.org/countries/
$atom_banned_countries = array(
	// 'RU',
	// 'BY'
);

/* ==[ Posts ]============================================================================================= */
// Default poster names
define('ATOM_POSTERNAME', 'Anonymous');
// Geolocation - identification of the country by IP
// Values: 'geoip', 'geoip2', '' to disable (see README for instructions)
define('ATOM_GEOIP', '');
// Unique ID's based on IP
define('ATOM_UNIQUEID', false);
// Generate unique poster names instead of ID's. Requires ATOM_UNIQUEID = true.
// Values: 'ua', 'custom', 'ru', '' to disable
// First names array is in inc/usernames/ATOM_UNIQUENAME/firstnames.php
// Last names array is in inc/usernames/ATOM_UNIQUENAME/lastnames.php
define('ATOM_UNIQUENAME', '');
// Tripcode seed - Must not change once set!
// Enter some random text (salt used when generating secure tripcodes and poster id's)
define('ATOM_TRIPSEED', '');
// Likes (reactions to posts)
define('ATOM_LIKES', true);
// Words longer than this many characters will be broken apart, 0 to disable
define('ATOM_WORDBREAK', 0);

/* ==[ Index page and threads ]============================================================================ */
// Amount of threads shown per index page
define('ATOM_THREADSPERPAGE', 10);
// Amount of posts previewed on index pages
define('ATOM_PREVIEWREPLIES', 5);
// Amount of text lines to truncate posts on index pages, 0 to disable
define('ATOM_TRUNC_LINES', 10);
// Text size in bytes to truncate posts on index pages, 0 to disable
define('ATOM_TRUNC_SIZE', 1536);
// Oldest threads are discarded when the thread count passes this limit, 0 to disable
define('ATOM_MAXTHREADS', 100);
// Maximum posts before a thread stops bumping, 0 to disable
// For endless mode: if the number of posts in a thread exceeds this value, old posts will be deleted
define('ATOM_THREAD_LIMIT', 500);

/* ==[ Reply form and posting ]============================================================================ */
// Delay (in seconds) between posts from the same IP address to help control flooding, 0 to disable
define('ATOM_POSTING_DELAY', 30);
// Redirect to thread after posting
define('ATOM_ALWAYSNOKO', true);
// Fields to hide when creating a new thread
// e.g. array('name', 'email', 'subject', 'message', 'file', 'embed', 'password')
$atom_hidefieldsop = array();
// Fields to hide when replying
$atom_hidefields = array();

/* ==[ Upload mime types ]================================================================================= */
// Empty array() to disable
// Format: MIME type => (extension, optional thumbnail extensiion)
// Video thumbnails require mediainfo and ffmpegthumbnailer (see README for instructions)
$atom_uploads = array(
	'image/jpeg' => array('jpg'),
	'image/pjpeg' => array('jpg'),
	'image/png' => array('png'),
	'image/gif' => array('gif'),
	'image/avif' => array('avif'),
	'image/webp' => array('webp'),
	'video/webm' => array('webm'),
	'audio/webm' => array('webm'),
	'video/mp4' => array('mp4'),
	'application/octet-stream' => array('mp4'),
	'video/quicktime' => array('mov')
);

/* ==[ Embed APIs ]======================================================================================== */
// Empty array() to disable
$atom_embeds = array(
	'SoundCloud.com' => 'https://soundcloud.com/oembed?format=json&url=ATOM_EMBED',
	'Vimeo.com'      => 'https://vimeo.com/api/oembed.json?url=ATOM_EMBED',
	'YouTube.com'    => 'https://www.youtube.com/oembed?url=ATOM_EMBED&format=json'
);

/* ==[ File control ]====================================================================================== */
// Maximum file size in kilobytes, 0 to disable
define('ATOM_FILE_MAXKB', 20480);
// Human-readable representation of the maximum file size
define('ATOM_FILE_MAXKBDESC', '20 MB');
// Maximum file size in kilobytes for passcode users (if enabled), 0 to disable
define('ATOM_FILE_MAXKB_PASS', 40960);
// Human-readable representation of the maximum file size for passcode users (if enabled)
define('ATOM_FILE_MAXKBDESC_PASS', '40 MB');
// Maximum number of uploaded files (up to 4)
define('ATOM_FILES_COUNT', 4);
// Thumbnail method to use: 'gd', 'imagemagick' (see README for instructions)
define('ATOM_FILE_THUMBDRIVER', 'gd');
// Add icons/video_overlay.png play icon over video and embedded thumbnails
// Requires mediainfo and ffmpegthumbnailer (see README for instructions)
define('ATOM_VIDEO_OVERLAY', true);
// Allow the creation of new threads without uploading a file
define('ATOM_NOFILEOK', false);
// Allow duplicate files
define('ATOM_FILE_DUPLICATE', false);
// Animate gif thumbnails. Apply when ATOM_FILE_THUMBDRIVER is set to 'imagemagick'
define('ATOM_FILE_ANIM_GIF', false);
// Thumbnail size - new thread
define('ATOM_FILE_MAXWOP', 230); // Width
define('ATOM_FILE_MAXHOP', 230); // Height
// Thumbnail size - reply
define('ATOM_FILE_MAXW', 230); // Width
define('ATOM_FILE_MAXH', 230); // Height

/* ==[ Captcha ]=========================================================================================== */
// Requiring users to pass a CAPTCHA when posting: 'simple', 'recaptcha', '' to disable
// Click [Rebuild All] in the management panel after enabling
define('ATOM_CAPTCHA', 'simple');
// The following only apply when ATOM_CAPTCHA is set to recaptcha
// For API keys visit https://www.google.com/recaptcha
define('ATOM_RECAPTCHA_SITE', ''); // Site key
define('ATOM_RECAPTCHA_SECRET', ''); // Secret key
