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
namespace app\cms\model;

use app\admin\model\RouteModel;
use think\db\Query;
use think\Model;
use tree\Tree;

class CmsChannelModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];


    protected static function init()
    {
        //添加后的操作
        self::afterInsert(function ($row) {
            
        });

        /**
         * 修改前操作
         */
        self::beforeUpdate(function ($row) {
            
        });

        /**
         * 修改后操作
         */
        self::afterUpdate(function ($row) {
            // if($row['delete_time'] > 0){//删除操作
            //     if (!empty($row['alias'])) {//设置别名
            //         $routeModel = new RouteModel();
            //         $routeModel->deleteRoute('cms/List/index', ['id' => $row['id']]);
            //         $routeModel->deleteRoute('cms/Archives/index', ['cid' => $row['id']]);
            //         $routeModel->getRoutes(true);//强制刷新
            //     }
            // }else{//回收站恢复操作
            //     if (!empty($row['alias'])) {
            //         $routeModel = new RouteModel();
            //         $routeModel->setRoute($row['alias'], 'cms/List/index', ['id' => $row['id']], 2, 5000);
            //         $routeModel->setRoute($row['alias'] . '/:id', 'cms/Archives/index', ['cid' => $row['id']], 2, 4999);
            //         $routeModel->getRoutes(true);//强制刷新
            //     } 
            // }
        });

        /**
         * 删除后操作
         */
        self::afterDelete(function ($row) {
            // if($row['type'] != 'link'){//连接
            //     //注册路由
            //     $routeModel = new RouteModel();
            //     $routeModel->deleteRoute('cms/List/index', ['id' => $row['id']]);
            //     $routeModel->deleteRoute('cms/Archives/index', ['cid' => $row['id']]);
            //     $routeModel->getRoutes(true);//强制刷新
            // }
        });
    }

    
           
    /**
     * 生成分类 select树形结构
     * @param int $selectId   需要选中的分类 id
     * @param int $currentCid 需要隐藏的分类 id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminChannelTree($selectId = 0, $currentCid = 0)
    {
        $channel = $this->order("list_order ASC")
            ->where('delete_time', 0)
            ->where(function (Query $query) use ($currentCid) {
                if (!empty($currentCid)) {
                    $query->where('id', 'neq', $currentCid);
                }
            })
            ->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        $newCategories = [];
        foreach ($channel as $item) {
            $item['selected'] = $selectId == $item['id'] ? "selected" : "";
            array_push($newCategories, $item);
        }

        $tree->init($newCategories);
        $str     = '<option model=\"{$model_id}\" value=\"{$id}\" {$selected}>{$spacer}{$name}</option>';
        $treeStr = $tree->getTree(0, $str);

        return $treeStr;
    }

    /**
     * 分类树形结构
     * @param int    $currentIds
     * @param string $tpl
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminChannelTableTree($currentIds = 0, $tpl = '')
    {
//        if (!empty($currentCid)) {
//            $where['id'] = ['neq', $currentCid];
//        }
        $channel = $this->order("list_order ASC")->where('delete_time', 0)->select()->toArray();

        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';

        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        $newCategories = [];
        foreach ($channel as $item) {
            $item['parent_id_node'] = ($item['parent_id']) ? ' class="child-of-node-' . $item['parent_id'] . '"' : '';
            $item['style']          = empty($item['parent_id']) ? '' : 'display:none;';
            $item['status_text']    = empty($item['status']) ? '<span class="label label-warning">隐藏</span>' : '<span class="label label-success">显示</span>';
            $item['checked']        = in_array($item['id'], $currentIds) ? "checked" : "";
            $item['str_action']     = '<a class="btn btn-xs btn-primary" href="' . url("AdminChannel/add", ["parent" => $item['id']]) . '">添加子分类</a>  <a class="btn btn-xs btn-primary" href="' . url("AdminChannel/edit", ["id" => $item['id']]) . '">' . lang('EDIT') . '</a>  <a class="btn btn-xs btn-danger js-ajax-delete" href="' . url("AdminChannel/delete", ["id" => $item['id']]) . '">' . lang('DELETE') . '</a> ';
            if ($item['status']) {
                $item['str_action'] .= '<a class="btn btn-xs btn-warning js-ajax-dialog-btn" data-msg="您确定隐藏此分类吗" href="' . url('AdminChannel/toggle', ['ids' => $item['id'], 'hide' => 1]) . '">隐藏</a>';
            } else {
                $item['str_action'] .= '<a class="btn btn-xs btn-success js-ajax-dialog-btn" data-msg="您确定显示此分类吗" href="' . url('AdminChannel/toggle', ['ids' => $item['id'], 'display' => 1]) . '">显示</a>';
            }
            //增加
            switch($item['type']){
                case 'channel':
                    $item['type']='栏目';
                    $item['url']            = cmf_url('cms/List/index', ['id' => $item['id']]);
                break;
                case 'list':
                    $item['type']='列表';
                    $item['url']            = cmf_url('cms/List/index', ['id' => $item['id']]);
                break;
                case 'link':
                    $item['type']='外部链接';
                    $item['url']            = $item['outlink'];
                break;
                default:
                break;
            }
            //模型名称
            $cmsModelxModel   = new CmsModelxModel();
            $model = $cmsModelxModel->where('id',$item['model_id'])->field('name')->find();
            if($model){
                $item['model_id']= $model->name;
            }else{
                $item['model_id']= '无';
            }
            
            array_push($newCategories, $item);
        }

        $tree->init($newCategories);

        if (empty($tpl)) {
            $tpl = " <tr id='node-\$id' \$parent_id_node style='\$style' data-parent_id='\$parent_id' data-id='\$id'>
                        <td style='padding-left:20px;'><input type='checkbox' class='js-check' data-yid='js-check-y' data-xid='js-check-x' name='ids[]' value='\$id' data-parent_id='\$parent_id' data-id='\$id'></td>
                        <td><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input-order'></td>
                        <td>\$id</td>
                        <td>\$type</td>
                        <td>\$model_id</td>
                        <td>\$spacer <a href='\$url' target='_blank'>\$name</a></td>
                        <td>\$status_text</td>
                        <td>\$str_action</td>
                    </tr>";
        }
        $treeStr = $tree->getTree(0, $tpl);

        return $treeStr;
    }

    /**
     * 添加文章分类
     * @param $data
     * @return bool
     */
    public function addChannel($data)
    {
        $result = true;
        self::startTrans();
        try {
            if (!empty($data['more']['thumbnail'])) {
                $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
            }

            //如果是栏目
            switch($data['type']){
                case "link"://外链
                case "channel"://栏目
                    $data['listtpl'] = $data['showtpl'] = '';
                break;
                case "list"://列表
                    if($data['parent_id']==0){//顶级
                        $data['channeltpl'] = 'channel';
                    }else{
                        $parentChannel = $this->get($data['parent_id']);
                        $data['channeltpl'] = $parentChannel['channeltpl'];
                    }
                break;
                default:
                break;
            }

            $this->allowField(true)->isUpdate(false)->save($data);
            $id = $this->id;
            if (empty($data['parent_id'])) {
                $this->where('id', $id)->update(['path' => '0-' . $id]);
            } else {
                $parentPath = $this->where('id', intval($data['parent_id']))->value('path');
                $this->where('id', $id)->update(['path' => "$parentPath-$id"]);
            }
            self::commit();
        } catch (\Exception $e) {
            self::rollback();
            $result = false;
        }

        if ($result != false) {
            //设置别名
            $routeModel = new RouteModel();
            if (!empty($data['alias']) && !empty($id)) {
                $routeModel->setRoute($data['alias'], 'cms/List/index', ['id' => $id], 2, 5000);
                $routeModel->setRoute($data['alias'] . '/:id', 'cms/Archives/index', ['cid' => $id], 2, 4999);
            }
            $routeModel->getRoutes(true);
        }

        return $result;
    }

    /**
     * 编辑文章分类
     * @param $data
     * @return bool
     */
    public function editChannel($data)
    {
        $result = true;

        $id          = intval($data['id']);
        $parentId    = intval($data['parent_id']);
        $oldCategory = $this->where('id', $id)->find();

        if (empty($parentId)) {
            $newPath = '0-' . $id;
        } else {
            $parentPath = $this->where('id', intval($data['parent_id']))->value('path');
            if ($parentPath === false) {
                $newPath = false;
            } else {
                $newPath = "$parentPath-$id";
            }
        }

        if (empty($oldCategory) || empty($newPath)) {
            $result = false;
        } else {

            $data['path'] = $newPath;
            if (!empty($data['more']['thumbnail'])) {
                $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
            }

            //如果是栏目
            switch($data['type']){
                case "link"://外链
                case "channel"://栏目
                    $data['listtpl'] = $data['showtpl'] = '';
                break;
                case "list"://列表
                    if($data['parent_id']==0){//顶级
                        $data['channeltpl'] = 'channel';
                    }else{
                        $parentChannel = $this->get($data['parent_id']);
                        $data['channeltpl'] = $parentChannel['channeltpl'];
                    }
                break;
                default:
                break;
            }

            $this->isUpdate(true)->allowField(true)->save($data, ['id' => $id]);

            $children = $this->field('id,path')->where('path', 'like', $oldCategory['path'] . "-%")->select();
            if (!$children->isEmpty()) {
                foreach ($children as $child) {
                    $childPath = str_replace($oldCategory['path'] . '-', $newPath . '-', $child['path']);
                    $this->where('id', $child['id'])->update([
                        'path' => $childPath, 
                        'channeltpl' => $data['channeltpl'], 
                        'id' => $child['id']
                    ]);
                    
                }
            }

            $routeModel = new RouteModel();
            if (!empty($data['alias'])) {
                $routeModel->setRoute($data['alias'], 'cms/List/index', ['id' => $data['id']], 2, 5000);
                $routeModel->setRoute($data['alias'] . '/:id', 'cms/Archives/index', ['cid' => $data['id']], 2, 4999);
            } else {
                $routeModel->deleteRoute('cms/List/index', ['id' => $data['id']]);
                $routeModel->deleteRoute('cms/Archives/index', ['cid' => $data['id']]);
            }

            $routeModel->getRoutes(true);
        }


        return $result;
    }

    /**
     * 模型表
     */
    public function model()
    {
        return $this->belongsTo('CmsModelxModel', 'model_id');//->setEagerlyType(0);
    }
}