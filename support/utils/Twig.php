<?php

namespace support\utils;

use library\logic\MenusLogic;
use library\service\sys\MenuService;
use support\Container;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use Webman\View;

/**
 * Class Blade
 * @package support\view
 * @example https://twig.symfony.com/doc/3.x/
 */
class Twig implements View
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * @param $name
     * @param null $value
     */
    public static function assign($name, $value = null)
    {
        static::$_vars = \array_merge(static::$_vars, \is_array($name) ? $name : [$name => $value]);
    }

    /**
     * @param $template
     * @param $vars
     * @param string $app
     * @return mixed
     */
    public static function render($template, $vars, $app = null)
    {
        static $views = [], $view_suffix;
        $view_suffix = $view_suffix ? : \config('app.view_suffix', 'html');
        $app = $app === null ? \request()->app : $app;
        if (!isset($views[$app])) {
            $view_path = $app === '' ? \app_path(). '/view/' : \app_path(). "/$app/view/";
            $views[$app] = new Environment(new FilesystemLoader($view_path), \config('app.view_options', []));
            self::addExtensions($views[$app]);
        }
        $vars = \array_merge(static::$_vars, $vars);
        $content = $views[$app]->render("$template.$view_suffix", $vars);
        return $content;
    }

    /**
     * @param $template
     * @param $vars
     * @param string $app
     * @return mixed
     */
    public static function layout($template, $vars, $app = null)
    {
        static $views = [], $view_suffix;
        $view_suffix = $view_suffix ? : \config('app.view_suffix', 'html');
        $app = $app === null ? \request()->app : $app;
        if (!isset($views[$app])) {
            $view_path = $app === '' ? \app_path(). '/layouts/' : \app_path(). "/$app/layouts/";
            $views[$app] = new Environment(new FilesystemLoader($view_path), \config('app.view_options', []));
            self::addExtensions($views[$app]);
        }
        $vars = \array_merge(static::$_vars, $vars);
        $content = $views[$app]->render("$template.$view_suffix", $vars);
        static::$_vars = [];
        return $content;
    }

    /**
     * 添加扩展
     */
    private static function addExtensions(Environment &$env){
        //模版-功能
        $url_func = new TwigFunction("url",function (string $url,array $params=[],string $method="GET"){
            return getRouteUrl($url,$params,$method);
        });
        $env->addFunction($url_func);
        $trans_func = new TwigFunction("trans",function (string $name, array $params = [], string $domain = null, string $locale = null){
            return trans($name,$params,$domain,$locale);
        });
        $env->addFunction($trans_func);
        $asset_func = new TwigFunction("asset",function (string $path, string $type,array $params=[]){
            return asset($path,$type,$params);
        });
        $env->addFunction($asset_func);
        $render_func = new TwigFunction("render",function (string $uri,array $params=[]){
            return self::render($uri,$params);
        });
        $env->addFunction($render_func);
        $hasGrant_func = new TwigFunction("hasGrant",function (string $action=null,string $controller=null,string $app=null,string $method="GET"){
            try{
                $request = \request();
                $url = $request->getAppUrl($action,$controller,$app);
                $menuLogic = Container::get(MenusLogic::class);
                $loginUser = getTokenUser('admin',$request->getUserToken());
                $menuLogic->verifyUserGrant($loginUser,$url,$method,'rbac');
                return true;
            }
            catch (\Exception $e){
                return false;
            }
        });
        $env->addFunction($hasGrant_func);
        $menus_func = new TwigFunction("menus",function (string $type,string $param=null){
            return self::getUserMenus($type,$param);
        });
        $env->addFunction($menus_func);
        $crumb_func = new TwigFunction("crumb",function (){
            return self::getCrumbMenus();
        });
        $env->addFunction($crumb_func);
        $paginator_func = new TwigFunction("paginator",function ($page, $sumPage, $num){
            return self::getPaginateRange($page, $sumPage, $num);
        });
        $env->addFunction($paginator_func);
        $uploadUrl_func =new TwigFunction("uploadUrl",function ($file_url,$size=null){
            return upload_url($file_url,$size);
        });
        $env->addFunction($uploadUrl_func);
        $uploadMd5_func = new TwigFunction("uploadMd5",function ($file_md5,$size=null){
            return upload_md5_url($file_md5,$size);
        });
        $env->addFunction($uploadMd5_func);
        $staticUrl_func= new TwigFunction("staticUrl",function ($file_url){
            return static_url($file_url);
        });
        $env->addFunction($staticUrl_func);
        $jsonDecode_func= new TwigFunction("json_decode",function ($json){
            return json_decode($json,true);
        });
        $env->addFunction($jsonDecode_func);
        $label_func= new TwigFunction("label",function ($num,$data,$attr=''){
            //default,primary,success,info,warning,danger
            foreach($data as $k=>$v){
                $data[$k]='<span '.$attr.' class="label label-sm label-'.$v[0].'">'.$v[1].'</span>';
            }
            return isset($data[$num])?$data[$num]:'';
        });
        $env->addFunction($label_func);
        /******************************************************/
        //模版-过滤器
        $trans_filter = new TwigFilter("trans",function (string $name, array $params = [], string $domain = null, string $locale = null){
            return trans($name,$params,$domain,$locale);
        });
        $env->addFilter($trans_filter);
        $count_filter = new TwigFilter("count",function (array $data){
            return count($data);
        });
        $env->addFilter($count_filter);
        $empty_filter = new TwigFilter("empty",function (string $name, string $true='',string $false=''){
            return empty($name)?$true:$false;
        });
        $env->addFilter($empty_filter);
        $eq_filter = new TwigFilter("eq",function ($val1,$val2,string $true='',string $false=''){
            return $val1==$val2?$true:$false;
        });
        $env->addFilter($eq_filter);
        $bitwise_filter = new TwigFilter("bitwise",function (int $data,string $type,int $num){
            switch ($type){
                case '&':           //与,两个位都为1时，结果才为1
                    return $data & $num;
                case '|':           //或,两个位都为0时，结果才为0
                    return $data | $num;
                case '^':           //异或,两个位相同为0，相异为1
                    return $data ^ $num;
                case '<<':          //左移,各二进位全部左移若干位，高位丢弃，低位补0
                    return $data << $num;
                case '>>':          //右移,各二进位全部右移若干位，对无符号数，高位补0
                    return $data >> $num;
            }
            return null;
        });
        $env->addFilter($bitwise_filter);
        /******************************************************/
        //模版-测验
        $admin_test = new TwigTest("admin",function (string $token=null){
            return \request()->checkIsAdmin($token);
        });
        $env->addTest($admin_test);
        $isLogin_test = new TwigTest("isLogin",function (string $token=null){
            return \request()->checkIsLogin($token);
        });
        $env->addTest($isLogin_test);
    }

    /**
     * 获取用户的菜单
     */
    private static function getUserMenus(string $type,string $param=null){
        $menuLogic = Container::get(MenusLogic::class);
        $userid = request()->getUserID();
        if(request()->checkIsAdmin()){
            $userid = -1;
        }
        if($type=='header'){
            return $menuLogic->getUserSelectMenus($userid,0);
        }
        elseif($type=='sidebar'){
            $menus = \request()->getCurrentMenu();
            $top_id = (isset($menus['top_id'])?$menus['top_id']:1);
            return $menuLogic->getUserAllMenus($userid,$top_id);
        }
        elseif($type=='action'){
            $menus = \request()->getCurrentMenu();
            if($menus['menu_type']==2){
                $list_id = (isset($menus['list_id'])?$menus['list_id']:0);
                return $menuLogic->getUserSelectMenus($userid,3,$list_id,$param);
            }
            else{
                $parent_id = (isset($menus['id'])?$menus['id']:0);
                return $menuLogic->getUserSelectMenus($userid,4,$parent_id,$param);
            }
        }
        elseif($type=='tools'){

        }
        return [];
    }

    /**
     * 获得数据的起始页和终止页
     * @param <page> 当前页面
     * @param <sumPage> 总页数
     * @param <num> 显示的数据
     * @return <type>
     */
    private static function getPaginateRange($page, $sumPage, $num = 5) {
        $start = 1;
        $end = $sumPage;
        if ($sumPage > $num) {
            if ($page <= ceil($num / 2)) {
                $end = $num;
            } else if ($sumPage - $page < ceil($num / 2)) {
                $start = $sumPage - ($num - 1);
                $end = $sumPage;
            } else {
                $start = $page - floor($num / 2);
                $end = $page + floor($num / 2);
            }
        }
        return range($start,$end);
    }

    /**
     * @return string
     */
    private static function getCrumbMenus(){
        $html = '<li>
                    <a href="'.url('/backend/main/index').'">首页</a>
                    <i class="fa fa-caret-right"></i>
                </li>';
        $menus = \request()->getCurrentMenu();
        if(!empty($menus)){
            if(isset($menus['menu_type']) && $menus['menu_type']>2){
                $menuService = Container::get(MenuService::class);
                $parentMenuObj = $menuService->get($menus['parent_id']);
                $html .= '<li>
                            &nbsp;<a href="'.url($parentMenuObj['route_url']).'">'.$parentMenuObj['menu_name'].'</a>
                            <i class="fa fa-caret-right"></i>
                         </li>';
            }
            $html.='<li><span>'.$menus['name'].'</span></li>';
        }
        return $html;
    }
}
