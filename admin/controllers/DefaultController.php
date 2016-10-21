<?php

namespace admin\controllers;

use admin\controllers\common\BaseController;
use common\components\phpanalysis\FenCiService;
use common\models\library\Book;
use common\models\posts\Posts;
use common\models\stat\StatAccess;
use common\models\stat\StatBlog;
use common\models\stat\StatDailyAccessSource;
use Yii;


class DefaultController extends BaseController
{
    public function actionIndex(){

        $data = [
            "posts" => [],
            "library" => []
        ];

        $total_posts = Posts::find()->count();
        $total_valid_posts = Posts::find()->where(['status' => 1])->count();

        $today_date = date("Y-m-d");
        $today_post = Posts::find()
            ->where([">=","created_time",$today_date." 00:00:00"])
            ->andWhere(["<=","created_time",$today_date." 23:23:59"])
            ->count();

        $data['posts'] = [
            "total" => $total_posts,
            "total_valid" => $total_valid_posts,
            "today" => $today_post
        ];


        $total_book = Book::find()->count();
        $total_valid_book = Book::find()->where(['status' => 1])->count();

        $data['library'] = [
            "total" => $total_book,
            "total_valid" => $total_valid_book
        ];

        /*画图*/
        $date_from = date("Y-m-d",strtotime("-30 day"));
        $date_to = date("Y-m-d");
        $stat_access_list = StatAccess::find()
            ->where(['>=',"date",$date_from])
            ->andWhere(['<=',"date",$date_to])
            ->orderBy("date asc")
            ->asArray()
            ->all();

        $data_access = [
            "categories" => [],
            "series" => [
                [
                    'name' => '访问量',
                    'data' => []
                ],
                [
                    'name' => 'IP总数',
                    'data' => []
                ]
            ]
        ];
        if( $stat_access_list ){
            foreach( $stat_access_list as $_item ){
                $data_access['categories'][] = $_item['date'];
                $data_access['series'][1]['data'][] = intval( $_item['total_ip_number'] );
                $data_access['series'][0]['data'][] = intval( $_item['total_number'] );
            }
        }

        $stat_blog_list = StatBlog::find()
            ->where(['>=',"date",$date_from])
            ->andWhere(['<=',"date",$date_to])
            ->orderBy("date asc")
            ->asArray()
            ->all();
        $data_blog = [
            "categories" => [],
            "series" => [
                [
                    'name' => '已发布',
                    'data' => []
                ],
                [
                    'name' => '待发布',
                    'data' => []
                ]
            ]
        ];

        if( $stat_blog_list ){
            foreach( $stat_blog_list as $_item ){
                $data_blog['categories'][] = $_item['date'];
                $data_blog['series'][0]['data'][] = intval( $_item['total_post_number'] );
                $data_blog['series'][1]['data'][] = intval( $_item['total_unpost_number'] );
            }
        }


        /*今日来源域名top10*/
        $ignore_source = ["direct","www.vincentguo.cn","m.vincentguo.cn" ];
		$source_list_top = StatDailyAccessSource::find()
			->where([ 'date' => date("Ymd") ])
			->andWhere([ 'not in','source',$ignore_source ])
			->orderBy([ 'total_number' => SORT_DESC ])
			->limit( 10 )
			->all();

        return $this->render("index",[
            "stat" =>$data,
            "data_access" => $data_access,
            "data_blog" => $data_blog,
			'source_list_top' => $source_list_top
        ]);
    }


}