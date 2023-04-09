<?php

namespace support\extend;

use Carbon\Carbon;
use support\exception\BusinessException;
use support\persist\AuthInterface;

class Controller
{
    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * 响应对象
     * @Inject
     * @var Response
     */
    protected $response;

    /**
     * 验证对象
     * @var Validator
     */
    protected $validation;

    /**
     * 逻辑层对象
     * @var Logic
     */
    protected $logic;

    /**
     * 服务层对象
     * @var Service
     */
    protected $service;

    /**
     * 视图层对象
     * @Inject
     * @var Layout
     */
    protected $layout;

    /**
     * 当前访问用户对象
     */
    protected $loginUser = null;

    protected function beforeAction(Request $request){
        $request->runtime = Carbon::now()->getTimestampMs();
    }

    /**
     * 设置用户数据
     * @param $loginUser
     */
    protected function setLoginUser($loginUser){
        $this->loginUser = $loginUser;
    }

    /**
     * 获取GET请求的数据
     * @param $name 指定字段
     * @param string $default 默认值
     */
    protected function getParams($name = null, $default = null)
    {
        if(is_array($name)){
            $params = [];
            foreach($name as $key=>$v){
                if(is_string($key)){
                    $params[$key] = $this->request->get($name,$v);
                }
                else{
                    $params[$v] = $this->request->get($v);
                }
            }
        }
        else{
            $params = $this->request->get($name,$default);
            if(!empty($params['searchType']) && isset($post['searchValue'])){
                $params[$params['searchType']] = $params['searchValue'];
            }
        }
        return $params;
    }

    /**
     * 获取所有的请求数据
     * @param $name
     * @param null $default
     */
    protected function getAllRequest(string $type='search',array $filter=[]){
        $params = $this->request->all();
        if($type=='search'){
            if(!empty($params)){
                foreach($params as $k=>$v){
                    if(!empty($filter) && in_array($k,$filter)){
                        unset($params[$k]);
                    }
                    else{
                        $params[$k] = $v;
                    }
                }
            }
            if(!empty($params['searchType']) && isset($params['searchValue'])){
                $params[$params['searchType']] = $params['searchValue'];
            }
            if(!isset($params['page'])){
                $params['page'] = 1;
            }
            if(!isset($params['size'])){
                $params['size'] = 10;
            }
            if($params['size']>50){
                $params['size'] = 50;
            }
        }
        else{
            foreach($params as $k=>$v){
                if($v===''){
                    unset($params[$k]);
                }
            }
        }
        $this->response->assign('params',$params);
        return $params;
    }

    /**
     * 添加请求锁，避免重复提交
     * @param array $data
     */
    protected function addRequestLock(array $data){
        $json = json_encode($data);
        $cache_key = 'lock.'.$this->request->getControllerName().'.'.md5($json);
        $exists = Redis::setNx($cache_key,time());
        if(!$exists) {
            throw new VerifyException('Please do not submit repeatedly');
        }
        Redis::expire($cache_key, 15);
    }

    /**
     * 删除请求锁
     * @param array $data
     */
    protected function deleteRequestLock(array $data,$msg=null){
        if($msg!='Please do not submit repeatedly'){
            $cache_key = 'lock.'.$this->request->getControllerName().'.'.md5(json_encode($data));
            Redis::del($cache_key);
        }
    }

    /**
     * 获取POST提交的数据
     * @param $name 指定字段
     * @param string $default 默认值
     */
    protected function getPost($name = null, $default = null)
    {
        if(is_array($name)){
            $post = [];
            foreach($name as $key=>$v){
                if(is_string($key)){
                    $post[$key] = $this->request->post($name,$v);
                }
                else{
                    $post[$v] = $this->request->post($v);
                }
            }
        }
        else{
            $post = $this->request->post($name,$default);
            if(!empty($post['searchType']) && isset($post['searchValue'])){
                $post[$post['searchType']] = $post['searchValue'];
            }
        }
        return $post;
    }

    /**
     * 获取验证层实例对象
     * @return Service
     */
    protected function getValidationObj(){
        if(empty($this->validation)){
            throw new BusinessException('not found validation');
        }
        return $this->service;
    }

    /**
     * 获取逻辑层实例对象
     * @return Logic
     */
    protected function getLogicObj(){
        if(empty($this->logic)){
            throw new BusinessException('not found logic');
        }
        return $this->logic;
    }

    /**
     * 获取服务层实例对象
     * @return Service
     */
    protected function getServiceObj(){
        if(empty($this->service)){
            throw new BusinessException('not found service');
        }
        return $this->service;
    }
}