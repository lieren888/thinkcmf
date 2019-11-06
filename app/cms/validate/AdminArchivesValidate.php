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
namespace app\cms\validate;

use think\Validate;

class AdminArchivesValidate extends Validate
{
    protected $rule = [
        'channel_id' => 'require',
        'title' => 'require',
        'content' => 'require',
    ];
    protected $message = [
        'channel_id.require' => '请指定栏目分类！',
        'title.require' => '标题不能为空！',
        'content.require' => '内容不能为空！',
    ];

    protected $scene = [
//        'add'  => ['user_login,user_pass,user_email'],
//        'edit' => ['user_login,user_email'],
    ];
}