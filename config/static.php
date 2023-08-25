<?php

/**
 * Static file settings
 */
return [
    'enable'     => false,
    'version' => 2023071122,
    'middleware' => [     // Static file Middleware
        support\middleware\StaticFile::class,
    ],
];