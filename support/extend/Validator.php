<?php

namespace support\extend;

use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use \Illuminate\Validation\Factory;
use Illuminate\Filesystem\Filesystem;
use support\utils\Data;

/**
 * 验证基类
 */
class Validator 
{
    private $message = 'ok';
    protected $rules = [];
    protected $attributes = [];
    protected $lang = 'en';
    /**
     * @var string {api,backend,frontend}
     */
    protected $type = null;
    
    public function __construct(string $type=null,string $lang = null) {
        if (!empty($type)) {
            $this->setType($lang);
        }
        if (!empty($lang)) {
            $this->setLangage($lang);
        }
    }

    /*
     * 创建验证实例
     * @return \Factory
     */

    private function getValidationInstance() {
        $test_translation_path = resource_path("translations");
        $translation_file_loader = new FileLoader(new Filesystem, $test_translation_path);
        $translator = new Translator($translation_file_loader, $this->lang);
        return new Factory($translator);
    }

    /**
     * 设置语言包
     * @param string $lang
     */
    public function setLangage(string $lang) {
        $this->lang = $lang;
    }

    /**
     * 设置应用类型
     * @param string $type
     */
    public function setType(string $type) {
        $this->type = $type;
    }

    /**
     * 提示语言
     * @param $msg
     */
    protected function trans(string $msg,array $parameters = []){
        return $msg;
    }

    /**
     * 设置验证规则
     * @param array $rules
     */
    protected function setRules(array $rules) {
        $this->rules = $rules;
    }

    /**
     * 获取验证规则
     * @return array
     */
    protected function getRules() {
        return $this->rules;
    }

    /**
     * 设置属性
     * @param array $attributes
     */
    protected function setAttributes(array $attributes) {
//        $this->attributes = $attributes;
    }

    /**
     * 获取属性
     * @return array
     */
    protected function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param array $data   验证数据
     * @return bool
     */
    protected function checkValidate(array $data = []) {
        if (empty($data)) {
            $this->message = 'data is empty';
            return false;
        }
        $validator = $this->getValidateRules();
        $validator = $validator->make($data, $this->getRules(), [], $this->getAttributes());
        if ($validator->fails()) {
            $this->message = $validator->messages();
            return false;
        }
        return true;
    }

    /**
     * 获取的验证器
     * @return Factory
     */
    private function getValidateRules(){
        $validator = $this->getValidationInstance();
        $validator->extend('account', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^([a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+)|(\d{5,})$/", $value);
        }, ':attribute Incorrect format');
        $validator->extend('mobile', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^1[3,4,5,6,7,8,9]{1}[0-9]{9}$/", $value);
        }, ':attribute Incorrect format');
        $validator->extend('isIdCard', function ($attribute, $value, $parameters, $validator) {
            return Data::verifyIdCard($value);
        }, ':attribute Incorrect format');
        $validator->extend('isBankCard', function ($attribute, $value, $parameters, $validator) {
            return Data::verifyBankCard($value);
        }, ':attribute Incorrect format');
        return $validator;
    }

    /**
     * 获取错误消息
     * @return string
     */
    public function getMessage() {
        if(is_string($this->message)){
            return $this->message;
        }
        return $this->message->getMessages();
    }

    /**
     * 验证方法的参数数据
     * @param string $method 方法名
     * @param array $data 请求的数据
     */
    public function verifyRequestData(string $name,array $data){
        $func = 'checking'.ucfirst($name);
        if(method_exists($this, $func)){
            return call_user_func([$this,$func],$data);
        }
        return true;
    }
    
    /**
     * 验证公用方法的参数数据
     * @param array $data 请求的数据
     */
    protected function checkingPublic(array $data){
        if($this->type=='api'){
            $rules = [
                'sign' => 'required|string',
                'timestamp' => 'required|int',
                'lang' => 'required|string',
                'version' => 'required|string',
            ];
            $attrs = [
                'sign'=>trans('Sign'),
                'timestamp'=>trans('Timestamp'),
                'lang'=>trans('Lang'),
                'version'=>trans('Version'),
            ];
            $this->setRules($rules);
            $this->setAttributes($attrs);
            return $this->checkValidate($data);
        }
        elseif($this->type=='backend'){
            $rules = [
                'sign' => 'required|string',
                'timestamp' => 'required|int',
                'lang' => 'required|string',
                'version' => 'required|string',
                'traceId' => 'required|string',
            ];
            $attrs = [
                'sign'=>trans('Sign'),
                'timestamp'=>trans('Timestamp'),
                'lang'=>trans('Lang'),
                'version'=>trans('Version'),
                'traceId'=>trans('TraceId'),
            ];
            $this->setRules($rules);
            $this->setAttributes($attrs);
            return $this->checkValidate($data);
        }
        return true;
    }
}
