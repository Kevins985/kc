<?php

namespace app\api\controller;

use library\logic\DictLogic;
use library\service\operate\AdvService;
use library\service\operate\ArticleService;
use library\service\operate\NoticeService;
use library\service\sys\AreaService;
use library\service\sys\CurrencyExchangeService;
use library\service\sys\CurrencyService;
use library\service\sys\DictListService;
use support\Container;
use support\controller\Api;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Home extends Api
{

    public function index(Request $request){
        return $this->response->view('home/index');
    }

    /**
     * 版本号
     * @param Request $request
     */
    public function version(Request $request)
    {
        try{
            $dictLogic = Container::get(DictLogic::class);
            $config = $dictLogic->getDictConfigs('app');
            return $this->response->json(true,$config);
        }
        catch (\Exception $e){
            return $this->response->json(false,[],$e->getMessage());
        }
    }

    /**
     * 获取支付配置信息
     * @param Request $request
     * @return \support\extend\Response
     */
    public function getPaymentInfo(Request $request)
    {
        try{
            $dictListService = Container::get(DictListService::class);
            $config = $dictListService->getDictList('payment');
            return $this->response->json(true,$config);
        }
        catch (\Exception $e){
            return $this->response->json(false,[],$e->getMessage());
        }
    }

    /**
     * 获取网站信息
     * @param $type  {website,recharge,withdraw}
     */
    public function getSiteInfo(Request $request)
    {
        try{
            $type = $this->getParams('type','website');
            $dictLogic = Container::get(DictLogic::class);
            $config = $dictLogic->getDictConfigs($type);
            if(empty($config)){
                throw new BusinessException("暂无相关数据");
            }
            return $this->response->json(true,$config);
        }
        catch (\Exception $e){
            return $this->response->json(false,[],$e->getMessage());
        }
    }

    /**
     * 关于我们接口
     */
    public function about(Request $request)
    {
        try{
            $id = 1;
            $newsService = Container::get(ArticleService::class);
            $data = $newsService->fetch(['category_id'=>$id]);
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 新闻列表接口
     * @param int $id
     */
    public function newsList(Request $request)
    {
        try{
            $params['page'] = $this->getParams('page',1);
            $params['is_rec'] = $this->getParams('is_rec');
            $params['category_id'] = 2;
            $newsService = Container::get(ArticleService::class);
            $data = $newsService->paginateData($params,['id'=>'desc']);
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 新闻详情接口
     * @param int $id
     */
    public function newsDetail(Request $request,int $id)
    {
        try{
            $newsService = Container::get(ArticleService::class);
            $data = $newsService->get($id);
            if(empty($data) || $data['status']!=1){
                throw new BusinessException("该内容不存在");
            }
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 弹窗通知
     * @param Request $request
     */
    public function noticeTips(Request $request)
    {
        try{
            $noticeService = Container::get(NoticeService::class);
            $data = $noticeService->fetch(['is_rec'=>1],['notice_id'=>'desc']);
            if(empty($data) || $data['status']!=1){
                throw new BusinessException("暂无数据");
            }
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取公告列表
     * @param Request $request
     * @return \support\extend\Response
     */
    public function noticeList(Request $request)
    {
        try{
            $params['page'] = $this->getParams('page',1);
            $noticeService = Container::get(NoticeService::class);
            $data = $noticeService->paginateData($params,['notice_id'=>'desc']);
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取公告详情接口
     * @param int $id
     */
    public function noticeDetail(Request $request,int $id)
    {
        try{
            $noticeService = Container::get(NoticeService::class);
            $data = $noticeService->get($id);
            if(empty($data) || $data['status']!=1){
                throw new BusinessException("该内容不存在");
            }
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 获取地址列表
     */
    public function getAreaList(Request $request)
    {
        try{
            $parent_id = $this->getParams('code',100000);
            $areaService = Container::get(AreaService::class);
            $data = $areaService->getAreaList($parent_id);
            return $this->response->json(true,$data);
        }
        catch (\Exception $e) {
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 广告列表
     */
    public function advList(Request $request){
        try{
            $type_code = $this->getParams('type_code');
            $location_code = $this->getParams('location_code');
            $params = [];
            if(!empty($type_code)){
                $params['type_code'] = ['has','AdvType',$type_code];
            }
            if(!empty($location_code)){
                $params['location_code'] = ['has','AdvLocation',$location_code];
            }
            $params['status']=1;
            if(empty($params)){
                throw new VerifyException('未指定来源广告类型');
            }
            $advService = Container::get(AdvService::class);
            $data = $advService->fetchAll($params);
            foreach ($data as $k=>$v){
                $data[$k]['adv_image'] = upload_md5_url($v['adv_image']);
            }
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 广告列表
     */
    public function advDetail(Request $request,string $code){
        try{
            $advService = Container::get(AdvService::class);
            $data = $advService->fetch(['status'=>1,'location_code'=>['has','AdvLocation',$code]]);
            if(empty($data)){
                throw new VerifyException('暂无广告数据');
            }
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}