<?php

namespace library\service\user;

use library\service\sys\FlowNumbersService;
use support\Container;
use support\extend\Service;
use library\model\user\OrderModel;

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
        return $flowNumberServer->getFlowOrderNo($this->model->getTable(),$suffix);
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
     * 发货
     * @param $data
     */
    public function dispatched(array $data){
        $data['order_status'] = 'dispatched';
        return $this->update($data['order_id'],$data);
    }
}
