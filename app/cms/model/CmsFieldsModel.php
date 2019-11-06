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

use think\Config;
use think\Model;
use think\Db;
use app\cms\library\Alter;

class CmsFieldsModel extends Model
{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
        //'status_text',
        //'content_list',
    ];
    protected static $listFields = ['select', 'selects', 'checkbox', 'radio', 'array'];
    //protected static $listField = ['select', 'selects', 'checkbox', 'radio', 'array'];

    public function setError($error)
    {
        $this->error = $error;
    }

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

            // if ($row->model_id) {
            //     $tableFields = \think\Db::name('cms_archives')->getTableFields();
            //     if (in_array(strtolower($row['name']), $tableFields)) {
            //         return "字段已经在主表存在了";
            //     }
            //     //判断字段是否已存在
            //     $tableFields = $this->getTableFields();
            //     if (in_array(strtolower($row['name']), $tableFields)) {
            //         $this->error("字段已经在副表存在了");
            //     }
            //     $tableFields = ['content'];
            //     if (in_array(strtolower($row['name']), $tableFields)) {
            //         return "字段为保留字段，请使用其它字段";
            //     }
            // } else {
            //     $tableFields = ['id', 'user_id', 'create_time', 'update_time'];
            //     if (in_array(strtolower($row['name']), $tableFields)) {
            //         return "字段为保留字段，请使用其它字段";
            //     }
            // }
        };
        
        //添加后的操作
        $afterInsertCallback = function ($row) {
            //为了避免引起更新的事件回调，这里采用直接执行SQL的写法
            $row->query($row->fetchSql(true)->update(['id' => $row['id'], 'list_order' => $row['id']]));
            $field = $row['model_id'] ? 'model_id' : 'diyform_id';
            $model = $row['model_id'] ? CmsModelxModel::get($row[$field]) : CmsDiyformModel::get($row[$field]);
            if ($model) {
                $sql = Alter::instance()
                    ->setTable($model['table'])
                    ->setName($row['name'])
                    ->setLength($row['length'])
                    ->setContent($row['content'])
                    ->setDecimals($row['decimals'])
                    ->setDefaultvalue($row['defaultvalue'])
                    ->setComment($row['title'])
                    ->setType($row['type'])
                    ->getAddSql();
                try {
                    Db::query($sql);
                    $fields = CmsFieldsModel::where($field, $model['id'])->field('name')->column('name');
                    $model->fields = implode(',', $fields);
                    $model->save();
                } catch (PDOException $e) {
                    $row->getQuery()->where('id', $row->id)->delete();
                    throw new Exception($e->getMessage());
                }
            }
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
        };
        
        //更新后的操作
        $afterUpdateCallback = function ($row) {
            $field = $row['model_id'] ? 'model_id' : 'diyform_id';
            $model = $row['model_id'] ? CmsModelxModel::get($row[$field]) : CmsDiyformModel::get($row[$field]);
            if ($model) {
                $alter = Alter::instance();
                if (isset($row['oldname']) && $row['oldname'] != $row['name']) {
                    $alter->setOldname($row['oldname']);
                }
                $sql = $alter
                    ->setTable($model['table'])
                    ->setName($row['name'])
                    ->setLength($row['length'])
                    ->setContent($row['content'])
                    ->setDecimals($row['decimals'])
                    ->setDefaultvalue($row['defaultvalue'])
                    ->setComment($row['title'])
                    ->setType($row['type'])
                    ->getModifySql();
                Db::query($sql);
                $fields = CmsFieldsModel::where($field, $model['id'])->field('name')->column('name');
                $model->fields = implode(',', $fields);
                $model->save();
            }
        };

        self::beforeInsert($beforeInsertCallback);
        self::beforeUpdate($beforeUpdateCallback);

        self::afterInsert($afterInsertCallback);
        self::afterUpdate($afterUpdateCallback);

        //删除后的操作
        self::afterDelete(function ($row) {
            $field = $row['model_id'] ? 'model_id' : 'diyform_id';
            $model = $row['model_id'] ? CmsModelxModel::get($row[$field]) : CmsDiyformModel::get($row[$field]);
            if ($model) {
                $sql = Alter::instance()
                    ->setTable($model['table'])
                    ->setName($row['name'])
                    ->getDropSql();
                try {
                    db()->query($sql);
                    $fields = CmsFieldsModel::where($field, $model['id'])->field('name')->column('name');
                    $model->fields = implode(',', $fields);
                    $model->save();
                } catch (PDOException $e) {
                }
            }
        });
    }

    public function getContentListAttr($value, $data)
    {
        return in_array($data['type'], self::$listField) ? ConfigModel::decode($data['content']) : $data['content'];
    }

    public function getStatusList()
    {
        return [1 => '显示', 0 => '隐藏'];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function model()
    {
        return $this->belongsTo('CmsModelxModel', 'model_id');//->setEagerlyType(0);
    }

    // public function diyform()
    // {
    //     return $this->belongsTo('AdminDiyform', 'diyform_id')->setEagerlyType(0);
    // }


    /**
     * 获取字典列表字段
     * @return array
     */
    public static function getListFields()
    {
        return self::$listFields;
    }
}
