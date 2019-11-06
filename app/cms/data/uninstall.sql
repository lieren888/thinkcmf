
--
-- 后台菜单栏目 `cmf_admin_menu`
--
DELETE FROM `cmf_admin_menu` where app='cms';

--
-- 前台导航栏目 `cmf_nav`
--
DELETE FROM cmf_nav where id=3;

--
-- 前台导航栏目副表 `cmf_nav_menu`
--
DELETE FROM cmf_nav_menu WHERE nav_id=3;

--
-- 模板主表 `cmf_theme`
--
DELETE FROM cmf_theme where theme in('cruiseship','cruiseship_en');

--
-- 模板副表 `cmf_theme_file`
--
DELETE FROM cmf_theme_file where theme in('cruiseship','cruiseship_en');

--
-- 模型数据 `cmf_cms_model`
--
DROP TABLE `cmf_cms_model`;

--
-- 模型字段数据 `cmf_cms_fields`
--
DROP TABLE `cmf_cms_fields`;

--
-- 栏目数据 `cmf_cms_channel`
--
DROP TABLE `cmf_cms_channel`;

--
-- 文章基础表数据 `cmf_cms_archives`
--
DROP TABLE `cmf_cms_archives`;

--
--	单页表数据 `cmf_cms_page`
--
DROP TABLE `cmf_cms_page`;

--
--	标签数据 `cmf_cms_tags`
--
DROP TABLE `cmf_cms_tags`;

--
-- 文章外表1数据 `cmf_cms_appnews`
--
DROP TABLE `cmf_cms_appnews`;

--
-- 文章外表2数据 `cmf_cms_appproducts`
--
DROP TABLE `cmf_cms_appproducts`;