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
//        print_r(func_get_args());
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

    /**
     * 设置注册上级用户团队数据
     * @param $data 用户注册数据{user_id,account,nickname,user_no,member_team{parent_id,parents_path}}
     * @param $event_name
     */
    function setUserTeamData($data,$event_name)
    {
        if(isset($data['member_team']) && !empty($data['member_team'])){
            $memberTeamService = Container::get(MemberTeamService::class);
            $memberTeam = $data['member_team'];
            if(!empty($memberTeam['parent_id'])){
                $parentArr = explode(',',$memberTeam['parents_path']);
                array_pop($parentArr);
                foreach($parentArr as $uid){
                    $parentTeamObj = $memberTeamService->get($uid);
                    $update = [
                        'team_cnt'=>($parentTeamObj['team_cnt']+1),
                    ];
                    if($uid==$memberTeam['parent_id']){
                        $update['invite_cnt'] = ($parentTeamObj['invite_cnt']+1);
                        $update['invite_path'] = (empty($parentTeamObj['invite_path'])?$data['user_id']:($parentTeamObj['invite_path'].','.$data['user_id']));
                    }
                    $parentTeamObj->update($update);
                }
            }
        }
    }
}