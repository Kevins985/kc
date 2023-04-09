<?php

namespace support\utils;

use support\extend\Db;
use support\exception\VerifyException;

class DbBackup
{
	private $maxLimit     = 1500;              //设置最大读取数据条数(条)
	private $partSize     = 100000;              //分卷大小(KB)
	private $fileName     = null;              //当前备份数据的文件名
	private $part         = 1;                 //分卷号初始值
	private $totalSize    = 0;                 //备份数据共占字节数
	private $dir          = null;              //备份路径
	private $fPrefix      = 'mutual';          //备份文件名前缀
	private $fExtend      = '.sql';            //备份文件扩展名
    private $conn         = null;
    private $adapter      = 'mysql';

	//构造函数
	public function __construct()
	{
	    $this->dir = resource_path('backup');
	    $this->conn =  Db::getInstance($this->adapter);
	}

    /**
     * 获取数据库所有表
     * @return array
     */
	public function getTableList(){
        return $this->conn->getTableList(false);
    }

	//备份的文件列表
	public function getFileList()
	{
		$fileArray  = [];
		$dirRes = opendir($this->dir);
		while( false !== ($fileName = readdir($dirRes)) )
		{
			if($fileName[0] == '.')
			{
				continue;
			}
			if(stripos($fileName,$this->fPrefix) !== false && stripos($fileName,$this->fExtend) !== false)
				$key = 'system';
			else
				$key = 'unsystem';
			$fileArray[$fileName] = array(
				'name' => $fileName,
				'type'=>$key,
				'size' => number_format(filesize($this->dir.'/'.$fileName)/1024,1),
				'time' => date('Y-m-d H:i:s', filemtime($this->dir.'/'.$fileName) )
			);
			krsort($fileArray);
		}
		return $fileArray;
	}

    /**
     * 下载文件
     * @param $file 备份文件名
     */
	public function getBackupUrl($file)
	{
	    if(!file_exists($this->dir.'/'.$file)){
	        throw new VerifyException("备份文件不存在");
        }
		return ($this->dir.'/'.$file);
	}

    /**
     * 删除数据备份文件
     * @param $file 备份文件名
     * @return bool
     */
	public function delete($file)
	{
        if(!file_exists($this->dir.'/'.$file)){
            throw new VerifyException("备份文件不存在");
        }
        return Files::unlink($this->dir.'/'.$file);
	}

    /**
     * 数据库还原
     * @param $file 备份文件名
     */
	public function reduction($file)
	{
        $fileName = $this->dir.'/'.$file;
        return $this->parseSQL($fileName);
	}

	//解析备份文件中的SQL
	function parseSQL($fileName)
	{
		//忽略外键约束
//		$this->conn->db->select("SET FOREIGN_KEY_CHECKS = 0;");
		$fhandle  = fopen($fileName,'r');
		while(!feof($fhandle))
		{
			$lstr = fgets($fhandle);     //获取指针所在的一行数据
			//判断当前行存在字符
			if(isset($lstr[0]) && $lstr[0]!='#')
			{
				$prefix = substr($lstr,0,2);  //截取前2字符判断SQL类型
				switch($prefix)
				{
					case '--' :
					case '//' :
					{
						continue 2;
					}
					case '/*':
					{
						if(substr($lstr,-5) == "*/;\r\n" || substr($lstr,-4) == "*/\r\n")
							continue 2;
						else
						{
							$this->skipComment($fhandle);
							continue 2;
						}
					}

					default :
					{
						$sqlArray[] = trim($lstr);
						if(substr(trim($lstr),-1) == ";")
						{
							$sqlStr   = join($sqlArray);
							$sqlArray = [];
							$this->conn->db->select($sqlStr);
							//回调函数
							$this->actionCallBack($fileName);
						}
					}
				}
			}
		}
		//开启外键约束
		//$this->conn->db->select("SET FOREIGN_KEY_CHECKS = 1;");
        return true;
	}

	//略过注释
	private function skipComment($fhandle)
	{
		$lstr = fgets($fhandle,4096);
		if(substr($lstr,-5) == "*/;\r\n" || substr($lstr,-4) == "*/\r\n")
			return true;
		else
			$this->skipComment($fhandle);
	}

    /**
     * 动作执行回调函数
     * @param $mess
     */
	private function actionCallBack($mess)
	{
		//防止超时
		set_time_limit(60);
	}

    /**
     * 设置备份的路径
     * @param $dir
     */
	public function setDir($dir)
	{
		$this->dir = $dir;
	}

    /**
     * 设置分卷大小(KB)
     * @param $size
     */
	public function setPartSize($size)
	{
		$this->partSize = $size;
	}

    /**
     * 设置最大读取数据条数(条)
     * @param $maxLimit
     */
	public function setMaxLimit($maxLimit)
	{
		$this->maxLimit = $maxLimit;
	}

