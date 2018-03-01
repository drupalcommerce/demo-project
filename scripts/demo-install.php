#!/usr/bin/env php
<?php

/**
 * @file
 * Installs and starts Drupal for a dev site.
 */

use DemoCommerce\DemoInstallCommand;
use DemoCommerce\DemoStartCommand;
use Symfony\Component\Console\Application;

if (PHP_SAPI !== 'cli') {
  return;
}

$classloader = require_once __DIR__ . '/../vendor/autoload.php';
$application = new Application('demo-install', '8.0.x');
$application->add(new DemoInstallCommand($classloader));
$application->add(new DemoStartCommand($classloader));
$application->run();
