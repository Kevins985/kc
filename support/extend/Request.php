<?php

namespace support\extend;

use library\logic\AuthLogic;
use library\logic\MenusLogic;
use library\service\sys\AdminService;
use library\service\sys\IpVisitService;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\Container;
use support\utils\Data;
use support\utils\JwtToken;

class Request extends \Webman\Http\Request
{
    /**
     * @var string
     */
    private $language;

    /**
     * 验证类对象
     * @var Validator
     */
    private $validation;

    /**
     * 用户数据
     * @var 用户数据
     */
    private $loginUser= [];

    /**
     * 请求错误信息
     * @var string
     */
    private $error_msg=null;
    /**
     * 用户类型
     * @var string
     */
    public $guard='admin';

    /**
     * 设置请求的错误信息
     * @param $msg
     */
    public function setErrorMsg($msg){
        $this->error_msg = $msg;
        $this->session()->set('error_msg',$msg);
    }

    /**
     * 获取请求的错误信息
     * @param $msg
     */
    public function getErrorMsg(){
        $msg = $this->error_msg;
        if(empty($msg)){
            $msg = $this->session()->get('error_msg');
        }
        return $msg;
    }

    /**
     * 获取最后的真实IP
     */
    public function getLastRealIp(){
        $ip = $this->getRealIp();
        $arr = explode(',',$ip);
        if(count($arr)>1){
            return @end($arr);
        }
        return $ip;
    }

    /**
     * 获取json请求体数据
     */
    public function getRawBody(){
        $query = $this->rawBody();
        if(!empty($query)){
            return json_decode($query,true);
        }
        return [];
    }

    /**
     * 获取语言列表
     */
    public function getLangList(){
        return ["en","cht","th","kor","vie","jap","arabic","french","german","por","spa","ru","hindi"];
    }

    /**
     * 获取请求的语言
     * @return string
     */
    public function getLanguage(){
        if(!empty($this->language)){
            return $this->language;
        }
        $this->language = $this->header('lang');
        if(!in_array($this->language,$this->getLangList())){
            $language = $this->header('accept-language');
            if(strpos($language,',')>-1){
                $regExp = '/^(.*?),/i';
                preg_match($regExp, $language, $matchAry);
                if (!empty($matchAry)) {
                    if($matchAry[1]=='zh'){
                        $this->language = 'zh';
                    }else{
                        $this->language = $matchAry[1];
                    }
                }
            }
        }
        if(!in_array($this->language,$this->getLangList())){
            $this->language = "zh";
        }
        return $this->language;
    }

    /**
     * 设置语言
     * @param string $language
     */
    public function setLanguage($language=null){
        if(empty($language)){
            $language = $this->header('lang');
        }
        if(!in_array($language,$this->getLangList())){
            $language = "zh";
        }
        locale($language);
        $this->language = $language;
    }

    /**
     * 获取控制器名称
     * @return string
     */
    public function getControllerName()
    {
        $arr = explode("\\",$this->controller);
        $name = array_pop($arr);
        return strtolower($name);
    }

    /**
     * 设置语言
     * @param Validator $validator
     */
    public function setValidation(Validator $validator){
        $this->validation = $validator;
    }

    /**
     * 获取验证层实例对象
     * @return Validator
     */
    public function getValidation(){
        if(empty($this->validation)){
            throw new BusinessException('not found validation');
        }
        $this->validation->setLangage($this->getLanguage());
        $this->validation->setType($this->app);
        return $this->validation;
    }

    /**
     * 验证数据有效性
     * @param type $methodName 验证方法
     * @param type $requestData 验证数据
     */
    public function verifyRequestData($methodName=null,$requestData=null)
    {
        if(empty($methodName)){
            $methodName = $this->action;
        }
        if(empty($requestData)){
            $requestData = $this->all();
        }
        if($methodName=='public' && empty($this->validation)){
            $this->validation = Container::get(Validator::class);
        }
        if(!empty($this->validation) && ($this->method()!='GET' || $this->app=='api')){
            $res = $this->getValidation()->verifyRequestData($methodName,$requestData);
            if(!$res){
                $msg = $this->validation->getMessage();
                if(is_string($msg)){
//                    if($this->getLanguage()==="cht"){
//                        $msg = str_replace(" ",'',$msg);
//                    }
                    throw new VerifyException($msg,400);
                }
                else{
                    $first = array_shift($msg);
                    $msg = $first[0];
                    throw new VerifyException($msg,400);
                }
            }
        }
    }

