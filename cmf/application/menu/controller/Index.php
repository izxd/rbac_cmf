<?php
namespace app\menu\controller;

use think\Controller;
use think\Db;
use org\util\Tree;

class Index extends Controller
{
    public function index()
    {
    	session("admin_menu_index","Menu/index");
    	$result = Db::name('adminMenu')->select();

    	$tree = new Tree();
    	$tree->icon = ['&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $newMenus = [];
        foreach ($result as $m) {
        	$newMenus[$m["id"]] = $m;
        }

        foreach ($result as $key => $value) {
        	$result[$key]["parent_id_node"] = ($value['parent_id']) ? ' class="child-of-node-' . $value['parent_id'] . '"' : '';
        	$result[$key]["style"] = empty($value["parent_id"]) ? "" : "display:none;";
        	$result[$key]["str_manager"] = '<a href="' . url("Menu/add", ["parent_id" => $value['id'], "menu_id" => $this->request->param("menu_id")])
                . '">添加子菜单</a>  <a href="' . url("Menu/edit", ["id" => $value['id'], "menu_id" => $this->request->param("menu_id")])
                . '">编辑</a>  <a class="js-ajax-delete" href="' . url("Menu/delete", ["id" => $value['id'], "menu_id" => $this->request->param("menu_id")]) . '">删除</a> ';
            $result[$key]['status']         = $value['status'] ? '显示' : '隐藏';
            // if (APP_DEBUG) {
                $result[$key]['app'] = $value['app'] . "/" . $value['controller'] . "/" . $value['action'];
            // }
        }

        $tree->init($result);

        $str      = "<tr id='node-\$id' \$parent_id_node style='\$style'>
                        <td style='padding-left:20px;'><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input input-order'></td>
                        <td>\$id</td>
                        <td>\$spacer\$name</td>
                        <td>\$app</td>
                        <td>\$status</td>
                        <td>\$str_manage</td>
                    </tr>";
        $category = $tree->get_tree(0, $str);
        $this->assign("category", $category);

        return $this->fetch();
    }
}
