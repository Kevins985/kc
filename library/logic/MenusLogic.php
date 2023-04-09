<?php

namespace library\logic;

use library\model\sys\MenuModel;
use library\service\sys\AdminService;
use library\service\sys\RouteService;
use library\service\sys\RoleService;
use library\service\sys\MenuService;
use support\exception\AuthorizeException;
use support\exception\VerifyException;
use support\extend\Cache;
use support\extend\Casbin;
use support\Container;
use support\extend\Logic;
use support\extend\Redis;
use support\utils\Data;

/**
 * 后台菜单逻辑层
 * @author Kevin
 */
class MenusLogic extends Logic
{

    /**
     * @Inject
     * @var MenuService
     */
    public $menuService;

    /**
     * @Inject
     * @var RouteService
     */
    public $routeService;

    /**
     * @Inject
     * @var ReflectionLogic
     */
    private $reflectLogic;

    /**
     * 获取菜单显示类型
     */
    public function getMenuTypes() {
        return [
            0 => '模块导航',
            1 => '左部菜单',
            2 => '左部功能',
            3 => '列表功能',
            4 => '特殊功能'
        ];
    }

    /**
     * 初始化权限列表
     * @param string $app 模块
     * @param false $isFilterDb 是否过滤数据中已经存在
     * @return array
     */
    public function initAppRouteMethod(string $app,$isFilterDb=false){
        try{
            $data = [];
            $controllers = $this->reflectLogic->getAppControllerList($app);
            foreach($controllers as $v){
                $list = $this->reflectLogic->getControllerReflectionList($v['class'],'not_authorized');
                if(!empty($list)){
                    $actions = $this->routeService->pluck('action',['module'=>$app,'controller'=>$v['name']]);
                    foreach($list as $c){
                        $url = $v['url'].'/'.$c['method']->name;
                        if($isFilterDb){
                            if(in_array($c['method']->name,$actions)){
                                continue;
                            }
                        }
                        $id = route_id($url);
                        $data[] = [
                            'id'=>$id,
                            'module'=>$app,
                            'controller'=>$v['name'],
                            'action'=>$c['method']->name,
                            'class'=>$v['class'],
                            'middleware'=>(in_array($app,['backend','merchant'])?'["AuthMiddleware"]':''),
                            'url'=>$url,
                        ];
                    }
                }
            }
            if(!empty($data)){
                $this->routeService->insert($data);
            }
            return count($data);
        }
        catch (\Exception $e){
            return false;
        }
    }

    /**
     * 获取菜单的下级菜单
     * @return array
     */
    public function getNotInDatabaseActions($module,$controller) {
        $actions = $this->routeService->pluck('action',['module'=>$module,'controller'=>$controller,'verify'=>2,'status'=>0]);
        return $actions;
    }

    /**
     * 获取所有的路由列表
     */
    public function getRouteList($app=null,$clearCache=true)
    {
        $cache_key = "route_list";
        if(!empty($app)){
            $cache_key.='_'.$app;
        }
        $data = Cache::get($cache_key);
        if(empty($data) || $clearCache){
            $rows  = $this->routeService->getSelectList($app);
            $data = [];
            foreach($rows as $v){
                $route_url = $v['url'];
                $middleware = [];
                if(!empty($v['middleware'])){
                    $middleware = json_decode($v['middleware'],true);
                    foreach($middleware as $k=>$name){
                        $middleware[$k] = sprintf("app\%s\middleware\%s",$v['module'],$name);
                    }
                }
                $methods = explode('|',$v['method']);
                $data[] = [
                    'methods'=>$methods,
                    'route_url'=>$route_url,
                    'class'=>$v['class'],
                    'action'=>$v['action'],
                    'middleware'=>$middleware
                ];
            }
            Cache::set($cache_key,$data,3600);
        }
        return $data;
    }

    /**
     * 获取具体的路由地址
     */
    public function getRouteUrl(string $url,string $method='GET'){
        $cache_key = "app_route";
        $route_id = route_id($url);
        $hash_key = $route_id;
        $route_url = Redis::hGet($cache_key,$hash_key);
        if(empty($route_url)){
            $params['id']=$route_id;
//            $params['method']=$method;
            $route_url = $this->routeService->value('url',$params);
            if(empty($route_url)){
                $route_url = $url;
            }
            Redis::hSet($cache_key,$hash_key,$route_url);
        }
        return $route_url;
    }

