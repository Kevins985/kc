<?php

namespace library\service\sys;

use support\Container;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\sys\IpVisitModel;
use support\utils\Ip2Region;

class IpVisitService extends Service
{

    private $ipRegion;
    public function __construct(IpVisitModel $model,Ip2Region $ipRegion)
    {
        $this->model = $model;
        $this->ipRegion = $ipRegion;
    }

    /**
     * 创建IP访问记录
     * @param $data {client_ip,user_id,limit_type,last_visit_time,descr}
     */
    public function createIpVisit($data){
        $info = $this->ipRegion->memorySearch($data['client_ip']);
        $arr = explode('|',$info['region']);
        if(!empty($arr[0])){
            $country = $arr[0];
            $data['country'] = $country;
            return $this->create($data);
        }
        return false;
    }

    /**
     * 获取黑名单IP地址
     */
    public function getIpBlacklist(){
        $rows = $this->fetchAll(['limit_type'=>1,'status'=>1]);
        $data = [];
        foreach($rows as $v){
            $data[$v['id']] = $v['client_ip'];
        }
        return $data;
    }

    /**
     * 获取所有IP数量
     */
    public function getGroupAllCnt($params=[])
    {
        $selector = $this->groupBySelector(['limit_type'],$params)->selectRaw('limit_type,count(*) as ct');
        $rows = $selector->get()->toArray();
        $data = ['total'=>0];
        foreach($rows as $v){
            $data['total']+=$v['ct'];
            $data[$v['limit_type']] = $v['ct'];
        }
        return $data;
    }
}
