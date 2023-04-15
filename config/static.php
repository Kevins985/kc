<?php

/**
 * Static file settings
 */
return [
    'enable'     => false,
    'version' => 20230512,
    'middleware' => [     // Static file Middleware
        support\middleware\StaticFile::class,
    ],
];