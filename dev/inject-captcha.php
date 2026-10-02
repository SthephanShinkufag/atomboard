<?php
declare(strict_types=1);

// This runs only while building the isolated HTTP test image. The repository's
// CAPTCHA implementation and the production image retain their random answer.
$path = '/var/www/html/test/inc/captcha.php';
$source = file_get_contents($path);
$original = '$captcha = new SimpleCaptcha();' . "\n" . '$captcha->createImage();';
$testVersion = <<<'PHP'
$captcha = new class extends SimpleCaptcha {
    protected function getRandomText(?int $length = null): string {
        return '48291';
    }
};
$captcha->createImage();
PHP;
if (substr_count($source, $original) !== 1) {
    throw new RuntimeException('CAPTCHA test injection point has changed');
}
file_put_contents($path, str_replace($original, $testVersion, $source));
