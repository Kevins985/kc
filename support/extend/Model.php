<?php

namespace support\extend;

use Illuminate\Database\Eloquent\Model as BaseModel;
use support\exception\BusinessException;

class Model extends BaseModel
{
//    public $timestamps = true;
    const CREATED_AT = 'created_time';
    const UPDATED_AT = 'updated_time';
    protected $keyType = 'int';
    protected $dateFormat = 'U';
    public $delete_field = 'status';

    /**
     * 指示模型主键是否递增
     * @var bool
     */
    public $incrementing = true;

    /**
     * 可以被批量赋值的属性
     * @var array
     */
    protected $fillable = [];
    /**
     * 模型的默认属性值。
     * @var array
     */
    protected $attributes = [];
    /**
     * 追加字段
     * @var array
     */
    protected $appends = [];
    /**
     * 搜索条件
     * @var array
     */
    protected $where = [];

    /**
     * 验证是否在搜索条件里面
     * @param $field
     */
    public function isWhereField($field){
        if(!empty($this->where)){
            return in_array($field,$this->where);
        }
        return false;
    }

    /**
     * 获取时间
     * @return String
     */
    public function getDateTime(string $field,$format='Y-m-d H:i:s'){
        if(is_numeric($field)){
            return date($format, $this->$field);
        }
        return $this->$field;
    }

    /**
     * 检测该字段是否可以搜索
     */
    public function checkSearchExists(string $field,$value){
        $verifyKey = false;
        if($this->isFillable($field) || $this->isWhereField($field) || $field==self::CREATED_AT || $field==self::UPDATED_AT){
            $verifyKey = true;
        }
        elseif(method_exists($this,$field)){
            $verifyKey = true;
        }
        $verifyValue = false;
        if(is_numeric($value) || !empty($value)){
            $verifyValue = true;
        }
        return $verifyKey && $verifyValue;
    }

    /**
     * 检测该字段是否可以搜索
     */
    public function filterSearchExists(string $field,$value){
        if($this->isFillable($field) || in_array($field,['created_time','updated_time','status','searchType','searchValue','page','size'])){
            return false;
        }
        return true;
    }

    /**
     * 设置model的对应参数数据
     * @param Array $rows
     * @return $this
     */
    public function setAttributes(array $rows) {
        foreach($rows as $key=>$v){
            if($this->isFillable($key)){
                if(is_numeric($v) || is_string($v) || !empty($v)){
                    $v = is_array($v)?json_encode($v,JSON_UNESCAPED_UNICODE):$v;
                    $this->setAttribute($key, $v);
                }
            }
        }
    }

    /**
     * 对象直接修改
     * @param array $attributes
     * @param array $options
     * @return Model
     */
    public function update(array $attributes = [], array $options = []){
        $this->setAttributes($attributes);
        $res = $this->save();
        return $res?$this:[];
    }

    /**
     * 修改数据
     * @param array $options
     * @return $this
     */
    public function save(array $options = []) {
        $saved = parent::save($options);
        if($saved){
            $this->flushObjectCache();
        }
        return $saved?$this:false;
    }

    /**
     * model某字段自增
     * @param string $field 字段
     * @param int $num  值
     * @return $this
     */
    public function increase(string $field,int $num=1){
        if($this->isFillable($field)){
            $this->$field+=$num;
        }
        return $this;
    }

    /**
     * model某字段自减
     * @param string $field 字段
     * @param int $num  值
     */
    public function decrease(string $field,int $num=1){
        if($this->isFillable($field)) {
            if ($this->$field < $num) {
                throw new BusinessException($this->table . ' ' . $field . '(' . $this->$field . ') less than ' . $num);
            }
            $this->$field -= $num;
        }
        return $this;
    }

    /**
     * 删除数据
     * @return boolean
     */
    public function delete(){
        $is_del = parent::delete();
        if($is_del){
            $this->deleteObjectCache();
        }
        return $is_del;
    }

    /**
     * 删除对象缓存
     */
    public function deleteObjectCache(){
        if(is_open_cache()  && !empty(static::$_dbcache)){
            foreach (static::$_dbcache as $v){
                $fieldVal = $this[$v['field']];
                if (is_numeric($fieldVal) || !empty($fieldVal)) {
                    $cache_key = $this->getDbCacheKey($v['field'], $fieldVal);
                    Cache::delete($cache_key);
                }
            }
        }
    }

    /**
     * 更新缓存
     */
    public function flushObjectCache(){
        if(is_open_cache() && !empty(static::$_dbcache)){
            $data = $this->toArray();
            foreach (static::$_dbcache as $v){
                $fieldVal = $this[$v['field']];
                if (is_numeric($fieldVal) || !empty($fieldVal)) {
                    $cache_key = $this->getDbCacheKey($v['field'], $fieldVal);
                    if (isset(static::$_dbcache[$v['field']]['range'])) {//只保存指定字段
                        $data = [];
                        foreach (static::$_dbcache[$v['field']]['range'] as $c) {
                            $data[$c] = $this[$c];
                        }
                    }
                    if (isset(static::$_dbcache[$v['field']]['expires'])) {
                        $expires = static::$_dbcache[$v['field']]['expires'];
                        Cache::set($cache_key,$data,$expires);
                    }
                    else {
                        Cache::set($cache_key,$data);
                    }
                }
            }
        }
    }

