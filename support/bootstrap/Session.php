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
namespace support\bootstrap;

use Webman\Bootstrap;
use Workerman\Protocols\Http;
use Workerman\Protocols\Http\Session as SessionBase;
use Workerman\Worker;

/**
 * Class Session
 * @package support
 */
class Session implements Bootstrap {

    /**
     * 避免重复设置php配置
     * @var array
     */
    private static $session = [];

    /**
     * @param Worker $worker
     * @return void
     */
    public static function start($worker)
    {
        $config = config('session');
        if(isset($config['gc_maxlifetime']) && !empty($config['gc_maxlifetime']) && empty(self::$session['gc_maxlifetime'])){
            self::$session['gc_maxlifetime'] = true;
            session_cache_expire($config['gc_maxlifetime']);
            \ini_set('session.gc_maxlifetime', $config['gc_maxlifetime']);
        }
        if(isset($config['cookie_lifetime']) && !empty($config['cookie_lifetime']) && empty(self::$session['cookie_lifetime'])){
            self::$session['cookie_lifetime'] = true;
            \ini_set('session.cookie_lifetime', $config['cookie_lifetime']);
        }
        Http::sessionName($config['session_name']);
        SessionBase::handlerClass($config['handler'], $config['config'][$config['type']]);
        //session_set_cookie_params(0, $config['path'], $config['domain'], $config['secure'], $config['http_only']);
    }
}