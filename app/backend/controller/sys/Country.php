<?php

namespace app\backend\controller\sys;

use library\service\sys\CountryService;
use library\service\sys\DataLangService;
use library\service\sys\GoodsLangService;
use library\service\sys\LangService;
use library\validator\sys\CountryValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Country extends Backend
{
    public function __construct(CountryService $service,CountryValidation $validation)
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
        if(!empty($params['name'])){
            $params['name'] = ['like',$params['name']];
        }
        $data = $this->service->paginate('/backend/country/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $continentList = $this->service->getContinentList();
        $this->response->assign('continentList',$continentList);
        return $this->response->layout('sys/country/list');
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
                $countryObj = $this->service->create($post);
                if(empty($countryObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                $this->deleteRequestLock($post,$e->getMessage());
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $continentList = $this->service->getContinentList();
        $this->response->assign('continentList',$continentList);
        return $this->response->layout('sys/country/add');
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
                $countryObj = $this->service->update($post['id'],$post);
                if(empty($countryObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        else {
            $id = $this->getParams('id',0);
            $countryObj = $this->service->get($id);
            if(empty($countryObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$countryObj);
            $continentList = $this->service->getContinentList();
            $this->response->assign('continentList',$continentList);
            $this->response->addScriptAssign(['initData'=>$countryObj->toArray()]);
            return $this->response->layout('sys/country/update');
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
                $res = $dataLangService->updateLangTran('sys_country',$id,$post['trans']);
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
            $langKeyValues = $dataLangService->getSelectList('sys_country',$id,'data_value');
            $this->response->assign('langKeyValues',$langKeyValues);
            $langService = Container::get(LangService::class);
            $langList = $langService->getSelectList();
            $this->response->assign('langList',$langList);
            return $this->response->layout('sys/country/set_lang_tran');
        }
    }

    /**
     * 删除
     */
    public function delete(Request $request)
    {
        try {
            $id = $this->getParams('id',0);
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
     * 获取国家列表
     */
    public function getCountryList(Request $request)
    {
        try {
            $continent = $this->getParams('continent',0);
            $type = $this->getParams('type','modal');
            if (!$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            if(empty($continent)){
                $data = $this->service->getContinentCountryList();
            }
            elseif($continent=='all'){
                $data = $this->service->getSelectList();
            }
            else{
                $data = $this->service->getSelectList($continent);
            }
            if($type=='modal'){
                return $this->response->view('sys/country/_modal',['data'=>$data]);
            }
            else{
                return $this->response->json(true,$data);
            }
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}