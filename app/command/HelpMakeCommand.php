<?php

namespace app\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class HelpMakeCommand extends Command
{
    protected function configure()
    {
        $this->setName('help:make')
            ->setDescription('一键生成的帮助文档');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $headers = ['类型',"操作命令", '描述'];
        $rows = [];
        $rows[] = ["控制器","php cli make:controller","显示后台所有可以创建的controller列表"];
        $rows[] = ["控制器","php cli make:controller all","创建所有现存表的controller文件"];
        $rows[] = ["控制器","php cli make:controller {name}","创建{指定表}的controller文件"];
        $rows[] = ["验证器","php cli make:validator","显示所有可以创建的validator列表"];
        $rows[] = ["验证器","php cli make:validator all","创建所有现存表的validator文件"];
        $rows[] = ["验证器","php cli make:validator {name}","创建{指定表}的validator文件"];
        $rows[] = ["服务层","php cli make:service","显示所有可以创建的service列表"];
        $rows[] = ["服务层","php cli make:service all","创建所有现存表的service文件"];
        $rows[] = ["服务层","php cli make:service {name}","创建{指定表}的service文件"];
        $rows[] = ["数据模型层","php cli make:model","显示所有可以创建的model列表"];
        $rows[] = ["数据模型层","php cli make:model all","创建所有现存表的model文件"];
        $rows[] = ["数据模型层","php cli make:model modify","修复所有现存model文件的属性fillable"];
        $rows[] = ["数据模型层","php cli make:model {name}","创建{指定表}的model文件"];
        $rows[] = ["数据模型层","php cli make:model {name} list","查询{指定表}所有字段在model属性fillable是否存在"];
        $rows[] = ["数据模型层","php cli make:model {name} modify","修复{指定表}model属性fillable"];
        $rows[] = ["查看修改记录","php cli make:modify controller {app}","查看{指定终端}Controller的3天内文件修改记录"];
        $rows[] = ["查看修改记录","php cli make:modify service","查看Service的3天内文件修改记录"];
        $rows[] = ["查看修改记录","php cli make:modify model","查看Model的3天内文件修改记录"];
        $rows[] = ["查看修改记录","php cli make:modify validator","查看Validator的3天内文件修改记录"];
        $rows[] = ["查看修改记录","php cli make:modify table","查看数据表结构的修改记录"];
        $table = new Table($output);
        $table->setHeaders($headers);
        $table->setRows($rows);
        $table->render();
        return self::SUCCESS;
    }
}