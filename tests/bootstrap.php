<?php

$configCache = dirname(__DIR__).'/bootstrap/cache/config.php';

if (is_file($configCache)) {
    fwrite(
        STDERR,
        "Refusing to run tests while Laravel configuration is cached. Run `composer test` instead.\n"
    );

    exit(1);
}

require dirname(__DIR__).'/vendor/autoload.php';
