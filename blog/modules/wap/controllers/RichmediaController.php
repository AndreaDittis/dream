<?php

namespace blog\modules\wap\controllers;

use blog\modules\wap\controllers\common\BaseController;
use common\components\DataHelper;
use common\models\posts\RichMedia;
use common\service\GlobalUrlService;
use Yii;

class RichmediaController extends BaseController
{
    private $page_size = 10;

    public function actionIndex()
    {

        $data = $this->search();

        $this->setTitle("富媒体--郭大帅哥的生活记录");
        return $this->render("index", [
            "media_list_html" => $this->buildItem($data),
            "has_next" => (count($data) < $this->page_size) ? false : true,
        ]);
    }

    public function actionSearch()
    {
        $p    = intval($this->get("p", 2));
        $data = $this->search(['p' => $p]);
        return $this->renderJSON([
            'html'     => $this->buildItem($data),
            "has_next" => (count($data) < $this->page_size) ? false : true,
            "has_data" => $data ? true : false
        ]);
    }

    private function search($params = [])
    {
        $p = isset($params['p']) ? $params['p'] : 1;
        $offset = ($p - 1) * $this->page_size;

        $query = RichMedia::find()->where(['status' => 1, 'type' => 'image']);
        $rich_media_list = $query->orderBy("id desc")
            ->offset($offset)
            ->limit($this->page_size)
            ->all();


        $data = [];
        if ($rich_media_list) {
            $domains = Yii::$app->params['domains'];
            foreach ($rich_media_list as $_rich_info) {
                $tmp_small_pic_url = GlobalUrlService::buildPic1Static($_rich_info['src_url'],['h' => 400]);
                $tmp_big_pic_url = GlobalUrlService::buildPic1Static($_rich_info['src_url'],['w' => 800]);
                $data[] = [
                    'id' => $_rich_info['id'],
                    'type' => $_rich_info['type'],
                    'small_src_url'   => $tmp_small_pic_url,
                    'big_src_url'   => $tmp_big_pic_url,
                    'thumb_url' => $_rich_info['thumb_url'] ? $_rich_info['thumb_url'] : $domains['static'] . "/wx/video_cover.jpg",
                    'address'   => $_rich_info['address']
                ];
            }
        }
        return $data;
    }

    private function buildItem($data)
    {
        return $this->renderPartial("item", [
            "media_list" => $data
        ]);
    }

}