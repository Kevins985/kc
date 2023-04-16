<?php

return [
    'user.login' => [
        [library\event\User::class, 'login'],
        // ...其它事件处理函数...
    ],
    'user.register' => [
        [library\event\User::class, 'register'],
    ],
    'user.logout' => [
        [library\event\User::class, 'logout'],
    ],
//    'user.*' => [
//        [library\event\User::class, 'deal']
//    ],
];