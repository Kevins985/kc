<?php
namespace library\event;

use library\logic\DictLogic;
use library\logic\OrderLogic;
use library\logic\WalletLogic;
use library\model\user\ProjectOrderModel;
use library\service\sys\AdminLoginLogsService;
use library\service\user\MemberTeamService;
use library\service\user\ProjectOrderService;
use support\Container;
use support\exception\BusinessException;

class User
{
    /**
     * 用户登陆的数据处理事件
     * @param $data
     * @param $event_name
     */
    function login($data,$event_name)
    {
        echo 'login';
    }

    /**
     * 用户退出登陆的数据处理事件
     * @param $data
     * @param $event_name
     */
    function register($data,$event_name)
    {
        echo 'register';
    }

    /**
     * 用户退出登陆的数据处理事件
     * @param $data
     * @param $event_name
     */
    function logout($data,$event_name)
    {
        echo 'logout';
    }
}