    /**
     * 获取用户所有的权限ID
     * @param int $user_id
     */
    public function getUserGrantIds(int $user_id){
        $casbin = Casbin::instance('rbac')->getPermissionsForUser('user'.$user_id);
        return Data::toFlatArray($casbin,1);
    }

    /**
     * 获取用户所有的菜单ID
     * @param int $user_id
     */
    public function getUserMenusIds(int $user_id){
        $adminService = Container::get(AdminService::class);
        $adminObj = $adminService->get($user_id);
        if(!empty($adminObj)){
            $ids = [];
            if(!empty($adminObj['menu_ids'])){
                $ids = array_merge($ids,json_decode($adminObj['menu_ids'],true));
            }
            if(!empty($adminObj['role_id'])){
                $roleService = Container::get(RoleService::class);
                $roleObj = $roleService->get($adminObj['role_id']);
                if(!empty($roleObj) && !empty($roleObj['menu_ids'])){
                    $ids = array_merge($ids,json_decode($roleObj['menu_ids'],true));
                }
            }
            if(!empty($ids)){
                return array_unique($ids);
            }
        }
        return [];
    }

    /**
     * 加载用户指定功能下面的权限
     * @param int $menu_id
     * @param int $menu_type
     * @return array
     */
    public function getUserSelectMenus($userid,$menu_type=0,$parent_id=0,$param=null) {
        $cache_key = 'logic.user_select_menus_'.$userid.'.'.$menu_type.'_'.$parent_id;
        $data = Cache::get($cache_key);
        if(empty($data)){
            $select = ['menu_id','menu_name','icon','route_url','route_id','choice_ids','btn_class','menu_path'];
            $where = ['menu_type'=>$menu_type,'status'=>1,'parent_id'=>$parent_id];
            if(!empty($param)){
                $where['param'] = $param;
            }
            $selector = $this->menuService->selector($where,['sort'=>'asc'],$select);
            if ($userid>0) {
                $menu_ids = $this->getUserMenusIds($userid);
                if(!empty($menu_ids)){
                    $selector->whereIn('menu_id',$menu_ids);
                }
                else{
                    $selector->where('menu_id','0');
                }
                $rows = $selector->get()->toArray();
                if($menu_type==0){
                    foreach($rows as $k=>$v){
                        $tmp = $this->menuService->value('route_url',['menu_type'=>2,'status'=>1,'menu_id'=>['in',$menu_ids],'menu_path'=>['left_like',$v['menu_path']]],['sort'=>'asc']);
                        $rows[$k]['route_url'] = $tmp;
                    }
                }
            }
            else{
                $rows = $selector->get()->toArray();
            }
            $data = $rows;
            Cache::set($cache_key,$rows,60);
        }
        return $data;
    }

    /**
     * 获取用户的功能列表
     * @return array
     */
    public function getUserAllMenus(int $user_id,int $parent_id) {
        $cache_key = 'logic.user_all_menus_'.$user_id.'.'.$parent_id;
        $data = Cache::get($cache_key);
        if(empty($data)){
            $select = ['menu_id','menu_name','parent_id','route_id','icon','route_url','menu_type'];
            $selector = $this->menuService->selector(['menu_type'=>['between',[1,2]],'status'=>1],['menu_type'=>'asc','sort'=>'asc'],$select);
            $selector->where('menu_path','like',','.$parent_id.',%');
            if ($user_id>0) {
                $menu_ids = $this->getUserMenusIds($user_id);
                if(!empty($menu_ids)){
                    $selector->whereIn('menu_id',$menu_ids);
                }
                else{
                    $selector->where('menu_id','0');
                }
            }
            $rows = $selector->get()->toArray();
            $menus = [];
            foreach($rows as $v){
                if($v['menu_type']==1){
                    $v['child'] = [];
                    $menus[$v['menu_id']] = $v;
                }
                elseif(isset($menus[$v['parent_id']])){
                    $menus[$v['parent_id']]['child'][$v['menu_id']] = $v;
                }
            }
            $data = $menus;
            Cache::set($cache_key,$menus,60);
        }
        return $data;
    }

