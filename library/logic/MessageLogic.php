<?php

namespace library\logic;
use library\service\user\MessageService;
use library\service\user\MessageRecordService;
use support\exception\BusinessException;
use support\extend\Logic;

class MessageLogic extends Logic
{
    /**
     * @Inject
     * @var MessageService
     */
    public $messageService;
    /**
     * @Inject
     * @var MessageRecordService
     */
    public $recordService;

    /**
     * 创建信息
     * @param $data {type,message_type,user_id,identity,content,message_id|mer_user_id}
     * @see $message_type:消息类型:0=富文本,1=图片,2=文件,3=商品卡片,4=订单卡片
     */
    public function createKefuMessage(array $data){
        try{
            $data['type'] = 2;
            if(isset($data['message_id']) && !empty($data['message_id'])){
                $res = $this->recordService->create($data);
                if(!empty($res)){
                    $this->messageService->update($data['message_id'],['content'=>$data['content']]);
                }
                return $res;
            }
            else{
                if(empty($data['mer_user_id'])){
                    throw new BusinessException("商户客服ID不存在");
                }
                $messageObj = $this->messageService->fetch(['user_id'=>$data['user_id'],'mer_user_id'=>$data['user_id'],'type'=>2]);
                if(empty($messageObj)){
                    $messageObj = $this->messageService->create($data);
                }
                if(empty($messageObj)){
                    throw new BusinessException("创建信息失败");
                }
                $data['message_id'] = $messageObj['message_id'];
                return $this->recordService->create($data);
            }
        }
        catch (\Exception $e){
            throw $e;
        }
    }


}
