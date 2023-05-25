<?php
if (!defined('ATOM_BOARD')) {
	die('');
}

define('ATOM_NEWTHREAD', '0');
define('ATOM_INDEXPAGE', false);
define('ATOM_RESPAGE', true);
define('ATOM_WORDBREAK_IDENTIFIER', '@!@ATOM_WORDBREAK@!@');
if (!defined('ATOM_FILE_MAXWOP')) {
	define('ATOM_FILE_MAXWOP', ATOM_FILE_MAXW);
}
if (!defined('ATOM_FILE_MAXHOP')) {
	define('ATOM_FILE_MAXHOP', ATOM_FILE_MAXH);
}
