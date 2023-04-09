<?php


namespace library\logic;

use support\utils\DocParser;
use support\utils\Files;

/**
 * 反射类逻辑层
 * @author Kevin
 */
class ReflectionLogic
{
    /**
     * 获取指定模块下面所有授权的方法
     * @param string $app 模块
     * @param string $filter 过滤类型 {not_authorized,only_authorized}
     */
    public function getAppMenuMethodList(string $app,$filter=null){
        try{
            $data = [];
            $controllers = $this->getAppControllerList($app);
            foreach($controllers as $v){
                $list = $this->getControllerReflectionList($v['class'],$filter);
                if(!empty($list)){
                    foreach($list as $c){
                        $data[] = [
                            'controller'=>$v['class'],
                            'action'=>$c['method']->name,
                            'method'=>(isset($c['doc']['method'])?$c['doc']['method']:null),
                            'middleware'=>(isset($c['doc']['middleware'])?$c['doc']['middleware']:null),
                            'url'=>(isset($c['doc']['url'])?$c['doc']['url']:$v['url'].'/'.$c['method']->name),
                        ];
                    }
                }
            }
            return $data;
        }
        catch (\Exception $e){
            return [];
        }
    }



    /**
     * 获取模块下面的所有控制器列表
     * @param string $app
     */
    public function getAppControllerList(string $app){
        try{
            $data = [];
            $klass_path = sprintf('app\%s\controller',$app);
            $path = app_path($app.'/controller');
            $files = Files::getPathFiles($path);
            if(is_array($files)){
                foreach($files as $f1){
                    if(strpos($f1,'.php')!==false){
                        $name = str_replace('.php','',$f1);
                        $klass = $klass_path.'\\'.$name;
                        $name = lcfirst($name);
                        $data[] = [
                            'name'=>$name,
                            'url'=>'/'.$app.'/'.$name,
                            'class'=>$klass
                        ];
                    }
                    else{
                        $subFiles = Files::getPathFiles($path.'/'.$f1);
                        if(!empty($subFiles)){
                            foreach($subFiles as $f2){
                                $name = str_replace('.php','',$f2);
                                $klass = $klass_path.'\\'.$f1.'\\'.$name;
                                $name = lcfirst($name);
                                $data[] = [
                                    'name'=>$name,
                                    'url'=>'/'.$app.'/'.$name,
                                    'class'=>$klass
                                ];
                            }
                        }
                    }
                }
            }
            return $data;
        }
        catch (\Exception $e){
            return [];
        }
    }

    /**
     * 获取控制器下面的所有方法
     */
    public function getControllerMethodList(string $klass,$filter=null){
        $methods = $this->getControllerReflectionList($klass,$filter);
        $data = [];
        if(!empty($methods)){
            foreach($methods as $k=>$method){
                $data[] = $method['method']->name;
            }
        }
        return $data;
    }

    /**
     * 获取控制器下面的所有方法
     */
    public function getControllerReflectionList(string $klass,$filter=null){
        $reflection = new \ReflectionClass($klass);
        $methods = $reflection->getMethods(true);
        $data = [];
        foreach($methods as $k=>$method){
            if(!in_array($method->name,['__construct','beforeAction','afterAction'])){
                if(empty($filter)){
                    $data[] = ['method'=>$method,'doc'=>[]];
                }
                else{
                    $doc = $this->getMethodDoc($method);
                    if($filter=='only_authorized'){
                        if(!empty($doc['authorized'])){
                            $data[] = ['method'=>$method,'doc'=>$doc];
                        }
                    }
                    elseif($filter=='not_authorized'){
                        if(!isset($doc['authorized'])){
                            $data[] = ['method'=>$method,'doc'=>$doc];
                        }
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 验证URL是否已授权
     * @param $url
     * @param string|null $method
     * @return bool
     */
    public function checkUrlIsAuthorized(string $url,string $method=null){
        try{
            $url = trim($url,'/');
            list($m,$c,$a) = explode('/',$url);
            $klass = sprintf('app\%s\controller\%s',$m,ucfirst($c));
            $reflection = new \ReflectionClass($klass);
            $methodObj = $reflection->getMethod($a);
            $doc = $methodObj->getDocComment();
            $docParse = new DocParser();
            $res = $docParse->parse($doc);
            if (isset($res['authorized']) && !empty($res['authorized'])) {
                if($res['authorized']=='YES'){
                    if(!empty($method) && !empty($res['method'])){
                        if(strtolower($method)==strtolower($res['method'])){
                            return true;
                        }
                    }
                    else{
                        return true;
                    }
                }
                return false;
            }
            return null;
        }
        catch (\Exception $e){
            return null;
        }
    }

    /**
     * 获取反射方法获取的注释文档
     * @param \ReflectionMethod $method
     * @return array
     */
    private function getMethodDoc(\ReflectionMethod $method){
        $doc = $method->getDocComment();
        $docParse = new DocParser();
        return $docParse->parse($doc);
    }

    /**
     * 获取类下面的所有方法列表
     * @param $klass
     */
    private function getClassMethodList($klass){
        $reflection = new \ReflectionClass($klass);
        $methods = $reflection->getMethods(true);
        $data = [];
        foreach($methods as $v){
            $doc = $this->getMethodDoc($v);
            $data[] = [
                'name'=>$v->name,
                'doc'=>(isset($doc['description'])?$doc['description']:(isset($doc['long_description'])?$doc['long_description']:null))
            ];
        }
        return $data;
    }

    public function getTaskFileList(){
        try{
            $data = [];
            $klass_path = sprintf('library\\task');
            $path = library_path('task');
            $files = Files::getPathFiles($path);
            if(is_array($files)){
                foreach($files as $f1){
                    if(strpos($f1,'.php')!==false){
                        $name = str_replace('.php','',$f1);
                        $klass = $klass_path.'\\'.$name;
                        $methods = $this->getClassMethodList($klass);
                        $name = lcfirst($name);
                        $data[] = [
                            'name'=>$name,
                            'class'=>$klass,
                            'method'=>$methods
                        ];
                    }
                    else{
                        $subFiles = Files::getPathFiles($path.'/'.$f1);
                        if(!empty($subFiles)){
                            foreach($subFiles as $f2){
                                $name = str_replace('.php','',$f2);
                                $klass = $klass_path.'\\'.$f1.'\\'.$name;
                                $methods = $this->getClassMethodList($klass);
                                $name = lcfirst($name);
                                $data[] = [
                                    'name'=>$name,
                                    'class'=>$klass,
                                    'method'=>$methods
                                ];
                            }
                        }
                    }
                }
            }
            return $data;
        }
        catch (\Exception $e){
            return [];
        }
    }

    /**
     * 获取任务下面的方法
     * @param string $klass
     */
    public function getTaskList(array $filterJobCmd=[]){
        $fileTaskList = $this->getTaskFileList();
        $data = [];
        foreach($fileTaskList as $v){
            if(!empty($v['method'])){
                foreach($v['method'] as $c){
                    $cmd = $v['class'].':'.$c['name'];
                    if(!empty($filterJobCmd) && in_array($cmd,$filterJobCmd)){
                        continue;
                    }
                    $data[] = [
                        'name'=>$v['name'],
                        'class'=>$v['class'],
                        'action'=>$c['name'],
                        'doc'=>$c['doc'],
                        'cmd'=>$cmd
                    ];
                }
            }
        }
        return $data;
    }
}