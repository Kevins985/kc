<?php


namespace library\task;


class Message
{
    /**
     * 同步邮件队列中的数据
     */
    public function syncMailQueue($count=50){
        return "同步邮箱";
    }
}