<?php

namespace app\command;

use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use library\logic\auth\AuthLogic;
use library\service\MenuService;
use library\service\user\UserService;
use Symfony\Component\Console\Command\Command;
use support\extend\Container;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use support\extend\Casbin;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;
use xingwenge\canal_php\Fmt;

class MysqlDiffCommand extends Command
{
    protected function configure()
    {
        $this->setName('tool:mysqldiff')
            ->setDescription('mysql对比工具')
            ->addArgument('type', InputArgument::OPTIONAL, 'type');
    }

    private function getDevPath($db='waihui'){
        $data = [
            "host" => "127.0.0.1",
            "user" => "root",
            "pwd" => "root",
            "db" => $db
        ];
        return '-1 '.$data['user'].':'.$data['pwd'].'@'.$data['host'].'~'.$data['db'].'#3306';
    }

    private function getTestPath($db='test'){
        $data = [
            "host" => "127.0.0.1",
            "user" => "root",
            "pwd" => "root",
            "db" => $db
        ];
        return '-2 '.$data['user'].':'.$data['pwd'].'@'.$data['host'].'~'.$data['db'].'#3306';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument("type");
        if(empty($type)){
            $output->writeln("请输入匹配的类型");
            return self::FAILURE;
        }
        if($type=='test'){
            $command = '/usr/local/mysqldiff/mysqldiff '.$this->getDevPath().' '.$this->getTestPath();
            $output->writeln($command.PHP_EOL);
            $res = @shell_exec($command);
            file_put_contents(runtime_path('change.sql'), $res);
            echo $res;
        }
        return self::SUCCESS;
    }
}
