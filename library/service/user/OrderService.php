<?php

namespace library\service\user;

use library\service\sys\FlowNumbersService;
use support\Container;
use support\extend\Service;
use library\model\user\OrderModel;
use support\utils\Data;

class OrderService extends Service
{
    public function __construct(OrderModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取订单编号
     * @param string $suffix
     * @return mixed
     */
    public function getOrderNo($suffix=''){
        $flowNumberServer = Container::get(FlowNumbersService::class);
        $number = $flowNumberServer->getFlowOrderNo($this->model->getTable(),$suffix);
        $orderObj = $this->get($number,'order_no');
        if(empty($orderObj)){
            return $number;
        }
        return $this->getOrderNo($suffix);
    }

    /**
     * 获取指定商品列表
     * @param array $spu_ids
     */
    public function getOrderList(array $order_ids,$fields=[]){
        $rows = $this->fetchAll(['order_id'=>['in',$order_ids]],[],$fields);
        return Data::toKeyArray($rows,'order_id');
    }

    /**
     * 获取所有客户的订单数量
     */
    public function getGroupAllStatusCnt($params=[])
    {
        $selector = $this->groupBySelector(['order_status'],$params)->selectRaw('order_status, count(*) as ct,sum(point) point');
        $rows = $selector->get()->toArray();
        $data = ['total'=>['ct'=>0,'point'=>0]];
        foreach($rows as $v){
            $data[$v['order_status']] = $v;
            $data['total']['ct']+=$v['ct'];
            $data['total']['point']+=$v['point'];
        }
        return $data;
    }

    /**
     * 验证用户是否参加过该项目
     * @param $project_id
     * @param $user_id
     */
    public function getBuyProjectOrderCount($project_id=null,array $where=[]){
        if(is_null($project_id)){
            $rows = $this->groupBySelector(['project_id'],$where)->selectRaw('project_id, count(order_id) as ct');
            $data = [];
            foreach($rows as $v){
                $data[$v['project_id']] = $v['ct'];
            }
            return $data;
        }
        else{
            $where['project_id'] = $project_id;
            return $this->count($where);
        }
    }

    /**
     * 发货
     * @param $data
     */
    public function dispatched(array $data){
        $data['order_status'] = 'dispatched';
        return $this->update($data['order_id'],$data);
    }
}
