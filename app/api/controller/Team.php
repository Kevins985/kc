<?php

namespace app\api\controller;

use library\logic\AuthLogic;
use library\service\operate\ArticleService;
use library\service\user\MemberBankService;
use library\service\user\MemberTeamService;
use library\validator\user\MemberBankValidation;
use support\Container;
use support\controller\Api;
use support\exception\VerifyException;
use support\extend\Request;

class Team extends Api
{

    public function __construct(MemberTeamService $service)
    {
        $this->service = $service;
    }

    /**
     * 获取的team信息
     */
    public function info(Request $request){
        try{
            $teamObj = $this->service->get($request->getUserID());
            return $this->response->json(true,$teamObj);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 我的团队成员
     * @param Request $request
     */
    public function child(Request $request)
    {
        try{
            $params['page'] = $this->getParams('page',1);
            $teamObj = $this->service->get($request->getUserID());
            $params['parents_path'] = ['left_like',$teamObj['parents_path'].','];
            $data = $this->service->paginateData($params,['user_id'=>'desc']);
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 我的直属队员列表
     */
    public function list(Request $request)
    {
        try{
            $params['page'] = $this->getParams('page',1);
            $parent_id = $this->getParams('parent_id');
            if(empty($parent_id)){
                $parent_id = $request->getUserID();
            }
            $params['parent_id'] = $parent_id;
            $data = $this->service->paginateData($params,['user_id'=>'desc']);
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}