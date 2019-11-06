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

use cmf\controller\AdminBaseController;
use think\Db;

class AdminModelxController extends AdminBaseController
{

    /**
     * Model模型对象
     */
    protected $model = null;

    public function initialize()//TP5.1写法
    {
        parent::initialize();
        $this->model = new \app\cms\model\CmsModelxModel;
    }


    /**
     * 内容模型列表
     * @adminMenu(
     *     'name'   => '模型管理',
     *     'parent' => 'cms/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '模型管理',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $list = $this->model
                //->where($condition)
                //->where($where)
                //->order($sort, $order)
                //->limit($offset, $limit)
                //->select();
                ->paginate(10);
        $this->assign('modelList', $list);
        $this->assign('page', $list->render());
        return $this->fetch();
    }

    /**
     * 添加操作
     */
    public function add()
    {
        $defaultTheme = config('template.cmf_default_theme');
        if ($temp = session('cmf_default_theme')) {
            $defaultTheme = $temp;
        }
        //栏目页模板
        $channeltpl = Db::name('theme_file')->where('theme', $defaultTheme)->where('file','like',['%channel%','%list%'],'OR')->field('file')->order('list_order ASC')->select()->toArray();

        //列表页模板
        $listtpl = Db::name('theme_file')->where('theme', $defaultTheme)->where('file','like','%list%')->field('file')->order('list_order ASC')->select()->toArray();

        //详情页模板
        $showtpl = Db::name('theme_file')->where('theme', $defaultTheme)->where('file','like','%show%')->field('file')->order('list_order ASC')->select()->toArray();

        $this->assign("channeltpl", $channeltpl);
        $this->assign("listtpl", $listtpl);
        $this->assign("showtpl", $showtpl);
        return $this->fetch();
    }

    /**
     * 提交操作
     */
    public function addPost()
    {
        if ($this->request->isPost()) {

            $data = $this->request->param();
            $post = $data['post'];

            $result = $this->validate($data, 'AdminModelx');
            if ($result !== true) {
                $this->error($result);
            }else{
                //判断
                $result = Db::name('cms_model')->where("name", $post['name'])->find();
                if ($result) {
                    $this->error("该模型名称已存在！");
                }
                $result = Db::name('cms_model')->where("table", $post['table'])->find();
                if ($result) {
                    $this->error("该表名已存在！");
                }
                
                $result = $this->model->allowField(true)->isUpdate(false)->save($post);
                if ($result) {
                    $this->success("添加成功!", url("AdminModelx/index"));
                } else {
                    $this->error("添加失败");
                }
            }
        }
    }

    /**
     * 编辑操作
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        if ($id > 0) {
            $data            = $this->model->get($id)->toArray();

            $defaultTheme = config('template.cmf_default_theme');
            if ($temp = session('cmf_default_theme')) {
                $defaultTheme = $temp;
            }
            //栏目页模板
            $channeltpl = Db::name('theme_file')->where('theme', $defaultTheme)->where('file','like',['%channel%','%list%'],'OR')->field('file')->order('list_order ASC')->select()->toArray();

            //列表页模板
            $listtpl = Db::name('theme_file')->where('theme', $defaultTheme)->where('file','like','%list%')->field('file')->order('list_order ASC')->select()->toArray();

            //详情页模板
            $showtpl = Db::name('theme_file')->where('theme', $defaultTheme)->where('file','like','%show%')->field('file')->order('list_order ASC')->select()->toArray();

            $this->assign("channeltpl", $channeltpl);
            $this->assign("listtpl", $listtpl);
            $this->assign("showtpl", $showtpl);
            $this->assign("post", $data);
            return $this->fetch();
        }else {
            $this->error('操作错误!');
        }
    }

    /**
     * 编辑模型提交
     * @adminMenu(
     *     'name'   => '编辑模型提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑模型提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {

            $data = $this->request->param();
            $post = $data['post'];
            unset($post['table']);//表名不能修改

            $result = $this->validate($data, 'AdminModelx');
            if ($result !== true) {
                $this->error($result);
            }else{
                //判断
                $count = Db::name('cms_model')->where([
                    ["name", "=", $post['name']],
                    ["id", "<>", $post["id"]]
                ])->count();
                if ($count > 0) {
                    $this->error("该模型名称已存在！");
                }
                $result = $this->model->allowField(true)->isupdate(true)->save($post);
                if ($result) {
                    $this->success("编辑成功!", url("AdminModelx/index"));
                } else {
                    $this->error("编辑失败");
                }
            }
        }
    }


    /**
     * 删除操作
     */
    public function delete()
    {
        $param           = $this->request->param();
        
        //单个id删除
        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $result = $this->model->destroy($id);
            if($result){
                $this->success("删除成功！", '');
            }else{
                $this->error("删除失败！");
            }
        }

        //多个id删除
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result = $this->model->destroy($ids);
            if($result){
                $this->success("删除成功！", '');
            }else{
                $this->error("删除失败！");
            }
        }
     }
}
