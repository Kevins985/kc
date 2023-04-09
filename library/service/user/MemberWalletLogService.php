<?php

namespace library\service\user;

use support\extend\Service;
use library\model\user\MemberWalletLogModel;

class MemberWalletLogService extends Service
{
    public function __construct(MemberWalletLogModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取所有的事件
     * @param null $num
     */
    public function getEventList($num=null){
        $list = [
            0=>'后台操作',
            1=>'从钱包提现',
            2=>'从钱包支付',
            5=>'其他扣款',
            10=>'注册赠送',
            11=>'充值到钱包',
            12=>'退款到钱包',
            13=>'充值奖励',
            14=>'邀请奖励',
            15=>'订单收益',
            16=>'推广收益',
            17=>'订单本金'
        ];
        if(!empty($num)){
            return isset($list[$num])?$list[$num]:null;
        }
        return $list;
    }

    /**
     * 获取所有类型的统计数据
     */
    public function getGroupAllTypeCnt($params=[])
    {
        $selector = $this->groupBySelector(['type'],$params)->selectRaw('type,count(*) as ct,sum(`change`) money');
        $rows = $selector->get()->toArray();
        $data = ['total'=>['ct'=>0,'money'=>0]];
        foreach($rows as $v){
            $data[$v['type']] = $v;
            $data['total']['ct']+=$v['ct'];
            $data['total']['money']+=$v['money'];
        }
        return $data;
    }
}
