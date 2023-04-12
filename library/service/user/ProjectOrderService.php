<?php

namespace library\service\user;

use library\model\goods\ProjectNumberModel;
use support\extend\Service;
use library\model\user\ProjectOrderModel;

class ProjectOrderService extends Service
{
    public function __construct(ProjectOrderModel $model)
    {
        $this->model = $model;
    }

    public function getOutProjectOrder($project_id,$project_number){
        return $this->fetch(['project_id'=>$project_id,'project_number'=>$project_number,'status'=>1],['user_number'=>'asc']);
    }

    public function getActiveProjectOrderList($project_id,$project_number){
        return $this->fetchAll(['project_id'=>$project_id,'project_number'=>$project_number,'status'=>1],['user_number'=>'asc']);
    }

    public function createProjectOrder(ProjectNumberModel $projectNumberObj,$order_id,$user_id){
        $projectNumberObj->increase('user_cnt')->save();
        $projectOrderData = [
            'order_id'=>$order_id,
            'project_id'=>$projectNumberObj['project_id'],
            'project_number'=>$projectNumberObj['project_number'],
            'user_id'=>$user_id,
            'user_number'=>$projectNumberObj['user_cnt'],
            'order_status'=>'pending',
            'status'=>1,
        ];
        return $this->create($projectOrderData);
    }
}
