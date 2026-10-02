<?php
declare(strict_types=1);

$settings = file_get_contents(__DIR__ . '/../settings.default.php');
if ($settings === false) {
    throw new RuntimeException('Cannot read default settings');
}

$overrides = [
    'BOARD' => 'test',
    'BOARD_DESCRIPTION' => 'Atomboard Test Board',
    'ADMINPASS' => getenv('ADMIN_PASSWORD') ?: 'test-admin-password',
    'DBMODE' => getenv('DB_MODE') ?: 'pdo',
    'DBHOST' => getenv('DB_HOST') ?: 'db',
    'DBUSERNAME' => getenv('DB_USER') ?: 'atomboard',
    'DBPASSWORD' => getenv('DB_PASSWORD') ?: 'atomboard_dev',
    'DBNAME' => getenv('DB_NAME') ?: 'atomboard',
    'TRIPSEED' => getenv('TRIP_SEED') ?: 'atomboard-local-test-seed',
    'NOFILEOK' => true,
    'POSTING_DELAY' => 0,
    'PASSCODES_ENABLED' => true,
];

foreach ($overrides as $key => $value) {
    $pattern = '/^define\(\'ATOM_' . $key . '\', .*?\);.*$/m';
    $replacement = "define('ATOM_" . $key . "', " . var_export($value, true) . ');';
    $count = 0;
    $settings = preg_replace($pattern, $replacement, $settings, 1, $count);
    if ($settings === null || $count !== 1) {
        throw new RuntimeException("Could not configure ATOM_$key");
    }
}

$path = __DIR__ . '/../settings.php';
if (file_put_contents($path, $settings) === false) {
    throw new RuntimeException('Cannot write settings.php');
}
chmod($path, 0600);
