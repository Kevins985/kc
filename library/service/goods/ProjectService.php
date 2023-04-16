<?php

namespace library\service\goods;

use library\service\sys\FlowNumbersService;
use library\service\user\MemberService;
use library\service\user\MemberTeamService;
use support\Container;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\goods\ProjectModel;
use support\utils\Data;

class ProjectService extends Service
{
    public function __construct(ProjectModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取项目列表
     */
    public function getSelecttList(){
        return $this->fetchAll();
    }

    /**
     * 获取项目编号
     * @param string $suffix
     * @return mixed
     */
    public function getProjectNo($suffix=''){
        $flowNumberServer = Container::get(FlowNumbersService::class);
        return $flowNumberServer->getFlowOrderNo($this->model->getTable(),$suffix);
    }

    /**
     * 获取指定项目列表
     * @param array $project_ids
     */
    public function getProjectList(array $project_ids,$fields=[]){
        $rows = $this->fetchAll(['project_id'=>['in',$project_ids]],[],$fields);
        return Data::toKeyArray($rows,'project_id');
    }

    /**
     * 获取进行中的活动
     * @return \support\extend\Model
     */
    public function getActiveProject($user_id,$is_match=true){
        $projectObj = $this->fetch(['user_id'=>$user_id,'status'=>1]);
        if(empty($projectObj) && $is_match){
            $date = date('Y-m-d');
            $where = ['status'=>1,'start_time'=>['lt',$date],'end_time'=>['gt',$date]];
            $projectObj = $this->fetch($where);
        }
        return $projectObj;
    }

    /**
     * 获取所有项目数据
     */
    public function getGroupAllProjectCnt($params=[])
    {
        $selector = $this->groupBySelector(['status'],$params)->selectRaw('status,count(*) as ct');
        $rows = $selector->get()->toArray();
        $data = ['total'=>0];
        foreach($rows as $v){
            $data['total']+=$v['ct'];
            $data[$v['status']] = $v['ct'];
        }
        return $data;
    }

    /**
     * 创建项目
     * @param $data
     */
    public function createProject($data){
        $data['project_no'] = $this->getProjectNo();
        $memberTeamService = Container::get(MemberTeamService::class);
        $memberTeamObj = $memberTeamService->get($data['user_id']);
        if(empty($memberTeamObj)){
            throw new BusinessException('该项目负责人ID不存在');
        }
        elseif($memberTeamObj['parent_id']!=0){
            throw new BusinessException('该项目负责人已有上级邀请人');
        }
        $userProjectObj = $this->get($data['user_id'],'user_id');
        if(!empty($userProjectObj)){
            throw new BusinessException('该发起人用户已经创建了一个项目');
        }
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $projectObj = $this->create($data);
            $projectNumberService = Container::get(ProjectNumberService::class);
            if(!empty($projectObj)){
                $projectNumberService->createProjectNumber($projectObj['project_id'],$projectObj['project_prefix'],$projectObj['number']);
            }
            $conn->commit();
            return $projectObj;
        }
        catch (\Throwable $e){
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * 修改项目
     * @param $data
     */
    public function updateProject($id,$data){
        $projectObj = $this->get($id);
        if(empty($projectObj)){
            throw new BusinessException("项目不存在");
        }
        return $projectObj->update($data);
    }

    /**
     * 设置SPU上下架
     * @param $id
     * @param $status
     * @return bool
     */
    public function setStatus($id,$status){
        $projectObj = $this->get($id);
        if(empty($projectObj)){
            throw new BusinessException('Exception request');
        }
        $data = ['status'=>$status];
        return $projectObj->update($data);
    }
}
