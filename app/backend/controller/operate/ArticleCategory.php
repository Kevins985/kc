<?php

namespace app\backend\controller\operate;

use library\service\operate\ArticleCategoryService;
use library\validator\operate\ArticleCategoryValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class ArticleCategory extends Backend
{
    public function __construct(ArticleCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/articleCategory/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $parentList = $this->service->getSelectList(0);
        $this->response->assign('parentList',$parentList);
        $parent_ids = Data::toFlatArray($data->items(),'parent_id');
        $parentNames = $this->service->getCategoryNameByIds($parent_ids);
        $this->response->assign('parentNames',$parentNames);
        return $this->response->layout('operate/articleCategory/list');
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
                $articleCategoryObj = $this->service->create($post);
                if(empty($articleCategoryObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $parentList = $this->service->getSelectList(0);
        $this->response->assign('parentList',$parentList);
        return $this->response->layout('operate/articleCategory/add');
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
                $articleCategoryObj = $this->service->update($post['category_id'],$post);
                if(empty($articleCategoryObj)){
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
            $articleCategoryObj = $this->service->get($id);
            if(empty($articleCategoryObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$articleCategoryObj);
            $this->response->addScriptAssign(['initData'=>$articleCategoryObj->toArray()]);
            $parentList = $this->service->getSelectList(0);
            $this->response->assign('parentList',$parentList);
            return $this->response->layout('operate/articleCategory/update');
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