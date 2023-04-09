<?php

namespace app\backend\controller\goods;

use library\service\goods\ProjectService;
use library\service\goods\SpuService;
use library\validator\goods\ProjectValidation;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Project extends Backend
{
    public function __construct(ProjectService $service,ProjectValidation $validation)
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
        $data = $this->service->paginate('/backend/project/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        unset($params['status']);
        $countList = $this->service->getGroupAllProjectCnt($params);
        $this->response->assign('countList',$countList);
        return $this->response->layout('goods/project/list');
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
                $projectObj = $this->service->createProject($post);
                if(empty($projectObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $spuService = Container::get(SpuService::class);
        $spuList =$spuService->getGoodsSelect();
        $this->response->assign('spuList',$spuList);
        return $this->response->layout('goods/project/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['project_id'])) {
                    throw new VerifyException('Exception request');
                }
                $projectObj = $this->service->updateProject($post['project_id'],$post);
                if(empty($projectObj)){
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
            $projectObj = $this->service->get($id);
            if(empty($projectObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $this->response->assign("data",$projectObj);
            $spuService = Container::get(SpuService::class);
            $spuList =$spuService->getGoodsSelect();
            $this->response->assign('spuList',$spuList);
            $this->response->addScriptAssign(['initData'=>$projectObj->toArray()]);
            return $this->response->layout('goods/project/update');
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
     * 获取项目信息
     */
    public function getProjectInfo(Request $request)
    {
        try {
            $id = $this->getParams('id');
            if(!$request->isAjax() || empty($id)){
                throw new VerifyException('Exception request');
            }
            $spuObj = $this->service->get($id);
            $this->response->assign('data',$spuObj);
            return $this->response->view('goods/project/_info');
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}