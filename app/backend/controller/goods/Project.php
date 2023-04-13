<?php

namespace app\backend\controller\goods;

use library\service\goods\ProjectNumberService;
use library\service\goods\ProjectService;
use library\service\goods\SpuService;
use library\service\user\ProjectOrderService;
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
            $projectObj = $this->service->get($id);
            if(empty($projectObj)){
                throw new VerifyException('项目数据不存在');
            }
            elseif($projectObj['status']==1){
                throw new VerifyException('进行中的项目不能删除');
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
     * @param Request $request
     * @return \support\extend\Response
     */
    public function addProjectNumber(Request $request){
        try{
            $project_id = $this->getPost('project_id',0);
            $projectObj = $this->service->get($project_id);
            if (empty($projectObj) || !$request->isAjax()) {
                throw new VerifyException('Exception request');
            }
            $projectNumberService = Container::get(ProjectNumberService::class);
            $res = $projectNumberService->createProjectNumber($projectObj['project_id'],$projectObj['project_prefix'],$projectObj['number']);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        }
        catch (\Throwable $e){
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
            $projectObj = $this->service->get($id);
            if(empty($projectObj)){
                throw new BusinessException('项目不存在');
            }
            $projectNumber = $projectObj->projectNumber;
            $this->response->assign('data',$projectObj);
            $this->response->assign('projectNumber',$projectNumber);
            return $this->response->view('goods/project/_info');
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取项目信息
     */
    public function getOrderMembers(Request $request)
    {
        try {
            $type = $this->getParams('type','number');
            $val = $this->getParams('val');
            if(!$request->isAjax() || empty($val)){
                throw new VerifyException('Exception request');
            }
            $projectOrderService = Container::get(ProjectOrderService::class);
            if($type=='number'){
                $data = $projectOrderService->fetchAll(['project_number'=>$val],['user_number'=>'asc']);
            }
            else{
                $data = $projectOrderService->fetchAll(['project_id'=>$val,'status'=>1],['project_number'=>'asc','user_number'=>'asc']);
            }
            $this->response->assign('data',$data);
            return $this->response->view('goods/project/_order_member');
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}