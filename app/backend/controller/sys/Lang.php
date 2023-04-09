<?php

namespace app\backend\controller\sys;

use library\service\sys\LangService;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Lang extends Backend
{
    public function __construct(LangService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/lang/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('sys/lang/list');
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
                if(isset($post['is_default']) && $post['is_default']==1){
                    $this->service->updateAll([],['is_default'=>0]);
                }
                $langObj = $this->service->create($post);
                if(empty($langObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        return $this->response->layout('sys/lang/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['lang_id'])) {
                    throw new VerifyException('Exception request');
                }
                if(isset($post['is_default']) && $post['is_default']==1){
                    $this->service->updateAll([],['is_default'=>0]);
                }
                $langObj = $this->service->update($post['lang_id'],$post);
                if(empty($langObj)){
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
            $langObj = $this->service->get($id);
            if(empty($langObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$langObj);
            $this->response->addScriptAssign(['initData'=>$langObj->toArray()]);
            return $this->response->layout('sys/lang/update');
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
            $res = $this->service->delete($id);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 生成语言包
     * @param Request $request
     */
    public function generate(Request $request)
    {
        try {
            $ids = $this->getPost('ids');
            if (empty($ids)) {
                throw new VerifyException('Exception request');
            }
            $this->service->createLangFile(0);
            foreach($ids as $id){
                $this->service->createLangFile($id);
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }
}