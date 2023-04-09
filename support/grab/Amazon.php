<?php

namespace support\grab;
use QL\QueryList;

class Amazon{

    /**
     * 抓取产品详情内容
     * @param $currency
     */
    public static function getProductDetail($url){
        $content =  file_get_contents($url);
        echo $content;
        $query = QueryList::getInstance()->setHtml($content);
        //print_r($query);
        $data['title'] = $query->find('#productTitle')->text();
        $ql = $query->find('#twister-plus-inline-twister-card>.inline-twister-row');
        $list = $ql->htmls()->getIterator();
        foreach($list as $spec){
            $ql = $query->rules([
                'currency'=>['td:eq(2)','text'],
                'buy'=>['td:eq(3)','text'],
                'sell'=>['td:eq(4)','text'],
                'max'=>['td:eq(5)','text'],
                'min'=>['td:eq(6)','text'],
                'currency_range'=>['td:eq(8)','text'],
                'time'=>['td:eq(9)','text'],
            ])->range('')->html($spec);
        }


        $data['sell_price'] = $query->find('#corePriceDisplay_desktop_feature_div .priceToPay .a-offscreen')->text();
        $data['market_price'] = $query->find('.basisPrice .a-offscreen')->text();
        print_r($data);
        return $data;
    }
}
