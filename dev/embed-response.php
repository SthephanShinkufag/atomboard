<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'html' => '<iframe src="https://fixture.local/watch/1" width="640" height="480"></iframe>',
    'title' => 'Local embed fixture',
    'thumbnail_url' => 'http://127.0.0.1/test/__test_thumbnail.png',
]);
