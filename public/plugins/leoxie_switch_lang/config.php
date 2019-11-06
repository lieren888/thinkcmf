<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Leoxie <380019813@qq.com>
// +----------------------------------------------------------------------
return [
    'default_lang' => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '设置默认语言', // 表单的label标题
        'type'    => 'radio',
        'options' => [
            'zh-cn' => '中文',
            'en-us' => '英文'
        ],
        'value' => 'zh-cn',// 表单的默认值
        'tip'   => '' //表单的帮助提示
    ],
    'pc_theme' => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => 'PC版模板名（中文版）', // 表单的label标题
        'type'  => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '',// 表单的默认值
        'tip'   => '模板名称可在后台“设置”->“模板管理”->“模板名称”里面查看' //表单的帮助提示
    ],
    'pc_theme_en' => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => 'PC版模板名（英文版）', // 表单的label标题
        'type'  => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '',// 表单的默认值
        'tip'   => '模板名称可在后台“设置”->“模板管理”->“模板名称”里面查看' //表单的帮助提示
    ],
    'mobile_theme' => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '手机模板名（中文版）', // 表单的label标题
        'type'  => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '',// 表单的默认值
        'tip'   => '在手机用户访问网站时会使用此模板，留空表示不开启' //表单的帮助提示
    ],
    'mobile_theme_en' => [// 在后台插件配置表单中的键名 ,会是config[text]
        'title' => '手机模板名（英文版）', // 表单的label标题
        'type'  => 'text',// 表单的类型：text,password,textarea,checkbox,radio,select等
        'value' => '',// 表单的默认值
        'tip'   => '在手机用户访问网站时会使用此模板，留空表示不开启' //表单的帮助提示
    ],
];
		