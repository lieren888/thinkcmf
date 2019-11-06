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
use app\admin\model\RouteModel;
use think\Validate;

class AdminChannelValidate extends Validate
{
    protected $rule = [
        'type' => 'require',
        'model_id' => 'number',
        'name'  => 'require',
        'alias' => 'checkAlias',
    ];

    protected $message = [
        'type.require'     => '类型不能为空',
        'model_id.number'  => '类型必须是数字',
        'name.require'     => '名称不能为空',
    ];

    // 自定义验证规则
    protected function checkAlias($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        if (preg_match("/^\d+$/", $value)) {
            return "别名不能为纯数字!";
        }

        $routeModel = new RouteModel();
        if (isset($data['id']) && $data['id'] > 0) {//修改操作
            $fullUrl = $routeModel->buildFullUrl('cms/List/index', ['id' => $data['id']]);
            $count = $routeModel::where([
                ['url', '=', $data['alias']],
                ['full_url', '<>', $fullUrl]
            ])->count();
            if($count > 0){
                return "别名已经存在!";
            }
        } else {//添加操作
            $count = $routeModel::where('url', $data['alias'])->count();
            if($count > 0){
                return "别名已经存在!";
            }
            //获取最新的
            $fullUrl = $routeModel->getFullUrlByUrl($data['alias']);
        }
        if (!$routeModel->existsRoute($value, $fullUrl)) {
            return true;
        } else {
            return "别名已经存在!";
        }
    }
}