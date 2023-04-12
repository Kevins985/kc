<?php

namespace library\service\goods;

use support\extend\Service;
use library\model\goods\ProjectNumberModel;

class ProjectNumberService extends Service
{
    public function __construct(ProjectNumberModel $model)
    {
        $this->model = $model;
    }

    public function createProjectNumber($project_id,$project_prefix,$number,$from_number=null){
        $number+=1;
        $project_number = $project_prefix.$number;
        $projectNumberObj = $this->create([
            'project_id'=>$project_id,
            'project_number'=>$project_number,
            'from_number'=>$from_number,
            'user_cnt'=>0
        ]);
        if(!empty($projectNumberObj)){
            $projectNumberObj->project->update([
                'number'=>$number
            ]);
        }
        return $projectNumberObj;
    }
}
