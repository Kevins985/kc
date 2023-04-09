<?php

namespace app\api\controller;

use library\service\user\MemberAddressService;
use library\validator\user\MemberAddressValidation;
use support\controller\Api;
use support\exception\BusinessException;
use support\extend\Request;

class Address extends Api
{
    public function __construct(MemberAddressService $service,MemberAddressValidation $validation)
    {
        $this->service = $service;
        $this->validation = $validation;
    }

    /**
     * 地址列表接口
     */
    public function list(Request $request)
    {
        try{
            $params['user_id'] = $request->getUserID();
            $data = $this->service->fetchAll($params);
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 添加地址信息接口
     */
    public function add(Request $request){
        try {
            $post = $this->getPost();
            $post['user_id'] = $request->getUserID();
            if(!empty($post['is_default'])){
                $this->service->updateAll(['user_id'=>$post['user_id']],['is_default'=>0]);
            }
            $addressObj = $this->service->create($post);
            if(empty($addressObj)){
                throw new BusinessException('添加失败');
            }
            return $this->response->json(true,$addressObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 修改地址信息接口
     */
    public function update(Request $request,int $id){
        try {
            $addressObj = $this->service->get($id);
            if(empty($addressObj) || $addressObj['user_id']!=$request->getUserID()){
                throw new BusinessException('异常请求');
            }
            $post = $this->getPost();
            $addressObj = $this->service->update($id,$post);
            if(empty($addressObj)){
                throw new BusinessException('修改失败');
            }
            return $this->response->json(true,$addressObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 删除地址信息接口
     */
    public function delete(Request $request,int $id)
    {
        try {
            $addressObj = $this->service->get($id);
            if(empty($addressObj) || $addressObj['user_id']!=$request->getUserID()){
                throw new BusinessException('异常请求');
            }
            $res = $this->service->delete($id);
            if(empty($res)){
                throw new BusinessException('删除失败');
            }
            return $this->response->json(true);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取地址详情
     */
    public function detail(Request $request,int $id)
    {
        try{
            $data = $this->service->get($id);
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}