<?php

namespace support\utils;

use support\exception\VerifyException;

/**
 * 数据处理
 * @author Kevin
 */
final class Data {

    public static function verifyBankCard($bankCardNo){
        $strlen = strlen($bankCardNo);
        if($strlen < 15 || $strlen > 19){
            return false;
        }
        if (!preg_match("/^\d{15,19}$/i",$bankCardNo)){
            return false;
        }
        $arr_no = str_split($bankCardNo);
        $last_n = $arr_no[count($arr_no)-1];
        krsort($arr_no);
        $i = 1;
        $total = 0;
        foreach ($arr_no as $n){
            if($i%2==0){
                $ix = $n*2;
                if($ix>=10){
                    $nx = 1 + ($ix % 10);
                    $total += $nx;
                }
                else{
                    $total += $ix;
                }
            }
            else{
                $total += $n;
            }
            $i++;
        }
        $total -= $last_n;
        $x = 10 - ($total % 10);
        if($x != $last_n){
            return false;
        }
        return true;
    }

    /**
     * 验证身份证
     * @param $id_card
     * @return bool
     */
    public static function verifyIdCard($id_card){
        if(strlen($id_card) != 18){
            return false;
        }
        $nowYear = date('Y');
        $year = substr($id_card,6,4);
        $month = substr($id_card,10,2);
        $day = substr($id_card,12,2);
        if($year > $nowYear){
            return false;
        }
        if($month > 12){
            return false;
        }
        if($day > 31){
            return false;
        }
        $id_card_base = substr($id_card,0,17);
        if(self::idCardVerifyNumber($id_card_base) != strtoupper(substr($id_card,17,1))){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 计算身份证校验码，根据国家标准GB 11643-1999
     * @param $id_card_base
     * @return bool|mixed
     */
    public static function idCardVerifyNumber($id_card_base){
        if(strlen($id_card_base)!=17){
            return false;
        }
        //加权因子
        $factor = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
        //校验码对应值
        $verify_number_list = array('1','0','X','9','8','7','6','5','4','3','2');
        $checksum = 0;
        for($i=0;$i<strlen($id_card_base);$i++){
            $checksum += substr($id_card_base,$i,1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }


    /**
     * 根据身份证号码获取性别
     * @param $id_card
     * @return string|null
     */
    public static function getIdCardSex($id_card) {
        if(empty($id_card)) return null;
        $sexInt = substr($id_card, 16, 1);
        return $sexInt % 2 === 0 ? '女' : '男';
    }

    /**
     * 获取出生年月
     * @param string $id_card 操作数据
     * @return mixed
     */
    public static function getIdCardBirthday($id_card){
        $year = substr($id_card, 6, 4);
        $month = substr($id_card, 10, 2);
        $day = substr($id_card, 12, 2);
        return $year.'-'.$month.'-'.$day;
    }
    /**
     * 获取周岁
     * @param string $id_card 操作数据
     * @return mixed
     */
    public static function getIdCardAge($id_card){
        $now_year = date('Y');
        $now_month = date('m');
        $now_day = date('d');
        $year = substr($id_card, 6, 4);
        $month = substr($id_card, 10, 2);
        $day = substr($id_card, 12, 2);
        $age = $now_year - $year - 1;
        if ($month < $now_month || ($month == $now_month && $day <= $now_day)){
            $age++;
        }
        return $age;
    }

    /**
     * 密码加密
     * @param $password
     * @return string|null
     * @throws VerifyException
     */
    public static function hashPassword($password): ?string
    {
        $key = config('app.app_key');
        if (Data::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $hash = password_hash($password, PASSWORD_BCRYPT, [
            'cost' => $key,
        ]);
        if ($hash === false) {
            throw new VerifyException('Bcrypt hashing not supported.');
        }
        return $hash;
    }

    /**
     * 根据经验获取用户等级
     * @param float $exp 经验值
     * @return int
     */

    public static function getLevelByExp($exp) {
        $num = ($exp/100)+0.25;
        $num = floor(sqrt($num)-0.5);
        $num += 1;
        return $num;
    }

    /**
     * 对数据进行签名
     * @param array $params
     */
    public static function sign(array $params){
        $params = array_filter($params);
        if(isset($params["sign"]) && !empty($params["sign"])){
            unset($params["sign"]);
        }
        //加密私钥
        $private_key = config('app.sign_private_key');
        ksort($params);
        $str='';
        foreach($params as $k=>$v){
            $str.=$k.'='.$v.'&';
        }
        $str.='key='.$private_key;
        return strtoupper(md5($str));
    }

    /**
     * 验证是否指定字符开头
     * @param string|array $needles
     */
    public static function startsWith(string $haystack, $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ('' !== $needle && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * 根据等级获取经验值
     * @param int $level 等级
     * @return int
     */
    public static function getExpByLevel($level) {
        $level--;
        return ($level * $level + $level) *100;
    }

    /**
     * 字符串转换成数组
     * @param $string
     * @param $delimiter
     * @return array
     */
    public static function strToArray($string, $delimiter = PHP_EOL) {

        $items = explode($delimiter, $string);
        foreach ($items as $key => &$item) {
            // 查找 # 的位置
            $pos = strpos($item, '#');
            if ($pos === 0) {
                // # 开头, 整行注释
                unset($items[$key]);
                // 直接到下一个
                continue;
            }
            if ($pos > 0) {
                // # 在中间, 后面一段注释
                $item = substr($item, 0, $pos);
            }
            $item = trim($item);
            if (empty($item)) {
                unset($items[$key]);
            }
        }
        return $items;
    }

    /**
     * 字符串截取
     * @param string $str 需要截取的字符串
     * @param int $len 截取长度
     * @return string
     */
    public static function cutStr($str, $len = 0, $append = true, $encode = 'utf8') {
        if (mb_strlen($str, $encode) < $len) {
            return $str;
        } else {
            return mb_substr($str, 0, $len, 'utf8') . (($append) ? '...' : '');
        }
    }

    /**
     * 请求或响应字段参数排序
     * @param $data 待排序数据
     * @param $fieldname 确定个数字段
     * @return 已排序数据
     */
    public static function fieldParamSort($data, $fieldname) {
        $inside = array_keys($data[$fieldname]);
        $outside = array_keys($data);
        $result = [];
        foreach ($inside as $vol) {
            foreach ($outside as $value) {
                $result[$vol][$value] = $data[$value][$vol];
            }
        }
        return $result;
    }

    /**
     * 对象集合转换成无键数组
     * @param type $iterator 对象集合
     * @param type $field 对象字段
     * @return array 
     */
    public static function toFlatArray($iterator, $field = null) {
        $field = $field ? $field : 'id';
        $rs = [];
        foreach ($iterator as $t) {
            if(isset($t[$field]) || isset($t->$field)){
                $rs[] = is_array($t) ? $t[$field] : $t->$field;
            }
        }
        return empty($rs)?[]:array_unique($rs);
    }

    /**
     * 对象集合转换成字符串
     * @param type $iterator 对象集合
     * @param type $field 对象字段
     * @param type $connector 连接的符号
     * @return string 
     */
    public static function toFlatString($iterator, $field = null, $connector = ',') {
        $data = self::toFlatArray($iterator, $field);
        return empty($data) ? array() : implode($connector, $data);
    }

    /**
     * 对象集合转换成有键数组
     * @param mixed $iterator 对象集合
     * @param string $fieldKey 对象字段 数组键
     * @param string $fieldKey 对象字段 数组值
     * @return array 
     */
    public static function toKVArray($iterator, $fieldKey, $fieldVal=null) {
        $rs = [];
        foreach ($iterator as $t) {
            $k = is_array($t) ? $t[$fieldKey] : $t->$fieldKey;
            if(!empty($fieldVal)){
                $v = is_array($t) ? $t[$fieldVal] : $t->$fieldVal;
            }
            else{
                $v = $t;
            }
            $rs[$k] = $v;
        }
        return $rs;
    }

    /**
     * 用户自定义排序 - $array数组值是引用传递
     * @param array &$array 需要排序的数组
     * @param string $field 需要使用排序的key
     * @param string $order asc 升序 desc 降序
     */
    public static function usort(&$array, $field, $sort = 'asc') {
//        $sort_names = array_column($array,$field);
//        if($sort=='asc'){
//            array_multisort($sort_names,SORT_ASC,$array);
//        }
//        else{
//            array_multisort($sort_names,SORT_DESC,$array);
//        }
        if ($sort == 'asc') {
            usort($array, function($a, $b)use($field) {
                $al = $a[$field];
                $bl = $b[$field];
                if ($al == $bl) {
                    return 0;
                }
                return ($al > $bl) ? +1 : -1;
            });
        } else {
            usort($array, function($a, $b)use($field) {
                $al = $a[$field];
                $bl = $b[$field];
                if ($al == $bl) {
                    return 0;
                }
                return ($al > $bl) ? -1 : +1;
            });
        }
    }

    /**
     * 对一个数组中的某个字段求和
     * @param $data
     * @param $field
     */
    public static function getArraySum($array,$field){
        $num = 0;
        foreach($array as $v){
            if(isset($v[$field])){
                $num+=$v[$field];
            }
        }
        return $num;
    }

    /**
     * 获取中文对应的拼音
     * @param type $_String 字符串
     * @param type $_Code 编码格式
     * @return type
     */
    public static function getPinyin($_String, $_Code = 'gb2312') {
        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" .
                "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" .
                "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" .
                "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" .
                "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" .
                "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" .
                "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" .
                "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" .
                "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" .
                "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" .
                "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" .
                "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" .
                "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" .
                "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" .
                "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" .
                "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";

        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" .
                "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" .
                "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" .
                "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" .
                "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" .
                "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" .
                "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" .
                "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" .
                "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" .
                "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" .
                "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" .
                "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" .
                "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" .
                "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" .
                "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" .
                "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" .
                "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" .
                "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" .
                "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" .
                "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" .
                "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" .
                "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" .
                "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" .
                "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" .
                "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" .
                "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" .
                "|-10270|-10262|-10260|-10256|-10254";
        $_TDataKey = explode('|', $_DataKey);
        $_TDataValue = explode('|', $_DataValue);
        $_Data = array_combine($_TDataKey, $_TDataValue);
        arsort($_Data);
        reset($_Data);
        if ($_Code != 'gb2312') {
            $_C = $_String;
            $_String = '';
            if ($_C < 0x80)
                $_String .= $_C;
            elseif ($_C < 0x800) {
                $_String .= chr(0xC0 | $_C >> 6);
                $_String .= chr(0x80 | $_C & 0x3F);
            } elseif ($_C < 0x10000) {
                $_String .= chr(0xE0 | $_C >> 12);
                $_String .= chr(0x80 | $_C >> 6 & 0x3F);
                $_String .= chr(0x80 | $_C & 0x3F);
            } elseif ($_C < 0x200000) {
                $_String .= chr(0xF0 | $_C >> 18);
                $_String .= chr(0x80 | $_C >> 12 & 0x3F);
                $_String .= chr(0x80 | $_C >> 6 & 0x3F);
                $_String .= chr(0x80 | $_C & 0x3F);
            }
            $_String = iconv('UTF-8', 'GB2312', $_String);
        }
        $_Res = '';
        for ($i = 0; $i < strlen($_String); $i++) {
            $_P = ord(substr($_String, $i, 1));
            if ($_P > 160) {
                $_Q = ord(substr($_String, ++$i, 1));
                $_P = $_P * 256 + $_Q - 65536;
            }
            if ($_P > 0 && $_P < 160)
                $_Res .= chr($_P);
            elseif ($_P < -20319 || $_P > -10247)
                $_Res .= '';
            else {
                foreach ($_Data as $k => $v) {
                    if ($v <= $_P)
                        break;
                }
                $_Res .= $k;
            }
        }
        return preg_replace("/[^a-z0-9]*/", '', $_Res);
    }

    /**
     * Two dimensional array sort
     * 二维数组排序
     * @param array $arrays 需要排序的数组
     * @param string $sort_key 按照key排序
     * @param int $sort_order   升序/降序
     * @param int $sort_type    排序的类型
     * @return array|bool
     */
    public static function sortTwoDimensionalArray($arrays, $sort_key, $sort_order = SORT_DESC, $sort_type = SORT_NUMERIC) {
        if (is_array($arrays)) {
            foreach ($arrays as $array) {
                if (is_array($array)) {
                    $key_arrays[] = $array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }

    /**
     * 把数组中某个value 作为key {当key有重复,后面覆盖前面}
     * @param mixed $array
     * @param string $key
     */
    public static function toKeyArray($array, $key) {
        $result = [];
        foreach ($array as $v) {
            $result[$v[$key]] = $v;
        }
        return $result;
    }

    /**
     * 获取数组中的级别
     * @param array $data 数据
     * @return int
     */
    public static function getArrayTreeList(array $list,$pk='id',$pid='pid',$child='children',$root=0) {
        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = & $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存bai在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = & $list[$key];
                } 
                else {
                    if (isset($refer[$parentId])) {
                        $parent = & $refer[$parentId];
                        $parent[$child][] = & $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
    
    /**
     * 获取数组中的级别
     * @return array $data 数据
     */
    public static $zoomAry=[];
    public static function getArrayZoomList(array $list,$name,$pk='id',$pid = 0, $level = 0){
        foreach ($list as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['pid'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $value[$name] = str_pad($value[$name], (strlen($value[$name]) + $level * 2), '--', STR_PAD_LEFT);
                self::$zoomAry[$value[$pk]] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($list[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                self::getArrayZoomList($list,$name,$pk, $value[$pk],$level+1);
            }
        }
        return self::$zoomAry;
    }
}
