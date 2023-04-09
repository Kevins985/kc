<?php

namespace library\logic;
use library\service\sys\DictListService;
use library\service\sys\DictService;
use support\extend\Cache;
use support\extend\Logic;

class DictLogic extends Logic
{
    /**
     * @Inject
     * @var DictService
     */
    public $dictService;
    /**
     * @Inject
     * @var DictListService
     */
    public $dictListService;

    /**
     * 获取字段类型
     */
    public function getDictFieldTypes()
    {
        return [
            'text'=>'text',
            'number'=>'number',
            'date'=>'date',
            'file'=>'file',
            'radio'=>'radio',
            'checkbox'=>'checkbox',
            'select'=>'select',
            'textarea'=>'textarea',
        ];
    }

    /**
     * 获取页面的可选数据
     * @param type $type
     */
    public function getDictTypes($type=null){
        $data = [
            0=>'系统配置',
            1=>'推广设置',
            2=>'存储配置',
            3=>'支付配置',
            4=>'其他配置',
        ];
        if(!empty($type)){
            return isset($data[$type])?$data[$type]:[];
        }
        return $data;
    }

    /**
     * 获取字典配置
     * @param $dict_code
     * @param false $clearCache
     * @return array|mixed|null
     */
    public function getDictConfigs($dict_code,$clearCache=false){
        $cache_key = 'logic.dict_configs_'.$dict_code;
        $data = Cache::get($cache_key);
        if(empty($data) || $clearCache){
            $rows = $this->dictListService->getDictList($dict_code);
            $data = [];
            foreach($rows as $v){
                $data[$v['field_code']] = $v['field_value'];
            }
            Cache::set($cache_key,$data,3600);
        }
        return $data;
    }
    
    /**
     * 获取某类型的配置
     * @param type $type
     */
    public function getDictListForType($type){
        $confList = $this->dictService->fetchAll(['dict_type'=>$type],['sort'=>'desc'],['dict_id','dict_name','dict_code'])->toArray();
        foreach($confList as &$v){
            $v['children'] =  $this->dictListService->getDictList($v['dict_code']);
        }
        return $confList;
    }

    /**
     * 保存配置数据
     * @param string $dict_code 指定字典编码
     * @param array $data 数据
     */
    public function saveDictListValue($dict_code,array $data){
        $list = $this->dictListService->fetchAll(['dict_code'=>$dict_code]);
        foreach($list as $v){
            if(isset($data[$v['field_code']])){
                $value = (is_array($data[$v['field_code']])?implode(',',$data[$v['field_code']]):$data[$v['field_code']]);
                $this->dictListService->update($v['id'],[
                    'field_value'=>$value
                ]);
            }
        }
        $cache_key = 'logic.dict_configs_'.$dict_code;
        Cache::delete($cache_key);
        return true;
    }

    /**
     * 保存字典数据
     * @param string $dict_code
     * @param array $data
     * @return int
     */
    public function saveDictConfigs(string $dict_code,array $data){
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $datalist = [];
            $list = $this->dictListService->fetchAll(['dict_code'=>$dict_code],[],['id','field_code'])->toArray();
            foreach($list as $v){
                $datalist[$v['field_code']] = $v;
            }
            $ct = 0;
            foreach ($data as $v){
                $v = array_filter($v);
                $v['status'] = 1;
                if(!empty($v['id'])){
                    $this->dictListService->update($v['id'],$v);
                    $ct++;
                }
                elseif(!empty($datalist[$v['field_code']])){
                    $this->dictListService->update($datalist[$v['field_code']]['id'],$v);
                    $ct++;
                }
                else{
                    $v['dict_code'] = $dict_code;
                    $this->dictListService->create($v);
                    $ct++;
                }
            }
            $cache_key = 'logic.dict_configs_'.$dict_code;
            Cache::delete($cache_key);
            $conn->commit();
            return $ct;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }
}
