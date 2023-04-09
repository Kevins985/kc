<?php

namespace app\backend\controller\module;

use library\service\module\TemplateService;
use library\validator\module\TemplateValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Template extends Backend
{
    public function __construct(TemplateService $service,TemplateValidation $validation)
    {
        $this->service = $service;
        $this->validation = $validation;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/template/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('module/template/list');
    }

    /**
     * 添加
     */
    public function add(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax()) {
                    throw new VerifyException('Exception request');
                }
                $templateObj = $this->service->create($post);
                if(empty($templateObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        return $this->response->layout('module/template/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['id'])) {
                    throw new VerifyException('Exception request');
                }
                $templateObj = $this->service->update($post['id'],$post);
                if(empty($templateObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        else {
            $id = $this->getParams('id');
            $templateObj = $this->service->get($id);
            if(empty($templateObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$templateObj);
            $this->response->addScriptAssign(['initData'=>$templateObj->toArray()]);
            return $this->response->layout('module/template/update');
        }
    }

    /**
     * 删除
     */
    public function delete(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if (empty($id)) {
                throw new VerifyException('Exception request');
            }
            $ids = explode(',',$id);
            if(count($ids)>1){
                $res = $this->service->batchDelete($ids);
            }
            else{
                $res = $this->service->delete($id);
            }
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 详情
     * @authorized YES
     * @method GET
     */
    public function detail(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if (empty($id)) {
                throw new VerifyException('Exception request');
            }
            $templateObj = $this->service->get($id);
            if(empty($templateObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $data = $templateObj->toArray();
            return $this->response->layout('module/template/detail', ["data" => $data]);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }
}