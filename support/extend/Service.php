<?php

namespace support\extend;

use support\exception\BusinessException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Service
{
    /**
     * model类名
     * @var Model
     */
    protected $model = null;

    /**
     * 获取model实例对象
     * @return Model
     */
    public function getModelObj(array $data=[]){
        if(empty($this->model)){
            throw new BusinessException('not found model object');
        }
        return new $this->model($data);
    }

    /**
     * 设置正则处理方式
     * @param $str
     */
    public function raw($str){
        return $this->getModelObj()->raw($str);
    }

    /**
     * 获取一个数据库连接
     * @param string $adapter
     * @return \Illuminate\Database\Connection
     */
    public function connection($adapter="mysql"){
        return Db::connection($adapter);
    }

    /**
     * 获取一个对象[如果模型中有配置缓存，自动读取缓存]
     * @param integer|string $id  查询值
     * @param string $field  查询字段
     * @return Model
     */
    public function get($id,string $field = null){
        if($id instanceof $this->model){
            return $id;
        }
        else{
            return $this->getModelObj()->get($id,$field);
        }
    }

    /**
     * 获取某一个字段
     * @param $id
     * @param $field
     */
    public function getValue($id,$field,$default=null){
        $obj = $this->get($id);
        return isset($obj[$field])?$obj[$field]:$default;
    }

    /**
     * @param $id
     * 直接从数据库中获取一个对象
     * @param int|string|array $id
     * @return Builder|Builder[]|Collection|Model|null
     * @throws BusinessException
     */
    public function find($id){
        return $this->getModelObj()->query()->find($id);
    }

    /**
     * @param $id
     * 直接从数据库中获取一个对象,未找到报错
     * @return Builder|Builder[]|Collection|Model|null
     * @throws BusinessException
     */
    public function findOrFail($id){
        return $this->getModelObj()->query()->findOrFail($id);
    }

    /**
     * 直接从数据库中获取一个对象
     * @param $id
     * @param string|null $field
     * @return Builder|Model|\object|null
     * @throws BusinessException
     */
    public function first($id,string $field = null){
        if(empty($field)){
            return $this->getModelObj()->query()->first($id);
        }
        return $this->getModelObj()->query()->firstWhere($field,$id);
    }

    /**
     * 创建一个对象
     * @param array $data  数据
     * @return false|Model
     * @throws BusinessException
     */
    public function create(array $data){
        try{
            $model = $this->getModelObj();
            $model->setAttributes($data);
            $res = $model->save();
            return $res?$model:[];
        }
        catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('/for key \'(.*?)\'/is',$msg, $match)){
                $msg=$match[1].' already exists';
            }
            throw new BusinessException($msg);
        }
    }

    /**
     * 尝试通过给定的属性查找数据库中模型，找不到则将插入一条记录
     * @param array $params 查询条件
     * @param array $data 保存的数据
     * @return Model
     * @throws BusinessException
     */
    public function firstOrCreate(array $params,array $data){
        try{
            $obj = $this->fetch($params);
            if(!empty($obj)){
                return $obj;
            }
            else{
                $model = $this->getModelObj();
                $model->setAttributes($data);
                return $model->save();
            }
        }
        catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('/for key \'(.*?)\'/is',$msg, $match)){
                $msg=$match[1].' already exists';
            }
            throw new BusinessException($msg);
        }
    }

    /**
     * 创建或修改数据
     * @param array $params 查询条件
     * @param array $data 保存的数据
     * @return Model
     * @throws BusinessException
     */
    public function createOrUpdate(array $params,array $data){
        try{
            $obj = $this->fetch($params);
            if(!empty($obj)){
                return $obj->update($data);
            }
            else{
                $model = $this->getModelObj();
                $model->setAttributes($data);
                return $model->save();
            }
        }
        catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('/for key \'(.*?)\'/is',$msg, $match)){
                $msg=$match[1].' already exists';
            }
            throw new BusinessException($msg);
        }
    }

    /**
     * 插入一批数据
     * @param array $data 二维数组
     * @param array $consult 一维数组
     * @return boolean
     * @throws BusinessException
     */
    public function insert(array $data,array $consult=[]){
        try{
            $model = $this->getModelObj();
            if(!empty($model::CREATED_AT)){
                $consult[$model::CREATED_AT]=time();
            }
            if(!empty($model::UPDATED_AT)){
                $consult[$model::UPDATED_AT]=time();
            }
            array_walk($data,function(&$item) use ($consult) {
                $item=array_merge($item, $consult);
            });
            return $model->insert($data);
        }
        catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('/for key \'(.*?)\'/is',$msg, $match)){
                $msg = $e->getMessage();
                if(preg_match('/for key \'(.*?)\'/is',$msg, $match)){
                    $msg=$match[1].' already exists';
                }
            }
            throw new BusinessException($msg);
        }
    }

    /**
     * 修改一个对象
     * @param $id
     * @param array $data
     * @return bool
     * @throws BusinessException
     */
    public function update($id,array $data){
        try{
            $model = $this->get($id);
            if(empty($model)){
                return false;
            }
            $model->setAttributes($data);
            $res = $model->save();
            return $res?$model:false;
        }
        catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('/for key \'(.*?)\'/is',$msg, $match)){
                $msg=$match[1].' already exists';
            }
            throw new BusinessException($msg);
        }
    }

    /**
     * 批量修改
     * @param $ids
     * @param bool $force
     * @return int|mixed
     * @throws BusinessException
     */
    public function batchUpdate($ids,array $data){
        try{
            $model = $this->getModelObj();
            $primaryKey = $model->primaryKey;
            return $this->selector([$primaryKey=>['in',$ids]])->update($data);
        }
        catch (\Exception $e) {
            $msg = $e->getMessage();
            if(preg_match('/for key \'(.*?)\'/is',$msg, $match)){
                $msg=$match[1].' already exists';
            }
            throw new BusinessException($msg);
        }
    }

    /**
     * 修改多个对象数据
     * @param type $params 查询参数
     * @param array $data  修改的数据
     * @return int
     */
    public function updateAll(array $params,array $data){
         $selector = $this->selector($params);
         $update = [];
         $modelObj = $this->getModelObj();
         foreach($data as $k=>$v){
             if($modelObj->isFillable($k)){
                 $update[$k] = $v;
             }
         }
         if(!empty($update)){
             return $selector->update($update);
         }
         return false;
    }

    /**
     * 删除一个对象
     * @param integer $id 主键ID
     * @param integer $force 是否物理删除
     * @return bool
     */
    public function delete($id,bool $force=false){
        $modelObj = $this->get($id);
        if($force || empty($modelObj::UPDATED_AT)){
            return $modelObj->delete();
        }
        else{
            $field = $modelObj->delete_field;
            if(!$modelObj->isFillable($field)){
                throw new BusinessException($field.'该字段不存在');
            }
            return $modelObj->update([$field=>-1]);
        }
    }

    /**
     * 批量删除
     * @param $ids
     * @param bool $force
     * @return int|mixed
     * @throws BusinessException
     */
    public function batchDelete($ids,bool $force=false){
        $modelObj = $this->getModelObj();
        $primaryKey = $modelObj->primaryKey;
        if($force || empty($modelObj::UPDATED_AT)){
            return $modelObj->destroy($ids);
        }
        else{
            $field = $modelObj->delete_field;
            if(!$modelObj->isFillable($field)){
                throw new BusinessException($field.'该字段不存在');
            }
            return $this->selector([$primaryKey=>['in',$ids]])->update([$field=>-1]);
        }
    }

    /**
     * 根据条件删除
     * @param type $params 查询参数
     * @param integer $force 是否物理删除
     * @return mixed|int
     */
    public function deleteAll(array $params,bool $force=false){
        $model = $this->getModelObj();
        $selector = $this->selector($params);
        if($force || empty($model::UPDATED_AT)){
            return $selector->delete();
        }
        else{
            $field = $model->delete_field;
            return $selector->update([$field=>-1]);
        }
    }

    /**
     * 查找对象列表构造器
     * @param array $params 查询参数
     * @param array $orderBy  根据字段排序
     * @param array $fields  制定查询字段
     * @return Builder
     */
    public function selector(array $params=[],array $orderBy=[],array $fields=[]){
        $modelObj = $this->getModelObj();
        $selector = $modelObj->query();
        $is_inner = false;
        foreach($params as $key=>$v){
            if(is_array($v) && $v[0]=='inner_join'){
                $is_inner = true;
                $selector = $this->createCondition($selector,$key,$v);
            }
            elseif($modelObj->checkSearchExists($key,$v)){
                $selector = $this->createCondition($selector,$key,$v);
            }
        }
        $delField = $modelObj->delete_field;
        if($is_inner==false && !empty($delField) && $modelObj->isFillable($delField) && !isset($params[$delField])){
            $selector->where($delField,'>',-1);
        }
        if(!empty($orderBy)){
            foreach($orderBy as $key=>$val){
                if($is_inner || $modelObj->isFillable($key)){
                    $selector->orderBy($key,$val);
                }
            }
        }
//        elseif($modelObj->isFillable('sort')){
//            $selector->orderBy('sort','desc');
//        }
//        else{
//            $selector->orderBy('created_time','desc');
//        }
        if(!empty($fields)){
            $selector->select($fields);
        }
        return $selector;
    }

    /**
     * 分组查询
     */
    public function groupBySelector($groupBy=[],array $params=[],$orderBy=[]){
        $modelObj = $this->getModelObj();
        $selector = $modelObj->query()->groupBy($groupBy);
        foreach($params as $key=>$v){
            if($modelObj->checkSearchExists($key,$v)){
                $selector = $this->createCondition($selector,$key,$v);
            }
            elseif($v!='' && $modelObj->filterSearchExists($key,$v)){
                throw new BusinessException($key."字段不存在");
            }
        }
        $delField = $modelObj->delete_field;
        if(!empty($delField) && $modelObj->isFillable($delField) && !isset($params[$delField])){
            $selector->where($delField,'>',-1);
        }
        if(!empty($orderBy)){
            foreach($orderBy as $key=>$val){
                if($modelObj->isFillable($key)){
                    $selector->orderBy($key,$val);
                }
            }
        }
        return $selector;
    }

    /**
     * 构造条件
     * @param Builder $selector 构造器
     * @param $key  字段
     * @param $v  搜索数据
     * @return mixed
     * @throws BusinessException
     */
    public function createCondition(Builder $selector,$key,$v){
        if(!is_array($v)){
            $selector->where($key,$v);
        }
        else{
            $type = strtolower($v[0]);
            switch ($type) {
                case 'eq':      //等于（=）
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'=',$v[1],$boolean);
                    break;
                case 'neq':     //不等于（<>）
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'<>',$v[1],$boolean);
                    break;
                case 'gt':      //大于（>）
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'>',$v[1],$boolean);
                    break;
                case 'gte':     //大于等于（>=）
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'>=',$v[1],$boolean);
                    break;
                case 'lt':      //小于（<）
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'<',$v[1],$boolean);
                    break;
                case 'lte':     //小于等于（<=）
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'<=',$v[1],$boolean);
                    break;
                case "empty":
                    $boolean = empty($v[1])?'and':$v[1];
                    $selector->where($key,'=','',$boolean);
                    break;
                case "null":
                    $boolean = empty($v[1])?'and':$v[1];
                    $selector->whereNull($key,$boolean);
                    break;
                case "not_null":
                    $boolean = empty($v[1])?'and':$v[1];
                    $selector->whereNotNull($key,$boolean);
                    break;
                case "right_like":
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'like','%'.$v[1],$boolean);
                    break;
                case "left_like":
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'like',$v[1].'%',$boolean);
                    break;
                case 'like':    //模糊查询
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'like','%'.$v[1].'%',$boolean);
                    break;
                case 'not_like':    //模糊查询
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->where($key,'not like','%'.$v[1].'%',$boolean);
                    break;
                case 'in':      //IN 查询
                    $boolean = empty($v[2])?'and':$v[2];
                    $values = (is_array($v[1])?$v[1]:explode(',', $v[1]));
                    $selector->whereIn($key,$values,$boolean);
                    break;
                case 'not_in':
                    $boolean = empty($v[2])?'and':$v[2];
                    $values = (is_array($v[1])?$v[1]:explode(',', $v[1]));
                    $selector->whereNotIn($key,$values,$boolean);
                    break;
                case 'between':
                    $boolean = empty($v[2])?'and':$v[2];
                    $values = (is_array($v[1])?$v[1]:explode(',', $v[1]));
                    $selector->whereBetween($key,$values,$boolean);
                    break;
                case 'not_between':
                    $boolean = empty($v[2])?'and':$v[2];
                    $values = (is_array($v[1])?$v[1]:explode(',', $v[1]));
                    $selector->whereNotBetween($key,$values,$boolean);
                    break;
                case 'has':
                    $selector->whereHas($v[1],function($query) use($key,$v){
                        $query->where($key,$v[2]);
                    });
                    break;
                case 'sql':
                    $boolean = empty($v[2])?'and':$v[2];
                    $selector->whereRaw($v[1],[],$boolean);
                    break;
                case 'inner_join':
                    $selector->join($v[1],$v[2],$v[3]);
                    if(!empty($v[4])){
                        foreach($v[4] as $k=>$v){
                            $selector = $this->createCondition($selector,$k,$v);
                        }
                    }
                    break;
                case 'with':
                    if(isset($v[1]) && is_array($v[1])){
                        $selector->with($key,function($query) use($v){
                            return $query->select($v[1]);
                        });
                    }
                    else{
                        $selector->with($key);
                    }
                case 'when':
                    if(isset($v[1]) && is_array($v[1])) {
                        $selector->when(isset($v[1]) && is_array($v[1]), function ($query) use ($v) {
                            $query->where(...$v[1]);
                        });
                    }
                    break;
                default:
                    throw new BusinessException($key."传输格式不正确");
            }
        }
        return $selector;
    }

    /**
     * 返回查询的字段值
     * @param string $column 制定查询字段
     * @param array $params 查询参数
     * @param array $orderBy  根据字段排序
     * @return mixed
     */
    public function value(string $column,array $params=[],array $orderBy=[]){
        return $this->selector($params, $orderBy)->value($column);
    }

    /**
     * 返回查询的字段数据
     * @param string $column 制定查询字段
     * @param array $params 查询参数
     * @param array $orderBy  根据字段排序
     * @return array
     */
    public function pluck(string $column,array $params=[],array $orderBy=[]){
        $size = 0;
        if(!empty($params['size']) && is_numeric($params['size']) && $params['size']>0){
            $size = (int)$params['size'];
            unset($params['size']);
        }
        $selector = $this->selector($params, $orderBy);
        if($size>0){
            $selector->take($size);
        }
        return $selector->pluck($column)->toArray();
    }

    /**
     * 统计数量
     * @param array $params 查询参数
     * @return int
     */
    public function count(array $params=[]){
        return $this->selector($params)->count();
    }

    /**
     * 数量求和
     * @param array $params 查询参数
     * @return int
     */
    public function sum($column,array $params=[]){
        return $this->selector($params)->sum($column);
    }

    /**
     * 查找一个对象
     * @param array $params 查询参数
     * @param array $orderBy  根据字段排序
     * @param array $fields  制定查询字段
     * @return Model
     */
    public function fetch(array $params=[],array $orderBy=[],array $fields=[]){
        return $this->selector($params, $orderBy, $fields)->first();
    }

    /**
     * 查找列表
     * @param array $params 查询参数
     * @param array $orderBy  根据字段排序
     * @param array $fields  制定查询字段
     * @return Collection
     */
    public function fetchAll(array $params=[],array $orderBy=[],array $fields=[]){
        $size = 0;
        if(!empty($params['size']) && is_numeric($params['size']) && $params['size']>0){
            $size = (int)$params['size'];
            unset($params['size']);
        }
        $selector = $this->selector($params, $orderBy, $fields);
        if($size>0){
            $selector->take($size);
        }
        return $selector->get();
    }

    /**
     * 分页搜索
     * @param array $params 查询参数
     * @param array $orderBy  根据字段排序
     * @param array $fields  制定查询字段
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(string $url,array $params=[],array $orderBy=[],array $fields = ['*']){
        $page = 1;
        if(!empty($params['page']) && is_numeric($params['page']) && $params['page']>1){
            $page = (int)$params['page'];
            unset($params['page']);
        }
        $size = 10;
        if(!empty($params['size']) && is_numeric($params['size']) && $params['size']>0){
            $size = (int)$params['size'];
            if($size>50){
                $size = 50;
            }
        }
        $selector = $this->selector($params,$orderBy,$fields);
        $paginate = $selector->paginate($size,$fields,'page',$page);
        $paginate->setPath($url);
        return $paginate;
    }

    /**
     * 获取分页的数据
     * @param array $params 查询参数
     * @param array $orderBy  根据字段排序
     * @param array $fields  制定查询字段
     * @return array
     */
    public function paginateData(array $params=[],array $orderBy=[],array $fields = ['*'])
    {
        $page = 1;
        if (!empty($params['page']) && is_numeric($params['page']) && $params['page'] > 1) {
            $page = (int)$params['page'];
            unset($params['page']);
        }
        $size = 10;
        if (!empty($params['size']) && is_numeric($params['size']) && $params['size'] > 0) {
            $size = (int)$params['size'];
            if ($size > 50) {
                $size = 50;
            }
        }
        $selector = $this->selector($params, $orderBy, $fields);
        $count = $selector->count();
        $offset = $page-1;
        if($offset>0){
            $offset = $offset * $size;
        }
        $data = $selector->offset($offset)->limit($size)->get()->toArray();
        $sum_page = ceil($count/$size);
        return ['page'=>$page,'size'=>$size,'count'=>$count,'sumPage'=>$sum_page,'data'=>$data];
    }
}