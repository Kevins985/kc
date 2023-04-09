<?php
namespace app\process;

use library\service\sys\AdminAuthService;
use library\service\sys\IpVisitService;
use library\service\user\MemberAuthService;
use support\Container;
use support\extend\Redis;
use Workerman\Timer;

class Task
{
    private $crawlerTimer;
    public function onWorkerStart()
    {
        Timer::add(1800, [$this, 'deleteExpiredAuthUser'], [], true);
        Timer::add(600, [$this, 'updateApiIpVisitCount'], [], true);
        // 每隔1800秒抓取外汇记录
        if(env('APP_ENV')=='prod'){
            $this->crawlerTimer = Timer::add(1800, [$this, 'crawlerCurrencyList'], [], true);
        }
    }

    /**
     * 抓取美元兑换其他货币的汇率
     */
    public function crawlerCurrencyList(){
        $data = \support\grab\Currency::getCurrencyList('USD');
        if(!empty($data)){
            echo '更新美元汇率记录:'.count($data).PHP_EOL;
            foreach($data as $k=>$v){
                if(!empty($k)){
                    $res = Redis::hGet('CurrencyRate',$k);
                    if(!empty($res)){
                        $cdata = json_decode($res,true);
                        if(strtotime($v['time'])>strtotime($cdata['time'])){
                            Redis::hSet('CurrencyRate',$k,json_encode($v));
                        }
                    }
                    else{
                        Redis::hSet('CurrencyRate',$k,json_encode($v));
                    }
                }
            }
        }
    }

    /**
     * 清除已过期用户授权数据
     */
    public function deleteExpiredAuthUser(){
        //删除后台认证过期的用户
        $adminAuthService = Container::get(AdminAuthService::class);
        $field = $adminAuthService->raw(time().'-expires_in');
        $rows = $adminAuthService->fetchAll(['status'=>1,'updated_time'=>['lt',$field]]);
        foreach($rows as $v){
            Redis::hDel('login_token',$v['token']);
        }
        $num = $adminAuthService->deleteAll(['status'=>1,'updated_time'=>['lt',$field]]);
        echo '后台认证过期用户数:'.$num.PHP_EOL;
        //删除后台认证过期的用户
        $userAuthService = Container::get(MemberAuthService::class);
        $field = $userAuthService->raw(time().'-expires_in');
        $rows = $userAuthService->fetchAll(['status'=>1,'updated_time'=>['lt',$field]]);
        foreach($rows as $v){
            Redis::hDel('login_token',$v['token']);
        }
        $num = $userAuthService->deleteAll(['status'=>1,'updated_time'=>['lt',$field]]);
        echo '前台认证过期用户数:'.$num.PHP_EOL;
    }

    /**
     * 更新接口IP访问统计数据
     */
    public function updateApiIpVisitCount(){
        $cache_key = 'visit_ip';
        $list = Redis::hGetAll($cache_key);
        $ipVisitService = Container::get(IpVisitService::class);
        $today_add_cnt = date('Hi')<5;
        foreach($list as $key=>$num){
            if($num>0){
                echo '更新IP'.$key.'统计数据:'.$num.PHP_EOL;
                $total_cnt = $ipVisitService->raw('total_visit_num+'.$num);
                $today_cnt = $num;
                if($today_add_cnt){
                    $today_cnt = $ipVisitService->raw('today_visit_num+'.$num);
                }
                $res = $ipVisitService->updateAll(['client_ip'=>$key],[
                    'total_visit_num'=>$total_cnt,
                    'today_visit_num'=>$today_cnt,
                    'last_visit_time'=>date('Y-m-d H:i:s')
                ]);
                if($res){
                    Redis::hSet($cache_key,$key,0);
                }
            }
        }
    }
}