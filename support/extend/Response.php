<?php

namespace support\extend;

use Carbon\Carbon;

class Response extends \Webman\Http\Response
{
    /**
     * 视图层对象
     * @Inject
     * @var Layout
     */
    public $layout;

    /**
     * 请求对象
     * @var Request
     */
    private $request;

    /**
     * JS注册的数据
     * @var array
     */
    private $scriptAssign=[];

    /**
     * 设置请求对象
     * @param Request $request
     */
    public function setRequest(Request $request){
        $this->request = $request;
    }

    /**
     * @param string $body
     * @param int $status
     * @param array $headers
     */
    public function output($body = '', $status = 200, $headers = []){
        $this->_body = $body;
        $this->_status = $status;
        $this->_header = $headers;
        return $this;
    }

    /**
     * 响应XML
     * @param $xml
     * @return $this
     */
    public function xml($xml)
    {
        if ($xml instanceof SimpleXMLElement) {
            $xml = $xml->asXML();
        }
        $this->_header = ['Content-Type' => 'text/xml'];
        $this->_body = (string)$xml;
        return $this;
    }

    /**
     * 文件下载
     * @param string $file
     * @param string $download_name
     * @return $this
     */
    public function download($file, $download_name = '')
    {
        if(empty($download_name)){
            $arr = explode('/',$file);
            $download_name = end($arr);
        }
        $new_response = new \Webman\Http\Response();
        return $new_response->download($file,$download_name);
    }

    /**
     * 添加需要注册的JS数据
     * @param array $params
     */
    public function addScriptAssign(array $params){
        foreach($params as $key=>$data){
            $this->scriptAssign[$key] = $data;
        }
    }

    /**
     * 添加需要注册的JS数据
     * @return array
     */
    public function getScriptAssign(){
        return $this->scriptAssign;
    }

    /**
     * @param $data
     * @param string $callback_name
     * @return $this
     */
    public function jsonp($data, $callback_name = 'callback')
    {
        if (!is_scalar($data) && null !== $data) {
            $data = json_encode($data);
        }
        $this->_header = [];
        $this->_status = 200;
        $this->_body = "$callback_name($data)";
        return $this;
    }

    /**
     * 异常json返回数据
     * @param array $data {data,msg,status}
     * @param int $status
     * @return $this
     */
    public function failJson($params=[],$status=401){
        $data = ['data'=>[],'msg'=>'error','status'=>-1];
        $data = array_merge($data,$params);
        $this->_header = ['Content-Type' => 'application/json'];
        $this->_status = $status;
        $this->_body = json_encode($data,JSON_UNESCAPED_UNICODE);
        return $this;
    }

    /**
     * 获取响应JSON数据
     * @param mixed $success
     * @param array $data
     * @param string $msg
     * @param int $code
     * @return $this
     */
    public function json($success, $data = [], $msg = 'success', $code = 0)
    {
        if (!is_array($data) && empty($data)) {
            $data = new \stdClass();
        }
        $res = [
            'status' => ($success ? 1 : 0),
            'data' => $data,
            'code' => $code,
            'msg' => trans($msg,[],null,$this->request->getLanguage()),
        ];
        if($this->request->runtime){
            $res['runtime'] = (Carbon::now()->getTimestampMs() - $this->request->runtime)/1000;
        }
        $this->_status = 200;
        $this->_header = ['Content-Type' => 'application/json'];
        $this->_body = json_encode($res,JSON_UNESCAPED_UNICODE);
        return $this;
    }

    /**
     * @param $location
     * @param int $status
     * @param array $headers
     * @return $this
     */
    public function redirect($location, $status = 302, $headers = [])
    {
        $this->_status = $status;
        $this->_header = ['Location' => $location];
        if (!empty($headers)) {
            $this->withHeaders($headers);
        }
        return $this;
    }

    /**
     * @param $template
     * @param array $vars
     * @param null $app
     * @return $this
     */
    public function view($template, $vars = [], $app = null)
    {
        $this->_status = 200;
        $this->_header = [];
        $vars["scriptAssign"] = $this->request->session()->get('scriptAssign');
        $this->_body = (string)view($template, $vars, $app);
        return $this;
    }

    /**
     * 获取body内容
     * @return string|null
     */
    public function getBody(){
        return $this->_body;
    }

    /**
     * @param $template
     * @param $layout
     * @param null $app
     * @return Response
     */
    public function layout($template,$vars = [],$app = null)
    {
        $view = $this->view($template, $vars, $app);
        $this->layout->setView($view);
        $this->layout->setRequest($this->request);
        $layout_template = $this->layout->path;
        $layout_vars = $this->layout->toArray();
        $layout_vars['cmenu'] = $this->request->getCurrentMenu();
        $this->_status = 200;
        $this->_header = [];
        $this->_body = (string)layout($layout_template, $layout_vars, $app);
        return $this;
    }

    /**
     * 给视图层传递参数
     * @param string $key 健
     * @param mixed $value 值
     */
    public function assign($key,$value=null){
        assign($key,$value);
    }
}