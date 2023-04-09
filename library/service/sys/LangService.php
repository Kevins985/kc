<?php

namespace library\service\sys;

use support\Container;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\sys\LangModel;

class LangService extends Service
{
    public function __construct(LangModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取语言ID
     * @param $lang_code
     */
    public function getLangId($lang_code){
        return $this->value('lang_id',['lang_code'=>$lang_code]);
    }

    /**
     * 获取可选择的语言列表
     * @return array
     */
    public function getSelectList(){
        $rows = $this->fetchAll([],['sort'=>'asc'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['lang_id']] = $v;
        }
        return $data;
    }

    /**
     * 生成语言包文件
     * @param $lang_id
     */
    public function createLangFile($lang_id=0){
        $langObj = $this->get($lang_id);
        $langKeyService = Container::get(LangKeyService::class);
        $keys = $langKeyService->fetchAll();
        $content = "<?php\n\n";
        $content.="return [\n";
        if(!empty($langObj)){
            $filepath = resource_path('translations/'.$langObj['lang_code'].'/messages.php');
            $langValueService = Container::get(LangValueService::class);
            $values = $langValueService->getKeyValue($lang_id);
            foreach($keys as $v){
                if(isset($values[$v['key_id']])){
                    if(strpos($values[$v['key_id']],"'")>0){
                        $content.="\t\t'".$v['key_name']."'=>\"".$values[$v['key_id']]."\",\n";
                    }
                    else{
                        $content.="\t\t'".$v['key_name']."'=>'".$values[$v['key_id']]."',\n";
                    }
                }
            }
        }
        else{
            $filepath = resource_path('translations/zh/messages.php');
            foreach($keys as $v){
                $content.="\t\t'".$v['key_name']."'=>'".$v['descr']."',\n";
            }
        }
        $content.="];";
        return file_put_contents($filepath,$content);
    }
}
