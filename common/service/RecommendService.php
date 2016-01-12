<?php
namespace common\service;


use common\models\posts\Posts;
use common\models\posts\PostsRecommend;

class RecommendService extends BaseService {

    public static function setRecommend($blog_id,$relate_blog_id,$params = []){
        $date_now = date("Y-m-d H:i:s");
        $info = PostsRecommend::findOne([ 'blog_id' => $blog_id,"relation_blog_id" => $relate_blog_id ]);

        $title_rate = isset( $params['title_rate'] )?$params['title_rate']:0;
        $content_rate = isset( $params['content_rate'] )?$params['content_rate']:0;
        $tags_rate = isset( $params['tags_rate'] )?$params['tags_rate']:0;
        $score = $title_rate * 0.25 + $content_rate * 0.15 + $tags_rate * 0.6;
        if( $info ){
            $model_posts_recommend = $info;
        }else{
            $model_posts_recommend = new PostsRecommend();
            $model_posts_recommend->blog_id = $blog_id;
            $model_posts_recommend->relation_blog_id = $relate_blog_id;
            $model_posts_recommend->created_time = $date_now;
        }

        $model_posts_recommend->title_rate = $title_rate;
        $model_posts_recommend->content_rate = $content_rate;
        $model_posts_recommend->tags_rate = $content_rate;
        $model_posts_recommend->score = round( $score );
        $model_posts_recommend->updated_time = $date_now;
        $model_posts_recommend->save(0);
    }


    public static function calculateRecommend( $blog_id ){
        $post_info = Posts::findOne(['id' => $blog_id,"status" => 1]);
        if( !$post_info ){
            return self::_err("post:{$blog_id} not found");
        }

        $post_list = Posts::find()
            ->where(['status' => 1])
            ->andWhere(['!=',"id",$blog_id])
            ->orderBy("id asc")
            ->all();

        foreach( $post_list as $_relate_post_info ){
            similar_text( strip_tags($post_info['content']),strip_tags($_relate_post_info['content']),$tmp_content_percent );
            similar_text( $post_info['title'],$_relate_post_info['title'],$tmp_title_percent );
            similar_text( $post_info['tags'],$_relate_post_info['tags'],$tmp_tags_percent );

            $params = [
                "title_rate" => $tmp_title_percent,
                "content_rate" => $tmp_content_percent,
                "tags_rate" => $tmp_tags_percent,
            ];
            self::setRecommend($post_info['id'],$_relate_post_info['id'],$params);
        }
        return true;
    }


} 