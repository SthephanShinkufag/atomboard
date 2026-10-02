<?php
declare(strict_types=1);

if (extension_loaded('pcov') && str_starts_with($_SERVER['SCRIPT_FILENAME'] ?? '', '/var/www/html/test/')) {
    \pcov\clear();
    \pcov\start();
    register_shutdown_function(static function (): void {
        $coverage = \pcov\collect();
        if ($coverage) {
            $path = '/web-coverage/' . getmypid() . '-' . bin2hex(random_bytes(8)) . '.json';
            file_put_contents($path, json_encode($coverage));
        }
    });
}
