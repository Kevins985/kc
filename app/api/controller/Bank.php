<?php

namespace app\api\controller;
;
use library\service\sys\BankTypeService;
use library\service\user\MemberBankService;
use library\validator\user\MemberBankValidation;
use support\Container;
use support\controller\Api;
use support\exception\BusinessException;
use support\extend\Request;

class Bank extends Api
{

    public function __construct(MemberBankService $service,MemberBankValidation $validation)
    {
        $this->service = $service;
        $this->validation = $validation;
    }

    /**
     * 银行卡类型接口
     */
    public function getTypeList(Request $request)
    {
        try{
            $bankTypeService = Container::get(BankTypeService::class);
            $data = $bankTypeService->getSelectList();
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 银行卡列表接口
     */
    public function list(Request $request)
    {
        try{
            $params['user_id'] = $request->getUserID();
            $data = $this->service->fetchAll($params);
            $bankTypeService = Container::get(BankTypeService::class);
            $typeList = $bankTypeService->getSelectList();
            foreach($data as $k=>$v){
                $data[$k]['type_color'] = $typeList[$v['bank_type_id']]['type_color'];
                $data[$k]['type_image'] = $typeList[$v['bank_type_id']]['image'];
            }
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 添加银行卡信息接口
     */
    public function add(Request $request){
        try {
            $post = $this->getPost();
            $post['user_id'] = $request->getUserID();
            if(!empty($post['is_default'])){
                $this->service->updateAll(['user_id'=>$post['user_id']],['is_default'=>0]);
            }
            $bankObj = $this->service->create($post);
            if(empty($bankObj)){
                throw new BusinessException('添加失败');
            }
            return $this->response->json(true,$bankObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 修改银行卡信息接口
     */
    public function update(Request $request,int $id){
        try {
            $bankObj = $this->service->get($id);
            if(empty($bankObj) || $bankObj['user_id']!=$request->getUserID()){
                throw new BusinessException('异常请求');
            }
            $post = $this->getPost();
            $bankObj = $this->service->update($id,$post);
            if(empty($bankObj)){
                throw new BusinessException('修改失败');
            }
            return $this->response->json(true,$bankObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 删除银行卡信息接口
     */
    public function delete(Request $request,int $id)
    {
        try {
            $bankObj = $this->service->get($id);
            if(empty($bankObj) || $bankObj['user_id']!=$request->getUserID()){
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
     * 获取银行卡详情
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