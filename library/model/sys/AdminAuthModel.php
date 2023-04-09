<?php

namespace library\model\sys;

use support\extend\Model;

class AdminAuthModel extends Model
{
    public $table = 'sys_admin_auth';
    public $primaryKey = 'id';
    public $connection = 'mysql';
     
    protected $fillable=[
		"id",
		"user_id",
		"token_type",
		"access_token",
		"refresh_token",
		"client_type",
		"client_ip",
		"expires_in",
		"refresh_expires_in",
		"status",
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    function admin(){
        return $this->belongsTo(AdminModel::class,'user_id','user_id');
    }

    public function afterCreate(){
        $this->admin()->update([
            'token'=>$this->access_token,
            'login_cnt'=>$this->getRawOriginal('login_cnt')+1,
            'login_time'=>time(),
            'client_ip'=>$this->client_ip,
        ]);
    }
}
