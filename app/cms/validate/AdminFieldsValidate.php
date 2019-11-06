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

class AdminFieldsValidate extends Validate
{
    protected $rule = [
        'post.name'   => 'require',
        'post.type'   => 'require',
        'post.title'  => 'require',
        'post.length' => 'require',
    ];

    protected $message = [
        'post.name.require'      => '字段名称不能为空',
        'post.type.require'      => '类型不能为空',
        'post.title.require'     => '标题不能为空',
        'post.length.require'    => '字段长度不能为空',
    ];

}