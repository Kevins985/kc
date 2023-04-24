<?php

namespace app\backend\controller\goods;

use library\service\goods\BrandService;
use library\service\goods\CategoryService;
use library\service\goods\SpuService;
use library\service\sys\LangService;
use library\validator\goods\SpuValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Spu extends Backend
{
    public function __construct(SpuService $service,SpuValidation $validation)
    {
        $this->service = $service;
        $this->validation = $validation;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $categoryService = Container::get(CategoryService::class);
        $params = $this->getAllRequest();
        if(isset($params['category_id']) && !empty($params['category_id'])){
            $category_ids = $categoryService->getCategoryChildIds($params['category_id']);
            if(!empty($category_ids)){
                $params['category_id'] = ['in',$category_ids];
            }
        }
        $data = $this->service->paginate('/backend/spu/list',$params,['spu_id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        unset($params['status']);
        $countList = $this->service->getGroupAllSpuCnt($params);
        $this->response->assign('countList',$countList);
        $categoryList = $categoryService->getSelectList(null,'tree');
        $this->response->assign('categoryList',$categoryList);
        return $this->response->layout('goods/spu/list');
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
                $spuObj = $this->service->saveGoodsData($post);
                if(empty($spuObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                $msg = $e->getMessage();
                if($e->getMessage()=='product_no already exists'){
                    $msg = '该产品编号已经存在';
                }
                return $this->response->json(false,null,$msg);
            }
        }
        $spu_no = $this->service->getGoodsNo();
        $this->response->assign('spu_no',$spu_no);
        $categoryService = Container::get(CategoryService::class);
        $categoryList = $categoryService->getSelectList(null,'tree');
        $this->response->assign('categoryList',$categoryList);
        $brandService = Container::get(BrandService::class);
        $brandList = $brandService->getSelectList();
        $this->response->assign('brandList',$brandList);
        return $this->response->layout('goods/spu/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                print_r($post);
                if (!$request->isAjax() || empty($post['spu_id'])) {
                    throw new VerifyException('Exception request');
                }
                $spuObj = $this->service->saveGoodsData($post);
                if(empty($spuObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                $msg = $e->getMessage();
                if($e->getMessage()=='product_no already exists'){
                    $msg = '该产品编号已经存在';
                }
                return $this->response->json(false,null,$msg);
            }
        }
        else {
            $id = $this->getParams('id');
            $spuObj = $this->service->get($id);
            if(empty($spuObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$spuObj);
            $categoryService = Container::get(CategoryService::class);
            $categoryList = $categoryService->getSelectList(null,'tree');
            $this->response->assign('categoryList',$categoryList);
            $imagesList = $spuObj->images;
            $this->response->assign("imagesList",$imagesList);
            $brandService = Container::get(BrandService::class);
            $brandList = $brandService->getSelectList();
            $this->response->assign('brandList',$brandList);
            $this->response->addScriptAssign(['initData'=>$spuObj->toArray()]);
            return $this->response->layout('goods/spu/update');
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
            $res = $this->service->deleteSpuList($ids);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            print_r($e->getTraceAsString());
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 设置SKU状态
     * @param Request $request
     */
    public function setStatus(Request $request)
    {
        try {
            $id = $this->getPost('id',0);
            $status = $this->getPost('status');
            if (empty($id) || !is_numeric($status) || !$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $res = $this->service->setStatus($id,$status);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 获取商品信息
     */
    public function getGoodsInfo(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if(!$request->isAjax() || empty($id)){
                throw new VerifyException('Exception request');
            }
            $spuObj = $this->service->get($id);
            $this->response->assign('data',$spuObj);
            $imagesList = $spuObj->images;
            $this->response->assign('imagesList',$imagesList);
            return $this->response->view('goods/spu/_info');
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 设置商品语言翻译
     * @param Request $request
     */
    public function setSpuCategory(Request $request)
    {
        $post = $this->getPost();
        if(!empty($post['category_id']) && !empty($post['spu_ids'])){
            try {
                $res = $this->service->updateAll(['spu_id'=>['in',$post['spu_ids']]],['category_id'=>$post['category_id']]);
                if(empty($res)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            } catch (\Exception $e) {
                return $this->response->json(false, [], $e->getMessage());
            }
        }
        else{
            $categoryService = Container::get(CategoryService::class);
            $categoryList = $categoryService->getSelectList(null,'tree');
            $this->response->assign('categoryList',$categoryList);
            return $this->response->view('goods/spu/_set_category');
        }
    }

    /**
     * 设置商品语言翻译
     * @param Request $request
     */
    public function setLangTran(Request $request,int $id)
    {
        $post = $this->getPost();
        $goodsLangService = Container::get(GoodsLangService::class);
        if($request->isAjax() && !empty($post['trans'])){
            try {
                $res = $goodsLangService->updateLangTran($id,$post['trans']);
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
            $langKeyValues = $goodsLangService->getSelectList($id);
            $this->response->assign('langKeyValues',$langKeyValues);
            $langService = Container::get(LangService::class);
            $langList = $langService->getSelectList();
            $this->response->assign('langList',$langList);
            return $this->response->layout('goods/spu/set_lang_tran');
        }
    }
}