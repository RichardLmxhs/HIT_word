<?php


namespace app\index\model;

use think\Db;
use think\Model;
class AdminModel extends BaseModel
{
    public function userList(){
        return Db::table('hit_user')->select();
    }

    //管理员查询
    public function adminQuery($admin_id){
        return Db::table("hit_admin")->where("admin_id",$admin_id)->find();
    }
    public function adminList(){
        return Db::table("hit_admin")->select();
    }

    //管理员登录
    public function adminLogin($admin_id, $admin_password){
        $admin_password = $this->encryptPassword($admin_password);
        $is_password = Db::table("hit_admin")->where("admin_id",$admin_id)->value("password");
        return $admin_password == $is_password ? true : false;
    }

    //管理员修改密码
    public function adminPasswordEdit($admin_id, $old_password, $new_password){
        $old_password = $this->encryptPassword($old_password);
        $new_password = $this->encryptPassword($new_password);
        $is_password = Db::table("hit_admin")->where("admin_id",$admin_id)->value("password");
        $data_edit = array(
            'admin_password' => $new_password
        );
        if($old_password == $is_password){
            return Db::table('hit_admin')->where("admin_id",$admin_id)->update($data_edit);
        }else{
            $this->error['state'] = -1;
            $this->error['msg'] = '旧密码输入错误';
            return $this->error;
        }
    }

    //管理员添加用户
    public function adminAdd($data_add){
        $is_exists = Db::table("hit_admin")->where('admin_id',$data_add['admin_id'])->find();
        if($is_exists != null){
            $this->error['state'] = -1;
            $this->error['msg'] = '添加的用户已存在';
            return $this->error;
        }
        $add = array(
            'admin_id' => $data_add['admin_id'],
            'admin_password' => $this->encryptPassword($data_add['password']),
        );
        return Db::table('hit_amdin')->insert($add);
    }

    //管理员删除用户
    public function adminDelete($admin_id){
        $is_exists = Db::table("hit_admin")->where('admin_id',$admin_id)->find();
        $is_null = Db::table('hit_admin')->select();
        if(count($is_null) == 1){
            $this->error['state'] = -1;
            $this->error['msg'] = "管理员用户只剩一个，无法删除";
            return $this->error;
        }
        if($is_exists == null){
            $this->error['state'] = -1;
            $this->error['msg'] = "要删除的用户不存在";
            return $this->error;
        }
        return Db::table('hit_admin')->where('admin_id',$admin_id)->delete();
    }

    //部门查询
    public function depQueryById($dep_id){
        return Db::table("hit_department")->where("dep_id",$dep_id)->find();
    }
    public function depQueryByName($dep_name){
        $res = Db::table("hit_department")->where("dep_name",$dep_name)->find();
        $ans = array();
        array_push($ans,$res);
        while($res['dep_pre'] != null){
            $res = Db::table("hit_department")->where("dep_id",$res['dep_pre'])->find();
            if($res != null) array_push($ans,$res);
        }
        return $ans;
    }
    //添加部门
    public function depAdd($data_add){
        $data = array(
            'dep_name' => $data_add['dep_name'],
            'dep_place' => $data_add['dep_place'],
            'dep_leader' => $data_add['dep_place'],
            'dep_level' => $data_add['dep_level'],
            'dep_pre' => $data_add['dep_pre']
        );
        return Db::table('hit_department')->insert($data);
    }

    //修改部门信息
    public function depEdit($data_edit){
        $data = array(
            'dep_name' => $data_edit['dep_name'],
            'dep_place' => $data_edit['dep_place'],
            'dep_leader' => $data_edit['dep_place'],
            'dep_level' => $data_edit['dep_level'],
            'dep_pre' => $data_edit['dep_pre']
        );
        $update = array(
            'dep_name' => $data_edit['dep_name']
        );
        $res1 = Db::table('hit_department')->update($data);
        $res2 = Db::table('user_department')->update($update);
        return $res1 & $res2;
    }

    //删除部门
    public function depDelete($dep_name){
        $is_null = Db::table('user_department')->where('dep_name',$dep_name)->find();
        if($is_null == null){
            return Db::table('hit_department')->where('dep_name',$dep_name)->delete();
        }else{
            $this->error['state'] = -1;
            $this->error['msg'] = '要删除的部门存在哟用户，无法删除';
            return $this->error;
        }
    }

    //角色查询
    public function roleQuery(){
        return Db::table('hit_role')->group('role_name')->select();
    }

    //给用户分配角色
    public function roleAdd($data_add){
        $is_exists = Db::table('hit_role')->where("role_user_id",$data_add['role_user_id'])->find();
        if($is_exists != null){
            $this->error['state'] = -1;
            $this->error['msg'] = '角色已存在';
            return $this->error;
        }
        $data = array(
            'role_name' => $data_add['role_name'],
            'role_user_id' => $data_add['role_user_id'],
            'role_state' => $data_add['role_state']
        );
        return Db::table('hit_role')->insert($data);
    }

    //剥夺某用户的角色
    public function roleDelete($user_id){
        $is_exists = Db::table('hit_role')->where("role_user_id",$user_id)->find();
        if($is_exists == null){
            $this->error['state'] = -1;
            $this->error['msg'] = '用户没有分配的角色';
            return $this->error;
        }
        return Db::table('hit_role')->where('role_user_id',$user_id)->delete();
    }

    //sql操作
    private function SQLOperation($code){
        //code == 0数据库备份
    }

    public function wordList(){
        return Db::table('hit_word')
            ->join('user_word','user_word.word_id = hit_word.word_id')
            ->select();
    }
}