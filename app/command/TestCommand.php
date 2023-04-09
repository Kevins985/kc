<?php

namespace app\command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'test';
    protected static $defaultDescription = '测试脚本';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return self::SUCCESS;
    }
}