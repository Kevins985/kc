<?php

namespace app\backend\controller\operate;

use library\service\operate\ArticleCategoryService;
use library\service\operate\ArticleService;
use library\service\sys\LangService;
use library\validator\operate\ArticleValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class Article extends Backend
{
    public function __construct(ArticleService $service,ArticleValidation $validation)
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
        $data = $this->service->paginate('/backend/article/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $categoryService = Container::get(ArticleCategoryService::class);
        $categoryList = $categoryService->getSelectList(null,'tree');
        $this->response->assign('categoryList',$categoryList);
        $category_ids = Data::toFlatArray($data->items(),'category_id');
        $categoryNames = $categoryService->getCategoryNameByIds($category_ids);
        $this->response->assign('categoryNames',$categoryNames);
        $langService = Container::get(LangService::class);
        $langList = $langService->getSelectList();
        $this->response->assign('langList',$langList);
        return $this->response->layout('operate/article/list');
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
                $this->addRequestLock($post);
                $articleObj = $this->service->create($post);
                if(empty($articleObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                $this->deleteRequestLock($post);
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $categoryService = Container::get(ArticleCategoryService::class);
        $categoryList = $categoryService->getSelectList(null,'tree');
        $this->response->assign('categoryList',$categoryList);
        $langService = Container::get(LangService::class);
        $langList = $langService->getSelectList();
        $this->response->assign('langList',$langList);
        return $this->response->layout('operate/article/add');
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
                $articleObj = $this->service->update($post['id'],$post);
                if(empty($articleObj)){
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
            $articleObj = $this->service->get($id);
            if(empty($articleObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$articleObj);
            $categoryService = Container::get(ArticleCategoryService::class);
            $categoryList = $categoryService->getSelectList(null,'tree');
            $this->response->assign('categoryList',$categoryList);
            $langService = Container::get(LangService::class);
            $langList = $langService->getSelectList();
            $this->response->assign('langList',$langList);
            $this->response->addScriptAssign(['initData'=>$articleObj->toArray()]);
            return $this->response->layout('operate/article/update');
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