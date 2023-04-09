<?php

namespace support\utils;
use \ReflectionClass;

/**
 * 反射类处理
 * @author Kevin
 */
class Reflection {
    
    /**
     * 获取反射类
     * @var ReflectionClass 
     */
    private $reflection = null;
    
    private $className = null;
    
    public function __construct($className) {
        $this->reflection = new ReflectionClass($className);
        $this->className = $className;
    }
    
    /**
     * 获取类的注释
     */
    public function getClassDocComment(){
        $doc = $this->reflection->getDocComment();
        $parser = new DocParser();
        return $parser->parse($doc);
    }
    
    /**
     * 获取一个方法
     * @param string $methodName 方法名
     */
    public function getMethodObject($methodName){
        return $this->reflection->getMethod($methodName);
    }
    
    /**
     * 获取一个方法的注释
     * @param string $methodName 方法名
     */
    public function getMethodDocComment($methodName){
        $doc = $this->getMethodObject($methodName)->getDocComment();
        return $this->getParseDoc($doc);
    }
    
    /**
     * 解析注视
     * @param type $doc
     */
    public function getParseDoc($doc){
        $parser = new DocParser();
        return $parser->parse($doc);
    }
    
    /**
     * 获取类的所有方法
     * @param string $match 匹配的值
     * @param array $filter  过滤的数组
     * @return array
     */
    public function getMethodListObj($match='Action',$filter=[]){
        $list = $this->reflection->getMethods();
        if(!empty($match)){
            foreach($list as $key=>$obj){
                if (preg_match('/'.$match.'/', $obj->name)) {
                    unset($list[$key]);
                }
                else{
                    $actionName = $obj->name;
                    if(!empty($filter) && in_array($actionName, $filter)){
                        unset($list[$key]);
                    }
                }
            }
        }
        return $list;
    }
    
    /**
     * 获取包含指定注释类型的方法名
     * @param string $type  匹配的值
     * @param array $filter  过滤的数组
     * @param type $type
     */
    public function getDocMethodListName($type='authorized',$filter=[]){
        $list = $this->getMethodListObj('Action',$filter);
        $data = [];
        foreach($list as $obj){
            $doc = $obj->getDocComment();
            if(!empty($doc)){
                $parser = new DocParser();
                $arr = $parser->parse($doc);
                if(!empty($arr) && array_key_exists($type, $arr)){
                    $data[] = $obj->name;
                }
            }
        }     
        return $data;
    }
    
    /**
     * 获取所有类的方法名
     * @param string $match  匹配的值
     * @param array $filter  过滤的数组
     * @return array 
     */
    public function getMethodListName($match='Action',$filter=[]){
        $data = [];
        $list = $this->getMethodListObj($match,$filter);
        foreach($list as $obj){
            $data[] = $obj->name;
        }    
        return $data;
    }
}
