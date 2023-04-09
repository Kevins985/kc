<?php

namespace app\backend\controller\operate;

use library\service\operate\NoticeCategoryService;
use library\validator\operate\NoticeCategoryValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class NoticeCategory extends Backend
{
    public function __construct(NoticeCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/noticeCategory/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('operate/noticeCategory/list');
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
                $noticeCategoryObj = $this->service->create($post);
                if(empty($noticeCategoryObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        return $this->response->layout('operate/noticeCategory/add');
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
                $noticeCategoryObj = $this->service->update($post['category_id'],$post);
                if(empty($noticeCategoryObj)){
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
            $noticeCategoryObj = $this->service->get($id);
            if(empty($noticeCategoryObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$noticeCategoryObj);
            $this->response->addScriptAssign(['initData'=>$noticeCategoryObj->toArray()]);
            return $this->response->layout('operate/noticeCategory/update');
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