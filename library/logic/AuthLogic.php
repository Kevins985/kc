<?php

namespace library\logic;

use library\service\sys\AdminAuthService;
use library\service\sys\AdminLoginLogsService;
use library\service\sys\AdminService;
use library\service\user\MemberAuthService;
use library\service\user\MemberLoginLogsService;
use library\service\user\MemberService;
use support\Container;
use support\exception\VerifyException;
use support\extend\Cache;
use support\extend\Logic;
use support\exception\BusinessException;
use support\extend\Redis;
use support\utils\Http;
use support\utils\Random;
use Webman\Event\Event;

/**
 * 用户认证逻辑层
 * @author Kevin
 */
class AuthLogic extends Logic
{
    /**
     * @var string
     */
    private $client_type='web';

    /**
     * @var string
     */
    public $guard = 'admin';

    /**
     * AuthLogic constructor.
     * @param string $guard
     */
    public function __construct($guard = 'admin')
    {
        $this->guard = $guard;
    }

    /**
     * 设置用户类型
     * @param $guard {admin,user}
     */
    public function setUserGuard($guard){
        $this->guard = $guard;
    }

    /**
     * 设置请求终端
     * @return $this
     */
    public function setClientType(String $client_type){
        $this->client_type = $client_type;
        return $this;
    }

    /**
     * 登录尝试次数限制
     * @param $account
     * @param int $number
     * @param int $n
     */
    public function loginFailure($account,$number = 5,$n = 3)
    {
        $fail_key = md5($this->guard.'_login_fail_'.$account);
        $numb = Cache::get($fail_key) ?? 0;
        $numb++;
        if($numb >= $number){
            $lock_key = md5($this->guard.'_login_lock_'.$account);
            Cache::set($lock_key,1,15*60);
            throw new BusinessException('账号或密码错误次数太多，清稍后尝试');
        }
        else{
            Cache::set($fail_key,$numb,5*60);
            $msg = '账号和密码错误';
            $_n = $number - $numb;
            if($_n <= $n){
                $msg .= ',还可尝试'.$_n.'次';
            }
            if(\request()->getLanguage()!='zh'){
                $msg = '账户名或密码错误';
            }
            throw new BusinessException($msg);
        }
    }

    /**
     * 用户登录
     * @param string $account 用户名
     * @param string $password 密码
     * @param array 需要修改的数据
     * @return string token
     * @throws BusinessException
     */
    public function login(string $account,string $password,$vcode=null) {
        try{
            if(!empty($client_type)){
                $this->client_type = $client_type;
            }
            $fail_key = md5($this->guard.'_login_fail_'.$account);
            $is_fail = Cache::get($fail_key);
//            if(!empty($is_fail)){
//                if(empty($vcode) || strtolower($vcode)!=session('captcha')){
//                    throw new VerifyException("输入的验证码不正确");
//                }
//            }
            $lock_key = md5($this->guard.'_login_lock_'.$account);
            $is_lock = Cache::get($lock_key);
            if(!empty($is_lock)){
                throw new VerifyException('账号或密码错误次数太多，清稍后尝试');
            }
            $userObj = null;
            switch ($this->guard){
                case 'admin':
                    $adminService = Container::get(AdminService::class);
                    $userObj = $adminService->get($account,'account');
                    break;
                case 'user':
                    $memberService = Container::get(MemberService::class);
                    $userObj = $memberService->get($account,'account');
                    break;
            }
            if (!empty($userObj)){
                if (!password_verify($password,$userObj->password)) {
                    $this->loginFailure($account);
                }
                elseif ($userObj['account']!='administrator' && $userObj['status'] == -1) {
                    throw new BusinessException('帐号已删除');
                }
                elseif ($userObj['account']!='administrator' && $userObj['status'] == 0) {
                    throw new BusinessException('帐号已锁定');
                }
            }
            else {
                throw new BusinessException('账户名或密码错误');
            }
            if($is_fail){
                Cache::delete($fail_key);
            }
            return $this->createUserToken($userObj);
        }
        catch (\Exception $e){
            $this->createLoginLogs($account,null,$e->getMessage());
            throw $e;
        }
    }

    /**
     * 创建token
     * @return string
     */
    private function createToken(){
        $snowflakeID = Random::getSnowflakeID();
        return sha1($snowflakeID);
    }

