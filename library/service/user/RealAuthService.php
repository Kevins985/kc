<?php

namespace library\service\user;

use library\logic\DictLogic;
use support\Container;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\user\RealAuthModel;

class RealAuthService extends Service
{
    public function __construct(RealAuthModel $model)
    {
        $this->model = $model;
    }

    /**
     * 创建实名认证
     * @param array $data
     */
    public function createData(array $data)
    {
        $realAuthObj = $this->fetch(['user_id'=> $data['user_id']]);
        if(!empty($realAuthObj)){
            throw new BusinessException('您已经提交过了');
        }
        $realAuthObj = $this->fetch(['card_id'=> $data['card_id']]);
        if(!empty($realAuthObj)){
            throw new BusinessException('该身份证已经提交过了');
        }
        $data['status'] = 1;
        $realAuthObj = $this->create($data);
        if(!empty($realAuthObj)){
            $realAuthObj->member()->update(['auth_type'=>1]);
        }
        return $realAuthObj;
    }

    /**
     * 实名验证
     * @param $id
     */
    public function verify($id,$status,$descr=''){
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $realAuthObj = $this->get($id);
            if(empty($realAuthObj) || $realAuthObj['status']!='0'){
                throw new BusinessException("状态异常，不能审核");
            }
            $update = [
                'descr'=>$descr,
                'status'=>$status,
            ];
            $res = $realAuthObj->update($update);
            if($res && $status==1){
                $realAuthObj->member()->update(['auth_type'=>1]);
                $dictLogic = Container::get(DictLogic::class);
                $config = $dictLogic->getDictConfigs('reward');
                if($config['push_open']=='Y'){
                    $memberTeamService = Container::get(MemberTeamService::class);
                    $teamObj = $memberTeamService->get($realAuthObj['user_id']);
                    if(!empty($teamObj) && !empty($teamObj['parent_id'])){
                        $memberTeamService->updateALl(['user_id'=>$teamObj['parent_id']],[
                            'reward'=>$memberTeamService->raw('reward+'.$config['push_money']),
                        ]);
                    }
                }
            }
            $conn->commit();
            return $res;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }
}
