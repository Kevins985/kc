<?php

namespace app\backend\controller\sys;

use library\service\sys\CurrencyExchangeService;
use library\service\sys\CurrencyService;
use library\validator\sys\CurrencyValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Currency extends Backend
{
    public function __construct(CurrencyService $service,CurrencyValidation $validation)
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
        $data = $this->service->paginate('/backend/currency/list',$params,['sort'=>'asc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('sys/currency/list');
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
                $currencyObj = $this->service->create($post);
                if(empty($currencyObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        return $this->response->layout('sys/currency/add');
    }

    /**
     * 设置货币汇率
     * @params post {type(add,update,delete),data[]}
     */
    public function setCurrencyExchange(Request $request)
    {
        $post = $this->getPost();
        if($request->isAjax() && !empty($post)){
            try{
                $exchangeService = Container::get(CurrencyExchangeService::class);
                if($post['type']=='add'){
                    $res = $exchangeService->create($post['data']);
                }
                elseif($post['type']=='update'){
                    $res = $exchangeService->update($post['data']['id'],$post['data']);
                }
                else{
                    $res = $exchangeService->delete($post['data'],true);
                }
                if(empty($res)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e){
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        else{
            $id = $this->getParams('id',0);
            $currencyObj = $this->service->get($id);
            if(empty($currencyObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $currencyList = $this->service->getSelectList();
            $this->response->addScriptAssign(['currency'=>$currencyList]);
            $this->response->assign('currencyObj',$currencyObj);
            $exchangeService = Container::get(CurrencyExchangeService::class);
            $exchangeList = $exchangeService->getCurrencyList($id);
            $this->response->assign('data',$exchangeList);
            return $this->response->layout('sys/currency/set_exchange');
        }
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['currency_id'])) {
                    throw new VerifyException('Exception request');
                }
                if($post['is_rec']){
                    $this->service->updateAll(['is_rec'=>1],['is_rec'=>0]);
                }
                $currencyObj = $this->service->update($post['currency_id'],$post);
                if(empty($currencyObj)){
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
            $currencyObj = $this->service->get($id);
            if(empty($currencyObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$currencyObj);
            $this->response->addScriptAssign(['initData'=>$currencyObj->toArray()]);
            return $this->response->layout('sys/currency/update');
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