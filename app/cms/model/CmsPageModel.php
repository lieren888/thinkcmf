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
use think\db\Query;

/**
 * @property mixed id
 */
class CmsPageModel extends Model
{
    // 表名
    protected $name = 'cms_page';

    protected $type = [
        'more' => 'array',
    ];

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    // 追加属性
    protected $append = [
        //'status_text',
        //'url'
    ];

    protected static function init()
    {
        /**
         * 新增后操作
         */
        self::afterInsert(function ($row) {
            $row->save(['list_order' => $row['id']]);
            //注册路由
            $routeModel = new RouteModel();
            $routeModel->setRoute($row['alias'], 'cms/Page/index', ['id' => $row['id']], 2, 5000);
            $routeModel->getRoutes(true);//强制刷新
        });

        /**
         * 修改后操作
         */
        self::afterUpdate(function ($row) {
            //注册路由
            $routeModel = new RouteModel();
            $routeModel->setRoute($row['alias'], 'cms/Page/index', ['id' => $row['id']], 2, 5000);
            $routeModel->getRoutes(true);//强制刷新
        });

        /**
         * 删除后操作
         */
        self::afterDelete(function ($row) {
            //注册路由
            $routeModel = new RouteModel();
            $routeModel->deleteRoute('cms/Page/index', ['id' => $row['id']]);
            $routeModel->getRoutes(true);//强制刷新
        });
    }

    // public function getUrlAttr($value, $data)
    // {
    //     return addon_url('cms/page/index', [':alias' => $data['alias']]);
    // }

    // public function getStatusList()
    // {
    //     return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    // }

    // public function getStatusTextAttr($value, $data)
    // {
    //     $value = $value ? $value : $data['status'];
    //     $list = $this->getStatusList();
    //     return isset($list[$value]) ? $list[$value] : '';
    // }


    /**
     * content 自动转化
     * @param $value
     * @return string
     */
    public function getContentAttr($value)
    {
        return cmf_replace_content_file_url(htmlspecialchars_decode($value));
    }

    /**
     * content 自动转化
     * @param $value
     * @return string
     */
    public function setContentAttr($value)
    {
        return htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($value), true));
    }

    /**
     * publishedtime 自动完成
     * @param $value
     * @return false|int
     */
    public function setPublishedTimeAttr($value)
    {
        return strtotime($value);
    }


    /**
     * 文章查询
     * @param      $filter
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function adminPageList($filter)
    {
        $join = [
            ['__USER__ u', 'a.user_id = u.id'],
        ];
        $field = 'a.*,u.user_login,u.user_nickname,u.user_email';

        $articles    = $this->alias('a')
            ->field($field)
            ->join($join)
            ->where('a.create_time', '>=', 0)
            ->where(function (Query $query) use ($filter) {
                $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
                if (!empty($keyword)) {
                    $query->where('a.title', 'like', "%$keyword%");
                }
            })
            ->order('update_time', 'DESC')
            ->paginate(10);
        return $articles;

    }

    /**
     * 后台管理添加页面
     * @param array $data 页面数据
     * @return $this
     */
    public function adminAddPage($data)
    {
        $data['user_id'] = cmf_get_current_admin_id();

        if (!empty($data['more']['thumbnail'])) {
            $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
        }
        $this->allowField(true)->isUpdate(false)->data($data, true)->save();

        return $this;

    }

    /**
     * 后台管理编辑页面
     * @param array $data 页面数据
     * @return $this
     */
    public function adminEditPage($data)
    {
        $data['user_id'] = cmf_get_current_admin_id();

        if (!empty($data['more']['thumbnail'])) {
            $data['more']['thumbnail'] = cmf_asset_relative_url($data['more']['thumbnail']);
        }

        $this->allowField(true)->isUpdate(true)->data($data, true)->save();
        return $this;
    }

    /**
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminDeletePage($data)
    {
        if (isset($data['id'])) {
            $this->destroy($data['id']);
            return true;
        } elseif (isset($data['ids'])) {
            $res = $this->destroy($data['ids']);
            return true;
        } else {
            return false;
        }
    }

}
