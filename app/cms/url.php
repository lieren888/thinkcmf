<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2019 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | LeoXie <380019813@qq.com>
// +----------------------------------------------------------------------
return [
    'List/index'    => [
        'name'   => 'CMS应用-文章列表',
        'vars'   => [
            'id' => [
                'pattern' => '\d+',
                'require' => true
            ]
        ],
        'simple' => true
    ],
    'Page/index'    => [
        'name'   => 'CMS应用-页面页',
        'vars'   => [
            'id' => [
                'pattern' => '\d+',
                'require' => true
            ]
        ],
        'simple' => true
    ],
    'Archives/index' => [
        'name'   => 'CMS应用-文章页',
        'vars'   => [
            'id'  => [
                'pattern' => '\d+',
                'require' => true
            ],
            'cid' => [
                'pattern' => '\d+',
                'require' => false
            ]
        ],
        'simple' => true
    ],
    'Search/index'  => [
        'name'   => 'CMS应用-搜索页',
        'vars'   => [

        ],
        'simple' => false
    ],
];