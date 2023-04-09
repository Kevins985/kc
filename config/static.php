<?php

/**
 * Static file settings
 */
return [
    'enable'     => false,
    'version' => 20230312,
    'middleware' => [     // Static file Middleware
        support\middleware\StaticFile::class,
    ],
];