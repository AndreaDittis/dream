<?php
return [
    "curl" => [
        "timeout" => 30
    ],
    "domains" => [
        "blog" => "http://blog.dr.local.com",
        "admin" => "http://admin.dr.local.com",
        "static" => "http://static.dr.local.com",
        "cdn_static" => "http://static.dr.local.com",
        "pic1" => "http://pic1.dr.local.com",
        "cdn_pic1" => "http://pic1.dr.local.com",
        "pic2" => "http://pic2.dr.local.com",
        "cdn_pic2" => "http://pic2.dr.local.com",
        "pic3" => "http://pic3.dr.local.com",
        "cdn_pic3" => "http://pic3.dr.local.com",
        "m" => "http://m.dr.local.com",
        "cookie" => ".dr.local.com",
		"awephp" => "http://awephp.dr.test",
		"book" => "http://book.imooc.test",
    ],
    "author" => [
        "nickname" => "编程浪子",
        "link" => "/default/about"
    ],
    'weixin' => [
        'mystarzone' => [
            'appid' => 'wx1b2fea3cb08d02ee',
            'appsecret' => 'xxx',
            'apptoken' => 'xxx'
        ],
        'imguowei_888' => [
            'appid' => 'wx936957aebefb4e76',
            'appsecret' => 'xxx',
            'apptoken' => 'xxx'
        ],
        'oauth' => [
            'appid' => 'xxxx',
            'appsecret' => 'xx',
            'apptoken' => 'xx'
        ]
    ],
    'upload' => [
        "pic1" => "/data/www/pic1/",
        "pic2" => "/data/www/pic2/",
        "pic3" => "/data/www/pic3/",
    ],
    'switch' => [
        "cdn" => [
            "static" => false,
            "pic1" => false,
            "pic2" => false,
            "pic3" => false
        ]
    ]
];