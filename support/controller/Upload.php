<?php

namespace support\controller;

use library\service\sys\UploadFilesService;
use library\validator\sys\UploadFilesValidation;
use support\extend\Controller;
use support\exception\BusinessException;
use support\extend\Request;
use support\upload\Upload as UploadFile;

/**
 * 上传 访问模式controller 继承
 */
class Upload extends Controller{

    protected $_extAllow = [
        'jpg','jpeg', 'png','gif'
    ];
    protected $engine = 'oss';      //local
    protected $_maxSize = 5120000;
    protected $upload_dir =  null;
    protected $upload_path = null;
    protected $img_quality = 100;
    protected $waterImage = null;
    protected $file_path = '/';

    /**
     * 初始化
     */
    public function __construct(UploadFilesService $service,UploadFilesValidation $validation){
        $this->service = $service;
        $this->validation = $validation;
    }

    /**
     * 初始化数据
     */
    public function beforeAction(Request $request)
    {
        try{
            $this->request = $request;
            $this->response->setRequest($request);
            $this->upload_dir = public_path("uploads");
            $this->upload_path = $this->request->getDomainUrl("uploads");
            locale($request->getLanguage());
            $this->loginUser = getTokenUser();
        }
        catch (\Exception $e){
            if($request->isAjax()){
                return $this->response->json(false,[],$e->getMessage(),$e->getCode());
            }
            else{
                return redirect($request->getLoginUrl());
            }
        }
    }


    public function afterAction(Request $request)
    {

    }

    /**
     * 获取裁切的图片尺寸
     * @param $type 类型
     * @return array|string[]
     */
    protected function getImgSize($type=null) {
        $file_type = $this->request->app.'_'.$this->request->getControllerName().'_'.$type;
        switch($file_type) {
//            case 'backend_upload_article':
//                return array(1 => '640x200');
        }
        return [];
    }
    
    /**
     * 上传需要缩略的图片
     * @param type $name 表单文件名
     * @param type $type 文件类型
     */
    protected function dispUploadFile($name,$type='item') {
        $file = $this->request->file($name);
        if ($file && $file->isValid()){
            $res = $this->checkFileExists($file);
            if(empty($res)){
                $file_path = $this->file_path . $type;
                $uploadObj = new UploadFile([
                    'engine'=>$this->engine,
                    'uploadPath'=>$this->upload_path,
                    'rootPath'=>$this->upload_dir,
                    'filePath'=>$file_path,
                    'allowType'=>$this->_extAllow,
                    'maxSize'=>$this->_maxSize,
                    'isRandName'=>true,
                    'imgQuality'=>$this->img_quality,
                    'cutArray'=>$this->getImgSize($type),
                ]);
                $res = $uploadObj->uploadFile($file,$this->waterImage);
                if($res){
                    return $this->saveUploadFile($uploadObj,$type);
                }else{
                    throw new BusinessException($uploadObj->getErrorMsg());
                }
            }
            return $res;
        }
        else{
            throw new BusinessException('上传文件不存在');
        }
    }
    
    /**
     * CURL上传需要缩略的图片
     * @param type $url 文件地址
     * @param type $type 文件类型
     */
    protected function dispCurlUploadFile($url,$type='item') {
        if (!empty($type) && !empty($url)) {
            $res = $this->checkFileExists($url);
            if(empty($res)){
                $file_path = $this->file_path . $type;
                $uploadObj = new UploadFile([
                    'engine'=>$this->engine,
                    'uploadPath'=>$this->upload_path,
                    'rootPath'=>$this->upload_dir,
                    'filePath'=>$file_path,
                    'allowType'=>$this->_extAllow,
                    'isRandName'=>true,
                    'maxSize'=>$this->_maxSize,
                    'imgQuality'=>$this->img_quality,
                    'cutArray'=>$this->getImgSize($type),
                ]);
                $res = $uploadObj->uploadCurlFile($url,$this->waterImage);
                if($res){
                    return $this->saveUploadFile($uploadObj,$type);
                }
                else{
                    throw new BusinessException($uploadObj->getErrorMsg());
                }
            }
            return $res;
        }
        else{
            throw new BusinessException('上传文件不存在');
        }
    }

    /**
     * 查看文件的hash
     * @param $file
     */
    private function checkFileExists($file){
        if(is_string($file)){
            $fileHash = md5_file($this->tmpFileName);
        }
        else{
            $fileHash = md5_file($file->getPathname());
        }
        $row = $this->service->get($fileHash,'file_md5');
        if(!empty($row)){
            return [
                "file_url"=>$row->getFileUrl(),
                'file_md5'=>$row["file_md5"],
                'type'=>$row['from_type'],
                'cut'=>[]
            ];
        }
        return null;
    }
    
    /**
     * 上传需要缩略的图片
     * @param UploadFile $uploadObj 上传对象
     * @param string $type 文件类型
     * @return {file_id,file_url,cut_name,cut_url}
     */
    private function saveUploadFile(UploadFile $uploadObj,string $type){
        $data = array(
            'user_id'=>$this->request->getUserID(),
            'from_type'=>$type,
            'engine'=>$this->engine,
            'file_name'=>$uploadObj->getNewFileName(),
            'file_url'=>$uploadObj->getUploadFileUrl(),
            "file_path"=>$uploadObj->getFilePath(),
            'file_ext'=>$uploadObj->getFileType(),
            'file_size'=>$uploadObj->getFileSize(),
            'origin_name'=>$uploadObj->getOriginName(),
            'width'=>$uploadObj->getImageWidth(),
            'height'=>$uploadObj->getImageHeight(),
            'file_md5'=>$uploadObj->getFileHash()
        );
        $res = $this->service->firstOrCreate(["file_md5"=>$data['file_md5']],$data);
        if(!empty($res)){
            if($res['file_name']!=$data['file_name']){
                unlink(public_path("uploads/".$data["file_path"]));
            }
            $cutAry = $uploadObj->getCutFileUrls();
            foreach($cutAry as $k=>$url){
                $cutAry[$k] = upload_url($url);
            }
            $result = [
                "file_url"=>upload_url($res['file_url']),
                'file_md5'=>$res["file_md5"],
                'type'=>$type,
                'cut'=>$cutAry
            ];
            return $result;
        }
        else{
            return [];
        }
    }
}
