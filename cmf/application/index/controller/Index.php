<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Index extends Controller
{
	/**
	* 角色管理列表
	*/
    public function index()
    {
        $data = Db::query("select * from cmf_role;");
        $this->assign('roles',$data);
        return $this->fetch();
    }

    /**
    * 角色添加
    */
    public function roleAdd()
    {
    	return $this->fetch();
    }

    /**
    * 添加角色提交
    */
    public function roleAddPost()
    {
    	if($this->request->isPost()){
			$data = $this->request->param();
			$result = $this->validate($data, 'RoleValidate');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $result = Db::name('role')->insert($data);
                if ($result) {
                    $this->success("添加角色成功", url("index/index"));
                } else {
                    $this->error("添加角色失败");
                }
            }
    	}
    }

    /**
    * 编辑角色
    */
    public function roleEdit()
    {
    	$id = $this->request->param("id",0,"intval");
    	if($id == 1){
    		$this->error("超级管理员角色不能修改！");
    	}

    	$data = Db::name('role')->where(["id" => $id])->find();
    	if(!$data){
    		$this->error("该角色不存在！");
    	}

    	$this->assign("data",$data);
    	return $this->fetch();
    }

    /**
	* 编辑角色提交
    */
    public function roleEditPost()
    {
    	$id = $this->request->param("id",0,"intval");
    	if ($id == 1) {
            $this->error("超级管理员角色不能被修改！");
        }
        if ($this->request->isPost()) {
            $data   = $this->request->param();
            $result = $this->validate($data, 'RoleValidate');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);

            } else {
                if (Db::name('role')->update($data) !== false) {
                    $this->success("保存成功！", url('index/index'));
                } else {
                    $this->error("保存失败！");
                }
            }
        }
    }

    /**
    * 删除角色
    */
    public function roleDelete()
    {
    	$id = $this->request->param("id", 0, 'intval');
    	if($id == 1){
    		$this->error("超级管理员角色不能被删除！");
    	}
    	$count = Db::name('RoleUser')->where(['role_id' => $id])->count();
    	if($count){
    		$this->error("该角色已经有用户！");
    	}else{
    		$status = Db::name('role')->delete($id);
    		if (!empty($status)) {
                $this->success("删除成功！", url('rbac/index'));
            } else {
                $this->error("删除失败！");
            }
    	}
    }
}