    /**
     * 创建用户的token
     * @param object $userObj 用户数据
     * @param int $expire_time 过期时间
     * @return object {token_type,expires_in,refresh_expires_in,access_token,refresh_token,token,client_type}
     */
    public function createUserToken($loginUser,int $expire_time=0){
        $http = Http::getInstance();
        if(empty($expire_time)){
            $expire_time = config('app.access_exp');
        }
        $refresh_exp = config('app.refresh_exp');
        $data = [
            'user_id'=>$loginUser['user_id'],
            'account'=>$loginUser['account'],
            'token_type'=>'Bearer',
            'access_token'=>$this->createToken(),
            'refresh_token'=>$this->createToken(),
            'client_type'=>$this->client_type,
            'client_ip'=>$http->getClientIP(),
            'expires_in'=>$expire_time,
            'refresh_expires_in'=>$refresh_exp,
            'created_time'=>time()
        ];
        $logsObj = $this->createLoginLogs($loginUser['account'],$data['access_token'],'登陆授权成功');
        if(empty($logsObj)){
            throw new BusinessException('Failed to add login log');
        }
        $res = $this->createAuthLogs($data);
        if(empty($res)){
            throw new BusinessException('Failed to auth');
        }
        $loginUser->update([
            'token'=>$data['access_token'],
            'login_cnt'=>($loginUser['login_cnt']+1),
            'login_time'=>time(),
        ]);
        Event::emit('user.login',$loginUser);
        return $data['access_token'];
    }
    
    /**
     * 更新用户token
     * @param int $userid 用户ID
     */
    public function refreshUserToken(string $refresh_token,int $expire_time=0){
        try{
            if($this->guard=='user'){
                $authService = Container::get(MemberAuthService::class);
            }
            else{
                $authService = Container::get(AdminAuthService::class);
            }
            $authObj = $authService->fetch(['refresh_token'=>$refresh_token]);
            if(!empty($authObj) && ($authObj['refresh_expires_in']+$authObj['updated_time'])>time()){
                return $authObj->update(['expires_in'=>$expire_time]);
            }
            return false;
        }
        catch (\Exception $e){
            throw $e;
        }
    }

    /**
     * 获取在线的token
     * @param $userid
     * @param $client_type
     */
    public function getClientTypeToken($userid,$client_type){
        switch ($this->guard){
            case 'admin':
                $authService = Container::get(AdminAuthService::class);
                return $authService->value('access_token',['user_id'=>$userid,'client_type'=>$client_type,'status'=>1],['id'=>'desc']);
            case 'user':
                $authService = Container::get(MemberAuthService::class);
                return $authService->value('access_token',['user_id'=>$userid,'client_type'=>$client_type,'status'=>1],['id'=>'desc']);
        }
        return false;
    }

    /**
     * 账号注册
     * @param $data
     */
    public function register($data){
        $data['client_ip'] = Http::getInstance()->getClientIP();
        switch ($this->guard){
            case 'admin':
                $adminService = Container::get(AdminService::class);
                return $adminService->createUser($data);
            case 'user':
                $memberService = Container::get(MemberService::class);
                return $memberService->createUser($data);
        }
    }

    /**
     * 根据用户ID获取用户对象
     * @param $userid
     */
    public function getUserById($userid){
        $userObj = null;
        switch ($this->guard){
            case 'admin':
                $userService = Container::get(AdminService::class);
                $userObj = $userService->get($userid);
                if(!empty($userObj) && $userObj['is_admin']){
                    return $userObj;
                }
                break;
            case 'user':
                $userService = Container::get(MemberService::class);
                $userObj = $userService->get($userid);
                break;
        }
        if(!empty($userObj) && $userObj['status']==1){
            return $userObj;
        }
        return false;
    }

    /**
     * 根据token获取用户ID
     */
    public function getUserByToken($token){
        $cache = Redis::hGet('login_token',$token);
        if(!empty($cache)){
            $data = json_decode($cache,true);
            if((strtotime($data['created_time'])+$data['expires_in'])>time()){
                return $this->getUserById($data['user_id']);
            }
        }
        else{
            switch ($this->guard){
                case 'admin':
                    $authService = Container::get(AdminAuthService::class);
                    $authObj = $authService->fetch(['access_token'=>$token,'client_type'=>$this->client_type,'status'=>1]);
                    if(empty($authObj)){
                        throw new BusinessException('用户Token已过期');
                    }
                    elseif($authObj['updated_time']+$authObj['expires_in']<time()){
                        throw new BusinessException('用户Token已过期');
                    }
                    return $this->getUserById($authObj['user_id']);
                case 'user':
                    $authService = Container::get(MemberAuthService::class);
                    $authObj = $authService->fetch(['access_token'=>$token,'client_type'=>$this->client_type,'status'=>1]);
                    if(empty($authObj)){
                        throw new BusinessException('用户Token已过期');
                    }
                    elseif($authObj['updated_time']+$authObj['expires_in']<time()){
                        throw new BusinessException('用户Token已过期');
                    }
                    return $this->getUserById($authObj['user_id']);
            }
        }
        return false;
    }

