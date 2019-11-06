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
use app\admin\model\ThemeModel;
use app\cms\model\CmsModelxModel;
use app\cms\model\CmsChannelModel;
use app\cms\service\PostService;
use think\Db;

class AdminArchivesController extends AdminBaseController
{

    /**
     * Archives模型对象
     */
    protected $model = null;
    protected $channelModel = null;
    public function initialize()//TP5.1写法
    {
        parent::initialize();
        $this->model = new \app\cms\model\CmsArchivesModel;
        $this->channelModel = new \app\cms\model\CmsChannelModel;
    }

    /**
     * 文章列表
     * @adminMenu(
     *     'name'   => '内容管理',
     *     'parent' => 'cms/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '内容管理',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $content = hook_one('cms_admin_archives_index_view');

        if (!empty($content)) {
            return $content;
        }

        $param = $this->request->param();

        $categoryId = $this->request->param('category', 0, 'intval');

        $postService = new PostService();
        $data        = $postService->adminArticleList($param);

        $data->appends($param);

        $categoryTree        = $this->channelModel->adminChannelTree($categoryId);

        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('articles', $data->items());
        $this->assign('category_tree', $categoryTree);
        $this->assign('category', $categoryId);
        $this->assign('page', $data->render());


        return $this->fetch();
    }

    /**
     * 添加文章
     * @adminMenu(
     *     'name'   => '添加文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add()
    {
        $content = hook_one('cms_admin_archives_add_view');

        if (!empty($content)) {
            return $content;
        }

        $modelxlist = CmsModelxModel::all();
        if (count($modelxlist)==0) {
            $this->error('请先在“模型管理”添加对应模型');
        }

        //cmf格式
        $categoryTree        = $this->channelModel->adminChannelTree(0);
        $this->assign('category_tree', $categoryTree);

        // $themeModel        = new ThemeModel();
        // $articleThemeFiles = $themeModel->getActionThemeFiles('cms/Archives/index');
        // $this->assign('article_theme_files', $articleThemeFiles);
        return $this->fetch();
    }

    /**
     * 添加文章提交
     * @adminMenu(
     *     'name'   => '添加文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加文章提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();

            //状态只能设置默认值
            $data['post']['status'] = 1;
            $post = $data['post'];
            $result = $this->validate($post, 'AdminArchives');
            if ($result !== true) {
                $this->error($result);
            }


            /**
             * 单选框
             */
            if(!empty($post['radio_list'])){
                $list = $post['radio_list'];
                foreach ($list as $v) {
                    if (!isset($post[$v])) {
                        $data['post'][$v] = [];//顺序不能乱
                    }
                }
                unset($data['post']['radio_list']);
            }

            /**
             * 复选框
             */
            if(!empty($post['checkbox_list'])){
                $list = $post['checkbox_list'];
                foreach ($list as $v) {
                    if (!isset($post[$v])) {
                        $data['post'][$v] = [];//顺序不能乱
                    }
                }
                unset($data['post']['checkbox_list']);
            }

            /**
             * 多图片\多文件处理
             */
            if(!empty($post['images_files'])){
                $list = $post['images_files'];
                foreach ($list as $v) {
                    $data['post'][$v] = [];//顺序不能乱
                    if (!empty($post[$v.'_names']) && !empty($post[$v.'_urls'])) {
                        foreach ($post[$v.'_urls'] as $key => $url) {
                            $photoUrl = cmf_asset_relative_url($url);
                            array_push($data['post'][$v], ["url" => $photoUrl, "name" => $post[$v.'_names'][$key]]);
                        }
                        unset($data['post'][$v.'_names']);
                        unset($data['post'][$v.'_urls']);
                    }
                }
                unset($data['post']['images_files']);
            }


            $this->model->adminAddArticle($data['post'], $data['post']['channel_id']);

            $data['post']['id'] = $this->model->id;
            $hookParam          = [
                'is_add'  => true,
                'archives' => $data['post']
            ];
            hook('cms_admin_after_save_archives', $hookParam);

