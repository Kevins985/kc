<?php

namespace app\backend\controller\operate;

use library\service\operate\AdvLocationService;
use library\service\operate\AdvService;
use library\service\operate\AdvTypeService;
use library\validator\operate\AdvValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Adv extends Backend
{
    public function __construct(AdvService $service,AdvValidation $validation)
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
        $data = $this->service->paginate('/backend/adv/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $typeService = Container::get(AdvTypeService::class);
        $this->response->assign('typeList',$typeService->getSelectList());
        $locationService = Container::get(AdvLocationService::class);
        $this->response->assign('locationList',$locationService->getSelectList());
        return $this->response->layout('operate/adv/list');
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
                $advObj = $this->service->create($post);
                if(empty($advObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $typeService = Container::get(AdvTypeService::class);
        $this->response->assign('typeList',$typeService->getSelectList('pc'));
        $locationService = Container::get(AdvLocationService::class);
        $this->response->assign('locationList',$locationService->getSelectList('pc'));
        return $this->response->layout('operate/adv/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['adv_id'])) {
                    throw new VerifyException('Exception request');
                }
                $advObj = $this->service->update($post['adv_id'],$post);
                if(empty($advObj)){
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
            $advObj = $this->service->get($id);
            if(empty($advObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$advObj);
            $this->response->addScriptAssign(['initData'=>$advObj->toArray()]);
            $typeService = Container::get(AdvTypeService::class);
            $this->response->assign('typeList',$typeService->getSelectList($advObj['from_term']));
            $locationService = Container::get(AdvLocationService::class);
            $this->response->assign('locationList',$locationService->getSelectList($advObj['from_term']));
            return $this->response->layout('operate/adv/update');
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
     * 获取广告位和类型
     */
    public function getAdvTypeOrLocation(Request $request)
    {
        try {
            $from_term = $this->getParams('from_term');
            if (empty($from_term) || !$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $typeService = Container::get(AdvTypeService::class);
            $typeList = $typeService->getSelectList($from_term);
            $locationService = Container::get(AdvLocationService::class);
            $locationList = $locationService->getSelectList($from_term);
            return $this->response->json(true,['typeList'=>$typeList,'locationList'=>$locationList]);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }
}