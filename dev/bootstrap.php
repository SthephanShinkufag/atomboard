<?php
declare(strict_types=1);

// Keep the real settings template authoritative while pointing tests at their own database.
$settings = file_get_contents(__DIR__ . '/../settings.default.php');
$overrides = [
    'ATOM_BOARD' => 'unit',
    'ATOM_BOARD_DESCRIPTION' => 'Test board',
    'ATOM_DBMODE' => getenv('TEST_DB_MODE') ?: 'pdo',
    'ATOM_DBDRIVER' => getenv('TEST_DB_DRIVER') ?: 'mysql',
    'ATOM_DBHOST' => getenv('TEST_DB_HOST') ?: 'localhost',
    'ATOM_DBPORT' => (int)(getenv('TEST_DB_PORT') ?: 3306),
    'ATOM_DBUSERNAME' => getenv('TEST_DB_USER') ?: 'atomboard_test',
    'ATOM_DBPASSWORD' => getenv('TEST_DB_PASSWORD') ?: 'atomboard_test',
    'ATOM_DBNAME' => getenv('TEST_DB_NAME') ?: 'atomboard_test',
    'ATOM_PASSCODES_ENABLED' => true,
];
foreach ($overrides as $name => $value) {
    $pattern = "/define\('" . $name . "', [^;]*\);/";
    $settings = preg_replace($pattern, "define('" . $name . "', " . var_export($value, true) . ");", $settings, 1, $count);
    if ($count !== 1) {
        throw new RuntimeException('Cannot configure test setting ' . $name);
    }
}
$testSettings = tempnam(sys_get_temp_dir(), 'atom-settings-');
file_put_contents($testSettings, $settings);
global $atom_ban_reasons, $atom_banned_countries, $atom_hidefieldsop, $atom_hidefields;
global $atom_replace_text, $atom_replace_rand, $atom_uploads, $atom_embeds;
require $testSettings;
unlink($testSettings);

$_SERVER['REMOTE_ADDR'] = '192.0.2.10';
$_SERVER['PHP_SELF'] = '/unit/imgboard.php';
require __DIR__ . '/../inc/functions.php';
require __DIR__ . '/../inc/html.php';
global $dbh, $mysqli;
require __DIR__ . '/../inc/database_' . ATOM_DBMODE . '.php';
