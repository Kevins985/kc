<?php

namespace support\extend;

use app\backend\controller\common\Menu;
use Carbon\Carbon;
use library\logic\MenusLogic;
use support\Container;
use support\exception\AuthorizeException;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\persist\QueueInterface;
use support\utils\Data;

/**
 * Class StaticFile
 * @package support\extend\Middleware
 */
class Middleware
{
    public function process(Request $request, callable $next)
    {
        $request->runtime = Carbon::now()->getTimestampMs();
        return $next($request);;
    }

    /**
     * 验证权限
     * @param Request $request
     * @param string $type
     */
    /**
     * 验证用户权限
     * @param $type
     * @throws VerifyException
     * @throws \Casbin\Exceptions\CasbinException
     * @throws \ReflectionException
     */
    public function verifyUserGrant(Request $request,$type="rbac"){
        $url = $request->getAppUrl();
        $method = $request->method();
        $loginUser = null;
        switch ($request->app){
            case 'backend':
                $request->guard='admin';
                $loginUser = getTokenUser('admin',$request->getUserToken());
                break;
            case 'api':
                $request->guard='user';
                $loginUser = getTokenUser('user',$request->getUserToken());
        }
        $menuLogic = Container::get(MenusLogic::class);
        $menuLogic->verifyUserGrant($loginUser,$url,$method,$type);
    }

    /**
     * 验证签名
     * @param Request $request
     */
    protected function verifyRequestSign(Request $request){
        if(validation_sign($request->app)){
            if(env('APP_ENV')!='prod'){
                return true;
            }
            if($request->app!='backend'){
                $request->guard='user';
            }
            $url_expire = config('app.url_expire');
            $headers['token'] = $request->getUserToken();                                //登陆授权后的用户token
            $headers['sign'] = $request->header('sign');                           //请求的签名字符，一般是根据请求参数加密
            $headers['timestamp'] = $request->header('timestamp');                 //时间戳
            $headers['version'] = $request->header('version');                     //客户端版本 如:1.0
            $headers['lang'] = $request->getLanguage();                                   //语言 如:en、zh
//            $headers['lang'] = $request->getLanguage();                                //语言 如:en、zh
//            $headers['traceId'] = $request->header('traceId');                        //全链路跟踪ID
            $request->verifyRequestData("public", $headers);
            $sign = Data::sign($headers);
            if (abs(time() - $headers['timestamp']) > $url_expire) {
                throw new AuthorizeException('时间戳异常', 400);
            }
            elseif ($sign != $headers['sign']) {
                throw new AuthorizeException('签名错误', 400);
            }
        }
    }

    /**
     * 写日志
     * @param Request $request
     */
    protected function writeRequestLog(Request $request)
    {
        if(write_operation_log($request->app)){
            $requestData = $request->post();
            $data = [
                'app'=>$request->app,
                'request_url'=>$request->uri(),
                'request_method'=>$request->method(),
                'refer_url'=> $request->header("referer"),
                'client_ip'=> $request->getLastRealIp(),
                'request_date'=>date('Y-m-d'),
                'user_id'=>$request->getUserID(),
                'request_data'=> $requestData
            ];
            addQueue(QueueWriteLogs,$data);
        }
    }
}
