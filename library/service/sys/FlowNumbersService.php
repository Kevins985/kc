<?php

namespace library\service\sys;

use support\extend\Redis;
use support\extend\Service;
use library\model\sys\FlowNumbersModel;
use support\utils\Random;

class FlowNumbersService extends Service
{
    public function __construct(FlowNumbersModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取订单的流水订单编号
     * @param string $table 表单名
     */
    public function getFlowOrderNo($table,$suffix=''){
        $order_no = '';
        $flowObj = $this->get($table,'from_table');
        if(!empty($flowObj) && $flowObj['status']>0){
            $cache_key = 'service.flow:'.$table.(!$suffix?'':('.'.$suffix));
            $lock_key = md5($cache_key);
            $is_lock= Redis::setNx($lock_key,time()+3);
            if($is_lock){
                $num = Redis::get($cache_key);
                if(empty($num)){
                    $num = $flowObj['flow_start_val'];
                    Redis::set($cache_key,$flowObj['flow_start_val']);
                }
                else{
                    $num+=1;
                    Redis::incr($cache_key);
                }
                if(!empty($flowObj['flow_prefix'])){
                    $order_no .= $flowObj['flow_prefix'];
                }
                if($flowObj['flow_rule']==1){
                    $order_no.= date('Y');
                }
                elseif($flowObj['flow_rule']==2){
                    $order_no.= date('Ym');
                }
                elseif($flowObj['flow_rule']==3){
                    $order_no.= date('Ymd');
                }
                elseif($flowObj['flow_rule']==4){
                    $order_no.= date('YmdH');
                }
                elseif($flowObj['flow_rule']==5){
                    $order_no.= date('YmdHi');
                }
                elseif($flowObj['flow_rule']==6){
                    $order_no.= date('YmdHis');
                }
                if($flowObj['flow_random']==0){
                    $order_num = sprintf('%0'.$flowObj['flow_digit'].'s', $num);
                }
                else{
                    $order_num = Random::getRandStr($flowObj['flow_digit'],0);
                }
                $order_no .= $order_num;
                if(!empty($suffix)){
                    $order_no .= $suffix;
                }
                elseif(!empty($flowObj['flow_suffix'])){
                    $order_no .= $flowObj['flow_suffix'];
                }
                Redis::del($lock_key);
            }
            else{
                $lock_time = Redis::get($lock_key);
                if($lock_time<time()){
                    Redis::del($lock_key);
                }
            }
        }
        return $order_no;
    }
}
