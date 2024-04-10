<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
        ->files()
        ->name('*.php')
        ->in($dir = 'app/');

return new Sami($iterator, array(
    'title' => 'Api Cuenca verde',
    'default_opened_level' => 2,
        ));

