<?php


namespace app\index\controller;


use app\index\model\AdminModel;
use app\index\model\UserModel;
use think\Controller;
use think\Request;
use think\Validate;

class LoginController extends Controller
{
    public function index(){
        return $this->fetch('admin/index');
    }
    //用户登录
    public function userLogin()
    {
        $post = Request::instance()->post();
        $rule = [
            'user_id' => 'require',
            'user_password' => 'require'
        ];
        $msg = [
            'user_id.require' => '用户名不能为空',
            'user_password.require' => '密码不能为空'
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($post);
        $user = new UserModel();
        if (!$result) {
            $this->error($validate->getError());
        } else {
            if ($user->userLogin($post['user_id'], $post['user_password'])) {
                session('user_id', $post['user_id']);
                $admin = new AdminModel();
                $role = $admin->roleQueryById($post['user_id']);
                if($role == null){
                    session('role','0000');
                }else{
                    session('role',$role['role_state']);
                }
                $this->success("登录成功", url("User/index"));
            }
        }
    }

    //管理员登录
    public function adminLogin()
    {
        $post = Request::instance()->post();
        $rule = [
            'admin_id' => 'require',
            'admin_password' => 'require'
        ];
        $msg = [
            'admin_id.require' => '用户名不能为空',
            'admin_password.require' => '密码不能为空'
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($post);
        $user = new AdminModel();
        if (!$result) {
            $this->error($validate->getError());
        } else {
            if ($user->AdminLogin($post['admin_id'], $post['admin_password'])) {
                session('admin_id', $post['admin_id']);
                $this->success("登录成功", url("Admin/index"));
            }
            $this->error('登录失败');
        }
    }
}