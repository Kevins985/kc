<?php

namespace app\command;

use library\service\sys\IpVisitService;
use support\Container;
use support\extend\Redis;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CacheCommand extends Command
{
    private $type;

    protected function configure()
    {
        $this->setName('cache')
             ->setDescription('缓存初始化')
             ->addArgument('type', InputArgument::OPTIONAL, '操作类型')
             ->addArgument('key', InputArgument::OPTIONAL, '操作Key');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument("type");
        $key = $input->getArgument("key");
        if(empty($type) || !in_array($type,['init','delete'])){
            $output->writeln('未指定操作类型(init,delete)');
            return self::FAILURE;
        }
        elseif(empty($key)){
            $output->writeln('必须指定操作的key');
            return self::FAILURE;
        }
        else{
            if($type=='delete'){
                if($key=='all'){
                    Redis::flushall();
                }
                else{
                    Redis::del($key);
                }
            }
            else{
                if($key=='all'){
                    $this->initIpBlacklist();
                }
                else{

                }
                $output->writeln('缓存初始化:'.$key);
            }
            return self::SUCCESS;
        }
    }

    /**
     * 初始化ip黑名单
     */
    private function initIpBlacklist($cache_key='ip_blacklist'){
        $ipVisitService = Container::get(IpVisitService::class);
        $blacklist = $ipVisitService->getIpBlacklist();
        Redis::hMSet($cache_key,$blacklist);
    }
}