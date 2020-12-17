<?php

declare(strict_types=1);

use ProxyManagerLts\Configuration;
use ProxyManagerLts\GeneratorStrategy\EvaluatingGeneratorStrategy;

require_once __DIR__ . '/../../vendor/autoload.php';

$configuration = new Configuration();

$configuration->setGeneratorStrategy(new EvaluatingGeneratorStrategy());
