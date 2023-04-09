<?php

namespace app\backend\controller\sys;

use library\service\sys\JobGroupService;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class JobGroup extends Backend
{
    public function __construct(JobGroupService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/jobGroup/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        return $this->response->layout('sys/jobGroup/list');
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
                $post['admin_id'] = $request->getUserID();
                $jobGroupObj = $this->service->create($post);
                if(empty($jobGroupObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        return $this->response->layout('sys/jobGroup/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['group_id'])) {
                    throw new VerifyException('Exception request');
                }
                $post['admin_id'] = $request->getUserID();
                $jobGroupObj = $this->service->update($post['group_id'],$post);
                if(empty($jobGroupObj)){
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
            $jobGroupObj = $this->service->get($id);
            if(empty($jobGroupObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$jobGroupObj);
            $this->response->addScriptAssign(['initData'=>$jobGroupObj->toArray()]);
            return $this->response->layout('sys/jobGroup/update');
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