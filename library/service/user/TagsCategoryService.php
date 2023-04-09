<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\TagsCategoryModel;

class TagsCategoryService extends Service
{
    public function __construct(TagsCategoryModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选的分类
     * @return array
     */
    public function getSelectList(){
        $rows = $this->fetchAll()->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['category_id']] = $v;
        }
        return $data;
    }
}
