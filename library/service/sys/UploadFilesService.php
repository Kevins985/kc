<?php

namespace library\service\sys;

use support\extend\Redis;
use support\extend\Service;
use library\model\sys\UploadFilesModel;

class UploadFilesService extends Service
{
    public function __construct(UploadFilesModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取文件地址
     * @param type $json JSON数据
     * @param type $size 尺寸
     */
    public function getResourceUrl($file_md5,$size=null){
        $cache_key = 'upload_file';
        $file_url = Redis::hGet($cache_key,$file_md5);
        if(empty($file_url)){
            $uploadObj = $this->get($file_md5,'file_md5');
            if(!empty($uploadObj['file_url'])){
                $file_url = upload_url($uploadObj['file_url'],$size);
                Redis::hSet($cache_key,$file_md5,$file_url);
            }
        }
        return $file_url;
    }
}