   /**
     * 强制从DB获取数据 - 获取单行行记录数据 - 根据 primary key | unique key
     * @param mixed $id 是 primary key | unique key 字段值
     * @param string $field 指定 unique key 字段名
     * @return $this
     */
    private function getFromDb($id,$field=''){
        if(empty($field)){
            return self::find($id);
        }
        return self::firstWhere($field, $id);;
    }

    /**
     * 获取DB的缓存键
     * @param string $field
     */
    private function getDbCacheKey($field,$value){
        $cache_key = strtolower($value);
        if (isset(static::$_dbcache[$field]['key_encrypt'])) {
            switch (static::$_dbcache[$field]['key_encrypt']) {
                case 'md5':
                    $cache_key = md5($cache_key);
                    break;
                case 'sha1':
                    $cache_key = sha1($cache_key);
                    break;
            }
        }
        $cache_key = sprintf(static::$_dbcache[$field]['key'], $cache_key);
        return $cache_key;
    }

    /**
     * 检测是否使用 kv table 缓存
     * @return boolean true 使用缓存 false 不使用缓存
     */
    public function checkUseKvTableCache(string $field) {
        if (!empty(static::$_dbcache) && is_array(static::$_dbcache)) {
            foreach (static::$_dbcache as $v) {
                if (!isset($v['key'])) {
                    throw new BusinessException($this->table.'  cache key not set');
                }
                if (!isset($v['field'])) {
                    throw new BusinessException($this->table.'  cache field not set');
                }
                if ($v['field']==$field && !empty($v['enable'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 从缓存中【如果model设定了 static::$_dbcache相关静态属性的话】，获取单行行记录数据 - 根据 primary key | unique key
     * @param mixed $id 是 primary key | unique key 字段值
     * @param string $field 指定 unique key 字段名
     * @return $this
     */
    public function get($id, $field = '') {
        if (!is_numeric($id) && empty($id)) {
            return null;
        }
        if ($field == '') {
            $field = $this->primaryKey;
        }
        $use_db_cache = false;
        $error = '';
        //从缓存读取数据 kv_table_{tablename}_{primary key}
        if (is_open_cache() && $this->checkUseKvTableCache($field)) {
            $use_db_cache = true;
            try {
                $cache_key = $this->getDbCacheKey($field, $id);
                $result = Cache::get($cache_key);
                if (!empty($result)) {
                    $result = $this->createRowClassByCacheData($result);
                    return $result;
                }
            }
            catch (BusinessException $e) {
                $error = $e->getMessage();
            }
        }
        $result = self::getFromDb($id,$field);
        if (!empty($result) && $use_db_cache && empty($error)) {
            $this->setKVToCache($id, $field, $result);
        }
        return $result;
    }

    /**
     * 清除缓存 - 该方法便于任何地方使用，目前主要后台
     * @param string $field 字段
     * @param mixed[string | array] $val
     */
    public function cleanKVCache($field, $val) {
        if (isset(static::$_dbcache[$field]) && is_array(static::$_dbcache[$field])) {
            if (is_array($val)) {
                foreach ($val as $v) {
                    $cache_key = $this->getDbCacheKey($field,$v);
                    Cache::delete($cache_key);
                }
            } else {
                $cache_key = $this->getDbCacheKey($field,$val);
                Cache::delete($cache_key);
            }
        }
    }

    /**
     * 使用 kv table 缓存内容，构造数据表的行对象
     * @param array $cacheData
     * @return Model
     */
    private function createRowClassByCacheData($cacheData) {
        if (empty($cacheData)) {
            return null;
        }
        $this->setAttributes($cacheData);
        $this->exists = 1;
        return $this;
    }

    /**
     * 设置数据行数据到 memcached|redis
     * @param string $id 值
     * @param string $field 字段名
     * @param object $result 数据行对象
     */
    private function setKVToCache($id, $field, $result) {
        if (isset(static::$_dbcache[$field]['expires'])) {
            if (empty(static::$_dbcache[$field]['expires'])) {
                static::$_dbcache[$field]['expires'] = null;
            }
        }
        $cache_key = $this->getDbCacheKey($field, $id);
        $data = $result->toArray();
        if (isset(static::$_dbcache[$field]['range'])) {//只保存指定字段
            $data = [];
            foreach (static::$_dbcache[$field]['range'] as $v) {
                $data[$v] = $result[$v];
            }
        }
        if (isset(static::$_dbcache[$field]['expires'])) {
            $expires = static::$_dbcache[$field]['expires'];
            Cache::set($cache_key,$data,$expires);
        }
        else {
            Cache::set($cache_key,$data);
        }
    }
}