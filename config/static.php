<?php

/**
 * Static file settings
 */
return [
    'enable'     => false,
    'version' => 202305112,
    'middleware' => [     // Static file Middleware
        support\middleware\StaticFile::class,
    ],
];