            $this->success('添加成功!', url('AdminArchives/edit', ['id' => $this->model->id]));
        }

    }

    /**
     * 编辑文章
     * @adminMenu(
     *     'name'   => '编辑文章',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function edit()
    {
        $content = hook_one('cms_admin_archives_edit_view');

        if (!empty($content)) {
            return $content;
        }

        $modelxlist = CmsModelxModel::all();
        if (count($modelxlist)==0) {
            $this->error('未找到对应模型');
        }

        $id = $this->request->param('id', 0, 'intval');

        $post            = $this->model->where('id', $id)->find();
        // $postCategories  = $post->channel()->alias('a')->column('a.name', 'a.id');
        // $postCategoryIds = implode(',', array_keys($postCategories));
        
        //外表
        $out_table  = $post->channel()->alias('a')->column('a.model_id');
        $out_id = $out_table[0];
        //外表插入
        $model = CmsModelxModel::get($out_id);
        $out_table_data = Db::name($model['table'])->get($id);
        //必定字段
        $post['content'] = cmf_replace_content_file_url(htmlspecialchars_decode($out_table_data['content']));

        //$themeModel        = new ThemeModel();
        //$articleThemeFiles = $themeModel->getActionThemeFiles('cms/Archives/index');
        //$this->assign('article_theme_files', $articleThemeFiles);

        
        //cmf格式
        $categoryTree        = $this->channelModel->adminChannelTree(0);
        $this->assign('category_tree', $categoryTree);
        $this->assign('post', $post);
        //$this->assign('dynamicFields', $dynamicFields->toArray());

        // $this->assign('post_categories', $postCategories);
        // $this->assign('post_category_ids', $postCategoryIds);

        return $this->fetch();
    }

    /**
     * 编辑文章提交
     * @adminMenu(
     *     'name'   => '编辑文章提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑文章提交',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     */
    public function editPost()
    {

        if ($this->request->isPost()) {
            $data = $this->request->param();

            //需要抹除发布、置顶、推荐的修改。
            unset($data['post']['status']);
            // unset($data['post']['is_top']);
            // unset($data['post']['recommended']);

            $post   = $data['post'];
            $result = $this->validate($post, 'AdminArchives');
            if ($result !== true) {
                $this->error($result);
            }

/**
             * 单选框
             */
            if(!empty($post['radio_list'])){
                $list = $post['radio_list'];
                foreach ($list as $v) {
                    if (!isset($post[$v])) {
                        $data['post'][$v] = [];//顺序不能乱
                    }
                }
                unset($data['post']['radio_list']);
            }

            /**
             * 复选框
             */
            if(!empty($post['checkbox_list'])){
                $list = $post['checkbox_list'];
                foreach ($list as $v) {
                    if (!isset($post[$v])) {
                        $data['post'][$v] = [];//顺序不能乱
                    }
                }
                unset($data['post']['checkbox_list']);
            }

            /**
             * 多图片\多文件处理
             */
            if(!empty($post['images_files'])){
                $list = $post['images_files'];
                foreach ($list as $v) {
                    $data['post'][$v] = [];//顺序不能乱
                    if (!empty($post[$v.'_names']) && !empty($post[$v.'_urls'])) {
                        foreach ($post[$v.'_urls'] as $key => $url) {
                            $photoUrl = cmf_asset_relative_url($url);
                            array_push($data['post'][$v], ["url" => $photoUrl, "name" => $post[$v.'_names'][$key]]);
                        }
                        unset($data['post'][$v.'_names']);
                        unset($data['post'][$v.'_urls']);
                    }
                }
                unset($data['post']['images_files']);
            }


            $this->model->adminEditArchives($data['post'], $data['post']['channel_id']);

            $hookParam = [
                'is_add'  => false,
                'article' => $data['post']
            ];
            hook('cms_admin_after_save_archives', $hookParam);

            $this->success('保存成功!');

        }
    }

    /**
     * 文章删除
     * @adminMenu(
     *     'name'   => '文章删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章删除',
     *     'param'  => ''
     * )
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete()
    {
        $param           = $this->request->param();
        
        //单条删除
        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            $resultPortal = $this->model
                ->where('id', $id)
                ->update(['delete_time' => time()]);
            $this->success("删除成功！", '');

        }

        //批量删除
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result  = $this->model->where('id', 'in', $ids)->update(['delete_time' => time()]);
            $this->success("删除成功！", '');
        }
    }

    /**
     * 文章发布
     * @adminMenu(
     *     'name'   => '文章发布',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章发布',
     *     'param'  => ''
     * )
     */
    public function publish()
    {
        $param           = $this->request->param();

        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');
            $this->model->where('id', 'in', $ids)->update(['status' => 1, 'published_time' => time()]);
            $this->success("发布成功！", '');
        }

        if (isset($param['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');
            $this->model->where('id', 'in', $ids)->update(['status' => 0]);
            $this->success("取消发布成功！", '');
        }

    }

    // /**
    //  * 文章置顶
    //  * @adminMenu(
    //  *     'name'   => '文章置顶',
    //  *     'parent' => 'index',
    //  *     'display'=> false,
    //  *     'hasView'=> false,
    //  *     'order'  => 10000,
    //  *     'icon'   => '',
    //  *     'remark' => '文章置顶',
    //  *     'param'  => ''
    //  * )
    //  */
    // public function top()
    // {
    //     $param           = $this->request->param();
        

    //     if (isset($param['ids']) && isset($param["yes"])) {
    //         $ids = $this->request->param('ids/a');

    //         $this->model->where('id', 'in', $ids)->update(['is_top' => 1]);

    //         $this->success("置顶成功！", '');

    //     }

    //     if (isset($_POST['ids']) && isset($param["no"])) {
    //         $ids = $this->request->param('ids/a');

    //         $this->model->where('id', 'in', $ids)->update(['is_top' => 0]);

    //         $this->success("取消置顶成功！", '');
    //     }
    // }

    // /**
    //  * 文章推荐
    //  * @adminMenu(
    //  *     'name'   => '文章推荐',
    //  *     'parent' => 'index',
    //  *     'display'=> false,
    //  *     'hasView'=> false,
    //  *     'order'  => 10000,
    //  *     'icon'   => '',
    //  *     'remark' => '文章推荐',
    //  *     'param'  => ''
    //  * )
    //  */
    // public function recommend()
    // {
    //     $param           = $this->request->param();
        

    //     if (isset($param['ids']) && isset($param["yes"])) {
    //         $ids = $this->request->param('ids/a');

    //         $this->model->where('id', 'in', $ids)->update(['recommended' => 1]);

    //         $this->success("推荐成功！", '');

    //     }
    //     if (isset($param['ids']) && isset($param["no"])) {
    //         $ids = $this->request->param('ids/a');

    //         $this->model->where('id', 'in', $ids)->update(['recommended' => 0]);

    //         $this->success("取消推荐成功！", '');

    //     }
    // }

    /**
     * 文章排序
     * @adminMenu(
     *     'name'   => '文章排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        //parent::listOrders(Db::name('portal_category_post'));
        parent::listOrders($this->model);
        $this->success("排序更新成功！", '');
    }

    
    /**
     * 获取栏目列表(添加页面)
     * @internal
     */
    public function get_channel_fields()
    {
        $channel_id = $this->request->post('channel_id');
        $archives_id = $this->request->post('archives_id');
        //获取栏目信息
        $channel = $this->channelModel->get($channel_id, 'model');
        if ($channel && $channel['type'] === 'list') {
            
            //如果是编辑操作
            $values = [];
            if ($archives_id) {
                $values = db($channel['model']['table'])->where('id', $archives_id)->find();
            }

            $fields = \app\cms\model\CmsFieldsModel::where([
                    "status"   => 1,//显示状态
                    'model_id' => $channel['model_id']
                ])
                ->order('list_order asc,id desc')
                ->select();
            if(count($fields)>0){
                foreach ($fields as $k => $v) {
                    //优先取编辑的值,再次取默认值
                    $v->value = isset($values[$v['name']]) ? $values[$v['name']] : (is_null($v['defaultvalue']) ? '' : $v['defaultvalue']);
                    //值
                    if(!empty($values)){
                        switch($v->type){
                            case "checkbox"://复选框
                                if(!empty($values[$v['name']])){
                                    $v->value = ','.$values[$v['name']].',';
                                }
                            break;
                            case "date"://日期
                                $v->value = date('Y-m-d', strtotime($v->value)); 
                            break;
                            case "time"://时间
                                $v->value = date('Y-m-d H:i', strtotime(date('Y-m-d', time()).' '.$v->value)); 
                            break;
                            case "datetime"://日期时间
                                $v->value = date('Y-m-d H:i', strtotime($v->value)); 
                            break;
                            case "editor"://编辑器
                                $v->value = cmf_replace_content_file_url(htmlspecialchars_decode($v->value));//转码
                            break;
                            case "image"://单图片
                                if(empty($v->value)){
                                    $v->value = '/themes/admin_simpleboot3/public/assets/images/default-thumbnail.png';
                                }else{
                                    $v->value = cmf_get_image_preview_url($v->value);
                                }
                            break;
                            case "images"://多图片
                                if(!empty($v->value)){
                                    $array_value = json_decode($v->value, true);
                                    $html = '';
                                    if(is_array($array_value)){
                                        foreach($array_value as $k => $vo){
                                            $img_url = cmf_get_image_preview_url($vo['url']);
                                            $html .= '<li id="saved-image'.$v['name'].$k.'">';
                                            $html .= '    <input id="photo-'.$v['name'].$k.'" type="hidden" name="post['.$v['name'].'_urls][]" value="'.$img_url.'">';
                                            $html .= '    <input class="form-control" id="photo-'.$v['name'].$k.'-name" type="text" name="post['.$v['name'].'_names][]" value="'.$vo['name'].'" style="width: 200px;" title="图片名称">';
                                            $html .= '    <img id="photo-'.$v['name'].$k.'-preview" src="'.$img_url.'" style="height:36px;width: 36px;" onclick="imagePreviewDialog(this.src);">';
                                            $html .= '    <a href="javascript:uploadOneImage(\'图片上传\',\'#photo-'.$v['name'].$k.'\');">替换</a>';
                                            $html .= '    <a href="javascript:(function(){$(\'#saved-image'.$v['name'].$k.'\').remove();})();">移除</a>';
                                            $html .= '</li>';
                                        }
                                    }
                                    $v->value = $html;
                                }
                            break;
                            case "file"://单文件
                                $html = '';
                                $file_url = cmf_get_file_download_url($v->value);
                                $html .= '<input id="file-'.$v['name'].'" class="form-control" type="text" name="post['.$v['name'].']" value="'.$v->value.'" placeholder="请上传文件" style="width: 200px;">';
                                $html .= '<a id="file-'.$v['name'].'-preview" href="'.$file_url.'"target="_blank">下载</a>';
                                $v->value = $html;
                            break;
                            case "files"://多文件
                                if(!empty($v->value)){
                                    $array_value = json_decode($v->value, true);
                                    $html = '';
                                    if(is_array($array_value)){
                                        foreach($array_value as $k => $vo){
                                            $file_url = cmf_get_file_download_url($vo['url']);
                                            $html .= '<li id="saved-file'.$v['name'].$k.'">';
                                            $html .= '    <input id="file-'.$v['name'].$k.'" type="hidden" name="post['.$v['name'].'_urls][]" value="'.$vo['url'].'">';
                                            $html .= '    <input class="form-control" id="file-'.$v['name'].$k.'-name" type="text" name="post['.$v['name'].'_names][]" value="'.$vo['name'].'" style="width: 200px;" title="文件名称">';
                                            $html .= '    <a id="file-'.$v['name'].$k.'-preview" href="'.$file_url.'" target="_blank">下载</a>';
                                            $html .= '    <a href="javascript:uploadOne(\'文件上传\',\'#file-'.$v['name'].$k.'\',\'file\');">替换</a>';
                                            $html .= '    <a href="javascript:(function(){$(\'#saved-file'.$v['name'].$k.'\').remove();})();">移除</a>';
                                            $html .= '</li>';
                                        }
                                    }
                                    $v->value = $html;
                                }
                            break;
                            default:
                            break;
                        }
                    }
                    //规则
                    $v->rule = str_replace(',', '; ', $v->rule);
                    if (in_array($v->type, ['checkbox', 'lists', 'images'])) {
                        $checked = '';
                        if ($v['minimum'] && $v['maximum']) {
                            $checked = "{$v['minimum']}~{$v['maximum']}";
                        } elseif ($v['minimum']) {
                            $checked = "{$v['minimum']}~";
                        } elseif ($v['maximum']) {
                            $checked = "~{$v['maximum']}";
                        }
                        if ($checked) {
                            $v->rule .= (';checked(' . $checked . ')');
                        }
                    }
                    if (in_array($v->type, ['checkbox', 'radio']) && stripos($v->rule, 'required') !== false) {
                        $v->rule = str_replace('required', 'checked', $v->rule);
                    }
                    if (in_array($v->type, ['selects'])) {
                        $v->extend .= (' ' . 'data-max-options="' . $v['maximum'] . '"');
                    }
                }
                echo json_encode($fields);
            } else {//没有字段
                echo "nofields";
            }
        }else{
            echo "nochannel";
        }
    }

    
    /**
     * 回收站
     */
    public function recyclebin()
    {
        $content = hook_one('cms_admin_archives_recycle_view');

        if (!empty($content)) {
            return $content;
        }

        $param = $this->request->param();

        $categoryId = $this->request->param('category', 0, 'intval');

        $postService = new PostService();
        $param['delete_time'] = 1;//随意赋值
        $data        = $postService->adminArticleList($param);

        $data->appends($param);

        $categoryTree        = $this->channelModel->adminChannelTree($categoryId);

        // $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        // $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('articles', $data->items());
        // $this->assign('category_tree', $categoryTree);
        // $this->assign('category', $categoryId);
        $this->assign('page', $data->render());


        return $this->fetch();
    }

    /**
     * 销毁
     * @param string $ids
     */
    public function destroy()
    {
        $param = $this->request->param();
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
     * 还原
     * @param mixed $ids
     */
    public function restore()
    {
        $param = $this->request->param();
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $result = $this->model->where('id', 'in', $ids)->update(['delete_time' => 0]);
            if($result){
                $this->success("还原成功！", '');
            }else{
                $this->error("还原失败！");
            }
        }
    }


}
