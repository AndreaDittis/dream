<?php

namespace admin\controllers;

use admin\components\AdminUrlService;
use admin\components\BlogService;
use admin\controllers\common\BaseController;
use common\components\DataHelper;
use common\components\phpanalysis\FenCiService;
use common\models\metaweblog\BlogSyncMapping;
use common\models\posts\Posts;
use common\models\posts\PostsRecommend;
use common\models\posts\PostsRecommendQueue;
use common\models\posts\PostsTags;
use common\service\CacheHelperService;
use common\service\Constant;
use common\service\RecommendService;
use common\service\SyncBlogService;
use Yii;
use yii\helpers\Url;

class PostsController extends BaseController{
    public function actionIndex(){
        $p = intval( $this->get("p",1) );
        $status = intval( $this->get("status",-99) );
        $order_by = $this->get("order_by",'');
        $kw = trim( $this->get("kw",'') );
        if(!$p){
            $p = 1;
        }

        $data = [];

        $query = Posts::find();
        if( $status >= -2 ){
            $query->andWhere([ 'status' => $status ]);
        }

        if( $kw ){
            $query->andWhere( ['LIKE', 'title', '%' . strtr($kw, ['%' => '\%', '_' => '\_', '\\' => '\\\\']) . '%', false] );
        }

		$total_count = $query->count();

        if( $order_by ){
			$query->orderBy([ $order_by => $this->get( $order_by )?SORT_DESC:SORT_ASC ]);
		}else{
			$query->orderBy([ 'id' => SORT_DESC ]);
		}


        $offset = ($p - 1) * $this->page_size;
        $posts_info = $query->offset($offset)
            ->limit( $this->page_size )
			->asArray()
            ->all();

        $page_info = DataHelper::ipagination([
            "total_count" => $total_count,
            "page_size" => $this->page_size,
            "page" => $p,
            "display" => 10
        ]);

        if($posts_info){
            $idx = 1;
            $domains = Yii::$app->params['domains'];

            foreach($posts_info as $_post){
                $tmp_title = $_post['title'];
                if(mb_strlen($tmp_title,"utf-8") > 30){
                    $tmp_title = mb_substr($_post['title'],0,30,'utf-8')."...";
                }

                $data[] = [
                    'idx' =>  $idx,
                    'id' => $_post['id'],
                    'title' => DataHelper::encode($tmp_title),
                    'status' => $_post['status'],
                    'hot' => $_post['hot'],
                    'view_count' => $_post['view_count'],
                    'status_info' => Constant::$status_desc[$_post['status']],
                    "original_info" => Constant::$original_desc[$_post['original']],
                    "hot_info" => Constant::$hot_desc[$_post['hot']],
                    'created' => $_post['created_time'],
                    'edit_url' => Url::toRoute("/posts/set?id={$_post['id']}"),
                    'view_url' => $domains['blog'].Url::toRoute("/default/{$_post['id']}")
                ];
                $idx++;
            }
        }

        $search_conditions = [
            'kw' => $kw,
            'status' => $status,
			'order_by' => $order_by,
			$order_by => $this->get($order_by)
        ];

        return $this->render("index",[
            "data" => $data,
            "page_info" => $page_info,
            "search_conditions" => $search_conditions,
			'status_mapping' => Constant::$status_desc
        ]);
    }

