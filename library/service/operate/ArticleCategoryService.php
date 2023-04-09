<?php

namespace library\service\operate;

use support\extend\Service;
use library\model\operate\ArticleCategoryModel;
use support\utils\Data;

class ArticleCategoryService extends Service
{
    public function __construct(ArticleCategoryModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可用的角色
     * @param null $ids
     * @param string $cell
     * @return array
     */
    public function getSelectList($parent_id=null,$type=null){
        $params = [];
        if(!is_null($parent_id)){
            $params['parent_id'] = $parent_id;
        }
        $rows = $this->fetchAll($params,[],['category_id','category_name','parent_id as pid','type'])->toArray();
        if($type=='tree'){
            Data::$zoomAry = [];
            return Data::getArrayZoomList($rows,'category_name','category_id');
        }
        else{
            $data = [];
            foreach($rows as $v){
                $data[$v['category_id']] = $v;
            }
            return $data;
        }
    }

    /**
     * 根据ID获取所有的名称
     * @param $role_ids
     */
    public function getCategoryNameByIds(array $category_ids){
        $data = $this->fetchAll(['category_id'=>['in',$category_ids]],[],['category_id','category_name'])->toArray();
        return Data::toKVArray($data,'category_id','category_name');
    }
}
