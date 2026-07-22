<?php

if (!defined('QUIQQER_SYSTEM')) {
    define('QUIQQER_SYSTEM', true);
}

if (!defined('QUIQQER_AJAX')) {
    define('QUIQQER_AJAX', true);
}

require_once __DIR__ . '/../../../../bootstrap.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'QUI\\ERP\\Payments\\Example\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = __DIR__ . '/../src/QUI/ERP/Payments/Example/'
        . str_replace('\\', '/', $relativeClass)
        . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});
