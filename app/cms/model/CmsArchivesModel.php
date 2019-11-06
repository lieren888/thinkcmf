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
use think\Model;
use think\Db;

/**
 * @property mixed id
 */
class CmsArchivesModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected static function init()
    {
        //添加前的操作
        $beforeInsertCallback = function ($row) {

            // if (!preg_match("/^([a-zA-Z0-9_]+)$/i", $row['name'])) {
            //     return "字段只支持字母数字下划线";
            // }
            // if (is_numeric(substr($row['name'], 0, 1))) {
            //     return "字段不能以数字开始";
            // }

            //为了避免引起更新的事件回调，这里采用直接执行SQL的写法
            // $model_id = CmsChannelModel::where('id', $row['channel_id'])->field('model_id')->column('model_id');
            // if(!empty($model_id)){
            //     $model = CmsModelxModel::get($model_id);
            //     if (!$model) {
            //         return "未找到对应模型";
            //     }
            //     //$fieldsList = CmsFieldsModel::where('model_id', $model['id'])->where('type', '<>', 'text')->select();
            // }
        };

        //更新前的操作
        $beforeUpdateCallback = function ($row) {

            // if (!preg_match("/^([a-zA-Z0-9_]+)$/i", $row['name'])) {
            //     return "字段只支持字母数字下划线";
            // }
            // if (is_numeric(substr($row['name'], 0, 1))) {
            //     return "字段不能以数字开始";
            // }

            // if ($row->model_id) {
            //     $tableFields = \think\Db::name('cms_archives')->getTableFields();
            //     if (in_array(strtolower($row['name']), $tableFields)) {
            //         return "字段已经在主表存在了";
            //     }
            // } else {
            //     $tableFields = ['id', 'user_id', 'create_time', 'update_time'];
            //     if (in_array(strtolower($row['name']), $tableFields)) {
            //         return "字段为保留字段，请使用其它字段";
            //     }
            // }

            //为了避免引起更新的事件回调，这里采用直接执行SQL的写法
            $model_ids = CmsArchivesModel::where('id', $row['id'])->field('model_id')->column('model_id');
            $model_id = $model_ids[0];
            if(!empty($model_id)){
                $model = CmsModelxModel::get($model_id);
                if (!$model) {
                    return "未找到对应模型";
                }
                $fieldsList = CmsFieldsModel::where('model_id', $model_id)->where('type', '<>', 'text')->select();
            }
        };

        //添加后的操作
        $afterInsertCallback = function ($row) {
            //为了避免引起更新的事件回调，这里采用直接执行SQL的写法
            $model_ids = CmsChannelModel::where('id', $row['channel_id'])->field('model_id')->column('model_id');
            $model_id = $model_ids[0];
            //记录栏目id
            CmsArchivesModel::where('id', $row['id'])->update(['model_id' => $model_id]);
            //获取对应的模型
            $model = CmsModelxModel::get($model_id);
            if (!$model) {
                return "未找到对应模型";
            }
            $table = $model['table'];//外表名称
            $content = '';
            if(isset($row['content'])){
                $content = $row['content'] = htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($row['content']), true));//转码
            }
            //数组
            $data = [
                'id' =>  $row['id'],
                'content'=> $content
            ];
            //动态字段
            $dynamicFields = CmsFieldsModel::where('model_id', $model_id)->field('name,type')->select();
            foreach($dynamicFields as $k => $v){
                $fieldname = $v['name'];//字段名称
                $fieldtype = strtolower($v['type']);//字段类型
                if(isset($row[$fieldname])){//存在属性
                    switch($fieldtype){
                        case "checkbox"://复选框
                            $row[$fieldname] = implode(",", $row[$fieldname]); //json_encode($row[$fieldname]);
                        break;
                        case "editor"://编辑器
                            $row[$fieldname] = htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($row[$fieldname]), true));//转码
                        break;
                        case "date"://日期
                            $row[$fieldname] = date('Y-m-d', strtotime($row[$fieldname])); 
                        break;
                        case "time"://时间
                            $row[$fieldname] = date('H:i:s', strtotime($row[$fieldname])); 
                        break;
                        case "datetime"://日期+时间
                            $row[$fieldname] = date('Y-m-d H:i:s', strtotime($row[$fieldname])); 
                        break;
                        case "images"://多图片
                        case "files"://多文件
                            // if (!empty($row[$fieldname.'_names']) && !empty($row[$fieldname.'_urls'])) {
                            //     $row[$fieldname] = [];
                            //     foreach ($row[$fieldname.'_urls'] as $key => $url) {
                            //         $urls = cmf_asset_relative_url($url);
                            //         array_push($row[$fieldname], ["url" => $urls, "name" => $row[$fieldname.'_names'][$key]]);
                            //     }
                            //     //消灭
                            //     unset($row[$fieldname.'_names']);
                            //     unset($row[$fieldname.'_urls']);
                            // }
                            // 把PHP数组转成JSON字符串（https://zhidao.baidu.com/question/235095737.html）
                            $row[$fieldname] = json_encode($row[$fieldname]); 
                        break;
                        default:
                        break;
                    }
                }
            }
            
            //外表其他字段
            $fields = [];
            $fieldsArray = $model['fields'];
            foreach($fieldsArray as $v){
                if(isset($row[$v])){
                    $fields[$v] = $row[$v];
                }
            }
            if(count($fields)>0){
                $data = array_merge($data, $fields);
            }
            Db::name($table)->insert($data, true);
        };

        //更新后的操作
        $afterUpdateCallback = function ($row) {
            //为了避免引起更新的事件回调，这里采用直接执行SQL的写法
            $model_ids = CmsArchivesModel::where('id', $row['id'])->field('model_id')->column('model_id');
            $model_id = $model_ids[0];
            //获取对应的模型
            $model = CmsModelxModel::get($model_id);
            if (!$model) {
                return "未找到对应模型";
            }
            $table = $model['table'];//外表名称
            $content = '';
            if(isset($row['content'])){
                $content = $row['content'] = htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($row['content']), true));//转码
            }
            //数组
            $data = [
                'id' =>  $row['id'],
                'content'=> $content
            ];
            //动态字段
            $dynamicFields = CmsFieldsModel::where('model_id', $model_id)->field('name,type')->select();
            foreach($dynamicFields as $k => $v){
                $fieldname = $v['name'];//字段名称
                $fieldtype = strtolower($v['type']);//字段类型
                if(isset($row[$fieldname])){//存在属性
                    switch($fieldtype){
                        case "checkbox"://复选框
                            $row[$fieldname] = implode(",", $row[$fieldname]); //json_encode($row[$fieldname]);
                        break;
                        case "editor"://编辑器
                            $row[$fieldname] = htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($row[$fieldname]), true));//转码
                        break;
                        case "date"://日期
                            $row[$fieldname] = date('Y-m-d', strtotime($row[$fieldname])); 
                        break;
                        case "time"://时间
                            $row[$fieldname] = date('H:i:s', strtotime($row[$fieldname])); 
                        break;
                        case "datetime"://日期+时间
                            $row[$fieldname] = date('Y-m-d H:i:s', strtotime($row[$fieldname])); 
                        break;
                        case "images"://多图片
                        case "files"://多文件
                            // if (!empty($row[$fieldname.'_names']) && !empty($row[$fieldname.'_urls'])) {
                            //     $row[$fieldname] = [];
                            //     foreach ($row[$fieldname.'_urls'] as $key => $url) {
                            //         $urls = cmf_asset_relative_url($url);
                            //         array_push($row[$fieldname], ["url" => $urls, "name" => $row[$fieldname.'_names'][$key]]);
                            //     }
                            //     //消灭
                            //     unset($row[$fieldname.'_names']);
                            //     unset($row[$fieldname.'_urls']);
                            // }
                            // 把PHP数组转成JSON字符串（https://zhidao.baidu.com/question/235095737.html）
                            $row[$fieldname] = json_encode($row[$fieldname]); 
                        break;
                        default:
                        break;
                    }
                }
            }
            
            //外表其他字段
            $fields = [];
            $fieldsArray = $model['fields'];
            foreach($fieldsArray as $v){
                if(isset($row[$v])){
                    $fields[$v] = $row[$v];
                }
            }
            if(count($fields)>0){
                $data = array_merge($data, $fields);
            }
            Db::name($table)->update($data);
        };

        self::beforeInsert($beforeInsertCallback);
        self::beforeUpdate($beforeUpdateCallback);

        self::afterInsert($afterInsertCallback);
        self::afterUpdate($afterUpdateCallback);

        //删除后的操作
        self::afterDelete(function ($row) {
            //删除副表
            $channel = CmsChannelModel::get($row['channel_id']);
            if ($channel) {
                $model = CmsModelxModel::get($channel['model_id']);
                if ($model) {
                    db($model['table'])->where("id", $row['id'])->delete();
                }
            }
        });
    }

    /**
     * 关联 user表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('UserModel', 'user_id');//->setEagerlyType(1);
    }

    /**
     * 关联分类表
     * @return \think\model\relation\BelongsToMany
     */
    // public function categories()
    // {
    //     return $this->belongsToMany('CmsChannelModel', 'portal_category_post', 'category_id', 'post_id');
    // }
    /**
     * 关联分类表
     * @return \think\model\relation\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo('CmsChannelModel', 'channel_id');//->setEagerlyType(1);
    }

    // /**
    //  * 关联标签表
    //  * @return \think\model\relation\BelongsToMany
    //  */
    // public function tags()
    // {
    //     return $this->belongsToMany('CmsTags', 'portal_tag_post', 'tag_id', 'post_id');
    // }

    /**
     * 模型表 content 自动转化
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * 模型表 content 自动转化
     * @param $value
     * @return string
     */
    public function setContentAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }

    /**
     * published_time 自动完成
     * @param $value
     * @return false|int
     */
    public function setPublishedTimeAttr($value)
    {
        return strtotime($value);
    }

    /**
     * 后台管理添加文章
     * @param array        $data       文章数据
     * @param array|string $categories 文章分类 id
     * @return $this
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function adminAddArticle($data, $categories)
    {
        $data['user_id'] = cmf_get_current_admin_id();

        if (!empty($data['more']['thumbnail'])) {
            $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
            $data['thumbnail']         = $data['more']['thumbnail'];
        }

        // allowField(true)：过滤post数组中的非数据表字段数据
        // isUpdate(false)：显式指定当前操作为新增操作
        $this->allowField(true)->isUpdate(false)->data($data, true)->save();

        //添加标签
        $data['keywords'] = str_replace('，', ',', $data['keywords']);
        $keywords = explode(',', $data['keywords']);
        $this->addTags($keywords, $this->id);

        return $this;

    }

    /**
     * 后台管理编辑文章
     * @param array        $data       文章数据
     * @param array|string $categories 文章分类 id
     * @return $this
     * @throws \think\Exception
     */
    public function adminEditArchives($data, $categories)
    {

        unset($data['user_id']);//用户ID不能改动
        unset($data['model_id']);//模型ID不能改动

        if (!empty($data['more']['thumbnail'])) {
            $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
            $data['thumbnail']         = $data['more']['thumbnail'];
        }

        // allowField：过滤post数组中的非数据表字段数据
        // isUpdate(true)：显式指定更新数据操作
        $this->allowField(true)->isUpdate(true)->data($data, true)->save();

        $data['keywords'] = str_replace('，', ',', $data['keywords']);
        $keywords = explode(',', $data['keywords']);
        $this->addTags($keywords, $data['id']);

        return $this;

    }

    /**
     * 增加标签
     * @param $keywords
     * @param $articleId
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function addTags($keywords, $articleId)
    {
        $cmsTagsModel = new CmsTagsModel();

        $tagIds = [];

        $data = [];

        if (!empty($keywords)) {

            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword)) {
                    //判断是已存在
                    $findTag = $cmsTagsModel->where('name', $keyword)->find();
                    if (empty($findTag)) {
                        $tagId = $cmsTagsModel->insertGetId([
                            'name'     => $keyword,
                            'archives' => $articleId,
                            'nums'     => 1
                        ]);
                    } else {
                        //查找历史数据
                        $oldTagIds = $cmsTagsModel->where('name', $keyword)->column('archives');

                        if (!in_array($articleId, $oldTagIds)) {
                            array_push($oldTagIds, $articleId);
                            //数组转换为字符串
                            $result = implode(',', $oldTagIds);
                            $cmsTagsModel->where('name', $keyword)->update(['archives' => $result]);
                        }
                    }
                }
            }
        } else {
            $cmsTagsModel->where('archives', $articleId)->delete();
        }
    }
}
