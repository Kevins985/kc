<?php

namespace support\persist;

interface QueueInterface
{
    public function getQueueName(int $id);
    public function getQueueAdapter(int $id);
    public function send(int $queueID,array $data,int $delay=0,array $headers=[]);
}