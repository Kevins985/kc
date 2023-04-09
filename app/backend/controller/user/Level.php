<?php

namespace app\backend\controller\user;

use library\service\user\LevelService;
use library\validator\user\LevelValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Level extends Backend
{
    public function __construct(LevelService $service,LevelValidation $validation)
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
        $data = $this->service->paginate('/backend/level/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('user/level/list');
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
                $levelObj = $this->service->create($post);
                if(empty($levelObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                $msg = $e->getMessage();
                if($e->getMessage()=='grade already exists'){
                    $msg = '该级别已经存在';
                }
                return $this->response->json(false,null,$msg);
            }
        }
        return $this->response->layout('user/level/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['level_id'])) {
                    throw new VerifyException('Exception request');
                }
                $levelObj = $this->service->update($post['level_id'],$post);
                if(empty($levelObj)){
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
            $levelObj = $this->service->get($id);
            if(empty($levelObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$levelObj);
            $this->response->addScriptAssign(['initData'=>$levelObj->toArray()]);
            return $this->response->layout('user/level/update');
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