<?php

/*
 * The shell environment may inject .env values (e.g. APP_ENV=local) into
 * $_SERVER, which Laravel reads with priority over $_ENV. PHPUnit applies
 * the forced <env> values from phpunit.xml to $_ENV before loading this
 * bootstrap, so syncing them here ensures the testing configuration wins.
 */
foreach ($_ENV as $key => $value) {
    $_SERVER[$key] = $value;
}

require __DIR__.'/../vendor/autoload.php';
