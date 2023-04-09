<?php

namespace app\backend\controller\user;

use library\service\user\TagsCategoryService;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class TagsCategory extends Backend
{
    public function __construct(TagsCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/tagsCategory/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('user/tagsCategory/list');
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
                $tagsCategoryObj = $this->service->create($post);
                if(empty($tagsCategoryObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        return $this->response->layout('user/tagsCategory/add');
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
                $tagsCategoryObj = $this->service->update($post['category_id'],$post);
                if(empty($tagsCategoryObj)){
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
            $tagsCategoryObj = $this->service->get($id);
            if(empty($tagsCategoryObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$tagsCategoryObj);
            $this->response->addScriptAssign(['initData'=>$tagsCategoryObj->toArray()]);
            return $this->response->layout('user/tagsCategory/update');
        }
    }

    /**
     * 获取标签分类
     */
    public function getCategoryList(Request $request)
    {
        try {
            $type = $this->getParams('type');
            if (empty($type)) {
                throw new VerifyException('Exception request');
            }
            $res = $this->service->getSelectList($type);
            if(empty($res)){
                throw new BusinessException('暂无分类数据');
            }
            return $this->response->json(true,$res);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
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