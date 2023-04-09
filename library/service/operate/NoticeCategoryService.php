<?php

namespace library\service\operate;

use support\extend\Service;
use library\model\operate\NoticeCategoryModel;

class NoticeCategoryService extends Service
{
    public function __construct(NoticeCategoryModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选的分类
     * @return array
     */
    public function getSelectList(){
        $rows = $this->fetchAll([],['sort'=>'desc'],['category_id','category_name'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['category_id']] = $v;
        }
        return $data;
    }
}
