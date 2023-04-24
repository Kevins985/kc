<?php

namespace library\service\user;

use library\service\goods\ProjectService;
use support\Container;
use support\extend\Log;
use support\extend\Service;
use library\model\user\MemberTeamModel;
use support\utils\Data;
use support\utils\Random;

class MemberTeamService extends Service
{
    public function __construct(MemberTeamModel $model)
    {
        $this->model = $model;
    }

    public function getInviteCode(){
        $str = Random::getRandStr(8);
        $inviteObj = $this->get($str,'invite_code');
        if(!empty($inviteObj)){
            return $this->getInviteCode();
        }
        return $str;
    }

    /**
     * 获取用的的级别
     * @param array $user_ids
     */
    public function getUserInviteLevel(array $user_ids){
        $rows = $this->fetchAll(['user_id'=>['in',$user_ids]],[],['user_id','invite_cnt'])->toArray();
        return Data::toKVArray($rows,'user_id','invite_cnt');
    }

    /**
     * 获取用的邀请列表
     * @param array $user_ids
     */
    public function getTeamListByIds(array $user_ids){
        $rows = $this->fetchAll(['user_id'=>['in',$user_ids]])->toArray();
        return Data::toKVArray($rows,'user_id');
    }

    /**
     * 查询树级菜单需要的数据
     */
    public function queryTreeMembers($user_id=0) {
        $data = [];
        if(!empty($user_id)){
            $memberTeamObj = $this->get($user_id);
            if(!empty($memberTeamObj)){
                $selector = $this->selector(['parents_path'=>['left_like',$memberTeamObj['parents_path'].',']],['parent_id'=>'asc'],['user_id as id', 'parent_id as pId', 'name','parents_path']);
                $data = $selector->get()->toArray();
                array_unshift($data,[
                    'id'=>$memberTeamObj['user_id'],
                    'pId'=>$memberTeamObj['parent_id'],
                    'name'=>$memberTeamObj['name'],
                    'parents_path'=>$memberTeamObj['parents_path'],
                ]);
            }
        }
        else{
            $selector = $this->selector([],['parent_id'=>'asc'],['user_id as id', 'parent_id as pId', 'name','parents_path']);
            $data = $selector->get()->toArray();
        }
        return $data;
    }

    /**
     * @param $memberTeam
     * @throws \Throwable
     */
    public function updateTeamInviteData($memberTeam){
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $topParentId = 0;
            if(!empty($memberTeam['parent_id'])){
                $parentArr = explode(',',$memberTeam['parents_path']);
                $topParentId = $parentArr[0];
                array_pop($parentArr);
                foreach($parentArr as $uid){
                    $parentTeamObj = $this->get($uid);
                    $update = [
                        'team_cnt'=>($parentTeamObj['team_cnt']+1),
                    ];
                    if($uid==$memberTeam['parent_id']){
                        $update['invite_cnt'] = ($parentTeamObj['invite_cnt']+1);
                        $update['invite_path'] = (empty($parentTeamObj['invite_path'])?$memberTeam['user_id']:($parentTeamObj['invite_path'].','.$memberTeam['user_id']));
                    }
                    $parentTeamObj->update($update);
                }
            }
            if(!empty($topParentId)){
                $projectService = Container::get(ProjectService::class);
                $projectObj = $projectService->getActiveProject($topParentId,false);
                if(!empty($projectObj)){
                    $memberService = Container::get(MemberService::class);
                    $memberService->update($memberTeam['user_id'],['source'=>$projectObj['project_no']]);
                }
            }
            $this->update($memberTeam['user_id'],['sync_time'=>time()]);
            $conn->commit();
        }
        catch (\Throwable $e){
            $conn->rollBack();
            Log::error('updateTeamInviteData:'.$e->getMessage());
        }
    }

    /**
     * @param $memberTeam
     * @throws \Throwable
     */
    public function updateTeamProjectData($memberTeam){
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $topParentId = 0;
            if(!empty($memberTeam['parent_id'])){
                $parentArr = explode(',',$memberTeam['parents_path']);
                array_pop($parentArr);
                foreach($parentArr as $uid){
                    $parentTeamObj = $this->get($uid);
                    $update = [
                        'team_money'=>($parentTeamObj['team_money']+$memberTeam['order_money']),
                    ];
                    if($uid==$memberTeam['parent_id']){
                        $update['invite_money'] = ($parentTeamObj['invite_money']+$memberTeam['order_money']);
                    }
                    $parentTeamObj->update($update);
                }
            }
            $conn->commit();
        }
        catch (\Throwable $e){
            $conn->rollBack();
            Log::error('updateTeamProjectData:'.$e->getMessage());
        }
    }
}