    /**
     * @param $table
     */
    private function checkTableLimit($table){
        //某个表分段
        if(in_array($table,['common_operation_logs'])){
//            $this->setMaxLimit(100);
        }
    }

    /**
     * 备份数据库
     */
    public function backupDatabase()
    {
        $name = $this->fPrefix.'_'.date('Ymd');
        $filename = $this->getFilename($name);
        $config = $this->conn->getConfig();
        $cmd = 'mysqldump';
        $process=$cmd." -h".$config['host']." -u".$config['username']."  -p".$config['password']."  ".$config['database']." >".$filename;
        $res = system($process,$output);
        if($res!=0){
            throw new \Exception($output);
        }
        return true;
    }

	//执行备份
	public function backup($tables)
	{
		//循环表
		foreach($tables as $name)
		{
			$this->checkTableLimit($name);
			$tableStruct = $this->createStructure($name);//生成表结构
			$sumTime     = $this->countTime($name);      //计算写入文件的总次数
			//生成表数据
			$tableData = '';
			for($time = 0;$time < $sumTime;$time++)
			{
				$offset = $time * $this->maxLimit;        //计算读取开始偏移值
				$data   = $this->getData($name,$offset);  //根据偏移值获取数据
				//数据存在
				if($data)
				{
					$tableData = "INSERT INTO `".$name."` VALUES\r\n";
				}
				foreach($data as $rs)
				{
					$tableData .= "(";
					foreach($rs as $key => $val)
					{
						if(is_int($key)) continue;
						$tableData .= '\''.addslashes(str_replace(array("\n","\r\n","\r","\t"),"",$val)).'\',';
					}
					$tableData  = rtrim($tableData,',');
					$tableData .= "),\r\n";
				}
				if($tableData)
				{
					$tableData  = rtrim($tableData,",\r\n");
					$tableData .= ";\r\n\r\n";
				}
				//表结构和$time次的表数据 总和
				$content = $tableStruct.$tableData;
				//判断文件是否溢出,如果溢出则分卷
				if($this->checkOverflow(strlen($content)))
				{
					$this->part+=1;
				}
				//清空数据
				$tableStruct = '';
				$tableData   = '';
				$this->writeFile($this->getFilename(),$content);//写入文件
			}
			//回调函数
			$this->actionCallBack($name);
		}
		return true;
	}

    /**
     * 清空数据表
     * @param $tables
     */
	public function truncate($tables)
    {
        $this->backup($tables);
        foreach($tables as $name)
        {
            $sql = "TRUNCATE TABLE `".$name."`;";
            $this->conn->db->select($sql);
        }
        return true;
    }

    //写入文件
    private function writeFile($fileName,$content)
    {
        $fileObj = new Files($fileName,'a+');
        $fileObj->write($content);
    }

	//检测文件是否存放的数据是否溢出
	private function checkOverflow($cSize)
	{
		$this->totalSize+=$cSize;
		if($this->totalSize >= ($this->partSize<<10)*$this->part)
			return true;
		else
			return false;
	}

    /**
     * 生成文件名
     * @return string
     */
	public function getFilename($name=null)
	{
		if($this->fileName === null)
		{
		    if(empty($name)){
                //获取当前时间:年月日_时分秒
                $nowTime = date('YmdHis');
                $name = $this->fPrefix.'_'.$nowTime.'_'.rand(10000,99999);
            }
            $this->fileName = $this->dir.'/'.$name;
			return $this->fileName.'_'.$this->part.$this->fExtend;
		}
		else
			return $this->fileName.'_'.$this->part.$this->fExtend;
	}

    /**
     * 获取分段数据(数据库)
     * @param $name
     * @param int $offset
     * @return array
     */
	private function getData($name,$offset=0)
	{
		//获取从$start至$limitNum这段数据
		$sql   = 'SELECT * FROM '.$name.' LIMIT '.$offset.','.$this->maxLimit;
		return $this->conn->db->select($sql);
	}

    /**
     * 计算$name数据表写入次数(数据库)
     * @param $name
     * @return false|float|int
     */
    private function countTime($name)
	{
		//获取数据表总的数据条数
		$sql      = 'SELECT COUNT(*) as num FROM '.$name;
		$numArray = $this->conn->db->select($sql);
		$dataNum  = $numArray[0]->num;
		//计算读取的分页数
		if($dataNum > 0)
			return ceil($dataNum/$this->maxLimit);
		else
			return 1;
	}

    /**
     * 创建$name数据表结构的SQL语句(数据库)
     * @param $name
     * @return string
     */
    private function createStructure($name)
	{
		//获取表结构创建语句
		$tableArray  = $this->conn->db->select('SHOW CREATE TABLE `'.$name.'`');
		$tableRow    = (array)$tableArray[0];
		$tableString = $tableRow['Create Table'];
		//SQL初始化拼接字符串
		$bakContent = "DROP TABLE IF EXISTS `".$name."`;\r\n".$tableString.";\r\n\r\n";
		return $bakContent;
	}
}