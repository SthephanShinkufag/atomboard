<?php
declare(strict_types=1);

chdir(dirname(__DIR__));
require 'settings.php';
require 'inc/functions.php';
require 'inc/html.php';
require 'inc/database_pdo.php';
date_default_timezone_set(ATOM_TIMEZONE);
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['PHP_SELF'] = '/' . ATOM_BOARD . '/imgboard.php';

$testPasscode = getenv('TEST_PASSCODE') ?: 'atomboard-local-test-passcode';
if (strlen($testPasscode) > 64) {
    throw new RuntimeException('TEST_PASSCODE must be 64 characters or fewer');
}
if (passByID($testPasscode) === null) {
    $now = time();
    pdoQuery(
        'INSERT INTO ' . ATOM_DBPASS .
        ' (id, issued, expires, blocked_till, meta, meta_admin, name) VALUES (?, ?, ?, 0, ?, ?, ?)',
        [$testPasscode, $now, $now + 31536000, 'Local test passcode', 'Docker test fixture', '']
    );
    echo "Created local test passcode\n";
}

$thread = null;
foreach (getThreads() as $candidate) {
    if ($candidate['subject'] === 'Welcome to the test board') {
        $thread = $candidate;
        break;
    }
}

if ($thread === null) {
    $post = newPost(0);
    $post['pass'] = 0;
    $post['name'] = 'Test Host';
    $post['subject'] = 'Welcome to the test board';
    $post['message'] = 'This is a sample thread for checking layout, replies, and moderation.';
    $post['nameblock'] = 'Test Host';
    $threadId = insertPost($post);

    $messages = [
        ['Anonymous', 'First reply: the board and database are online.'],
        ['Layout Tester', 'Second reply with enough text to check how a longer message wraps across the page on desktop and mobile screens.'],
        ['Anonymous', 'Third reply: try posting a message of your own.'],
        ['Test Host', 'Fourth reply: images, links, and formatting can be tested here.'],
        ['Anonymous', 'Fifth reply: the catalog and index should both list this thread.'],
        ['Layout Tester', 'Sixth reply: the replies are stored in MariaDB and survive container restarts.'],
        ['Anonymous', 'Seventh reply: this sample data is inserted only once.'],
        ['Test Host', 'Eighth reply: use the management page to explore moderation tools.'],
    ];
    foreach ($messages as [$name, $message]) {
        $reply = newPost($threadId);
        $reply['pass'] = 0;
        $reply['name'] = $name;
        $reply['nameblock'] = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $reply['message'] = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        insertPost($reply);
    }
    echo "Created test thread $threadId with " . count($messages) . " replies\n";
}

foreach (getThreads() as $existingThread) {
    rebuildThreadPage((int)$existingThread['id']);
}
rebuildIndexPages();
