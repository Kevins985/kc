<?php

namespace library\service\goods;

use library\service\sys\FlowNumbersService;
use support\Container;
use support\exception\BusinessException;
use support\extend\Service;
use library\model\goods\SpuModel;
use support\utils\Data;

class SpuService extends Service
{
    public function __construct(SpuModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取商品编号
     * @param string $suffix
     * @return mixed
     */
    public function getGoodsNo($suffix=''){
        $flowNumberServer = Container::get(FlowNumbersService::class);
        $spu_no = $flowNumberServer->getFlowOrderNo($this->model->getTable(),$suffix);
        $spuObj = $this->fetch(['spu_no'=>$spu_no]);
        if(!empty($spuObj)){
            return $this->getGoodsNo();
        }
        return $spu_no;
    }

    public function saveGoodsData($post){
        $photo = $post['photo']??[];
        if(isset($post['spu_id']) && !empty($post['spu_id'])){
            $spuObj = $this->update($post['spu_id'],$post);
            $spuObj->images()->where('type',1)->delete();
        }
        else{
            $spuObj = $this->create($post);
        }
        foreach($photo as $k=>$url){
            $spuObj->images()->create([
                'image_url'=>$url,
                'spu_id'=>$spuObj['spu_id'],
                'type'=>1,
                'sort'=>($k+1)
            ]);
        }
        return $spuObj;
    }

    /**
     * 获取可以选择的商品
     */
    public function getGoodsSelect(){
        $rows = $this->fetchAll(['status'=>1]);
        return $rows;
    }

    /**
     * 获取指定商品列表
     * @param array $spu_ids
     */
    public function getGoodsList(array $spu_ids,$fields=[]){
        $rows = $this->fetchAll(['spu_id'=>['in',$spu_ids]],[],$fields);
        return Data::toKeyArray($rows,'spu_id');
    }

    /**
     * 获取所有商品数量
     */
    public function getGroupAllSpuCnt($params=[])
    {
        $selector = $this->groupBySelector(['status'],$params)->selectRaw('status,count(*) as ct');
        $rows = $selector->get()->toArray();
        $data = ['total'=>0];
        foreach($rows as $v){
            $data['total']+=$v['ct'];
            $data[$v['status']] = $v['ct'];
        }
        return $data;
    }

    /**
     * 设置SPU上下架
     * @param $id
     * @param $status
     * @return bool
     */
    public function setStatus($id,$status){
        $spuObj = $this->get($id);
        if(empty($spuObj)){
            throw new BusinessException('暂未找到该商品');
        }
        $data = ['status'=>$status];
        if($status==1){
            if(empty($spuObj['image_url'])){
                throw new BusinessException('请上传商品封面图');
            }
            elseif(empty($spuObj['description'])){
                throw new BusinessException('商品描述不能为空');
            }
            $data['up_time'] = date('Y-m-d H:i:s');
        }
        if($status==2){
            $data['down_time'] = date('Y-m-d H:i:s');
        }
        return $spuObj->update($data);
    }

    /**
     * 删除平台商品
     * @param $ids
     */
    public function deleteSpuList(array $ids){
        $res = $this->deleteAll(['spu_id'=>['in',$ids]]);
        return $res;
    }
}
