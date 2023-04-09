<?php

namespace app\backend\controller;

use library\service\sys\UploadFilesService;
use support\controller\Upload as Controller;
use support\extend\Request;

/**
 * Class Upload
 * @package app\backend\controller
 */
class Upload extends Controller
{
    public function __construct(UploadFilesService $service)
    {
        $this->service = $service;
    }

    /**
     * 本地图片上传
     */
    public function file(Request $request)
    {
        $callback = $this->getPost('callback');
        try {
            $type = $this->getPost('type', 'items');
            $num = $this->getPost('num',0);
            $dom = $this->getPost('dom');
            $result = $this->dispUploadFile('file', $type);
            if(empty($result)){
                throw new \Exception('上传数据失败');
            }
            $result['num'] = $num;
            $result['dom'] = $dom;
            $result['type'] = $type;
            if(empty($callback)){
                return $this->response->json(true,$result,'上传成功');
            }
            $json = json_encode(['status'=>1,'data'=>$result,'msg'=>'上传成功']);
        }
        catch (\Exception $e) {
            if(empty($callback)){
                return $this->response->json(false,[],$e->getMessage());
            }
            $json = json_encode(['status'=>0,'data'=>[],'msg'=>$e->getMessage()]);
        }
        if(!empty($callback)){
            return "<script type=\"text/javascript\">try{parent.".$callback."(" . $json . ");}catch(e){console.log(e)}</script>";
        }
        return $json;
    }

    /**
     * curl上传
     */
    public function curl(Request $request)
    {
        try {
            $url = $this->getPost('url');
            if (empty($url)) {
                throw new \Exception('图片地址不存在');
            }
            $type = $this->getPost('type', 'item');
            $num = $this->getPost('num',0);
            $dom = $this->getPost('dom');
            $result = $this->dispCurlUploadFile($url, $type);
            if(empty($result)){
                throw new \Exception('上传数据失败');
            }
            $result['num'] = $num;
            $result['dom'] = $dom;
            return $this->response->json(true,$result,'上传成功');
        }
        catch (\Exception $e) {
            return $this->response->json(false,[],$e->getMessage());
        }
    }

    /**
     * 图片上传
     */
    public function images(Request $request)
    {
        $type = $this->getParams('type','item');
        $params['page'] = $this->getParams('page', 1);
        $params['user_id'] = $request->getUserID();
        $data = $this->service->paginate('/backend/upload/images',$params);
        $data->appends(['type'=>$type]);
        $this->response->assign('type',$type);
        $this->response->assign('data',$data);
        return $this->response->view('upload/images');
    }

    /**
     * 文本编辑器图片上传
     */
    public function editor(Request $request)
    {
        try {
            $type = $this->getPost('type', 'editor');
            $result = $this->dispUploadFile('imgFile', $type);
            if(empty($result)){
                throw new \Exception('上传数据失败');
            }
            $json = json_encode(['error'=>0,'url'=>$result['file_url']]);
        }
        catch (\Exception $e) {
            $json = json_encode(['error' => 1, 'message' =>$e->getMessage()]);
        }
        return $json;
    }

    /**
     * 文本编辑器图片管理
     */
    public function editorManager(Request $request)
    {
        $order = $this->getParams('order');
        $orderBy = [];
        if($order=='NAME'){
            $orderBy['file_name']='desc';
        }elseif($order=='TYPE'){
            $orderBy['file_ext']='desc';
        }elseif($order=='SIZE'){
            $orderBy['file_size']='desc';
        }
        $params = ['from_type'=>'editor','user_id'=>$request->getUserID()];
        $rows = $this->service->fetchAll($params,$orderBy,['file_name as filename','file_ext as filetype','file_size as filesize','updated_time as datetime'])->toArray();
        foreach($rows as $k=>$v){
            $rows[$k]['is_dir'] = false;
            $rows[$k]['has_file'] = false;
            $rows[$k]['dir_path'] = false;
            $rows[$k]['is_photo'] = true;
            $rows[$k]['datetime'] = date('Y-m-d H:i:s',$v['datetime']);
        }
        if($this->engine=='oss'){
            $path = 'https://mutual-test.oss-cn-hangzhou.aliyuncs.com';
        }
        else{
            $path = upload_url();
        }
        $moveup_dir_path = 'editor';
        $current_dir_path = 'editor';
        $current_url=  $path.'/editor/';
        $result = [];
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($rows);
        //文件列表数组
        $result['file_list'] = $rows;
        //输出JSON字符串
        header('Content-type: application/json; charset=UTF-8');
        return json_encode($result);
    }
}