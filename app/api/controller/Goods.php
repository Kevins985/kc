<?php

namespace app\api\controller;

use library\service\goods\CategoryService;
use library\service\goods\SpuService;
use support\Container;
use support\controller\Api;
use support\exception\BusinessException;
use support\extend\Request;

class Goods extends Api
{

    public function __construct(SpuService $service)
    {
        $this->service = $service;
    }

    /**
     * 获取分类列表
     */
    public function getCategoryList(Request $request){
        try{
            $categoryService = Container::get(CategoryService::class);
            $data = $categoryService->getSelectList();
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 商品列表
     */
    public function list(Request $request)
    {
        try{
            $params['page'] = $this->getParams('page',1);
            $params['category_id'] = $this->getParams('category_id');
            $params['status'] = 1;
            $params['images'] = ['with'];
            $data = $this->service->paginateData($params);

            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }

    /**
     * 商品详情
     */
    public function detail(Request $request,int $id)
    {
        try{
            $spuObj = $this->service->get($id);
            if(empty($spuObj) || $spuObj['status']<1){
                throw new BusinessException("该商品不存在");
            }
            $data = $spuObj->toArray();
            $data['images'] = $spuObj->getImagesList();
            return $this->response->json(true,$data);
        }
        catch (\Throwable $e){
            return $this->response->json(false,null,$e->getMessage());
        }
    }
}