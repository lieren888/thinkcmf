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
namespace app\cms\controller;

use cmf\controller\HomeBaseController;
use app\cms\model\CmsChannelModel;

class ListController extends HomeBaseController
{
    /***
     * 文章列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $id                  = $this->request->param('id', 0, 'intval');
        $cmsChannelModel = new CmsChannelModel();

        $category = $cmsChannelModel->where('id', $id)->where('status', 1)->find();
       
        $this->assign('category', $category);

        $listTpl = '';
        switch($category['type']){
            case 'channel':
                $listTpl = $category['channeltpl'];
            break;
            case 'list':
                $listTpl = $category['listtpl'];
            break;
            default:
                $listTpl = 'channel';//默认栏目页面
            break;
        }

        return $this->fetch('/' . $listTpl);
    }

}
