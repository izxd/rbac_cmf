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

    /**
    * 管理员编辑
    */
    public function edit()
    {
        $id = $this->request->param("id",0,"intval");
        $roles = DB::name("role")->where(["status" => 1])->order("id DESC")->select();
        $this->assign("roles",$roles);
        $role_ids = DB::name("RoleUser")->where(["user_id" => $id])->column("role_id");
        $this->assign("role_ids",$role_ids);

        $user = DB::name("user")->where(["id" => $id])->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
    * 管理员编辑提交
    */
    public function editPost()
    {
        if($this->request->isPost()){
            if(!empty($_POST["role_id"]) && is_array($_POST["role_id"])){
                if(empty($_POST["user_pass"])){
                    unset($_POST["user_pass"]);
                }else{
                    $_POST["user_pass"] = $_POST["user_pass"];
                }

                $role_ids = $this->request->param("role_id/a");
                unset($_POST["role_id"]);

                $result = $this->validate($this->request->param(), "UserValidate.edit");
            
                if($result !== true){
                    // 验证失败 输出错误信息
                    $this->error($result);
                }else{
                    $result = DB::name("user")->update($_POST);
                    if($result !== false){
                        $uid = $this->request->param("id",0,"intval");
                        DB::name("RoleUser")->where(["user_id" => $uid])->delete();
                        foreach ($role_ids as $role_id) {
                            if ( $role_id == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            DB::name("RoleUser")->insert(["role_id" => $role_id, "user_id" => $uid]);
                        }
                        $this->success("保存成功！");
                    }else{
                        $this->error("保存失败！");
                    }                 
                }
            }else{
                $this->error("请为此用户指定角色！");
            }
        }
    }

    /**
    * 管理员删除
    */
    public function delete()
    {
        $id = $this->request->param("id",0,"intval");

        if($id == 1){
            $this->error("最高管理员不能删除！");
        }

        if(Db::name("user")->delete($id) !== false){
            Db::name("RoleUser")->where(["user_id" => $id])->delete();
            $this->success("删除成功！");
        } else {
            $this->error("删除失败！");
        }
    }
}
