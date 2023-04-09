<?php

namespace app\backend\controller\sys;

use library\logic\DictLogic;
use library\service\sys\DictListService;
use library\service\sys\DictService;
use library\validator\sys\DictValidation;
use support\controller\Backend;
use support\exception\BusinessException;
use support\exception\VerifyException;
use support\extend\Request;

class Dict extends Backend
{

    /**
     * @Inject
     * @var DictListService
     */
    private $dictListService;

    public function __construct(DictService $service,DictValidation $validation,DictLogic $logic)
    {
        $this->service = $service;
        $this->validation = $validation;
        $this->logic = $logic;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $params = $this->getAllRequest();
        $data = $this->service->paginate('/backend/dict/list',$params);
        $data->appends($this->getAllRequest('paginate'));
        $this->response->assign('data',$data);
        $types = $this->logic->getDictTypes();
        $this->response->assign('types',$types);
        return $this->response->layout('sys/dict/list');
    }

    /**
     * 添加
     */
    public function add(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax()) {
                    throw new VerifyException('Exception request');
                }
                $dictObj = $this->service->create($post);
                if(empty($dictObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $types = $this->logic->getDictTypes();
        $this->response->assign('types',$types);
        return $this->response->layout('sys/dict/add');
    }

    /**
     * 修改
     */
    public function update(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax() || empty($post['dict_id'])) {
                    throw new VerifyException('Exception request');
                }
                $dictObj = $this->service->update($post['dict_id'],$post);
                if(empty($dictObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        else {
            $id = $this->getParams('id',0);
            $dictObj = $this->service->get($id);
            if(empty($dictObj)){
                return $this->redirectErrorUrl('Exception request');
            }
            $types = $this->logic->getDictTypes();
            $this->response->assign('types',$types);
            $this->response->assign("data",$dictObj);
            $this->response->addScriptAssign(['initData'=>$dictObj->toArray()]);
            return $this->response->layout('sys/dict/update');
        }
    }

    /**
     * 删除
     */
    public function delete(Request $request)
    {
        try {
            $id = $this->getParams('id',0);
            if (empty($id)) {
                throw new VerifyException('Exception request');
            }
            $ids = explode(',',$id);
            if(count($ids)>1){
                $res = $this->service->batchDelete($ids);
            }
            else{
                $res = $this->service->delete($id);
            }
            if(empty($res)){
                throw new BusinessException('Execution failed');
            }
            return $this->response->json(true);
        } catch (\Exception $e) {
            return $this->response->json(false, [], $e->getMessage());
        }
    }

    /**
     * 设置字典数据
     */
    public function setting(Request $request)
    {
        $post = $this->getPost();
        if (!empty($post)) {
            try {
                if (!$request->isAjax()) {
                    throw new VerifyException('Exception request');
                }
                $dictObj = $this->logic->saveDictConfigs($post['dict_code'],$post['list']);
                if(empty($dictObj)){
                    throw new BusinessException('Execution failed');
                }
                return $this->response->json(true);
            }
            catch (\Exception $e) {
                return $this->response->json(false,null,$e->getMessage());
            }
        }
        $id = $this->getParams('id',0);
        $dictObj = $this->service->get($id);
        if(empty($dictObj)){
            return $this->redirectErrorUrl('Exception request');
        }
        $this->response->assign('data',$dictObj);
        $data = $this->dictListService->getDictList($dictObj['dict_code']);
        $this->response->assign('list',$data);
        $fieldTypes = $this->logic->getDictFieldTypes();
        $this->response->assign('fieldTypes',$fieldTypes);
        return $this->response->layout('sys/dict/setting');
    }

    /**
     * 设置系统配置
     */
    public function saveConfig(){
        try {
            $type = $this->getParams('type');
            $post = $this->getPost();
            if (empty($type) || empty($post)) {
                throw new VerifyException('Exception request');
            }
            $data = $data = $this->logic->saveDictListValue($type,$post);
            if(empty($data)){
                throw new \Exception('暂未获取到配置');
            }
            return $this->response->json(true,$data);
        }
        catch (\Exception $e) {
            return $this->response->json(false, null, $e->getMessage());
        }
    }
}