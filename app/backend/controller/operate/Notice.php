<?php

namespace app\backend\controller\operate;

use library\service\operate\NoticeCategoryService;
use library\service\operate\NoticeService;
use library\validator\operate\NoticeValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Notice extends Backend
{
    public function __construct(NoticeService $service,NoticeValidation $validation)
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
        $data = $this->service->paginate('/backend/notice/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $categoryService = Container::get(NoticeCategoryService::class);
        $this->response->assign('categoryList',$categoryService->getSelectList());
        return $this->response->layout('operate/notice/list');
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
                $noticeObj = $this->service->create($post);
                if(empty($noticeObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $categoryService = Container::get(NoticeCategoryService::class);
        $this->response->assign('categoryList',$categoryService->getSelectList());
        return $this->response->layout('operate/notice/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['notice_id'])) {
                    throw new VerifyException('Exception request');
                }
                $noticeObj = $this->service->update($post['notice_id'],$post);
                if(empty($noticeObj)){
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
            $noticeObj = $this->service->get($id);
            if(empty($noticeObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$noticeObj);
            $categoryService = Container::get(NoticeCategoryService::class);
            $this->response->assign('categoryList',$categoryService->getSelectList());
            $this->response->addScriptAssign(['initData'=>$noticeObj->toArray()]);
            return $this->response->layout('operate/notice/update');
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