    /**
     * 查询树级菜单需要的数据
     */
    public function queryTreeMenus($clearCache = false) {
        $cache_key = 'logic.admin_tree_menus';
        $data = Cache::get($cache_key);
        if ($clearCache || empty($data)) {
            $params = ['status'=>1,'admin_id'=>0];
            $selector = $this->menuService->selector($params,['sort'=>'asc','created_time'=>'asc'],['menu_id as id', 'parent_id as pId', 'menu_name as name']);
            $data = $selector->get()->toArray();
            Cache::set($cache_key, $data, 3600 * 4);
        }
        return $data;
    }

    /**
     * 获取菜单的下级菜单
     * @return array
     */
    public function getParentSelectMenus($menu_type = 0,$pid=null) {
        $params = ['status'=>1,'menu_type'=>['lt',$menu_type]];
        if(is_numeric($pid)){
            $params['parent_id'] = $pid;
        }
        $selector = $this->menuService->selector($params,['parent_id'=>'asc','sort'=>'asc']);
        $rows = $selector->get(['menu_id','menu_name','parent_id as pid'])->toArray();
        Data::$zoomAry = [];
        return Data::getArrayZoomList($rows,'menu_name','menu_id');
    }

    /**
     * 获取当前菜单数据
     * @return array
     */
    public function getCurrentMenu($url){
        $menuObj = $this->menuService->fetch([
            'route_id'=>route_id($url),
            'menu_type'=>['gt',1]
        ]);
        $menu = [];
        if(!empty($menuObj)){
            $arr = $menuObj->getMenuPath();
            $menu = [
                'top_id'=>$arr[0],
                'menu_id'=>$arr[1],
                'list_id'=>$arr[2],
                'icon'=>$menuObj['icon'],
                'name'=>$menuObj['menu_name'],
                'id'=>$menuObj['menu_id'],
                'parent_id'=>$menuObj['parent_id'],
                'route_url'=>$menuObj['route_url'],
                'menu_type'=>$menuObj['menu_type'],
                'url'=>$url
            ];
        }
        return $menu;
    }

    /**
     * 验证当前登陆用户权限
     * @param $url
     * @param $method
     * @param string $type {rbac,restful}
     * @throws VerifyException
     * @throws \Casbin\Exceptions\CasbinException
     */
    public function verifyUserGrant($loginUser,$url,$method,$type="rbac"){
        $route_id = route_id($url);
        $routeObj = $this->routeService->get($route_id);
        if(!empty($routeObj) && $routeObj['verify']>0){
            if(empty($loginUser)){
                throw new AuthorizeException('Unauthorized',403);
            }
            $userid = $loginUser['user_id'];
            if($routeObj['verify']>1 && !$loginUser['is_admin']){
                if($routeObj['verify']==3){
                    throw new AuthorizeException('Unauthorized',403);
                }
                //验证权限
                $userid = (is_numeric($userid)?'user'.$userid:$userid);
                $verify = Casbin::instance($type)->enforce($userid,$route_id,$method);
                if(!$verify){
                    throw new AuthorizeException('Unauthorized',403);
                }
            }
        }
//        else{
//            $reflectionLogic = Container::get(ReflectionLogic::class);
//            if(!$reflectionLogic->checkUrlIsAuthorized($url,$method)){
//                throw new AuthorizeException('Not Unauthorized',403);
//            }
//        }
    }

    /**
     * 创建菜单
     * @param type $data
     * @return MenuModel
     */
    public function create($data) {
        $conn = $this->connection();
        try{
            $conn->beginTransaction();
            $menu_path = '';
            if(!empty($data['parent_id'])){
                $parentObj = $this->menuService->get($data['parent_id']);
                $menu_path = $parentObj['menu_path'];
            }
            $model = $this->menuService->create($data);
            if($model){
                if(!empty($model['route_id'])){
                    $this->routeService->updateAll(['id'=>$model['route_id']],['status'=>1]);
                }
                $model->menu_path = (empty($menu_path)?',':$menu_path).$model['menu_id'].',';
                $model->save();
            }
            $conn->commit();
            return $model;
        }
        catch (\Exception $e){
            $conn->rollBack();
            throw $e;
        }
    }

    /**
     * 修改菜单数据
     * @param $id
     * @param array $data
     */
    public function update($id,$data) {
        $menu_path = '';
        if(!empty($data['parent_id'])){
            $parentObj = $this->get($data['parent_id']);
            $menu_path = $parentObj['menu_path'];
        }
        $data['menu_path'] = $menu_path.$data['menu_id'].',';
        return $this->menuService->update($id,$data);
    }
}
