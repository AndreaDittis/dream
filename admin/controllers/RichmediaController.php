<?php

namespace admin\controllers;

use admin\controllers\common\BaseController;
use common\components\DataHelper;
use common\models\posts\RichMedia;
use Yii;
use yii\helpers\Url;


class RichmediaController extends BaseController
{
    private $status_desc = [
        0 => ['class' => 'danger','desc' => "隐藏"],
        1 => ['class' => 'success','desc' => "正常"]
    ];
    public function actionIndex(){
        $p = intval( $this->get("p",1) );
        if(!$p){
            $p = 1;
        }

        $data = [];
        $pagesize = 20;

        $query = RichMedia::find();
        $total_count = $query->count();
        $offset = ($p - 1) * $pagesize;
        $rich_list = $query->orderBy("id desc")
            ->offset($offset)
            ->limit($pagesize)
            ->all();

        $page_info = DataHelper::ipagination([
            "total_count" => $total_count,
            "pagesize" => $pagesize,
            "page" => $p,
            "display" => 10
        ]);

        if($rich_list){
            $idx = 1;
            $domains = Yii::$app->params['domains'];
            foreach($rich_list as $_rich_info){
                $data[] = [
                    'idx' =>  $idx,
                    'id' => $_rich_info['id'],
                    'src_url' => $domains['pic1'].$_rich_info['src_url'],
                    'status' => $_rich_info['status'],
                    'status_info' => $this->status_desc[$_rich_info['status']],
                    'created' => $_rich_info['created_time'],
                ];
                $idx++;
            }
        }

        return $this->render("index",[
            "data" => $data,
            "page_info" => $page_info,
            "page_url" => "/richmedia/index"
        ]);
    }

    public function actionOps($id){
        $id = intval($id);
        $act = trim($this->post("act","online"));
        if( !$id ){
            return $this->renderJSON([],"操作的多媒体可能不是你的吧!!",-1);
        }
        $richmedia_info = RichMedia::findOne(["id" => $id]);

        if( !$richmedia_info ){
            return $this->renderJSON([],"操作的多媒体可能不是你的吧!!",-1);
        }

        if($act == "del" ){
            $richmedia_info->status = 0;
        }else{
            $richmedia_info->status = 1;
        }

        $richmedia_info->updated_time = date("Y-m-d H:i:s");
        $richmedia_info->update(0);
        return $this->renderJSON([],"操作成功!!");
    }

}