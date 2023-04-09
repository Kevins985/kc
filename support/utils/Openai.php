<?php

namespace support\utils;

use support\extend\Request;

/**
 * Openai操作类
 * @author Kevin
 */
class Openai {

    public static function sendMsg($prompt,$return='message'){
        $api_key = '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';
        $url = 'https://api.openai.com/v1/completions';
        $headers = [
            "Content-Type:application/json",
            "Authorization: Bearer ".$api_key
        ];
        $params = [
            "model"=> "text-davinci-003",
            "prompt"=> $prompt,
            "temperature"=> 0.7,
            "max_tokens"=> 256,
            "top_p"=> 1,
            "frequency_penalty"=> 0,
            "presence_penalty"=> 0
        ];
        $response = Curl::getInstance()->post($url,json_encode($params),$headers);
        if(!empty($response)){
            $result = json_decode($response,true);
            if($return=='message'){
                return $result['choices'][0]['text'];
            }
            return $result;
        }
        return null;
    }
}