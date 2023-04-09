<?php

namespace support\extend;

/**
 * 布局插件
 */
class Layout {

    /**
     * layout 模板路径
     * @var string
     */
    public $path = null;
    /**
     * 版本号
     * @var int
     */
    private $version = 0;

    /**
     * view 对象
     * @var Response
     */
    private $view = null;

    /**
     * 请求体 对象
     * @var Request
     */
    private $request = null;

    /**
     * seo 数据
     * @var array
     */
    private $seo_meta = [];

    /**
     * 当前登陆用户
     * @var array
     */
    private $loginUser = [];

    public function __construct(){
        if(env('APP_ENV')!='dev'){
            $this->version = config('static.version');
        }
        else{
            $this->version = time();
        }
    }
    
    /**
     * 设置 view 对象
     * @param Response $view
     */
    public function setView(Response $view) {
        $this->view = $view;
    }

    /**
     * 设置 laytout 路径
     * @param string $path
     */
    public function setLayout(string $path) {
        $this->path = $path;
    }

    /**
     * 设置 request 路径
     * @param Request $request
     */
    public function setRequest(Request $request) {
        $this->request = $request;
    }

    /**
     * 设置 登陆用户数据
     * @param array $loginUser
     */
    public function setLoginUser($loginUser) {
        $this->loginUser = $loginUser;
    }
    
    /**
     * 设置 seo 数据
     * @param string $data {title,keywords,description}
     */
    public function setSeoMeta(array $data) {
        $this->seo_meta = $data;
    }

    /**
     * 获取每个页面的 seo meta 信息
     * @return array
     */
    private function getSeoMeta() {
        $seoCfg = config('seo');
        $module = strtolower($this->request->app);
        $controller = $this->request->getControllerName();
        $action = strtolower($this->request->action);
        $level1 = $module . '-' . $controller . '-' . $action;
        $level2 = $module . '-' . $controller;
        $level3 = $module;
        $metaInfo = '';
        if (isset($seoCfg[$level1])) {
            $metaInfo = $seoCfg[$level1];
        } 
        else if (isset($seoCfg[$level2])) {
            $metaInfo = $seoCfg[$level2];
        } 
        else if (isset($seoCfg[$level3])) {
            $metaInfo = $seoCfg[$level3];
        } 
        else {
            $metaInfo = $seoCfg['common'];
        }
        if(!empty($this->seo_meta) && !empty($metaInfo)){
            $metaInfo = array_merge_recursive($metaInfo,$this->seo_meta);
            foreach($metaInfo as &$k){
                if(is_array($k)){
                    $k = implode('-', $k);
                }
            } 
        }
        elseif(!empty($this->seo_meta)){
            $metaInfo = $this->seo_meta;
        }
        return $metaInfo;
    }

    /**
     * 获取注册的js数据
     */
    public function getScriptAssign($scriptAssign=[]){
        $scriptAssign = !empty($scriptAssign)?$scriptAssign:[];
        $domain = $this->request->header("host");
        $resurceDomain = [
            'WEB'     => $this->request->getDomainUrl(),
            'STATIC'     => $this->request->getDomainUrl('static'),
            'PLUGINS'    => $this->request->getDomainUrl('/static/plugins'),
        ];
        $staticVersion = $this->version;
        $scriptJson = json_encode($scriptAssign,JSON_UNESCAPED_UNICODE);
        $resurceJson = json_encode($resurceDomain,JSON_UNESCAPED_UNICODE);
        $output = <<<JS
        <script type="text/javascript">
            var version = {$staticVersion};
            var initScriptData = {$scriptJson};
            var domain   = {$resurceJson};
            function getCookieDomain (){
                return "{$domain}";
            }
        </script>\r\n
JS;
        return $output;
    }

    /**
     * 获取 html doctype
     * @return string
     */
    private function getDoctype() {
        return "<!DOCTYPE html>";
    }

    public function toArray(array $data=[]){
        $script = $this->view->getScriptAssign();
        $data['layout']=[
            'version'=>$this->version,
            'doctype'=>$this->getDoctype(),
            'seo'=>$this->getSeoMeta(),
            'controller'=>$this->request->getControllerName(),
            'action'=>$this->request->action,
            'content'=> $this->view->rawBody(),
            'script'=>$this->getScriptAssign($script)
        ];
        $data['login'] = $this->loginUser;
        return $data;
    }
}