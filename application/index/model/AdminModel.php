<?php


namespace app\index\model;

use think\Db;
use think\Model;
class AdminModel extends BaseModel
{
    public function userList(){
        return Db::table('hit_user')
//            ->join('user_department','hit_user.user_id = user_department.user_id',"Left")
            ->select();
    }

    //用户+部门+角色查询
    public function userDepRoleList(){
        $res =  Db::table('hit_user')->select();
        for($i = 0;$i < count($res);$i++){
            $user_id = $res[$i]['user_id'];
            $dep_name = Db::table('user_department')->where('user_id',$user_id)->value('dep_name');
            $res[$i]['dep_name'] = $dep_name;

            $role_name = Db::table('user_role')->where('user_id',$user_id)->value('role_name');
            $res[$i]['role_name'] = $role_name;
        }
        return $res;
    }

    //角色查询
    public function roleList(){
        return Db::table('hit_role')->select();
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
        $is_password = Db::table("hit_admin")->where("admin_id",$admin_id)->value("admin_password");
        return $admin_password == $is_password ? true : false;
    }

    //管理员修改密码
    public function adminPasswordEdit($admin_id, $old_password, $new_password){
        $old_password = $this->encryptPassword($old_password);
        $new_password = $this->encryptPassword($new_password);
        $is_password = Db::table("hit_admin")->where("admin_id",$admin_id)->value("admin_password");
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
            'admin_password' => $this->encryptPassword($data_add['admin_password']),
        );
        return Db::table('hit_admin')->insert($add);
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
        return Db::table('hit_department')->insert($data_add);
    }

    //修改部门信息
    public function depEdit($data_edit){
        $update = array(
            'dep_name' => $data_edit['dep_name']
        );
        $is_exists = Db::table('user_department')->where('dep_id',$data_edit['dep_id'])->find();
        if($is_exists == null){
            return Db::table('hit_department')->where('dep_id',$data_edit['dep_id'])->update($data_edit);
        }
        else{
            $res1 = Db::table('hit_department')->where('dep_id',$data_edit['dep_id'])->update($data_edit);
            $res2 = Db::table('user_department')->where('dep_id',$data_edit['dep_id'])->update($update);
            return $res1 & $res2;
        }
    }

    //删除部门
    public function depDelete($dep_id){
        $is_null = Db::table('user_department')->where('dep_id',$dep_id)->find();
        if($is_null == null){
            $is_null1 = Db::table('hit_department')->where('dep_id',$dep_id)->find();
            if($is_null1 == null){
                $this->error['state'] = -1;
                $this->error['msg'] = '要删除的部门不存在';
                return $this->error;
            }
            return Db::table('hit_department')->where('dep_id',$dep_id)->delete();
        }else{
            $this->error['state'] = -1;
            $this->error['msg'] = '要删除的部门存在用户，无法删除';
            return $this->error;
        }
    }

    //角色查询
    public function roleQuery(){
        return Db::table('hit_role')->select();
    }
    public function roleQueryById($user_id){
        return Db::table('hit_role')
            ->join('user_role','user_role.role_name=hit_role.role_name')
            ->where('user_id',$user_id)
            ->find();
    }

    //新建角色
    public function roleAdd($data_add){
        $is_exists = Db::table('hit_role')->where('role_name',$data_add['role_name'])->find();
        if($is_exists != null){
            $this->error['state'] = -1;
            $this->error['msg'] = '角色已存在';
            return $this->error;
        }
        return Db::table('hit_role')->insert($data_add);
    }

    //删除角色
    public function roleDel($name){
        $is_exists = Db::table('hit_role')->where('role_name',$name)->find();
        if($is_exists == null){
            $this->error['state'] = -1;
            $this->error['msg'] = '角色不存在';
            return $this->error;
        }
        return Db::table('hit_role')->where('role_name',$name)->delete();
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
        return Db::table('hit_word_process')
            ->join('user_word','user_word.word_id = hit_word_process.word_id')
            ->select();
    }

    public function depList(){
        return Db::table('hit_department')
//            ->join('user_department','hit_department.dep_id = user_department.dep_id')
            ->select();
    }

    //发送消息
    public function messageAdd($add_data){
        $tmp = [
            'user_msg' => $add_data['message']
        ];
        return Db::table('hit_user')->where('user_id',$add_data['user_id'])->update($tmp);
    }

    //修改用户的部门
    public function userDepEdit($edit){
        $name = Db::table('hit_department')->where("dep_id",$edit['dep_id'])->value('dep_name');
        if($name == null){
            $this->error['state'] = -1;
            $this->error['msg'] = '部门错误';
            return $this->error;
        }
        $had_assgin = Db::table('user_department')->where('user_id',$edit['user_id'])->find();
        if($had_assgin == null){
            return Db::table('user_department')->insert($edit);
        }else{
            return Db::table('user_department')->where('user_id',$edit['user_id'])->update($edit);
        }
    }

    //给用户分配角色
    public function userRoleAssgin($data){
        $is_exists = Db::table('user_role')->where('user_id',$data['user_id'])->find();
        if($is_exists == null){
            return Db::table('user_role')->insert($data);
        }
        return Db::table('user_role')->where('user_id',$data['user_id'])->update($data);
    }
}