<?php

namespace app\backend\controller\goods;

use library\service\goods\CategoryService;
use library\service\sys\DataLangService;
use library\service\sys\LangService;
use library\validator\goods\CategoryValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Category extends Backend
{
    public function __construct(CategoryService $service,CategoryValidation $validation)
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
        $params['children']=['with'];
        $params['parent_id'] = 0;
        $data = $this->service->paginate('/backend/category/list',$params,['sort'=>'asc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $parentList =$this->service->getSelectList(0);
        $this->response->assign('parents',$parentList);
        return $this->response->layout('goods/category/list');
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
                $categoryObj = $this->service->create($post);
                if(empty($categoryObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $parentList =$this->service->getSelectList(0);
        $this->response->assign('parents',$parentList);
        return $this->response->layout('goods/category/add');
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
                $categoryObj = $this->service->update($post['category_id'],$post);
                if(empty($categoryObj)){
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
            $categoryObj = $this->service->get($id);
            if(empty($categoryObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$categoryObj);
            $parentList =$this->service->getSelectList(0);
            $this->response->assign('parents',$parentList);
            $this->response->addScriptAssign(['initData'=>$categoryObj->toArray()]);
            return $this->response->layout('goods/category/update');
        }
    }

    /**
     * 设置状态
     */
    public function setStatus(Request $request)
    {
        try {
            $id = $this->getPost('id');
            $status = $this->getPost('status',1);
            if (empty($id)) {
                throw new VerifyException('Exception request');
            }
            $res = $this->service->setCategoryStatus($id,$status);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true,['status'=>$res['status']]);
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

    /**
     * 设置商品语言翻译
     * @param Request $request
     */
    public function setLangTran(Request $request,int $id)
    {
        $post = $this->getPost();
        $dataLangService = Container::get(DataLangService::class);
        if($request->isAjax() && !empty($post['trans'])){
            try {
                $res = $dataLangService->updateLangTran('goods_category',$id,$post['trans']);
                if(empty($res)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            } catch (\Exception $e) {
                return $this->response->json(false, [], $e->getMessage());
            }
        }
        else{
            $data = $this->service->get($id);
            $this->response->assign('data',$data);
            $langKeyValues = $dataLangService->getSelectList('goods_category',$id,'data_value');
            $this->response->assign('langKeyValues',$langKeyValues);
            $langService = Container::get(LangService::class);
            $langList = $langService->getSelectList();
            $this->response->assign('langList',$langList);
            return $this->response->layout('goods/category/set_lang_tran');
        }
    }
}