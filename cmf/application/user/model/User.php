<?php 
namespace app\user\model;

use think\Model;

class User extends Model
{
	protected function getUserStatusAttr($value)
	{
		$status = [0=>'已拉黑',1=>'正常',2=>'未验证'];
		return $status[$value];
	}

	// 合约开始时间读取器
	protected function getLastLoginTimeAttr($value)
	{
		return date('Y-m-d H:i:s',$value);
	}
}


 ?>