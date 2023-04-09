<?php

namespace support\utils;

/**
 * 加密解密类
 * @author Kevin
 */
final class Encrypt {

    /**
     * Passport 加密函数
     * @param  string  等待加密的原字串
     * @param  string  私有密匙(用于解密和加密)
     * @return string  原字串经过私有密匙加密后的结果
     */
    public static function encrypt($txt, $key) {
        // 使用随机数发生器产生 0~32000 的值并 MD5()
        srand((int)(microtime(true) * 10000));
        $encrypt_key = md5((string)rand(0, 32000));
        // 变量初始化
        $ctr = 0;
        $tmp = '';
        // for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
        for ($i = 0; $i < strlen($txt); $i++) {
            // 如果 $ctr = $encrypt_key 的长度，则 $ctr 清零
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            // $tmp 字串在末尾增加两位，其第一位内容为 $encrypt_key 的第 $ctr 位，
            // 第二位内容为 $txt 的第 $i 位与 $encrypt_key 的 $ctr 位取异或。然后 $ctr = $ctr + 1
            $tmp .= $encrypt_key[$ctr] . ($txt[$i] ^ $encrypt_key[$ctr++]);
        }
        // 返回结果，结果为 passport_key() 函数返回值的 base64 编码结果
        return base64_encode(self::passport_key($tmp, $key));
    }

    /**
     * Passport 解密函数
     * @param  string  加密后的字串
     * @param  string  私有密匙(用于解密和加密)
     * @return string  字串经过私有密匙解密后的结果
     */
    public static function decrypt($txt, $key) {
        // $txt 的结果为加密后的字串经过 base64 解码，然后与私有密匙一起，
        // 经过 passport_key() 函数处理后的返回值
        $txt = self::passport_key(base64_decode($txt), $key);
        // 变量初始化
        $tmp = '';
        // for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
        for ($i = 0; $i < strlen($txt); $i++) {
            // $tmp 字串在末尾增加一位，其内容为 $txt 的第 $i 位，
            // 与 $txt 的第 $i + 1 位取异或。然后 $i = $i + 1
            $tmp .= $txt[$i] ^ $txt[++$i];
        }
        // 返回 $tmp 的值作为结果
        return $tmp;
    }

    /**
     * Passport 密匙处理函数
     * @param   string  待加密或待解密的字串
     * @param   string  私有密匙(用于解密和加密)
     * @return  string  处理后的密匙
     */
    public static function passport_key($txt, $encrypt_key) {
        // 将 $encrypt_key 赋为 $encrypt_key 经 md5() 后的值
        $encrypt_key = md5($encrypt_key);
        // 变量初始化
        $ctr = 0;
        $tmp = '';
        // for 循环，$i 为从 0 开始，到小于 $txt 字串长度的整数
        for ($i = 0; $i < strlen($txt); $i++) {
            // 如果 $ctr = $encrypt_key 的长度，则 $ctr 清零
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            // $tmp 字串在末尾增加一位，其内容为 $txt 的第 $i 位，
            // 与 $encrypt_key 的第 $ctr + 1 位取异或。然后 $ctr = $ctr + 1
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        // 返回 $tmp 的值作为结果
        return $tmp;
    }

    /**
     * 打包
     * @param type $params 需压缩加密的对象或数组
     * @param type $privateKey 私钥
     * @return string 返回加密的字符
     * @throws Exception
     */
    public static function pack($params, $privateKey) {
        if (is_object($params)) {
            $json_params = serialize($params);
            $dataType = 'serialize';
        } else {
            $json_params = json_encode($params);
            $dataType = 'json';
        }
        if (!$json_params) {
            throw new \Exception('json_encode fail rawData:' . $params);
        }
        if (is_array($params) && !empty($params['errno'])) {
            $errno = $params['errno'];
            unset($params['errno']);
            if (!empty($params['error'])) {
                $error = $params['error'];
                unset($params['error']);
            }
        } else {
            $errno = 0;
            $error = '';
        }
        $newParams = array();
        $newParams['api_len'] = strlen($json_params);
        $newParams['api_md5'] = md5($json_params);
        $newParams['api_params'] = $json_params;
        $newParams['api_errno'] = $errno;
        $newParams['api_error'] = $error;
        $newParams['api_privateKey'] = $privateKey;
        $newParams['api_datatype'] = $dataType;
        $newParams = json_encode($newParams);
        return self::strEnAndDe($newParams, "ENCODE", $privateKey);
    }

    /**
     * 解包
     * @param type $string 需解密的字符
     * @param type $privateKey 私钥
     * @throws Exception
     */
    public static function unpack($string, $privateKey) {
        if (empty($string)) {
            return false;
        }
        $params = self::strEnAndDe($string, "DECODE", $privateKey);
        if (empty($params)) {
            throw new \Exception("strEnAndDe error");
        }
        $debody = json_decode($params);
        if (empty($debody)) {
            throw new \Exception('decode json fail rawData:' . $params);
        }
        $api_params = $debody->api_params;
        if (empty($api_params)) {
            throw new \Exception('decoe json fail rawData:' . $params);
        }
        if (isset($debody->api_datatype) && $debody->api_datatype == 'serialize') {
            $debody->api_params = unserialize($api_params);
        } else {
            $debody->api_params = json_decode($api_params, true);
        }
        $api_len = $debody->api_len;
        $api_md5 = $debody->api_md5;
        $api_privateKey = $debody->api_privateKey;
        if ((md5($api_params) != $api_md5) || ($api_len != strlen($api_params)) || ($api_privateKey != $privateKey)) {
            throw new \Exception('decode json fail rawData:' . $params);
        }
        if ($debody->api_errno > 0 || !empty($debody->api_error)) {
            throw new \Exception($debody->api_error, $debody->api_errno);
        }
        return $debody->api_params;
    }

    /**
     * 参数 加密/解密
     * @param string $string
     * @param DECODE|ENCODE $operation 操作类型
     * @param string $privateKey 私钥
     * @return string
     */
    private static function strEnAndDe($string, $operation, $privateKey = null) {
        $privateKey = md5($privateKey);
        $key_length = strlen($privateKey);
        /**
         * 如果解密，先对密文解码
         * 如果加密,将密码算子和待加密字符串进行md5运算后取前8位
         * 并将这8位字符串和待加密字符串连接成新的待加密字符串
         */
        $string = $operation == 'DECODE' ? base64_decode($string) : substr(md5($string . $privateKey), 0, 8) . $string;
        $string_length = strlen($string);
        $rndkey = $box = array();
        $result = '';
        /**
         * 初始化加密变量,$rndkey和$box
         */
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($privateKey[$i % $key_length]);
            $box[$i] = $i;
        }
        /**
         * $box数组打散供加密用
         */
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        /**
         * $box继续打散,并用异或运算实现加密或解密
         */
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $privateKey), 0, 8)) {
                return substr($result, 8);
            } else {
                return '';
            }
        } else {
            return str_replace('=', '', base64_encode($result));
        }
    }

}
