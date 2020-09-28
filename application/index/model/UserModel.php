<?php


namespace app\index\model;

use think\Db;

class UserModel extends BaseModel
{
    //获取密码
    function getPassword($user_id)
    {
        $password = Db::table("hit_user")->where('user_id', $user_id)->value("user_password");
        return $password == null ? false : $password;
    }

    //用户登录
    function userLogin($user_id, $password)
    {
        $user_password = $this->getPassword($user_id);
        $password = $this->encryptPassword($password);
        return $password == $user_password ? true : false;
    }

    //用户注册
    function userRegister($user_data)
    {
        return $this->userAdd($user_data);
    }

    //用户所在部门查询
    function userDepQuery($user_id)
    {
        return Db::table('user_department')->where('user_id', $user_id)->select();
    }

    //所有部门查询
    function allDepQuery()
    {
        return Db::table("hit_department")->select();
    }

    //用户角色查询
    function userRoleQuery($user_id)
    {
        return Db::table("hit_role")->where("role_user_id", $user_id)->find();
    }

    //用户上传的文件查询
    function userWordQuery($user_id)
    {
        return Db::table("user_word")
            ->join("hit_word_process",'user_word.word_id = hit_word_process.word_id')
            ->where('user_id', $user_id)->select();
    }

    //用户修改密码
    function editPassword($user_id, $old_password, $new_password)
    {
        $existed_password = Db::table("hit_user")->where("user_id", $user_id)->value("password");
        if ($existed_password != null && $existed_password == $old_password) {
            $data_change = array('password' => $new_password);
            return Db::table('hit_user')->where('user_id', $user_id)->update($data_change);
        } else {
            $this->error['state'] = -1;
            $this->error['msg'] = '原密码错误或用户不存在';
            return $this->error;
        }
    }
}