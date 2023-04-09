<?php

namespace app\backend\controller\sys;

use library\service\sys\IpVisitService;
use support\Container;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Redis;
use support\extend\Request;
use support\utils\Ip2Region;

class IpVisit extends Backend
{
    public function __construct(IpVisitService $service)
    {
        $this->service = $service;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/ipVisit/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        unset($params['limit_type']);
        $countList = $this->service->getGroupAllCnt($params);
        $this->response->assign('countList',$countList);
        $this->response->assign('data',$data);
        return $this->response->layout('sys/ipVisit/list');
    }

    /**
     * 添加黑名单
     */
    public function add(Request $request)
    {
        try {
            $ip = $this->getPost('ip');
            $status = $this->getPost('status');
            if (empty($ip)) {
                throw new VerifyException('Exception request');
            }
            $ipVisitObj = $this->service->get($ip,'client_ip');
            if(empty($ipVisitObj)){
                $res = $this->service->createIpVisit([
                    'client_ip'=>$ip,
                    'user_id'=>0,
                    'limit_type'=>$status,
                    'last_visit_time'=>null,
                    'descr'=>'后台添加'
                ]);
            }
            else{
                $res = $ipVisitObj->update([
                    'limit_type'=>$status,
                    'status'=>1,
                    'descr'=>'后台设置'
                ]);
            }
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            $cache_key = 'ip_blacklist';
            if($status==1){
                Redis::hMSet($cache_key,$res['id'],$ip);
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 添加
     */
    public function setLimitStatus(Request $request)
    {
        try {
            $id = (int)$this->getPost('id',0);
            $status = $this->getPost('status',1);
            if (!$request->isAjax() || empty($id)) {
                throw new VerifyException('Exception request');
            }
            $ipVisitObj = $this->service->get($id);
            if(empty($ipVisitObj)){
                throw new VerifyException('Exception request');
            }
            $res = $ipVisitObj->update(['limit_type'=>$status]);
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            $cache_key = 'ip_blacklist';
            if($status==1){
                Redis::hSet($cache_key,$id,$ipVisitObj['client_ip']);
            }
            else{
                Redis::hDel($cache_key,$id);
            }
            return $this->response->json(true);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
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
            $cache_key = 'ip_blacklist';
            if(count($ids)>1){
                foreach($ids as $k){
                    Redis::hDel($cache_key,$k);
                }
                $res = $this->service->batchDelete($ids);
            }
            else{
                Redis::hDel($cache_key,$id);
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