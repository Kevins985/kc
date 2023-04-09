<?php

namespace support\persist;

interface MailerInterface
{
    public function send($mailto,$mail_title,$mail_body,$attach=[]);
    public function sendAll(array $mailto,$mail_title,$mail_body,$attach=[]);
    public function getErrorMsg();
}