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
use app\cms\model\ConfigModel;

class AdminFieldsController extends AdminBaseController
{

    /**
     * Fields模型对象
     */
    protected $model = null;

    public function initialize()//TP5.1写法
    {
        parent::initialize();
        $this->model = new \app\cms\model\CmsFieldsModel;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign('typeList', ConfigModel::getTypeList());
        // $this->view->assign('regexList', ConfigModel::getRegexList());
    }

    /**
     * 查看
     */
    public function index()
    {
        $model_id = $this->request->param('model_id', 0);
        $diyform_id = $this->request->param('diyform_id', 0);
        $condition = $model_id ? ['model_id' => $model_id] : ['diyform_id' => $diyform_id];
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        //if ($this->request->isAjax()) {
            //list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            // $total = $this->model
            //     ->where($condition)
            //     ->where($where)
            //     ->order($sort, $order)
            //     ->count();

            $list = $this->model
                ->where($condition)
                //->where($where)
                ->order('list_order', 'asc')
                //->limit($offset, $limit)
                //->select();
                ->paginate(10);

            $nameArr = [
                'id'          => '主键',
                'user_id'     => '会员ID',
                'channel_id'  => '栏目ID',
                'model_id'    => '模型ID',
                'title'       => '标题',
                //'style'       => '样式',
                //'flag'        => '标志',
                //'image'       => '缩略图',
                'keywords'    => '关键字',
                'description' => '描述',
                //'tags'        => '标签',
                'list_order'  => '权重',
                'hits'       => '浏览次数',
                //'comments'    => '评论次数',
                //'likes'       => '点赞次数',
                //'dislikes'    => '点踩次数',
                //'diyname'     => '自定义名称',
                'create_time'  => '创建时间',
                'update_time'  => '更新时间',
                'published_time' => '发布时间',
                'delete_time'  => '删除时间',
                //'memo'        => '备注',
                'status'      => '状态'
            ];
            // if ($model_id) {
            //     $list = collection($list)->toArray();
            //     $tableInfoList = \think\Db::name('cms_archives')->getTableInfo();
            //     $tableInfoList['fields'] = array_reverse($tableInfoList['fields']);
            //     foreach ($tableInfoList['fields'] as $index => $field) {
            //         $type = isset($tableInfoList['type'][$field]) ? substr($tableInfoList['type'][$field], 0, stripos($tableInfoList['type'][$field], '(')) : 'unknown';
            //         $item = [
            //             'state' => false, 'model_id' => $model_id, 'diyform_id' => '-', 'name' => $field, 'title' => isset($nameArr[$field]) ? $nameArr[$field] : '',
            //             'type'  => $type, 'issystem' => true, 'isfilter' => 0, 'iscontribute' => 0, 'status' => 1, 'create_time' => 0, 'update_time' => 0
            //         ];
            //         $list[] = $item;
            //     }
            // }
            // $result = array("total" => $total, "rows" => $list);

            // return json($result);
        //}

        $this->assign('fieldsList', $list);
        $this->assign('model_id', $model_id);
        $this->assign('diyform_id', $diyform_id);
        $this->assign('page', $list->render());

        // $model = $model_id ? \app\cms\model\ModelxModel::get($model_id) : \app\cms\model\DiyformModel::get($diyform_id);
        // $this->assign('model', $model);
        // $modelList = $model_id ? \app\cms\model\ModelxModel::all() : \app\cms\model\DiyformModel::all();
        // $this->assign('modelList', $modelList);

        return $this->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $model_id = $this->request->param('model_id', 0);
        $diyform_id = $this->request->param('diyform_id', 0);
        $this->view->assign('model_id', $model_id);
        $this->view->assign('diyform_id', $diyform_id);
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

            $result = $this->validate($this->request->param(), 'AdminFields');
            if ($result !== true) {
                $this->error($result);
            }else{
                //判断
                if (!preg_match("/^([a-zA-Z0-9_]+)$/i", $post['name'])) {
                    $this->error("字段只支持字母数字下划线");
                }
                if (is_numeric(substr($post['name'], 0, 1))) {
                    $this->error("字段不能以数字开始");
                }
    
                if ($post['model_id']) {
                    $tableFields = Db::name('cms_archives')->getTableFields();
                    if (in_array(strtolower($post['name']), $tableFields)) {
                        $this->error("字段已经在主表存在了");
                    }
                    //判断字段是否已存在
                    $tableFields = \app\cms\model\CmsModelxModel::where('id', $post['model_id'])->column('fields');
                    if (in_array(strtolower($post['name']), $tableFields)) {
                        $this->error("字段已经在副表存在了");
                    }
                    $tableFields = ['content'];
                    if (in_array(strtolower($post['name']), $tableFields)) {
                        $this->error("字段为保留字段，请使用其它字段");
                    }
                } else {
                    $tableFields = ['id', 'user_id', 'create_time', 'update_time'];
                    if (in_array(strtolower($post['name']), $tableFields)) {
                        $this->error("字段为保留字段，请使用其它字段");
                    }
                }

                //判断
                $result = $this->model->where(["model_id"=>$post['model_id'],"name"=>$post['name']])->find();
                if ($result) {
                    $this->error("该字段名称已存在！");
                }

                $result = $this->model->allowField(true)->save($post);

                if ($result) {
                    $this->success("添加成功!", url("AdminFields/index", ['model_id'=>$post['model_id']]));
                } else {
                    $this->error("添加失败");
                }
            }
        }
    }


     /**
     * 编辑
     */
    public function edit()
    {
        $id = $this->request->param('id', 0);
        $model = $this->model->get($id)->toArray();
        $this->view->assign($model);
        return $this->fetch();
    }

    /**
     * 编辑提交
     */
    public function editPost(){

        if ($this->request->isPost()) {

            $data = $this->request->param();
            $post = $data['post'];

            $result = $this->validate($this->request->param(), 'AdminFields');
            if ($result !== true) {
                $this->error($result);
            }else{
                //判断
                if (!preg_match("/^([a-zA-Z0-9_]+)$/i", $post['name'])) {
                    $this->error("字段只支持字母数字下划线");
                }
                if (is_numeric(substr($post['name'], 0, 1))) {
                    $this->error("字段不能以数字开始");
                }
    
                $id = $post['id'];
                if ($id) {
                    $tableFields = Db::name('cms_archives')->getTableFields();
                    if (in_array(strtolower($post['name']), $tableFields)) {
                        $this->error("字段已经在主表存在了");
                    }
                } else {
                    $tableFields = ['id', 'user_id', 'create_time', 'update_time'];
                    if (in_array(strtolower($post['name']), $tableFields)) {
                        $this->error("字段为保留字段，请使用其它字段");
                    }
                }

                //判断
                $result = $this->model->where('id', '<>', $id)->where(["name"=>$post['name']])->find();
                if ($result&&$result['id']==$id) {
                    $this->error("该字段名称已存在！");
                }

                $result = $this->model->allowField(true)->isupdate(true)->save($post);

                if ($result) {
                    $this->success("修改成功!", url("AdminFields/index", ['model_id'=>$post['model_id']]));
                } else {
                    $this->error("修改失败");
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
        
        //单条删除
        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');

            //$this->model->where('id',$id)->delete(); 
            //以上这种删除方式不触发after_delete事件
            //解决参考：https://www.baidu.com/link?url=zxH3BQ1BDdWmsdtpgJ7H_aJNWryCk1kvx9P7yq2Dbu71Jdj0GRGeJ1exipKIpATtj28bnQrkeKIlgIg1JESW_dBEQ6TdVX1P_Lw4ezXjz-y&wd=&eqid=8e07fe8d0001d3fb000000065cb553a7
            
            $result = $this->model->destroy($id);
            if($result){
                $this->success("删除成功！", '');
            }else{
                $this->error("删除失败！");
            }
        }

        //批量删除
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

     /**
     * 字段排序
     */
    public function listOrder()
    {
        parent::listOrders($this->model);
        $this->success("排序更新成功！", '');
    }
}
