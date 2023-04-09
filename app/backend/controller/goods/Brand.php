<?php

namespace app\backend\controller\goods;

use library\service\goods\BrandService;
use library\validator\goods\BrandValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Brand extends Backend
{
    public function __construct(BrandService $service,BrandValidation $validation)
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
        $data = $this->service->paginate('/backend/brand/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('goods/brand/list');
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
                $brandObj = $this->service->create($post);
                if(empty($brandObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        return $this->response->layout('goods/brand/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['brand_id'])) {
                    throw new VerifyException('Exception request');
                }
                $brandObj = $this->service->update($post['brand_id'],$post);
                if(empty($brandObj)){
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
            $brandObj = $this->service->get($id);
            if(empty($brandObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$brandObj);
            $this->response->addScriptAssign(['initData'=>$brandObj->toArray()]);
            return $this->response->layout('goods/brand/update');
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
     * 详情
     * @authorized YES
     * @method GET
     */
    public function detail(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if (empty($id)) {
                throw new VerifyException('Exception request');
            }
            $brandObj = $this->service->get($id);
            if(empty($brandObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $data = $brandObj->toArray();
            return $this->response->layout('goods/brand/detail', ["data" => $data]);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }
}