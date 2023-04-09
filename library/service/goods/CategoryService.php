<?php

namespace library\service\goods;

use support\extend\Service;
use library\model\goods\CategoryModel;
use support\utils\Data;

class CategoryService extends Service
{
    public function __construct(CategoryModel $model)
    {
        $this->model = $model;
    }

    public function getAllChildList(){
        $categories = CategoryModel::with(['allChildren' => function($query) {
            $query->select('category_id', 'category_name', 'parent_id');
        }])->select('category_id', 'category_name', 'parent_id')->get();
        return $categories;
    }

    /**
     * 获取可选的产品类型
     * @return array
     */
    public function getSelectList($parent_id=null,$type=null){
        $params = [];
        if(!is_null($parent_id)){
            $params['parent_id'] = $parent_id;
        }
        $rows = $this->fetchAll($params,['sort'=>'desc'],['category_id','category_name','parent_id as pid','status','sort','created_time'])->toArray();
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
     * @param $id
     * @param $status
     */
    public function setCategoryStatus($id,$status){
        $categoryObj = $this->get($id);
        if(!empty($categoryObj)){
            if($categoryObj['parent_id']==0 && $status==0){
                $this->updateAll(['parent_id'=>$id],['status'=>0]);
            }
            return $categoryObj->update(['status'=>$status]);
        }
        return false;
    }

    /**
     * 获取分类列表
     * @param array $ids
     */
    public function getCategoryNames(array $ids){
        return $this->pluck('category_name',['category_id'=>['in',$ids]]);
    }

    /**
     * 获取子集的分类ID
     * @param $parent_id
     */
    public function getCategoryChildIds($parent_id){
          $ids = $this->pluck('category_id',['status'=>1,'parent_id'=>$parent_id]);
          if(empty($ids)){
              $ids = [];
          }
          $ids[] = $parent_id;
          return $ids;
    }

    /**
     * 获取分类数据
     */
    public function getAppCatetoryList(){
        $field = ['category_id','category_name','parent_id','icon'];
        $rows = $this->fetchAll(['status'=>1],['sort'=>'asc'],$field);
        return $rows;
    }
}
