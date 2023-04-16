<?php

namespace library\service\goods;

use support\exception\BusinessException;
use support\extend\Cache;
use support\extend\Service;
use library\model\goods\ProjectNumberModel;

class ProjectNumberService extends Service
{
    public function __construct(ProjectNumberModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param $project_id
     * @param $project_number
     */
    private function getProjectNumber($project_id,$project_number){
        $numberObj = $this->fetch(['project_id'=>$project_id,'project_number'=>$project_number]);
        return $numberObj;
    }

    public function createProjectNumber($project_id,$project_prefix,$number,$from_number=null){
        $number+=1;
        $data = [
            'project_id'=>$project_id,
            'project_number'=>$project_prefix.$number,
            'from_number'=>$from_number,
            'user_cnt'=>0
        ];
        if(!empty($from_number)){
            $fromNumberObj = $this->getProjectNumber($project_id,$from_number);
            if(!empty($fromNumberObj)){
                $data['parent_id'] = $fromNumberObj['id'];
            }
        }
        $projectNumberObj = $this->create($data);
        if(!empty($projectNumberObj)){
            $projectNumberObj->project->update([
                'number'=>$number
            ]);
        }
        return $projectNumberObj;
    }

    /**
     * 查询树级菜单需要的数据
     */
    public function queryTreeNumbers($project_id) {
        $selector = $this->selector(['project_id'=>$project_id],['project_number'=>'asc'],['id', 'parent_id as pId', 'project_number as name']);
        $data = $selector->get()->toArray();
        return $data;
    }
}
