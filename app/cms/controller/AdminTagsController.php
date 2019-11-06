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

use app\cms\model\CmsTagsModel;
use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class AdminTagsController 标签管理控制器
 * @package app\cms\controller
 */
class AdminTagsController extends AdminBaseController
{
    /**
     * 标签管理
     * @adminMenu(
     *     'name'   => '标签管理',
     *     'parent' => 'cms/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章标签',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('cms_admin_tags_index_view');

        if (!empty($content)) {
            return $content;
        }

        $cmsTagModel = new CmsTagsModel();
        $tags           = $cmsTagModel->paginate();

        //$this->assign("arrStatus", $cmsTagModel::$STATUS);
        $this->assign("tags", $tags);
        $this->assign('page', $tags->render());
        return $this->fetch();
    }

    /**
     * 添加文章标签
     * @adminMenu(
     *     'name'   => '添加文章标签',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章标签',
     *     'param'  => ''
     * )
     */
    // public function add()
    // {
    //     $cmsTagsModel = new CmsTagsModel();
    //     $this->assign("arrStatus", $cmsTagsModel::$STATUS);
    //     return $this->fetch();
    // }

    /**
     * 添加文章标签提交
     * @adminMenu(
     *     'name'   => '添加文章标签提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章标签提交',
     *     'param'  => ''
     * )
     */
    // public function addPost()
    // {

    //     $arrData = $this->request->param();

    //     $cmsTagsModel = new CmsTagsModel();
    //     $cmsTagsModel->isUpdate(false)->allowField(true)->save($arrData);

    //     $this->success(lang("SAVE_SUCCESS"));

    // }

    /**
     * 更新文章标签状态
     * @adminMenu(
     *     'name'   => '更新标签状态',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '更新标签状态',
     *     'param'  => ''
     * )
     */
    // public function upStatus()
    // {
    //     $intId     = $this->request->param("id");
    //     $intStatus = $this->request->param("status");
    //     $intStatus = $intStatus ? 1 : 0;
    //     if (empty($intId)) {
    //         $this->error(lang("NO_ID"));
    //     }

    //     $cmsTagsModel = new CmsTagsModel();
    //     $cmsTagsModel->isUpdate(true)->save(["status" => $intStatus], ["id" => $intId]);

    //     $this->success(lang("SAVE_SUCCESS"));

    // }

    /**
     * 删除文章标签
     * @adminMenu(
     *     'name'   => '删除文章标签',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除文章标签',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $intId = $this->request->param("id", 0, 'intval');
        if (empty($intId)) {
            $this->error(lang("NO_ID"));
        }
        $cmsTagsModel = new CmsTagsModel();
        // //更新外表数据
        // $cmsArchivesModel = new CmsArchivesModel();
        // $archives = $cmsTagsModel->where('id' , $intId)->column('archives');
        // $name = $cmsTagsModel->where('id' , $intId)->column('name');
        // //创建SQL语句字符串
        // $sql = "update cmf_cms_archives set keywords=replace(keywords,) where id in("+$archives+");";
        // //执行插入操作
        // $affected = Db::execute($sql);     
        //删除
        $cmsTagsModel->where('id' , $intId)->delete();
        $this->success(lang("DELETE_SUCCESS"));
    }
}
