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

class CmsModelxModel extends Model
{
    // 表名
    protected $name = 'cms_model';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
    ];

    public static function init()
    {
        /**
         * 插入后操作
         */
        self::afterInsert(function ($row) {
            $prefix = config('database.prefix');
            $sql = "CREATE TABLE `{$prefix}{$row['table']}` (`id` int(10) NOT NULL,`content` longtext NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='{$row['name']}'";
            Db::query($sql);
        });

        /**
         * 删除后的操作
         */
        self::afterDelete(function ($row) {
            if ($row['table']) {
                $sql = Alter::instance()
                    ->setTable($row['table'])
                    ->setName($row['name'])
                    ->getDropTableSql();
                try {
                    db()->query($sql);
                } catch (PDOException $e) {
                }
            }
        });
    }


    public function getFieldsAttr($value, $data)
    {
        return is_array($value) ? $value : ($value ? explode(',', $value) : []);
    }

}
