<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

use TYPO3Fluid\Fluid\Tools\ConsoleRunner;

if (file_exists(__DIR__ . '/../autoload.php')) {
    require_once __DIR__ . '/../autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once __DIR__ . '/../../../autoload.php';
}

$runner = new ConsoleRunner();
try {
    echo $runner->handleCommand($argv);
} catch (InvalidArgumentException $error) {
    echo PHP_EOL . 'ERROR! ' . $error->getMessage() . PHP_EOL . PHP_EOL;
    echo $runner->dumpSupportedParameters();
}
