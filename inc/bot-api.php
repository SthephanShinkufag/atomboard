<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');
function botResponse(int $status, array $body): never {
	http_response_code($status);
	exit(json_encode($body, JSON_THROW_ON_ERROR));
}
function fancyDie(string $message): never {
	botResponse(500, ['error' => 'Board service unavailable']);
}
// Set by the private web-server listener, never by a client HTTP header.
if (($_SERVER['BOT_INTERNAL_REQUEST'] ?? '') !== '1') {
	botResponse(404, ['error' => 'Not found']);
}
$secretFile = getenv('BOT_API_TOKEN_FILE') ?: '';
$secret = $secretFile ? trim((string)@file_get_contents($secretFile)) : (getenv('BOT_API_TOKEN') ?: '');
$provided = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!$secret || !hash_equals('Bearer ' . $secret, $provided)) {
	botResponse(401, ['error' => 'Unauthorized']);
}
chdir(dirname(__DIR__));
require 'settings.php';
if (ATOM_GEOIP === 'geoip2') {
	require 'vendor/autoload.php';
}
require 'inc/functions.php';
require 'inc/html.php';
require 'inc/database_' . ATOM_DBMODE . '.php';
require 'inc/bot.php';
date_default_timezone_set(ATOM_TIMEZONE);
$_SERVER['PHP_SELF'] = '/' . ATOM_BOARD . '/imgboard.php';
$_SESSION = [];
try {
	if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['op'] ?? '') === 'post') {
		$post = getPost((int)($_GET['id'] ?? 0));
		botResponse(200, ['post' => $post ? reportPostSnapshot($post) : null]);
	}
	if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['op'] ?? '') === 'reports') {
		$reports = [];
		foreach (getAllReports() as $r) {
			$post = getPost((int)$r['postnum']);
			$reports[] = ['id' => (int)$r['id'], 'poster_ip' => $post['ip'] ?? ''];
		}
		botResponse(200, ['reports' => $reports]);
	}
	if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['op'] ?? '') === 'case') {
		$ip = $_GET['ip'] ?? '';
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			botResponse(400, ['error' => 'Invalid IP']);
		}
		botResponse(200, botCaseSnapshot($ip));
	}
	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		botResponse(405, ['error' => 'POST required for moderation']);
	}
	$command = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
	botResponse(200, botModerate($command));
} catch (InvalidArgumentException | JsonException $e) {
	botResponse(400, ['error' => $e->getMessage()]);
} catch (Throwable $e) {
	error_log('Bot moderation: ' . $e->getMessage());
	botResponse(500, ['error' => 'Moderation failed; retry the same operation']);
}
