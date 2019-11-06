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
namespace app\cms\api;

use app\cms\model\CmsPageModel;
use think\db\Query;

class PageApi
{
    /**
     * 页面列表 用于模板设计
     * @param array $param
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index($param = [])
    {
        $cmsPageModel = new CmsPageModel();

        $where = [
            //'post_type'      => 2,
            'status'    => 1//,
            //'delete_time'    => 0
        ];

        //返回的数据必须是数据集或数组,item里必须包括id,name,如果想表示层级关系请加上 parent_id
        return $cmsPageModel->field('id,title AS name')
            ->where($where)
            ->where('published_time',['<', time()], ['> time', 0],'and')
            ->where(function (Query $query) use ($param) {
                if (!empty($param['keyword'])) {
                    $query->where('title', 'like', "%{$param['keyword']}%");
                }
            })->select();
    }

    /**
     * 页面列表 用于导航选择
     * @return array
     */
    public function nav()
    {
        $cmsPageModel = new CmsPageModel();

        $where = [
            //'post_type'      => 2,
            'status'    => 1//,
            //'delete_time'    => 0
        ];


        $pages = $cmsPageModel->field('id,title AS name')
            ->where('published_time',['<', time()], ['> time', 0],'and')
            ->where($where)->select();

        $return = [
            'rule'  => [
                'action' => 'cms/Page/index',
                'param'  => [
                    'id' => 'id'
                ]
            ],//url规则
            'items' => $pages //每个子项item里必须包括id,name,如果想表示层级关系请加上 parent_id
        ];

        return $return;
    }

}