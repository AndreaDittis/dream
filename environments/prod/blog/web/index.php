<?php
defined('YII_DEBUG') or define('YII_DEBUG', false);
defined('YII_ENV') or define('YII_ENV', 'prod');

require(__DIR__ . '/../../vendor/autoload.php');
require(__DIR__ . '/../../vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../../common/config/bootstrap.php');
require(__DIR__ . '/../config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../common/config/main.php'),
    require(__DIR__ . '/../../common/config/main-local.php'),
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/main-local.php')
);

/*定义版本号变量*/
if(file_exists("/data/htdocs/release_version/version_blog")){
    define("RELEASE_VERSION",file_get_contents("/data/htdocs/release_version/version_wap"));
}else{
    define("RELEASE_VERSION","20150731141600");
}

$application = new yii\web\Application($config);
$application->run();