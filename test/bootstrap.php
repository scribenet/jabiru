<?php
/*
 * This file is part of Jabiru
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if (false === ($loader = includeIfExists(__DIR__.'/../vendor/autoload.php'))) {
    die(
        'You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL.
        'php composer.phar install' . PHP_EOL
    );
}

$loader->add('Scribe\Jabiru\Tests', __DIR__);

return $loader;