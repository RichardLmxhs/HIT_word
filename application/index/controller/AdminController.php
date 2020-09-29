<?php


namespace app\index\controller;

use app\index\model\AdminModel;
use app\index\model\UserModel;
use think\Controller;
use think\Request;
use think\Validate;

class AdminController extends Controller
{
    public function loginView(){
        return $this->fetch("login");
    }

    public function index(){
        return $this->fetch();
    }

    public function userManageView(){
        $admin = new AdminModel();
        $data = $admin->userList();
        $this->assign("user_list",$data);
        return $this->fetch('userManage');
    }

    public function adminManageView(){
        $admin = new AdminModel();
        $data = $admin->adminList();
        $this->assign("admin_list",$data);
        return $this->fetch('adminManage');
    }

    public function adminChangePasswordView(){
        return $this->fetch('adminChangePassword');
    }

    public function adminRoleManageView(){
        $admin = new AdminModel();
        $data = $admin->roleQuery();
        $this->assign('role_list',$data);
        return $this->fetch('adminRoleManage');
    }

    public function adminWordManage(){
        $admin = new AdminModel();
        $word_list = $admin->wordList();
        $this->assign('word_list',$word_list);
        return $this->fetch('adminWordManage');
    }
    public function userAdd(){
        $post = Request::instance()->post();
        $rule = [
            'user_id' => 'require',
            'user_password' => 'require',
            'user_name' => 'require',
            'user_sex' => 'require',
            'user_age' => 'between:1,120',
            'user_email' => 'email',
            'user_phone' => 'max:12'
        ];
        $validate = new Validate($rule);
        $result = $validate->check($post);
        if (!$result) {
            $this->error($validate->getError());
        } else {
            $admin = new AdminModel();
            $res = $admin->userAdd($post);
            if($res != null) {
                $this->success('添加成功', url('Admin\userManage'));
            } else{
                $this->error('添加失败');
            }
        }
    }

    public function userDelete(){
        $get = Request::instance()->get();
        if(isset($get['user_id']) && $get['user_id'] != null){
            $admin = new AdminModel();
            $res = $admin->userDelete($get['user_id']);
            if(isset($res['state']) && $res['state'] == -1){
                $this->error($res['msg']);
            } else{
                $this->success('删除成功', url('Admin\userManage'));
            }
        }
    }

    public function adminAdd(){
        $post = Request::instance()->post();
        $rule = [
            'admin_id' => 'require',
            'admin_password' => 'require',
            'admin_password_again' => 'require'
        ];
        $msg = [
            'admin_id.require' => '用户名不能为空',
            'admin_password.require' => '密码不能为空'
        ];
        $validate = new Validate($rule,$msg);
        $result = $validate->check($post);
        if(!$result){
            $this->error($validate->getError());
        }else{
            if($post['admin_password'] !== $post['admin_password_again']){
                $this->error('两次输入密码不一致');
            }
            $admin = new AdminModel();
            $res = $admin->adminAdd($post);
            if(isset($res['state']) && $res['state'] == -1){
                $this->error($res['msg']);
            }else{
                $this->success('添加管理员成功');
            }
        }
    }

    public function adminChangePassword(){
        $admin_id = session('admin_id');
        $post = Request::instance()->post();
        $admin = new AdminModel();
        $rule = [
            'password_old' => 'require',
            'password_new' => 'require'
        ];
        $validate = new Validate($rule);
        $result = $validate->check($post);
        if(!$result){
            $this->error($validate->getError());
        }
        $res = $admin->adminPasswordEdit($admin_id,$post['password_old'],$post['password_new']);
        if(isset($res['state']) && $res['state'] == -1){
            $this->error($res['msg']);
        }
        $this->success('修改成功',url('admin/adminChangePassword'));
    }

    public function adminWordDownload(){
        $get = Request::instance()->get();
        $name = $get['name'];
        $path = ROOT_PATH . 'public' . DS . 'uploads/'. "$name";
        if(!file_exists($path)){
            $this->error('文件不存在');
        }
        // 打开文件
        $file1 = fopen($path, "r");
        // 输入文件标签
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".filesize($path));
        Header("Content-Disposition: attachment;filename=" . $path);
        ob_clean();     // 重点！！！
        flush();        // 重点！！！！可以清除文件中多余的路径名以及解决乱码的问题：
        //输出文件内容
        //读取文件内容并直接输出到浏览器
        echo fread($file1, filesize($path));
        fclose($file1);
        exit();
    }

    public function adminWordDelete(){
        $get = Request::instance()->get();
        $name = $get['name'];
        $path = ROOT_PATH . 'public' . DS . 'uploads/'. "$name";
        if(!file_exists($path)){
            $this->error('文件不存在');
        }
        $admin = new AdminModel();
        $word_id = $get['word_id'];
        $res = $admin->wordDelete($word_id);
        if(!$res){
            $this->error('删除失败');
        }
        else{
            $result = unlink($path);
            if(!$result){
                $this->error('删除失败');
            }
            $this->success('删除成功',url('admin/adminWordManage'));
        }
    }

    public function adminWordAudit(){
        $get = Request::instance()->get();
        $name = $get['name'];
        $path = ROOT_PATH . 'public' . DS . 'uploads/'. "$name";
        $word_id = $get['word_id'];
        $admin = new AdminModel();
        $isProcess = $admin->wordQuery($word_id);
        if($isProcess['word_state'] == -1){
            $this->error('非流程文档');
        }else{
            $state = $get['state'];
            $word_data = [
                'word_state' => $state
            ];
            $res = $admin->wordEdit($word_data);
            if(!$res){
                $this->error('数据库错误');
            }else{
                $this->success('流程变动成功');
            }
        }
    }

}