<?php


namespace app\index\controller;


use app\index\model\UserModel;
use think\Controller;
use think\Request;
use think\Validate;

class RegisterController extends Controller
{
    public function index()
    {
        $html = $this->fetch();
        return $html;
    }

    //用户注册
    public function userRegister()
    {
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
        $user = new UserModel();
        $validate = new Validate($rule);
        $result = $validate->check($post);
        if (!$result) {
            $this->error($validate->getError());
        } else {
            if ($user->userRegister($post)) {
                $this->success('注册成功', url('Index\index'));
            } else {
                $this->error('注册失败');
            }
        }
    }
}