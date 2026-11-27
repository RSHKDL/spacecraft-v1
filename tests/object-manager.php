<?php

/**
 * Small setup class needed for PHPStan doctrine.
 * @see https://github.com/phpstan/phpstan-doctrine
 */

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

new Dotenv()->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel(environment: 'dev', debug: true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
