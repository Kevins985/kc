<?php

use library\service\sys\UploadFilesService;
use support\extend\Request;
use support\extend\Response;
use support\extend\Translation;
use support\utils\Twig;
use Webman\App;
use Webman\Config;
use Webman\Route;
use library\logic\MenusLogic;
use support\Container;

define('BASE_PATH', realpath(__DIR__ . '/../'));

/**
 * @return string
 */
function base_path(string $name=null)
{
    $path = BASE_PATH;
    if(!empty($name)){
        $path.=DIRECTORY_SEPARATOR.$name;
    }
    return $path;
}

/**
 * @return string
 */
function app_path(string $name=null)
{
    $path = BASE_PATH . DIRECTORY_SEPARATOR . 'app';
    if(!empty($name)){
        $path.=DIRECTORY_SEPARATOR.$name;
    }
    return $path;
}

/**
 * @return string
 */
function config_path(string $name=null)
{
    $path = BASE_PATH . DIRECTORY_SEPARATOR . 'config';
    if(!empty($name)){
        $path.=DIRECTORY_SEPARATOR.$name;
    }
    return $path;
}

/**
 * @return string
 */
function library_path(string $name=null)
{
    $path = BASE_PATH . DIRECTORY_SEPARATOR . 'library';
    if(!empty($name)){
        $path.=DIRECTORY_SEPARATOR.$name;
    }
    return $path;
}

/**
 * @return string
 */
function public_path(string $name=null)
{
    $path = BASE_PATH . DIRECTORY_SEPARATOR . 'public';
    if(!empty($name)){
        $path.=DIRECTORY_SEPARATOR.$name;
    }
    return $path;
}

/**
 * @return string
 */
function public_url(string $name=null)
{
    $path = '';
    if(!empty($name)){
        $path.=DIRECTORY_SEPARATOR.$name;
    }
    return $path;
}

/**
 * @return string
 */
function static_url(string $name=null)
{
    $path = public_url("static");
    if(!empty($name)){
        $path.=$name;
    }
    return $path;
}

/**
 * @return string
 */
function upload_path(string $name=null)
{
    $path = public_path("uploads");
    if(!empty($name)){
        $path.=$name;
    }
    return $path;
}

/**
 * @return string
 */
function upload_url(string $url=null,$size=null)
{
    if(strpos($url,'http')===0){
        return $url;
    }
    $path = public_url("uploads");
    if(!empty($url)){
        $path.=$url;
    }
    elseif(!is_null($url)){
        $path = static_url('/common/images/nopic.png');
    }
    return $path;
}

/**
 * @param $file_md5
 * @param $size
 * @return string
 */
function upload_md5_url($file_md5,$size=null){
    if(empty($file_md5)){
        return static_url('/common/images/nopic.png');
    }
    $uploadService = Container::get(UploadFilesService::class);
    return $uploadService->getResourceUrl($file_md5,$size);
}

/**
 * @return string
 */
function resource_path(string $name=null)
{
    $path = BASE_PATH . DIRECTORY_SEPARATOR . 'resource';
    if(!empty($name)){
        $path.=DIRECTORY_SEPARATOR.$name;
    }
    return $path;
}

/**
 * @return string
 */
function runtime_path(string $name=null)
{
    $path = BASE_PATH . DIRECTORY_SEPARATOR . 'runtime';
    if(!empty($name)){
        $path.=DIRECTORY_SEPARATOR.$name;
    }
    return $path;
}

/**
 * @return Request
 */
function request()
{
    return App::request();
}

/**
 * @param int $status
 * @param array $headers
 * @param string $body
 * @return Response
 */
function response($body = '', $status = 200, $headers = array())
{
    return new Response($status, $headers, $body);
}

/**
 * @param $data
 * @param int $options
 * @return Response
 */
function json($data, $options = JSON_UNESCAPED_UNICODE)
{
    return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
}

/**
 * @param $xml
 * @return Response
 */
function xml($xml)
{
    if ($xml instanceof SimpleXMLElement) {
        $xml = $xml->asXML();
    }
    return new Response(200, ['Content-Type' => 'text/xml'], $xml);
}

/**
 * @param $data
 * @param string $callback_name
 * @return Response
 */
function jsonp($data, $callback_name = 'callback')
{
    if (!is_scalar($data) && null !== $data) {
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
    }
    return new Response(200, [], "$callback_name($data)");
}

/**
 * @param $location
 * @param int $status
 * @param array $headers
 * @return Response
 */
function redirect($location, $status = 302, $headers = [])
{
    $response = new Response($status, ['Location' => $location]);
    if (!empty($headers)) {
        $response->withHeaders($headers);
    }
    return $response;
}

/**
 * 命令行输出数据
 * @param $msg
 */
function writeln($msg){
    echo $msg.PHP_EOL;
}

/**
 * 给视图层传递参数
 */
function assign($key,$value=null)
{
    Twig::assign($key, $value);
}

/**
 * 视图层渲染
 */
function view($template, $vars = [], $app = null)
{
    return Twig::render($template, $vars, $app);
}

/**
 * 模版层渲染
 */
function layout($template, $layout, $app = null)
{
    return Twig::layout($template, $layout, $app);
}

/**
 * @param $key
 * @param null $default
 * @return mixed
 */
function config($key = null, $default = null)
{
    return Config::get($key, $default);
}

/**
 * @param $name
 * @param array $parameters
 * @return string
 */
function route($name, $parameters = [])
{
    $route = Route::getByName($name);
    if (!$route) {
        return '';
    }
    return $route->url($parameters);
}

/**
 * @param null $key
 * @param null $default
 * @return mixed
 */
function session($key = null, $default = null)
{
    $session = request()->session();
    if (null === $key) {
        return $session;
    }
    if (\is_array($key)) {
        $session->put($key);
        return null;
    }
    return $session->get($key, $default);
}

/**
 * @param null|string $id
 * @param array $parameters
 * @param string|null $domain
 * @param string|null $locale
 * @return string
 */
function trans(string $id, array $parameters = [], string $domain = null, string $locale = null)
{
    $res = Translation::trans($id, $parameters, $domain, $locale);
    return $res === '' ? $id : $res;
}

/**
 * @param null|string $locale
 * @return string
 */
function locale(string $locale = null)
{
    if (!$locale) {
        return Translation::getLocale();
    }
    Translation::setLocale($locale);
}

/**
 * @param $worker
 * @param $class
 */
function worker_bind($worker, $class) {
    $callback_map = [
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWebSocketConnect'
    ];
    foreach ($callback_map as $name) {
        if (method_exists($class, $name)) {
            $worker->$name = [$class, $name];
        }
    }
    if (method_exists($class, 'onWorkerStart')) {
        call_user_func([$class, 'onWorkerStart'], $worker);
    }
}

/**
 * @return int
 */
function cpu_count() {
    // Windows does not support the number of processes setting.
    if (\DIRECTORY_SEPARATOR === '\\') {
        return 1;
    }
    if (strtolower(PHP_OS) === 'darwin') {
        $count = shell_exec('sysctl -n machdep.cpu.core_count');
    } else {
        $count = shell_exec('nproc');
    }
    $count = (int)$count > 0 ? (int)$count : 4;
    return $count;
}