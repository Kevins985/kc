<?php

namespace app\backend\controller\sys;

use library\logic\ReflectionLogic;
use library\service\sys\JobGroupService;
use library\service\sys\JobLogService;
use library\service\sys\JobService;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Job extends Backend
{
    /**
     * @Inject
     * @var JobGroupService
     */
    private $jobGroupService;

    public function __construct(JobService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/job/list',$params,['job_id'=>'desc']);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $groupService = Container::get(JobGroupService::class);
        $this->response->assign('groupList',$groupService->getSelectList());
        return $this->response->layout('sys/job/list');
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
                $jobObj = $this->service->create($post);
                if(empty($jobObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $groupList = $this->jobGroupService->getSelectList();
        $this->response->assign('groupList',$groupList);
        return $this->response->layout('sys/job/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['job_id'])) {
                    throw new VerifyException('Exception request');
                }
                $jobObj = $this->service->update($post['job_id'],$post);
                if(empty($jobObj)){
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
            $jobObj = $this->service->get($id);
            if(empty($jobObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $groupList = $this->jobGroupService->getSelectList();
            $this->response->assign('groupList',$groupList);
            $this->response->assign("data",$jobObj);
            $this->response->addScriptAssign(['initData'=>$jobObj->toArray()]);
            return $this->response->layout('sys/job/update');
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
     * 日志列表
     */
    public function logs(Request $request,int $id)
    {
        $params = [];
        $params['job_id'] = $id;
        $params['page'] = $this->getParams('page', 1);
        $params['size'] = $this->getParams('size', 10);
        $jobLogsService = Container::get(JobLogService::class);
        $jobObj = $this->service->get($id);
        if(empty($jobObj)){
            return $this->redirectErrorUrl("物流方式不存在");
        }
        $this->response->assign('job',$jobObj);
        $data = $jobLogsService->paginate('/backend/job/logs/'.$id,$params,['log_id'=>'desc']);
        $this->response->assign('data',$data);
        return $this->response->layout('sys/job/logs');
    }

    /**
     * 设置规则
     * @param Request $request
     */
    public function setRules(Request $request)
    {
        try {
            if (!$request->isAjax()) {
                throw new \Exception('非法请求');
            }
            return $this->response->view('sys/job/_set_rule');
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 获取未选择的任务列表
     * @param Request $request
     */
    public function getTaskList(Request $request)
    {
        try {
            if (!$request->isAjax()) {
                throw new \Exception('非法请求');
            }
            $jobCommendList = $this->service->getJobCommandList();
            $logic = Container::get(ReflectionLogic::class);
            $taskList = $logic->getTaskList($jobCommendList);
            return $this->response->view('sys/job/_set_cmd',['data'=>$taskList]);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 手动执行
     * @param Request $request
     */
    public function exec(Request $request)
    {
        try {
            $ids = $this->getPost('id');
            if (empty($ids)) {
                throw new VerifyException('Exception request');
            }
            foreach($ids as $id){
                $this->service->execJobTask($id);
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 设置状态
     */
    public function setStatus(Request $request)
    {
        try {
            $ids = $this->getPost('ids');
            $status = $this->getPost('status');
            if (empty($ids) || !in_array($status,[1,2])) {
                throw new VerifyException('Exception request');
            }
            $res = $this->service->updateAll(['job_id'=>['in',$ids]],['status'=>$status]);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }
}