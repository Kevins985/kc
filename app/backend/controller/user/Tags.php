<?php

namespace app\backend\controller\user;

use library\service\user\TagsCategoryService;
use library\service\user\TagsService;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\Container;
use support\extend\Request;

class Tags extends Backend
{
    public function __construct(TagsService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/tags/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $categoryService = Container::get(TagsCategoryService::class);
        $this->response->assign('categoryList',$categoryService->getSelectList());
        return $this->response->layout('user/tags/list');
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
                $tagsObj = $this->service->create($post);
                if(empty($tagsObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $categoryService = Container::get(TagsCategoryService::class);
        $this->response->assign('categoryList',$categoryService->getSelectList(1));
        return $this->response->layout('user/tags/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['tag_id'])) {
                    throw new VerifyException('Exception request');
                }
                $tagsObj = $this->service->update($post['tag_id'],$post);
                if(empty($tagsObj)){
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
            $tagsObj = $this->service->get($id);
            if(empty($tagsObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$tagsObj);
            $categoryService = Container::get(TagsCategoryService::class);
            $this->response->assign('categoryList',$categoryService->getSelectList($tagsObj['type']));
            $this->response->addScriptAssign(['initData'=>$tagsObj->toArray()]);
            return $this->response->layout('user/tags/update');
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