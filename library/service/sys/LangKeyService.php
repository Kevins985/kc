<?php

namespace library\service\sys;

use support\Container;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\sys\LangKeyModel;
use support\utils\Data;

class LangKeyService extends Service
{
    public function __construct(LangKeyModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取可选择的语言列表
     * @return array
     */
    public function getSelectList($parent_id=null,$type=null){
        $params = [];
        if(!is_null($parent_id)){
            $params['parent_id'] = $parent_id;
        }
        $rows = $this->fetchAll($params,[],['key_id','key_name','parent_id as pid'])->toArray();
        if($type=='tree'){
            Data::$zoomAry = [];
            return Data::getArrayZoomList($rows,'key_id','key_name');
        }
        else{
            $data = [];
            foreach($rows as $v){
                $data[$v['key_id']] = $v;
            }
            return $data;
        }
    }

    /**
     * 创建语言翻译
     * @param $data
     */
    public function createLangKey(array $data,array $value_name=[]){
        $res = $this->fetch(['key_name'=>$data['key_name'],'parent_id'=>(!empty($data['parent_id'])?$data['parent_id']:0)]);
        if(empty($res)){
            $res = $this->create($data);
        }
        if($res && !empty($value_name)){
            $langValueService = Container::get(LangValueService::class);
            foreach($value_name as $lang_id=>$name){
                $langValueService->create([
                    'lang_id'=>$lang_id,
                    'lang_key_id'=>$res['key_id'],
                    'value_name'=>$name
                ]);
            }
        }
        return $res;
    }

    /**
     * 创建语言翻译
     * @param $data
     */
    public function updateLangKey(array $data,array $value_name=[]){
        if(empty($data['key_id'])){
            throw new BusinessException("主键ID不存在");
        }
        $res = $this->update($data['key_id'],$data);
        if($res && !empty($value_name)){
            $langValueService = Container::get(LangValueService::class);
            $langValueList = $langValueService->getLangValue($res['key_id']);
            foreach($value_name as $lang_id=>$name){
                if(isset($langValueList[$lang_id])){
                    $langValueList[$lang_id]->update(['value_name'=>$name]);
                }
                else{
                    $langValueService->create([
                        'lang_id'=>$lang_id,
                        'lang_key_id'=>$res['key_id'],
                        'value_name'=>$name
                    ]);
                }
            }
        }
        return $res;
    }
}
