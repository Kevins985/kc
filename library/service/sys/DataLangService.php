<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\DataLangModel;

class DataLangService extends Service
{
    public function __construct(DataLangModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取指定语言的数据
     * @param $data_type 指定表的语言
     * @param $lang_id
     * @return array
     */
    public function getLangValue($data_type,$lang_id){
        $rows = $this->fetchAll(['data_type'=>$data_type,'lang_id'=>$lang_id]);
        $data = [];
        foreach($rows as $v){
            $data[$v['data_id']] = $v['data_value'];
        }
        return $data;
    }

    /**
     * 获取可选择的语言列表
     * @return array
     */
    public function getSelectList($data_type,$data_id,$field=null){
        $rows = $this->fetchAll(['data_type'=>$data_type,'data_id'=>$data_id]);
        $data = [];
        foreach($rows as $v){
            if(!empty($field) && isset($v[$field])){
                $data[$v['lang_id']] = $v[$field];
            }
            else{
                $data[$v['lang_id']] = $v;
            }
        }
        return $data;
    }

    /**
     * 创建语言翻译
     * @param $data
     */
    public function updateLangTran($data_type,$data_id,array $trans=[]){
        $langValueList = $this->getSelectList($data_type,$data_id);
        foreach($trans as $lang_id=>$value){
            if(isset($langValueList[$lang_id])){
                $langValueList[$lang_id]->update(['data_value'=>$value]);
            }
            else{
                $this->create([
                    'lang_id'=>$lang_id,
                    'data_type'=>$data_type,
                    'data_id'=>$data_id,
                    'data_value'=>$value,
                ]);
            }
        }
        return true;
    }
}
