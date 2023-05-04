<?php

namespace app\api\controller;

use library\logic\ProjectOrderLogic;
use library\service\sys\UploadFilesService;
use library\service\user\MemberExpLogService;
use library\service\user\MemberExtendService;
use library\service\user\MemberPointLogService;
use library\service\user\MemberProfitLogService;
use library\service\user\MemberService;
use library\service\user\MemberWalletLogService;
use library\service\user\ProjectOrderDayService;
use library\service\user\ProjectOrderLogService;
use library\service\user\RealAuthService;
use library\validator\user\MemberValidation;
use support\Container;
use support\controller\Api;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;
use support\upload\Upload as UploadFile;
use support\utils\Data;

class Account extends Api
{
    public function __construct(MemberService $service,MemberValidation $validator)
    {
        $this->service = $service;
        $this->validation = $validator;
    }

    /**
     * 密码验证
     * @param $type {login,pay}
     */
    public function verifyPassword(Request $request){
        try {
            $password = $this->getPost('password');
            $type = $this->getPost('type','login');
            if(empty($password)){
                throw new VerifyException('密码不能为空');
            }
            $memberObj = getTokenUser('user',$request->getUserToken());
            if($type=='pay'){
                $user_pass = $memberObj['pay_password'];
            }
            else{
                $user_pass = $memberObj['password'];
            }
            if(!password_verify($password,$user_pass)){
                throw new VerifyException('密码错误');
            }
            return $this->response->json(true);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取用户数据接口
     */
    public function getUserinfo(Request $request){
        try{
            $userObj = getTokenUser('user',$request->getUserToken());
            if(empty($userObj)){
                throw new BusinessException('暂无信息');
            }
            $data = $userObj->toArray();
            $data['extra'] = $userObj->team;
            $data['level_name'] = $userObj->getLevelName();
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 保存用户数据接口
     */
    public function saveUserinfo(Request $request){
        try {
            $post = $this->getPost();
            $memberObj = $this->service->update($request->getUserID(),$post);
            if(empty($memberObj)){
                throw new BusinessException('修改失败');
            }
            return $this->response->json(true,$memberObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取实名认证数据
     */
    public function getRealnameAuth(Request $request){
        try {
            $realAuthService = Container::get(RealAuthService::class);
            $realAuthObj = $realAuthService->fetch(['user_id'=> $request->getUserID()]);
            return $this->response->json(true,$realAuthObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 实名认证
     */
    public function submitRealnameAuth(Request $request)
    {
        try {
            $post = $this->getPost();
            $post['user_id'] = $request->getUserID();
            $realAuthService = Container::get(RealAuthService::class);
            $realAuthObj = $realAuthService->createData($post);
            if(empty($realAuthObj)){
                throw new BusinessException('操作失败');
            }
            return $this->response->json(true,$realAuthObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 设置支付密码接口
     */
    public function setPayPassword(Request $request)
    {
        try {
            $password = $this->getPost('password');
            $memberObj = $this->service->modifyPayPassword($request->getUserID(),$password);
            if(empty($memberObj)){
                throw new BusinessException('修改失败');
            }
            return $this->response->json(true,$memberObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 修改支付密码
     * @param Request $request
     * @return \support\extend\Response
     */
    public function updatePayPassword(Request $request)
    {
        try {
            $old_pass = $this->getPost('old_pass');
            $new_pass = $this->getPost('new_pass');
            $memberObj = $this->service->modifyPayPassword($request->getUserID(),$new_pass,$old_pass);
            if(empty($memberObj)){
                throw new BusinessException('修改失败');
            }
            return $this->response->json(true,$memberObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 修改密码
     */
    public function updatePassword(Request $request)
    {
        try {
            $old_pass = $this->getPost('old_pass');
            $new_pass = $this->getPost('new_pass');
            $memberObj = $this->service->modifyPassword($request->getUserID(),$new_pass,$old_pass);
            if(empty($memberObj)){
                throw new BusinessException('修改失败');
            }
            return $this->response->json(true,$memberObj);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 修改头像地址
     */
    public function uploadImage(Request $request)
    {
        try {
            $file = $request->file('image');
            $content = $this->getPost('image');
            $type = $this->getPost('type','photo');
            if(empty($content) && empty($file)){
                throw new BusinessException('上传文件不存在');
            }
            $uploadObj = null;
            if ($file && $file->isValid()){
                $uploadObj = new UploadFile([
                    'engine'=>'local',
                    'uploadPath'=>$request->getDomainUrl("uploads"),
                    'rootPath'=>public_path("uploads"),
                    'filePath'=>'/user',
                    'allowType'=>['jpg','jpeg', 'png','gif'],
                    'maxSize'=>5120000,
                    'isRandName'=>true,
                    'imgQuality'=>100
                ]);
                $res = $uploadObj->uploadFile($file);
            }
            else{
                $uploadObj = new UploadFile([
                    'engine'=>'local',
                    'uploadPath'=>$request->getDomainUrl("uploads"),
                    'rootPath'=>public_path("uploads"),
                    'filePath'=>'/user',
                    'allowType'=>['jpg','jpeg', 'png','gif'],
                    'maxSize'=>5120000,
                    'isRandName'=>true,
                    'imgQuality'=>100
                ]);
                $res = $uploadObj->uploadBase64Content($content);
            }
            if(empty($uploadObj)){
                throw new BusinessException('上传文件不存在');
            }
            elseif(!$res){
                throw new BusinessException($uploadObj->getErrorMsg());
            }
            $data = array(
                'user_id'=>$request->getUserID(),
                'from_type'=>'user',
                'engine'=>'local',
                'file_name'=>$uploadObj->getNewFileName(),
                'file_url'=>$uploadObj->getUploadFileUrl(),
                "file_path"=>$uploadObj->getFilePath(),
                'file_ext'=>$uploadObj->getFileType(),
                'file_size'=>$uploadObj->getFileSize(),
                'origin_name'=>$uploadObj->getOriginName(),
                'width'=>$uploadObj->getImageWidth(),
                'height'=>$uploadObj->getImageHeight(),
                'file_md5'=>$uploadObj->getFileHash()
            );
            $uploadService = Container::get(UploadFilesService::class);
            $res = $uploadService->firstOrCreate(["file_md5"=>$data['file_md5']],$data);
            if(empty($res)){
                throw new BusinessException('保存数据失败');
            }
            if($type=='photo'){
                $this->service->update($request->getUserID(),['photo_url'=>$res["file_md5"]]);
            }
            $result = [
                "file_url"=>upload_url($res['file_url']),
                'file_md5'=>$res["file_md5"]
            ];
            return $this->response->json(true,$result);

        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 我的金额数据
     */
    public function wallet(Request $request){
        try{
            $extendService = Container::get(MemberExtendService::class);
            $data = $extendService->get($request->getUserID());
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 资产日志
     * @param Request $request
     */
    public function walletLogs(Request $request){
        try{
            $type = $this->getParams('type','wallet');
            $params['page'] = $this->getParams('page',1);
            $params['user_id'] = $request->getUserID();
            if($type=='point'){
                $logService = Container::get(MemberPointLogService::class);
            }
            elseif($type=='exp'){
                $logService = Container::get(MemberExpLogService::class);
            }
            elseif($type=='profit'){
                $logService = Container::get(MemberProfitLogService::class);
            }
            else{
                $params['event'] = $this->getParams('event');
                $logService = Container::get(MemberWalletLogService::class);
            }
            $data = $logService->paginateData($params,['id'=>'desc']);
            return $this->response->json(true,$data);
        }
        catch (\Exception $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}