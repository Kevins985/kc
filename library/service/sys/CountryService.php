<?php

namespace library\service\sys;

use support\extend\Service;
use library\model\sys\CountryModel;

class CountryService extends Service
{
    public function __construct(CountryModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取国家所在洲
     * @param int $num
     */
    public function getContinentList(int $num=0){
        $list = [
            1=>['name'=>'亚洲','ename'=>'Asia'],
            2=>['name'=>'欧洲','ename'=>'Europe'],
            3=>['name'=>'非洲','ename'=>'Africa'],
            4=>['name'=>'北美洲','ename'=>'North America'],
            5=>['name'=>'南美洲','ename'=>'South America'],
            6=>['name'=>'大洋洲','ename'=>'Oceania'],
            7=>['name'=>'南极洲','ename'=>'Antarctica'],
            8=>['name'=>'其他','ename'=>'Other']
        ];
        if(!empty($num)){
            return isset($list[$num])?$list[$num]:[];
        }
        return $list;
    }

    /**
     * 根据国家编码获取国家数据
     * @param array $code
     */
    public function getSelectListByCode(array $code){
        $rows = $this->fetchAll(['code'=>['in',$code]],['name_en'=>'asc'],['id','code','name','name_en','num_code'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['code']] = $v;
        }
        return $data;
    }

    /**
     * 获取所在的国家
     * @return array
     */
    public function getSelectList($continent=null){
        $rows = $this->fetchAll(['continent'=>$continent,'status'=>1],['name_en'=>'asc'],['id','code','name','name_en','num_code'])->toArray();
        $data = [];
        foreach($rows as $v){
            $data[$v['code']] = $v;
        }
        return $data;
    }

    /**
     * 获取所有的国家
     * @return array
     */
    public function getContinentCountryList(){
        $rows = $this->fetchAll(['continent'=>['gt',0]],['continent'=>'asc','name_en'=>'asc'],["continent",'code','name','name_en'])->toArray();
        $data = [];
        $continent = $this->getContinentList();
        foreach($rows as $v){
            if(!empty($data[$v['continent']])){
                $data[$v['continent']]['country'][]=$v;
            }
            else{
                $tmp = $continent[$v['continent']];
                $data[$v['continent']] = [
                    'id'=>$v['continent'],
                    'name'=>$tmp['name'],
                    'country'=>[
                        $v
                    ]
                ];
            }
        }
        return $data;
    }
}
