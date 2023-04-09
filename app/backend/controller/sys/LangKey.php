<?php

namespace app\backend\controller\sys;

use library\service\sys\LangKeyService;
use library\service\sys\LangService;
use library\service\sys\LangValueService;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\utils\Data;

class LangKey extends Backend
{
    public function __construct(LangKeyService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/langKey/list',$params,['key_id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $langService = Container::get(LangService::class);
        $langList = $langService->getSelectList();
        $key_ids = Data::toFlatArray($data->items(),'key_id');
        $langKeyValues = [];
        if(!empty($key_ids)){
            $langKeyService = Container::get(LangValueService::class);
            $langKeyValues = $langKeyService->getLangValueArray($key_ids);
        }
        $this->response->assign('langKeyValues',$langKeyValues);
        $this->response->assign('langList',$langList);
        return $this->response->layout('sys/langKey/list');
    }

    /**
     * 添加
     */
    public function add(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['value_name'])) {
                    throw new VerifyException('Exception request');
                }
                $valueNameAry = $post['value_name'];
                unset($post['value_name']);
                $langKeyObj = $this->service->createLangKey($post,$valueNameAry);
                if(empty($langKeyObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $langService = Container::get(LangService::class);
        $langList = $langService->getSelectList();
        $this->response->assign('langList',$langList);
//        $parentList = $this->service->getSelectList(null,'tree');
//        $this->response->assign('langKeyList',$parentList);
        return $this->response->layout('sys/langKey/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['key_id']) || empty($post['value_name'])) {
                    throw new VerifyException('Exception request');
                }
                $valueNameAry = $post['value_name'];
                unset($post['value_name']);
                $langKeyObj = $this->service->updateLangKey($post,$valueNameAry);
                if(empty($langKeyObj)){
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
            $langKeyObj = $this->service->get($id);
            if(empty($langKeyObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$langKeyObj);
            $langService = Container::get(LangService::class);
            $langList = $langService->getSelectList();
            $this->response->assign('langList',$langList);
//            $parentList = $this->service->getSelectList(null,'tree');
//            $this->response->assign('langKeyList',$parentList);
            $langKeyService = Container::get(LangValueService::class);
            $langKeyValues = $langKeyService->getLangValue($id,'value_name');
            $this->response->assign('langKeyValues',$langKeyValues);
            $this->response->addScriptAssign(['initData'=>$langKeyObj->toArray()]);
            return $this->response->layout('sys/langKey/update');
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
     * @param Request $request
     */
    public function getTranslateValue(Request $request){
        try{
            $lang_id = $this->getPost('lang_id');
            $ids = $this->getPost('ids');
            $type = $this->getPost('type');
            if (empty($ids)) {
                throw new VerifyException('Exception request');
            }
            if($type=='translate'){
                throw new VerifyException('暂未对接翻译平台');
            }
            else{
                $langValueService = Container::get(LangValueService::class);
                $data = $langValueService->getLangValueArray($ids,$lang_id);
                return $this->response->json(true,$data);
            }
        }
        catch (\Throwable $e){
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 批量翻译
     * @param Request $request
     * @return \support\extend\Response
     */
    public function translate(Request $request)
    {
        try{
            $type = $this->getPost('type','view');
            if($type=='save'){
                $data = $this->getPost('data');
                $langValueService = Container::get(LangValueService::class);
                foreach($data as $v){
                    $langValueService->updateAll(['lang_id'=>$v['lang_id'],'lang_key_id'=>$v['lang_key_id']],['value_name'=>$v['value_name']]);
                }
                return $this->response->json(true);
            }
            else{
                $ids = $this->getPost('ids');
                if (empty($ids)) {
                    throw new VerifyException('Exception request');
                }
                $langService = Container::get(LangService::class);
                $langList = $langService->getSelectList();
                $this->response->assign('langList',$langList);
                $data = $this->service->fetchAll(['key_id'=>['in',$ids]]);
                $this->response->assign('data',$data);
                return $this->response->view('sys/langKey/translate');
            }
        }
        catch (\Throwable $e){
            return $this->response->json(false, [], $e->getMessage());
        }
    }
}