<?php

namespace library\service\user;

use Support\Container;
use support\extend\Service;
use library\model\user\TagsModel;

class TagsService extends Service
{
    public function __construct(TagsModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选择的标签
     * @return array
     */
    public function getSelectList($category_id,$field=null){
        $rows = $this->fetchAll(['category_id'=>$category_id],[],['tag_id','tag_name'])->toArray();
        $data = [];
        foreach($rows as $v){
            if(!empty($field) && isset($v[$field])){
                $data[$v['tag_id']] = $v[$field];
            }
            else{
                $data[$v['tag_id']] = $v;
            }
        }
        return $data;
    }

    /**
     * 获取标签
     * @return array
     */
    public function getCategoryTagsList(){
        $rows = $this->fetchAll([],['sort'=>'asc'],['tag_id','tag_name','category_id'])->toArray();
        $categoryService = Container::get(TagsCategoryService::class);
        $categoryList =$categoryService->getSelectList();
        $data = [];
        foreach($rows as $v){
            if(!empty($data[$v['category_id']])){
                $data[$v['category_id']]['child'][] = ['tag_id'=>$v['tag_id'],'tag_name'=>$v['tag_name']];
            }
            else{
                $data[$v['category_id']] = [
                    'category_id'=>$v['category_id'],
                    'category_name'=>$categoryList[$v['category_id']]['category_name'],
                    'child'=>[['tag_id'=>$v['tag_id'],'tag_name'=>$v['tag_name']]]
                ];
            }
        }
        return $data;
    }
}
