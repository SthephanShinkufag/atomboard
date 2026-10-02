<?php
declare(strict_types=1);

// Only the isolated HTTP image gets a local embed service; production settings
// continue to use the configured public providers.
$settingsPath = '/var/www/html/test/settings.default.php';
$settings = file_get_contents($settingsPath);
$needle = "'YouTube.com'    => 'https://www.youtube.com/oembed?url=ATOM_EMBED&format=json'";
$replacement = $needle . ",\n\t'fixture.local' => 'http://127.0.0.1/test/__test_embed.php?url=ATOM_EMBED'";
if (substr_count($settings, $needle) !== 1) {
    throw new RuntimeException('Embed test injection point has changed');
}
file_put_contents($settingsPath, str_replace($needle, $replacement, $settings));
copy('/var/www/html/test/dev/embed-response.php', '/var/www/html/test/__test_embed.php');

$image = imagecreatetruecolor(32, 24);
$blue = imagecolorallocate($image, 30, 80, 180);
imagefill($image, 0, 0, $blue);
imagepng($image, '/var/www/html/test/__test_thumbnail.png');
imagedestroy($image);