    /**
     * 删除用户token
     * @param int $userid 用户ID
     */
    public function deleteUserToken(int $userid){
        try{
            $userObj = $this->getUserById($userid);
            $this->createLoginLogs($userObj['account'],null,'退出登陆成功',2);
            switch ($this->guard){
                case 'admin':
                    $authService = Container::get(AdminAuthService::class);
                    $authUserList = $authService->fetchAll(['user_id'=>$userid,'status'=>1]);
                    foreach($authUserList as $v){
                        Redis::hDel('login_token',$v['access_token']);
                        $v->update(['status'=>0]);
                    }
                    session(['token_admin'=>'']);
                    break;
                case 'user':
                    $authService = Container::get(MemberAuthService::class);
                    $authUserList = $authService->fetchAll(['user_id'=>$userid,'status'=>1]);
                    foreach($authUserList as $v){
                        Redis::hDel('login_token',$v['access_token']);
                        $v->update(['status'=>0]);
                    }
                    session(['token_user'=>'']);
                    break;
            }
            return true;
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 退出登录
     */
    public function logout($token=null) {
         try{
             if(empty($token)){
                 $token = session("token_{$this->guard}",null);
             }
             $cache = Redis::hGet('login_token',$token);
             if(!empty($cache)){
                 $data = json_decode($cache,true);
                 Redis::hDel('login_token',$token);
                 switch ($this->guard){
                     case 'admin':
                         session(['token_admin'=>'']);
                         $authService = Container::get(AdminAuthService::class);
                         $authService->updateAll(['access_token'=>$token,'client_type'=>$this->client_type,'status'=>1],['status'=>0]);
                         break;
                     case 'user':
                         session(['token_user'=>'']);
                         $authService = Container::get(MemberAuthService::class);
                         $authService->updateAll(['access_token'=>$token,'client_type'=>$this->client_type,'status'=>1],['status'=>0]);
                         break;
                 }
                 $this->createLoginLogs($data['account'],$token,'退出登陆成功',2);
             }
             return true;
         }
         catch (\Exception $e) {
             throw $e;
         }
    }

    /**
     * 添加登陆日志
     * @param $account 账号
     * @param $token Token
     * @param $result 结果
     * @param $status 状态(1:登陆,2:退出)
     */
    private function createLoginLogs($account,$token,$result,$type=1){
        $http = Http::getInstance();
        $data = [
            'account'=>$account,
            'token'=>$token,
            'os'=>$http->getClientOS('os'),
            'browser'=>$http->getBrowser('browser'),
            'client_ip'=>$http->getClientIP(),
            'login_date'=>date('Y-m-d'),
            "result"=>$result,
            "type"=>$type
        ];
        switch ($this->guard){
            case 'admin':
                if($type==2 && !empty($token)){
                    $authService = Container::get(AdminAuthService::class);
                    $authService->updateAll(['access_token'=>$token],['status'=>0]);
                }
                $logsService = Container::get(AdminLoginLogsService::class);
                return $logsService->create($data);
            case 'user':
                if($type==2 && !empty($token)){
                    $authService = Container::get(MemberAuthService::class);
                    $authService->updateAll(['access_token'=>$token],['status'=>0]);
                }
                $logsService = Container::get(MemberLoginLogsService::class);
                return $logsService->create($data);
        }
        return false;
    }

    /**
     * 添加授权记录
     * @param array $data 授权的数据
     */
    private function createAuthLogs(array $data){
        $authLogObj = null;
        switch ($this->guard){
            case 'admin':
                $authService = Container::get(AdminAuthService::class);
                $authLogObj = $authService->create($data);
                break;
            case 'user':
                $authService = Container::get(MemberAuthService::class);
                $authLogObj = $authService->create($data);
                break;
        }
        if(!empty($authLogObj)){
            $session_auth_key = 'token_'.$this->guard;
            session([$session_auth_key=>$data['access_token']]);
            $cdata = $authLogObj->toArray();
            $cdata['account'] = $data['account'];
            Redis::hSet('login_token',$data['access_token'],json_encode($cdata));
            return $authLogObj;
        }
        return false;
    }
}
