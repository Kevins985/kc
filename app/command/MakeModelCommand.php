<?php

namespace app\command;

use support\Container;
use support\extend\Db;
use support\make\Model;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class MakeModelCommand extends Command
{
    /**
     * 数据库连接
     * @var string
     */
    private $adapter = 'mysql';

    protected function configure()
    {
        $this->setName('make:model')
              ->setDescription('一键生成Model')
              ->addArgument('name', InputArgument::OPTIONAL, '操作表')
              ->addArgument('type', InputArgument::OPTIONAL, '操作类型');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument("name");
        if(empty($name)){
            return $this->list($output);
        }
        elseif($name=="modify"){
            return $this->modifyAllTable($output);
        }
        else{
            $type = $input->getArgument("type");
            if(empty($type)){
                return $this->create($name,$output);
            }
            else{
                return $this->modify($name,$type,$output);
            }
        }
    }

    /**
     * 展示所有可以创建的model列表
     * @param OutputInterface $output
     * @return int
     */
    private function list(OutputInterface $output)
    {
        $make = new Model($this->adapter);
        $tableLists = $make->getList();
        if(empty($tableLists)){
            $output->writeln('暂无需要创建的model类');
            return self::FAILURE;
        }else{
            $headers = ['数据库表', '文件地址'];
            $rows = [];
            foreach ($tableLists as $v){
                $path = $make->getFilePath($v);
                $rows[] = [$v,$path];
            }
            $table = new Table($output);
            $table->setHeaders($headers);
            $table->setRows($rows);
            $table->render();
            return self::SUCCESS;
        }
    }

    /**
     * 创建model层对象
     * @param string $name 表名
     * @param OutputInterface $output
     * @return int
     */
    private function create(string $name, OutputInterface $output){
        $conn = Db::connection($this->adapter);
        try{
            $conn->beginTransaction();
            $make = new Model($this->adapter);
            $tableLists = $make->getList();
            if($name==="all"){
                if(empty($tableLists)){
                    throw new \Exception('暂无需要添加的数据');
                }
                foreach ($tableLists as $table){
                    $res = $this->createMakeLogs($make,$table);
                    if(!$res){
                        throw new \Exception('创建失败:'.$table);
                    }
                    $output->writeln('创建成功:'.$table);
                }
            }
            else{
                if(in_array($name,$tableLists)){
                    $res = $this->createMakeLogs($make,$name);
                    if(!$res){
                        throw new \Exception('创建失败:'.$name);
                    }
                    $output->writeln('创建成功:'.$name);
                }
                else{
                    throw new \Exception('指定的表不存在:'.$name);
                }
            }
            $conn->commit();
            return self::SUCCESS;
        }
        catch (\Exception $e){
            $conn->rollBack();
            $output->writeln($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * 修复所有表结构fillable属性
     * @param OutputInterface $output
     * @return int
     */
    private function modifyAllTable(OutputInterface $output){
        try{
            $make = new Model($this->adapter);
            $tableLists = $make->db->getTableList();
            foreach ($tableLists as $table){
                $filePath = $make->getFilePath($table);
                if(file_exists($filePath)){
                    $class = $make->getFileClass($table);
                    $modelObj = Container::get($class);
                    $fields = $make->db->getTableColumns($table,true);
                    $fillList = $modelObj->getFillable();
                    $is_modify = false;
                    foreach ($fields as $v){
                        if(!in_array($v,$fillList) && !in_array($v,["created_time","updated_time"])){
                            $is_modify = true;
                        }
                    }
                    if($is_modify){
                        $res = $this->modifyModelFillable($modelObj,$filePath,$fields);
                        if(!$res){
                            throw new \Exception("修改model文件数据失败");
                        }
                        $conn = Db::connection($this->adapter);
                        $conn->table('sys_make_logs')->where('type','model')->where("table",$table)->update(['is_modify'=>0]);
                        $output->writeln('操作成功:'.$table);
                    }
                    else{
                        $output->writeln("暂无修改:".$table);
                    }
                }
            }
            return self::SUCCESS;
        }
        catch (\Exception $e){
            $output->writeln($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * 修改model层对象
     * @param string $name 表名
     * @param string $type 操作类型
     * @param OutputInterface $output
     * @return int
     */
    private function modify(string $name,string $type,OutputInterface $output){
        try{
            $make = new Model($this->adapter);
            $class = $make->getFileClass($name);
            $modelObj = Container::get($class);
            if(!$modelObj instanceof \support\extend\Model){
                throw new \Exception("暂未获取到".$name."Model对象");
            }
            if($type==="list"){
                $fields = $make->db->getTableColumns($name,true);
                $fillList = $modelObj->getFillable();
                $headers = ['字段名', '是否存在'];
                $rows = [];
                foreach ($fields as $v){
                    $exists = in_array($v,$fillList)?'存在':'不存在';
                    $rows[] = [$v,$exists];
                }
                $table = new Table($output);
                $table->setHeaders($headers);
                $table->setRows($rows);
                $table->render();
            }
            elseif($type==="modify"){
                $filePath = $make->getFilePath($name);
                $fields = $make->db->getTableColumns($name,true);
                $res = $this->modifyModelFillable($modelObj,$filePath,$fields);
                if(!$res){
                    throw new \Exception("修改model文件数据失败");
                }
                $conn = Db::connection($this->adapter);
                $conn->table('sys_make_logs')->where('type','model')->where("table",$name)->update(['is_modify'=>0]);
           }
            else{
                throw new \Exception("类型操作只包含(list、modify)");
            }
            $output->writeln('操作成功:'.$name);
            return self::SUCCESS;
        }
        catch (\Exception $e){
            $output->writeln($e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * 修改数据库填充部分数据
     * @param \support\extend\Model $modelObj
     * @param string $filePath
     * @param array $dbFields
     * @return bool
     */
    private function modifyModelFillable(\support\extend\Model $modelObj,string $filePath,array $dbFields){
        $content = file_get_contents($filePath);
        $fillable='fillable=['.PHP_EOL;
        foreach($dbFields as $name){
            if(!in_array($name,['created_time','updated_time'])){
                $fillable.="\t\t".'"'.$name.'",'.PHP_EOL;
            }
        }
        $fillable.="    ]";
        preg_match('/fillable\s?=\s?\[(.*?)\]/ius',$content,$match);
        if(isset($match[1]) && !empty($match[1])){
            $content = preg_replace('/fillable\s?=\s?\[(.*?)\]/ius',$fillable,$content);
            return file_put_contents($filePath,$content);
        }
        return false;
    }

    /**
     * 添加创建表的日志记录
     * @param Model $make
     * @param $table
     * @return bool|int
     * @throws \Exception
     */
    private function createMakeLogs(Model $make,$table){
        $fileClass = $make->getFileClass($table);
        $addDate = date('Y-m-d');
        $data = ["type"=>'model',"table"=>$table,'file_class'=>$fileClass,'make_date'=>$addDate,'created_time'=>time(),'updated_time'=>time()];
        $conn = Db::connection($this->adapter);
        $res = $conn->table('sys_make_logs')->insert($data);
        if($res){
            return $make->createFile($table);
        }
        return 0;
    }
}