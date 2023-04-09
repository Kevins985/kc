<?php
/**
 * This file is part of cli.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */

return [
    'files' => [
        base_path('app/functions.php')
    ],
    'routes'=> [
        config_path('route/api.php'),
        config_path('route/backend.php'),
    ],
];