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

use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use app\cms\model\CmsArchivesModel;
use app\cms\service\PostService;
use app\admin\model\ThemeModel;

class AdminPageController extends AdminBaseController
{

    /**
     * Page模型对象
     */
    protected $model = null;

    public function initialize()//TP5.1写法
    {
        parent::initialize();
        $this->model = new \app\cms\model\CmsPageModel;
    }

    /**
     * 单页管理
     * @adminMenu(
     *     'name'   => '单页管理',
     *     'parent' => 'cms/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10001,
     *     'icon'   => '',
     *     'remark' => '单页管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('cms_admin_page_index_view');

        if (!empty($content)) {
            return $content;
        }

        $param = $this->request->param();

        $data  = $this->model->adminPageList($param);

        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('pages', $data);
        $this->assign('page', $data->render());

        return $this->fetch();
    }

    /**
     * 添加页面
     * @adminMenu(
     *     'name'   => '添加页面',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加页面',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $content = hook_one('cms_admin_page_add_view');

        if (!empty($content)) {
            return $content;
        }

        $themeModel     = new ThemeModel();
        $pageThemeFiles = $themeModel->getActionThemeFiles('cms/Page/index');
        $this->assign('page_theme_files', $pageThemeFiles);
        return $this->fetch();
    }

    /**
     * 添加页面提交
     * @adminMenu(
     *     'name'   => '添加页面提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加页面提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $data = $this->request->param();
        $post = $data['post'];

        $result = $this->validate($post, 'AdminPage');
        if ($result !== true) {
            $this->error($result);
        }

        //路由判断
        $routeModel = new RouteModel();
        if ($routeModel::where('url', $post['alias'])->count() > 0) {
            $this->error("别名已经存在!");
        }

        $this->model->adminAddPage($post);
        $this->success(lang('ADD_SUCCESS'), url('AdminPage/edit', ['id' => $this->model->id]));

    }

    /**
     * 编辑页面
     * @adminMenu(
     *     'name'   => '编辑页面',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑页面',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $content = hook_one('cms_admin_page_edit_view');

        if (!empty($content)) {
            return $content;
        }

        $id = $this->request->param('id', 0, 'intval');

        $post            = $this->model->where('id', $id)->find();

        $themeModel     = new ThemeModel();
        $pageThemeFiles = $themeModel->getActionThemeFiles('cms/Page/index');

        $routeModel         = new RouteModel();
        $alias              = $routeModel->getUrl('cms/Page/index', ['id' => $id]);
        $post['alias'] = $alias;

        $this->assign('page_theme_files', $pageThemeFiles);
        $this->assign('post', $post);

        return $this->fetch();
    }

    /**
     * 编辑页面提交
     * @adminMenu(
     *     'name'   => '编辑页面提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑页面提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data = $this->request->param();

        $result = $this->validate($data['post'], 'AdminPage');
        if ($result !== true) {
            $this->error($result);
        }

        $this->model->adminEditPage($data['post']);

        $this->success(lang('SAVE_SUCCESS'));

    }

    /**
     * 删除页面
     * @author    iyting@foxmail.com
     * @adminMenu(
     *     'name'   => '删除页面',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除页面',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $data            = $this->request->param();

        $result = $this->model->adminDeletePage($data);
        if ($result) {
            $this->success(lang('DELETE_SUCCESS'));
        } else {
            $this->error(lang('DELETE_FAILED'));
        }

    }

}
