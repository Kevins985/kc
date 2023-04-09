<?php

namespace app\command;

use library\logic\MenusLogic;
use support\Container;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class InitDataCommand extends Command
{
    /**
     * 数据库连接
     * @var string
     */
    private $adapter = 'mysql';

    protected function configure()
    {
        $this->setName('init:data')
             ->setDescription('初始化数据')
             ->addArgument('type', InputArgument::OPTIONAL, '操作类型')
             ->addArgument('app', InputArgument::OPTIONAL, '模块');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument("type");
        if(empty($type)){
            $output->writeln('未指定操作类型');
            return self::FAILURE;
        }
        else{
            $output->writeln('初始化:'.$type);
            if($type=='route'){
                $app = $input->getArgument("app");
                $this->initEmptyRoute($output,$app);
            }
            else{
                $output->writeln('暂无该类型:'.$type);
            }
            return self::SUCCESS;
        }

    }

    /**
     * 添加数据到权限表
     * @param OutputInterface $output
     */
    private function initEmptyRoute(OutputInterface $output,$app='backend'){
        if(!in_array($app,['backend','api'])){
            $output->writeln("暂无该类型:".$app);
        }
        else{
            $menuLogic = Container::get(MenusLogic::class);
            $num = $menuLogic->initAppRouteMethod($app,true);
            if($num){
                $output->writeln("添加权限数量:".$num);
            }
        }
    }
}