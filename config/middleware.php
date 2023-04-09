<?php

return [
    '' => [
        support\middleware\ActionHook::class,
//        support\middleware\Cors::class,
        support\middleware\LimitVisit::class,
    ]
];