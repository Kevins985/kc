<?php

namespace app\process;

use library\logic\MessageLogic;
use library\service\user\RechargeOrderService;
use library\service\user\WithdrawOrderService;
use support\Container;
use support\exception\BusinessException;
use support\extend\Log;
use Workerman\Connection\TcpConnection;

class Websocket
{
    private $connectAry = [];

    public function onConnect(TcpConnection $connection)
    {
        $data =  [
            'id'=>$connection->id,
            'remoteIp'=>$connection->getRemoteIp(),
            'workerId'=>$connection->worker->workerId
        ];
        Log::channel("websocket")->info("onConnect",$data);
    }

    public function onWebSocketConnect(TcpConnection $connection, $http_buffer)
    {
        $data =  [
            'id'=>$connection->id,
            'remoteIp'=>$connection->getRemoteIp(),
            'workerId'=>$connection->worker->workerId
        ];
        Log::channel("websocket")->info("onWebSocketConnect",$data);
        $connection->send("success");
    }

    /**
     * 获取用户连接对象
     * @param $uid
     */
    private function getUserConnection($uid){

    }

    public function onMessage(TcpConnection $connection, $data)
    {
        $cdata =  [
            'id'=>$connection->id,
            'remoteIp'=>$connection->getRemoteIp(),
            'workerId'=>$connection->worker->workerId
        ];
        try{
            $res = json_decode($data,true);
            if(!is_array($res) || !isset($res['type'])){
                throw new BusinessException('接收信息错误');
            }
            $cdata = array_merge($cdata,$res);
            Log::channel("websocket")->info("onMessage",$cdata);
            if($res['type']=='ping'){
                $connection->send(json_encode(['type'=>'ping','msg'=>'connect success']));
            }
            elseif($res['type']=='login'){
                $connection->uid = $res['user_id'];
                $this->connectAry[$res['user_id']] = $connection;
                $connection->send(json_encode(['type'=>'login','msg'=>'login success']));
            }
            elseif($res['type']=='sendMsg'){
                if($res['mer_user_id'] == $connection->uid){
                    throw new BusinessException('不能给自己发送信息');
                }
                else{
                    $result = $this->createMessage($res);
                    $uid = $result['message']['user_id']??0;
                    if(isset($this->connectAry[$uid])){
                        unset($result['message']);
                        $result['type'] = 'receiveMsg';
                        $connection->send(json_encode($result));
                        $this->connectAry[$uid]->send(json_encode($result));
                    }
                }
            }
            elseif($res['type']=='backend_message'){
                $data = $this->getBackendMessageData();
                $connection->send(json_encode($data));
            }
            else{
                throw new BusinessException("暂无该类型");
            }
        }
        catch (\Throwable $e){
            $connection->send(json_encode(['type'=>'error','msg'=>$e->getMessage()]));
        }
    }

    public function onClose(TcpConnection $connection)
    {
        $data =  [
            'id'=>$connection->id,
            'remoteIp'=>$connection->getRemoteIp(),
            'workerId'=>$connection->worker->workerId
        ];
        Log::channel("websocket")->info("onClose",$data);
    }

    /**
     * @param $data {message_type,user_id,identity,content,message_id,mer_user_id,to_user_id}
     * @throws BusinessException
     */
    private function createMessage($data){
        $messageLogic = Container::get(MessageLogic::class);
        $res = $messageLogic->createKefuMessage($data);
        if(empty($res)){
            throw new BusinessException('创建信息失败');
        }
        $data = $res->toArray();
        $data['member'] = $res->member()->get(['user_id','nickname','photo_url']);
        if(isset($data['message_id']) && !empty($data['message_id'])){
            $message = $res->message()->get(['user_id','mer_user_id']);
        }
        else{
            $message = ['user_id'=>$data['user_id'],'mer_user_id'=>$data['mer_user_id']];
        }
        $data['message'] = $message;
        return $data;
    }

    private function getBackendMessageData(){
        $msg = '';
        $withdrawOrderService = Container::get(WithdrawOrderService::class);
        $withdraw_num = $withdrawOrderService->count(['status'=>0]);
        if($withdraw_num>0){
            $msg.=',待审核提现订单+'.$withdraw_num;
        }
        $rechargeOrderService = Container::get(RechargeOrderService::class);
        $recharge_num = $rechargeOrderService->count(['status'=>0]);
        if($recharge_num>0){
            $msg.=',待审核充值订单+'.$recharge_num;
        }
        $order_num = 0;
        $data = [
            'type'=>'backend_message',
            'withdraw_num'=>$withdraw_num,
            'recharge_num'=>$recharge_num,
            'order_num'=>$order_num,
            'msg'=>$msg
        ];
        return $data;
    }
}
