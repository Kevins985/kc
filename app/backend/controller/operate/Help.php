<?php

namespace app\backend\controller\operate;

use library\service\operate\HelpCategoryService;
use library\service\operate\HelpService;
use library\validator\operate\HelpValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Help extends Backend
{
    public function __construct(HelpService $service,HelpValidation $validation)
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
        $data = $this->service->paginate('/backend/help/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $categoryService = Container::get(HelpCategoryService::class);
        $this->response->assign('categoryList',$categoryService->getSelectList());
        return $this->response->layout('operate/help/list');
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
                $helpObj = $this->service->create($post);
                if(empty($helpObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $categoryService = Container::get(HelpCategoryService::class);
        $this->response->assign('categoryList',$categoryService->getSelectList());
        return $this->response->layout('operate/help/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['help_id'])) {
                    throw new VerifyException('Exception request');
                }
                $helpObj = $this->service->update($post['help_id'],$post);
                if(empty($helpObj)){
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
            $helpObj = $this->service->get($id);
            if(empty($helpObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$helpObj);
            $this->response->addScriptAssign(['initData'=>$helpObj->toArray()]);
            $categoryService = Container::get(HelpCategoryService::class);
            $this->response->assign('categoryList',$categoryService->getSelectList());
            return $this->response->layout('operate/help/update');
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

}