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
    'cms_before_assign_archives'    => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '文章显示之前', // 钩子名称
        "description" => "文章显示之前", //钩子描述
        "once"        => 0 // 是否只执行一次
    ],
    'cms_admin_after_save_archives' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '后台文章保存之后', // 钩子名称
        "description" => "后台文章保存之后", //钩子描述
        "once"        => 0 // 是否只执行一次
    ],
    'cms_admin_archives_index_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章管理列表界面', // 钩子名称
        "description" => "门户后台文章管理列表界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_archives_add_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章添加界面', // 钩子名称
        "description" => "门户后台文章添加界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_archives_edit_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章编辑界面', // 钩子名称
        "description" => "门户后台文章编辑界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_channel_index_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章分类管理列表界面', // 钩子名称
        "description" => "门户后台文章分类管理列表界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_channel_add_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章分类添加界面', // 钩子名称
        "description" => "门户后台文章分类添加界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_channel_edit_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章分类编辑界面', // 钩子名称
        "description" => "门户后台文章分类编辑界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_page_index_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台页面管理列表界面', // 钩子名称
        "description" => "门户后台页面管理列表界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_page_add_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台页面添加界面', // 钩子名称
        "description" => "门户后台页面添加界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_page_edit_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台页面编辑界面', // 钩子名称
        "description" => "门户后台页面编辑界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_tag_index_view' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章标签管理列表界面', // 钩子名称
        "description" => "门户后台文章标签管理列表界面", //钩子描述
        "once"        => 1 // 是否只执行一次
    ],
    'cms_admin_archives_edit_view_right_sidebar' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章添加编辑界面右侧栏', // 钩子名称
        "description" => "门户后台文章添加编辑界面右侧栏", //钩子描述
        "once"        => 0 // 是否只执行一次
    ],
    'cms_admin_archives_edit_view_main' => [
        "type"        => 2,//钩子类型(默认为应用钩子;2:应用钩子;3:模板钩子;4:后台模板钩子)
        "name"        => '门户后台文章添加编辑界面主要内容', // 钩子名称
        "description" => "门户后台文章添加编辑界面主要内容", //钩子描述
        "once"        => 0 // 是否只执行一次
    ],
];