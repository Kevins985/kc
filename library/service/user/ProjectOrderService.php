<?php

namespace library\service\user;

use library\model\goods\ProjectNumberModel;
use library\model\user\OrderModel;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\user\ProjectOrderModel;

class ProjectOrderService extends Service
{
    public function __construct(ProjectOrderModel $model)
    {
        $this->model = $model;
    }

    public function getOutProjectOrder($project_id,$project_number){
        return $this->fetch(['project_id'=>$project_id,'project_number'=>$project_number,'status'=>1,'user_progress'=>['lt',ProjectUserCnt]],['user_number'=>'asc']);
    }

    public function getActiveProjectOrderList($project_id,$project_number){
        return $this->fetchAll(['project_id'=>$project_id,'project_number'=>$project_number,'status'=>1,'user_progress'=>['lt',ProjectUserCnt]],['user_number'=>'asc']);
    }

    public function createProjectOrder(ProjectNumberModel $projectNumberObj,$order_id,$user_id,$user_progress=0){
        $projectNumberObj->increase('user_cnt')->save();
        $projectOrderData = [
            'order_id'=>$order_id,
            'project_id'=>$projectNumberObj['project_id'],
            'project_number'=>$projectNumberObj['project_number'],
            'user_id'=>$user_id,
            'user_number'=>$projectNumberObj['user_cnt'],
            'user_progress'=>$user_progress,
            'order_status'=>'pending',
            'status'=>1,
        ];
        return $this->create($projectOrderData);
    }

    /**
     * 直推4个人进行插队重新排序
     * @param OrderModel $parentOrderObj
     */
    public function resetProjectOrder(OrderModel $parentOrderObj){
         $parentProjectOrderObj = $this->fetch(['user_id'=>$parentOrderObj['user_id'],'project_id'=>$parentOrderObj['parent_id'],'order_id'=>$parentOrderObj['order_id']]);
         if(empty($parentProjectOrderObj)){
             throw new BusinessException("暂未找到该邀请人的项目订单");
         }
         elseif($parentProjectOrderObj['status']==1 && $parentProjectOrderObj['reset_status']==0){
             $rows = $this->fetchAll(['project_id'=>$parentOrderObj['parent_id'],'project_number'=>$parentProjectOrderObj['project_number'],'status'=>1,'reset_status'=>0],['user_number'=>'asc']);
             $user_number = 0;
             $index = 0;
             foreach($rows as $k=>$v){
                 if($k==0){
                     $user_number = $v['user_number'];
                     $index = $v['user_number'];
                     if($v['id']==$parentProjectOrderObj['id']){
                         break;
                     }
                 }
                 $index++;
                 if($v['id']==$parentProjectOrderObj['id']){
                     $v->update(['user_number'=>$user_number,'user_progress'=>3,'reset_status'=>1]);
                 }
                 else{
                     $v->update(['user_number'=>$index,'user_progress'=>0]);
                 }
             }
         }
    }
}
