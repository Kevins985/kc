<?php

namespace app\backend\controller\operate;

use library\service\operate\HelpCategoryService;
use library\validator\operate\HelpCategoryValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class HelpCategory extends Backend
{
    public function __construct(HelpCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/helpCategory/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('operate/helpCategory/list');
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
                $helpCategoryObj = $this->service->create($post);
                if(empty($helpCategoryObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        return $this->response->layout('operate/helpCategory/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['category_id'])) {
                    throw new VerifyException('Exception request');
                }
                $helpCategoryObj = $this->service->update($post['category_id'],$post);
                if(empty($helpCategoryObj)){
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
            $helpCategoryObj = $this->service->get($id);
            if(empty($helpCategoryObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$helpCategoryObj);
            $this->response->addScriptAssign(['initData'=>$helpCategoryObj->toArray()]);
            return $this->response->layout('operate/helpCategory/update');
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