<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\LangValueModel;

class LangValueService extends Service
{
    public function __construct(LangValueModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取指定语言的翻译内容
     * @param $lang_id
     */
    public function getKeyValue($lang_id){
        $rows = $this->fetchAll(['lang_id'=>$lang_id]);
        $data = [];
        foreach($rows as $v){
            $data[$v['lang_key_id']] = $v['value_name'];
        }
        return $data;
    }

    /**
     * 获取指定key的语言翻译内容
     * @param $lang_key_ids
     */
    public function getLangValueArray(array $lang_key_ids,$lang_id=null){
        $rows = $this->fetchAll(['lang_key_id'=>['in',$lang_key_ids],'lang_id'=>$lang_id]);
        $data = [];
        foreach($rows as $v){
            if(!empty($lang_id)){
                $data[$v['lang_key_id']] = $v['value_name'];
            }
            else{
                if(isset($data[$v['lang_key_id']])){
                    $data[$v['lang_key_id']][$v['lang_id']] = $v['value_name'];
                }
                else{
                    $data[$v['lang_key_id']] = [$v['lang_id']=>$v['value_name']];
                }
            }
        }
        return $data;
    }

    /**
     * 获取指定key的语言翻译内容
     * @param $lang_id
     */
    public function getLangValue($lang_key_id,$field=null){
        $rows = $this->fetchAll(['lang_key_id'=>$lang_key_id]);
        $data = [];
        foreach($rows as $v){
            $data[$v['lang_id']] = $v['value_name'];
        }
        return $data;
    }
}
