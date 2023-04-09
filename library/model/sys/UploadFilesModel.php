<?php

namespace library\model\sys;

use support\extend\Model;

class UploadFilesModel extends Model
{
    public $table = 'sys_upload_files';
    public $primaryKey = 'file_id';
    public $connection = 'mysql';
     
    protected $fillable = [
		"file_id",
		"file_md5",
		"user_id",
		"from_type",
		"engine",
		"file_name",
		"file_path",
		"origin_name",
		"file_url",
		"file_ext",
		"file_size",
		"width",
		"height",
    ];

    /**
     * 定义缓存
     * @var key_encrypt {md5,sha1}
     */
    protected static $_dbcache = [
        'file_md5'=>[
            'key'     => 'model.upload.%s',
            'field'   => 'file_md5',
            'expires' => 3600,
            'enable' => true,
        ],
    ];

    /**
     * 获取文件地址
     */
    public function getFileUrl(){
        if(strpos($this->file_url,'http')!==false){
            return $this->file_url;
        }
        return upload_url($this->file_url);
    }
}