    /**
     * 设置访问的用户ID
     * @param int $userid
     */
    public function setLoginUser($loginUser){
        $this->loginUser = $loginUser;
    }

    /**
     * 获取访问的用户ID
     * @return int
     */
    public function getUserID(){
        try{
            if(empty($this->loginUser)){
                $authLogic = Container::get(AuthLogic::class);
                $authLogic->setUserGuard($this->guard);
                $token = $this->getUserToken();
                $this->loginUser = $authLogic->getUserByToken($token);
            }
            if(!empty($this->loginUser)){
                return $this->loginUser['user_id'];
            }
            return 0;
        }
        catch (\Exception $e){
            return 0;
        }
    }

    /**
     * 获取用户Token
     */
    public function getUserToken()
    {
        $token = $this->header('authorization', '');
        if(empty($token)){
            $token = session("token_{$this->guard}",null);
        }
        return $token;
    }

    /**
     * 验证token的session数据
     * @param Request $request
     * @throws VerifyException
     */
    public function verifyUserLogin($guard,$token=null){
        if(empty($this->loginUser)){
            $loginUser = getTokenUser($guard,$token);
            if(empty($loginUser)){
                throw new VerifyException("No logined",401);
            }
            $this->guard = $guard;
            $this->setLoginUser($loginUser);
        }
        return $this->loginUser;
    }

    /**
     * 验证用户是否登陆
     * @param string|null $token
     */
    public function checkIsLogin(string $token=null){
        try{
            $data = $this->verifyUserLogin($this->guard,$token);
            return !empty($data)?true:false;
        }
        catch (\Exception $e){
            return false;
        }
    }

    /**
     * @param string $token
     * 验证用户是否管理员
     */
    public function checkIsAdmin(string $token=null){
        try{
            $data = $this->verifyUserLogin($this->guard,$token);
            return !empty($data)&&$data['is_admin']==1;
        }
        catch (\Exception $e){
            return false;
        }
    }

    /**
     * 验证是否白名单
     */
    public function verifyIpWhiteList($account)
    {
        $adminService = Container::get(AdminService::class);
        $res = $adminService->fetch(['account'=>$account,'verify_ip'=>1]);
        if(!empty($res)){
            $ip = $this->getLastRealIp();
            $ipVisitService = Container::get(IpVisitService::class);
            $res = $ipVisitService->fetch(['client_ip'=>$ip,'limit_type'=>2]);
            if(empty($res)){
                throw new BusinessException("您的IP访问未被授权");
            }
        }
        return true;
    }

    /**
     * 验证是否黑名单
     * @param Request $request
     */
    public function verifyIpBlacklist()
    {
        $ip = $this->getLastRealIp();
        $cache_key = 'ip_blacklist';
        $ip_blacklist = Redis::hGetAll($cache_key);
        if(!empty($ip_blacklist) && in_array($ip,$ip_blacklist)){
            throw new BusinessException("IP被限制访问");
        }
    }

    /**
     * 获取App原始URL
     */
    public function getAppUrl(string $action=null,string $controller=null,string $app=null){
        if(is_null($action)){
            $action = $this->action;
        }
        if(is_null($controller)){
            $controller = $this->getControllerName();
        }
        if(is_null($app)){
            $app = $this->app;
        }
        return '/'.$app.'/'.$controller.'/'.$action;
    }

    /**
     * 获取当前菜单数据
     * @return array
     */
    public function getCurrentMenu(){
        $menu = [];
        switch ($this->app){
            case "backend":
                $menuLogic = Container::get(MenusLogic::class);
                $url = $this->getAppUrl();
                $menu = $menuLogic->getCurrentMenu($url);
                break;
        }
        return $menu;
    }

    /**
     * 获取访问的域名地址
     * @param null $name 资源名称
     * @return string
     */
    public function getDomainUrl($name=null){
//        $protocol = $this->connection->worker->name;
        $protocol = 'https';
        if(env('APP_ENV')!='prod'){
            $protocol = 'https';
        }
        $host = $this->header("host");
        $path =  $protocol.'://'.$host;
        if(!empty($name)){
            $path.='/'.$name;
        }
        return $path;
    }

    /**
     * 获取登陆地址
     * @return array
     */
    public function getLoginUrl($type=null){
        switch ($this->app){
            case "backend":
                if($type=='success'){
                    return "/backend/main/index";
                }
                return '/backend/login';
            case "frontend":
                if($type=='success'){
                    return "/merchant/dashboard";
                }
                return '/login';
        }
        return "";
    }
}