    public function actionSet(){
        $request = Yii::$app->request;
        if($request->isGet){
            $id = trim($this->get("id",0));
            $info = [];
            if($id){
                $post_info = Posts::findOne(['id' => $id]);
                if($post_info){
                    $domains = Yii::$app->params['domains'];
                    $info = [
                        "id" => $post_info['id'],
                        "title" => DataHelper::encode($post_info['title']),
                        "content" => DataHelper::encode($post_info['content']),
                        "type" => $post_info['type'],
                        "status" => $post_info['status'],
                        "original" => $post_info['original'],
                        "tags" => DataHelper::encode($post_info['tags']),
                        'view_url' => $domains['blog'].Url::toRoute("/default/{$post_info['id']}")
                    ];
                }
            }
            //set or add
            return $this->render("set",[
                "info" => $info,
                "posts_type" => Constant::$posts_type,
                "status_desc" => Constant::$status_desc,
                "original_desc" => Constant::$original_desc
            ]);
        }
        $uid = $this->current_user->uid;
        $id =trim($this->post("id",0));
        $title =trim($this->post("title"));
        $content = trim($this->post("content"));
        $tags = trim($this->post("tags"));
        $type = trim($this->post("type"));
        $status = trim($this->post("status",0));
        $original = trim($this->post("original",0));

        if( mb_strlen($title,"utf-8") <= 0 || mb_strlen($title,"utf-8") > 100 ){
            return $this->renderJSON([],"请输入博文标题并且少于100个字符",-1);
        }

        if( mb_strlen($content,"utf-8") <= 0 ){
            return $this->renderJSON([],"请输入更多点博文内容",-1);
        }

        if( mb_strlen($tags,"utf-8") <= 0 ){
            return $this->renderJSON([],"请输入博文tags",-1);
        }

        if( intval($type) <= 0){
            return $this->renderJSON([],"请选择类型",-1);
        }

        $date_now = date("Y-m-d H:i:s");
        if($id){
            $model_posts = Posts::findOne(['id' => $id]);
        }else{
            $model_posts = new Posts();
            $model_posts->uid = $uid;
			$model_posts->sn = $this->getUniqueSn();
            $model_posts->created_time = $date_now;
        }
        $tags_arr = [];
        $tags = explode(",",$tags);
        if($tags){
            foreach($tags as $_tag){
                if(!in_array($_tag,$tags_arr)){
                    $tags_arr[] = $_tag;
                }
            }
        }

        preg_match('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i',$content,$match_img);
        if( $match_img && count($match_img) == 3 ){
            $model_posts->image_url = $match_img[2];
        }else{
            $model_posts->image_url = "";
        }

        $model_posts->title = $title;
        $model_posts->content = $content;
        $model_posts->type = $type;
        $model_posts->original = $original;
        $model_posts->status = $status;
        $model_posts->tags = $tags_arr?implode(",",$tags_arr):"";
        $model_posts->updated_time = $date_now;
        $model_posts->save(0);

        $post_id = $model_posts->id;
        BlogService::buildTags($post_id);

        CacheHelperService::buildFront(true);
        if( $status == 1 ){//只有在线的才进入同步队列
            SyncBlogService::addQueue( $post_id );
            RecommendService::addQueue( $post_id );
        }

        return $this->renderJSON(['post_id' => $post_id],"博文发布成功");
    }

    public function actionGet_tags(){
        $content = trim($this->post("content"));
        $tags = FenCiService::getTags($content);
        return $this->renderJSON($tags);
    }

    public function actionOps($id){
        $id = intval($id);
        $act = trim($this->post("act","online","down-hot","go-hot"));
        if( !$id ){
            return $this->renderJSON([],"操作的博文可能不是你的吧!!",-1);
        }
        $post_info = Posts::findOne(["id" => $id,'uid' => [0,$this->current_user->uid ] ]);

        if( !$post_info ){
            return $this->renderJSON([],"操作的博文可能不是你的吧!!",-1);
        }

        switch($act){
            case "del":
                $post_info->status = 0;
                break;
            case "online":
                $post_info->status = 1;
                BlogService::buildTags($id);
                break;
            case "go-hot":
                $post_info->hot = 1;
                break;
            case "down-hot":
                $post_info->hot = 0;
        }

        $post_info->updated_time = date("Y-m-d H:i:s");
        $post_info->update(0);

        switch($act){
            case "del":
                $this->afterDel( $id );
                break;
            case "online":
                $this->afterOnline( $id );
                break;
        }

        return $this->renderJSON([],"操作成功!!");
    }

    private function afterDel( $blog_id ){
        PostsTags::deleteAll(["posts_id" => $blog_id ]);
        PostsRecommend::deleteAll([ 'OR',[ 'blog_id' =>$blog_id ],[ "relation_blog_id" => $blog_id] ]);
    }

    private function afterOnline( $blog_id ){
        BlogService::buildTags( $blog_id );
        RecommendService::addQueue( $blog_id );
    }


	private function getUniqueSn( ){
		do{
			$sn = md5( "dream_blog_".time() );
			$sn = mb_substr($sn,5,8);
		}while( Posts::findOne(['sn'=>$sn])  );
		return $sn;
	}
}