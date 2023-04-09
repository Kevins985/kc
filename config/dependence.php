<?php

use Psr\Container\ContainerInterface;

return [
    support\persist\QueueInterface::class => function(ContainerInterface $container) {
        return $container->make(support\queue\RedisQueue::class);
    },
    support\persist\MailerInterface::class => function(ContainerInterface $container) {
        return $container->make(support\mailer\SwiftMailer::class);
    }
];