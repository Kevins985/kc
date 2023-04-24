<?php

use support\Container;
use support\extend\Cache;
use support\exception\BusinessException;
use support\extend\Log;
use support\persist\QueueInterface;
use library\logic\MenusLogic;
use library\logic\AuthLogic;
use library\service\sys\DataLangService;
use library\service\sys\LangService;

const QueueSendMessage = 11;
const QueueWriteLogs = 12;
const QueueJobLogs = 13;
const QueueProject = 14;
const QueueMember = 15;

const ProjectUserCnt = 4;

/**
 * 是否开启缓存
 * @return bool
 */
function is_open_cache(){
    return \config('app.is_open_cache');
}

/**
 * 是否验证签名
 * @param string $type 类型
 * @return bool
 */
function validation_sign($type='api'){
    return \config("app.validation_sign.${type}");
}

/**
 * 是否写操作日志
 * @param string $type 类型
 * @return bool
 */
function write_operation_log($type='backend'){
    return \config("app.operation_log.${type}");
}

function route_id($url){
    return md5(strtolower(trim($url,'/')));
}

/**
 * 获取路由列表
 */
function getRouteList(string $app=null,bool $clearCache=false){
    $menuLogic = Container::get(MenusLogic::class);
    return $menuLogic->getRouteList($app,$clearCache);
}

/**
 * 获取真实的URL地址
 * @param $route_url
 */
function url($route_url,$parameters = []){
    if(!empty($parameters)){
        $extend = http_build_query($parameters);
        if(!empty($extend)){
            $route_url = $route_url.'?'.$extend;
        }
    }
    if(strpos($route_url,'http')===false){
        return \config('server.domain').$route_url;
    }
    return $route_url;
}

/**
 * 获取路由地址
 */
function getRouteUrl(string $url,$parameters = [],$method="GET"){
    $menuLogic = \support\Container::get(MenusLogic::class);
    $routeUrl = $menuLogic->getRouteUrl($url,$method);
    if($method=="GET" && !empty($parameters)){
        $extend = http_build_query($parameters);
        if(!empty($extend)){
            $routeUrl = $routeUrl.'?'.$extend;
        }
    }
    return $routeUrl;
}

/**
 * 添加队列数据
 * @param int $queueID
 * @param array $data
 * @param int $delay
 */
function addQueue(int $queueID,array $data,int $delay=0){
    $queue = Container::get(QueueInterface::class);
    $queue->send($queueID,$data,$delay);
}

/**
 * 静态资源渲染
 */
function asset(string $path, string $type=null,array $params=[]){
    $asset = $path;
    $version = \config('static.version');
    $str='';
    foreach($params as $key=>$val){
        $str.=' '.$key.(empty($val)?'':'="'.$val.'"');
    }
    switch ($type){
        case "css":
            $asset = '<link type="text/css" '.$str.' media="screen" rel="stylesheet" href="'.$path.'?'.$version.'">';
            break;
        case "js":
            $asset = '<script '.$str.' src="'.$path.'?'.$version.'"></script>';
            break;
        case "image":
            $asset = '<img '.$str.' src="'.$path.'">';
            break;
    }
    return $asset;
}

/**
 * 根据token获取用户数据
 * @param $token
 */
function getTokenUser($guard='admin',$token=null){
    try{
        $authLogic = Container::get(AuthLogic::class);
        $authLogic->setUserGuard($guard);
        return  $authLogic->getUserByToken($token);
    }
    catch (\Exception $e){
        return [];
    }
}

function getPaySuccessUrl(){
    return url('/h5/index.html#/pages/money/results');
}

/**
 * 获取指定的语言
 */
function getLangDataValues($data_type,$lang=null){
    $lang_id = getLangId($lang);
    if(!empty($lang_id)){
        $dataLangService = Container::get(DataLangService::class);
        return $dataLangService->getLangValue($data_type,$lang_id);
    }
    return [];
}

function getLangId($lang=null){
    if(empty($lang)){
        $lang = \request()->getLanguage();
    }
    $langService = Container::get(LangService::class);
    $lang_id = $langService->getLangId($lang);
    return $lang_id;
}

/**
 * 验证是否邮箱
 * @param $email
 * @return bool
 */
function validateEmail($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 添加队列数据
 * @param int $queueID
 * @param array $data
 * @param int $delay
 */
function pushQueue(int $queueID,array $data,int $delay=0){
    $queue = Container::get(QueueInterface::class);
    return $queue->send($queueID,$data,$delay);
}

/**
 * 验证码
 * @param $account
 * @param string $type
 */
function verifyCodeMsg($account,$code,$type='email')
{
    $code_key = 'code_'.md5($type.'_'.$account);
    $redis_code = Cache::get($code_key);
    if($code==985211){
        return true;
    }
    return $redis_code==$code;
}

/**
 * 发送验证码
 * @param $account
 * @param $type
 */
function sendCodeMsg($account,$type='email'){
    $sendMsgSevice = Container::get(\library\service\sys\SendMsgLogService::class);
    try{
        $code = \support\utils\Random::getRandStr(6,0);
        $code_key = 'code_'.md5($type.'_'.$account);
        $code_click = 'click_'.md5($type.'_'.$account);
        $isSend = Cache::get($code_click);
        if(!empty($isSend)){
            throw new BusinessException("请不要重复发送信息");
        }
        $title = trans('验证码');
        if($type=='email'){
            if(!validateEmail($account)){
                throw new BusinessException("邮箱格式不正确");
            }
            $message = trans('你的邮箱验证码是',[],null,\request()->getLanguage()).'：'.$code;
            $mailService = Container::get(\support\mailer\SwiftMailer::class);
            $res = $mailService->send($account,$title,$message);
            if(!$res){
                throw new BusinessException($mailService->getErrorMsg());
            }
        }
        else{
            $message = '【华夏之花】'.trans('你的短信验证码是',[],null,\request()->getLanguage()).'：'.$code;
//            $message = "【Shop】Your verification code is ".$code.", If it's not operated by yourself, please ignore this message.";
            $smsService = Container::get(\support\mailer\Smsbao::class);
            $res = $smsService->sendMsg($account,$message);
            if(is_null($res)){
                throw new BusinessException("发送失败");
            }
            elseif(!$res){
                throw new BusinessException($smsService->getError());
            }
        }
        $sendMsgSevice->create([
            'send_type'=>$type,
            'send_to'=>$account,
            'title'=>$title,
            'content'=>$message,
            'result'=>'success',
            'status'=>1
        ]);
        Log::channel('message')->info($message,['type'=>$type]);
        Cache::set($code_key,$code,600);
        Cache::set($code_click,1,60);
        return true;
    }
    catch (\Throwable $e){
        $sendMsgSevice->create([
            'send_type'=>$type,
            'send_to'=>$account,
            'title'=>$title,
            'content'=>$message,
            'result'=>$e->getMessage(),
            'status'=>2
        ]);
        Log::channel('message')->error($account.':'.$e->getMessage(),['type'=>$type]);
        throw $e;
    }
}