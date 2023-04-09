<?php

namespace support\grab;
use QL\QueryList;

class Currency{

    /**
     * 抓取汇率信息列表
     * @param $currency
     */
    public static function getCurrencyList($currency){
        if($currency=='USD' && env('APP_ENV')=='prod'){
            return self::getUsdCurrencyList();
        }
        else{
            return self::getHuilvCurrencyList($currency);
        }
    }

    /**
     * 获取币种汇率数据
     */
    private static function getUsdCurrencyList(){
        $url = 'https://hk.investing.com/currencies/single-currency-crosses';
        $query = QueryList::getInstance()->get($url);
        $ql = $query->find('#cr1>tbody>tr');
        $list = $ql->htmls()->getIterator();
        $data = [];
        foreach($list as $tr){
            $ql = $query->rules([
                'currency'=>['td:eq(2)','text'],
                'buy'=>['td:eq(3)','text'],
                'sell'=>['td:eq(4)','text'],
                'max'=>['td:eq(5)','text'],
                'min'=>['td:eq(6)','text'],
                'currency_range'=>['td:eq(8)','text'],
                'time'=>['td:eq(9)','text'],
            ])->range('')->html($tr);
            $ql->query()->getData(function ($item) use(&$data){
                if(strpos($item['currency'],'USD')===0){
                    $item['currency_range']=str_replace('%','',$item['currency_range']);
                    $timeArr = explode('/',$item['time']);
                    if(!empty($timeArr) && isset($timeArr[1])){
                        $item['time'] = date('Y').'-'.$timeArr[1].'-'.$timeArr[0];
                    }
                    else{
                        $item['time'] = date('Y-m-d');
                    }
                    $item['buy'] = str_replace(',','',$item['buy']);
                    $item['sell'] = str_replace(',','',$item['sell']);
                    $item['max'] = str_replace(',','',$item['max']);
                    $item['min'] = str_replace(',','',$item['min']);
                    $item['rate'] = $item['sell'];
                    unset($item['currency']);
                    $data[$item['currency']] = $item;
                }
            });
        }
        return $data;
    }

    /**
     * 抓取美元的币种数据
     */
    private static function getHuilvCurrencyList($currency)
    {
        $url = 'http://www.cnhuilv.com/' . $currency . '/';
        $query = QueryList::getInstance()->get($url);
        $html = $query->find('table:eq(0)')->html();
        $list = $query->setHtml($html)->find('td')->htmls()->getIterator();
        $data = [];
        $time = date('Y-m-d H:i:s');
        foreach($list as $k=>$txt){
            if($k>0){
                $ql = $query->setHtml($txt);
                $key = $ql->find('a')->attr('href');
                $key = strtoupper(trim($key,'/'));
                $rate = $ql->find('b')->text();
                $rate = str_replace(',','',$rate);
                $data[$key] = ['rate'=>$rate,'time'=>$time];
            }
        }
        return $data;
    }

    /**
     * 获取币种汇率数据
     * @param $current_currency
     * @param $target_currency
     */
    public static function getCurrencyRate($current_currency,$target_currency){
        $data = [];
        if(env('APP_ENV')=='prod'){
            $key = strtolower($current_currency.'-'.$target_currency);
            $url = 'https://hk.investing.com/currencies/'.$key;
            echo "采集汇率数据{$url}\r\n";
            $query = QueryList::getInstance()->get($url);
            $rate = $query->find('span[data-test=instrument-price-last]')->text();
            if(!empty($rate)){
                $data['rate'] = str_replace(',','',$rate);
            }
            $time = $query->find('time.instrument-metadata_text__2iS5i')->attr('datetime');
            if(!empty($time)){
                $time = str_replace(['T','.000Z'],' ',$time);
                $data['time'] = $time;
            }
            $ql = $query->find('dl[data-test=key-info]>.flex');
            $list = $ql->htmls()->getIterator();
            foreach($list as $k=>$txt){
                if($k==0){
                    $data['end'] = $query->html($txt)->find('dd>span>span:eq(0)')->text();
                    $data['end'] = str_replace(',','',$data['end']);
                }
                elseif($k==1){
                    $data['buy'] = $query->html($txt)->find('.key-info_dd-numeric__2cYjc>span:eq(0)')->text();
                    $data['buy'] = str_replace(',','',$data['buy']);
                }
                elseif($k==2){
                    $data['min'] = $query->html($txt)->find('.key-info_dd-numeric__2cYjc:eq(0)>span:eq(0)')->text();
                    $data['max'] = $query->html($txt)->find('.key-info_dd-numeric__2cYjc:eq(1)>span:eq(0)')->text();
                    $data['min'] = str_replace(',','',$data['min']);
                    $data['max'] = str_replace(',','',$data['max']);
                }
                elseif($k==3){
                    $data['start'] = $query->html($txt)->find('dd>span>span:eq(0)')->text();
                    $data['start'] = str_replace(',','',$data['start']);
                }
                elseif($k==4){
                    $data['sell'] = $query->html($txt)->find('dd>span>span:eq(0)')->text();
                    $data['sell'] = str_replace(',','',$data['sell']);
                }
            }
        }
        if(!empty($data)){
            $data['currency_range'] = ($data['rate']/$data['end'] - 1)*100;
            return $data;
        }
        else{
            return self::getHuilvCurrencyRate($current_currency,$target_currency);
        }
    }

    /**
     * 获取币种汇率数据
     * @param $current_currency
     * @param $target_currency
     */
    private static function getHuilvCurrencyRate($current_currency,$target_currency){
        $url = 'http://www.cnhuilv.com/'.$current_currency.'/'.$target_currency.'/';
        echo "采集汇率数据{$url}\r\n";
        $query = QueryList::getInstance()->get($url);
        $data = ['currency_range'=>0];
        $data['rate'] = $query->find('.mexl')->text();
        $data['rate'] = str_replace(',','',$data['rate']);
        $time = $query->find('.uptime>span')->text();
        $data['time'] = str_replace('更新时间：','',$time);
        $ql = $query->find('#exMore>.col-mex-6');
        $list = $ql->htmls()->getIterator();
        foreach($list as $k=>$txt){
            if($k==0){
                $data['end'] = $query->html($txt)->find('.rowright')->text();
                $data['end'] = str_replace([',','N/A'],['',0],$data['end']);
            }
            elseif($k==1){
                $data['start'] = $query->html($txt)->find('.rowright')->text();
                $data['start'] = str_replace([',','N/A'],['',0],$data['start']);
            }
            elseif($k==2){
                $data['buy'] = $query->html($txt)->find('.rowright')->text();
                $data['buy'] = str_replace([',','N/A'],['',0],$data['buy']);
            }
            elseif($k==3){
                $data['sell'] = $query->html($txt)->find('.rowright')->text();
                $data['sell'] = str_replace([',','N/A'],['',0],$data['sell']);
            }
            elseif($k==4){
                $wave = $query->html($txt)->find('.rowright')->text();
                $wave = str_replace([',','N/A'],['',0],$wave);
                $arr = explode('-',$wave);
                $data['min'] = $arr[0];
                $data['max'] = isset($arr[1])?$arr[1]:0;
            }
        }
        if($data['end']>0){
            $data['currency_range'] = ($data['rate']/$data['end'] - 1)*100;
        }
        return $data;
    }
}
