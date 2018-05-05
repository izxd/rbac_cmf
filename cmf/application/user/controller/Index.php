<?php
namespace app\user\controller;

use app\user\model\User as UserModel;
use think\Controller;
use think\Db;

class Index extends Controller
{
	/**
	* 管理员列表
	*/
    public function index()
    {
        $userModel = new UserModel();
        $where = ["user_type" => 1];
        /** 搜索条件 **/
        $user_login = $this->request->param("user_login");
        $user_email = trim($this->request->param('user_email'));

        if($user_login){
        	$where['user_login'] = ['like', "%$user_login%"];
        }

        if($user_email){
        	$where['user_email'] = ['like', "%$user_email%"];
        }

        $users = $userModel
        		->where($where)
        		->order("id DESC")
        		->paginate(10);

        // 获取分页显示
        $page = $users->render();

        $this->assign("page", $page);
        $this->assign("users", $users);

        return $this->fetch();
    }

    /**
	* 管理员添加
    */
    public function add()
    {
    	$roles = Db::name("role")->where(["status" => 1])->order("id DESC")->select();
    	$this->assign("roles", $roles);
    	return $this->fetch();
    }

    /**
    * 管理员添加提交
    */
    public function addPost()
    {
    	if($this->request->isPost()){
    		if(!empty($_POST['role_id']) && is_array($_POST['role_id'])){
    			$role_ids = $_POST['role_id'];
    			unset($_POST['role_id']);
    			$result = $this->validate($this->request->param(),'UserValidate');
    			if($result !== true){
    				$this->error($result);
    			}else{
    				// $_POST["user_pass"] = cmf_password($_POST["user_pass"]);.
    				$result = Db::name("user")->insertGetId($_POST);
    				if($result !== false){
    					foreach ($role_ids as $role_id) {
    						# code...
    						if($role_id == 1){
								$this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
    						}
    						Db::name('RoleUser')->insert(["role_id" => $role_id, "user_id" => $result]);
    					}
    					$this->success("添加成功！", url("user/index/index"));
    				}else{
						$this->error("添加失败！");
    				}
    			}
    		}else{
    			$this->error("请为此用户指定角色！");
    		}
    	}
    }
}
