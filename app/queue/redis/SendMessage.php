<?php

namespace app\queue\redis;

use library\service\sys\SendMsgLogService;
use support\Container;
use Webman\RedisQueue\Consumer;
use support\persist\MailerInterface;
use support\extend\Log;

class SendMessage implements Consumer
{
    // 要消费的队列名
    public $queue = 'send_message';

    // 连接名，对应 config/redis_queue.php 里的连接`
    public $connection = 'default';

    /**
     * @var SendMsgLogService
     */
    private $service;

    public function __construct(SendMsgLogService $service)
    {
        $this->service = $service;
    }

    /**
     * 消费数据
     * @param $data {log_id,send_type,send_to,title,content,status}
     */
    public function consume($data)
    {
        try{
            if($data['send_type']=='mobile'){
                $this->sendMobileMsg($data);
            }
            else{
                $this->sendEmailMsg($data);
            }
        }
        catch (\Exception $e){
            Log::channel("message")->error($e->getMessage(),["type"=>"error"]);
        }
    }

    private function sendEmailMsg($sendMsgObj){
        $mailService = Container::get(\support\mailer\SwiftMailer::class);
        $res = $mailService->send($sendMsgObj['send_to'],$sendMsgObj['title'],$sendMsgObj['content']);
        if(!$res){
            Log::channel('message')->error($mailService->getErrorMsg(),['type'=>'email']);
            $this->service->updateAll(['log_id'=>$sendMsgObj['log_id']],['status'=>2,'result'=>$mailService->getErrorMsg()]);
        }
        else{
            $this->service->updateAll(['log_id'=>$sendMsgObj['log_id']],['status'=>1,'result'=>'发送成功']);
        }
        return $res;
    }

    private function sendMobileMsg($sendMsgObj){
        $smsService = Container::get(\support\mailer\Smsbao::class);
        $res = $smsService->sendMsg($sendMsgObj['send_to'],$sendMsgObj['content']);
        if(is_null($res)){
            Log::channel('message')->error('发送失败',['type'=>'mobile']);
            $this->service->updateAll(['log_id'=>$sendMsgObj['log_id']],['status'=>2,'result'=>'发送失败']);
        }
        elseif(!$res){
            Log::channel('message')->error($smsService->getError(),['type'=>'mobile']);
            $this->service->updateAll(['log_id'=>$sendMsgObj['log_id']],['status'=>2,'result'=>$smsService->getError()]);
        }
        else{
            $this->service->updateAll(['log_id'=>$sendMsgObj['log_id']],['status'=>1,'result'=>'发送成功']);
        }
    }
}