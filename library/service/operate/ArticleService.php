<?php

namespace library\service\operate;

use support\extend\Service;
use library\model\operate\ArticleModel;

class ArticleService extends Service
{
    public function __construct(ArticleModel $model)
    {
        $this->model = $model;
    }

    /**
     * 获取分类语言内容
     * @param $category_id
     * @param $lang_id
     */
    public function getCategoryLangArticle($category_id,$lang=null){
        $lang_id = getLangId($lang);
        if(!empty($lang_id)){
            return $this->fetch(['category_id'=>$category_id,'lang_id'=>$lang_id]);
        }
        return [];
    }
}
