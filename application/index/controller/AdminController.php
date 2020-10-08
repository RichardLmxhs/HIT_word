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
        $data = $admin->userDepList();
        $dep_list = $admin->depList();
//        var_dump($data);
        $this->assign("user_list",$data);
        $this->assign("dep_list",$dep_list);
        return $this->fetch('userManage');
    }

    public function adminManageView(){
        $admin = new AdminModel();
        $data = $admin->adminList();
        $this->assign("admin_list",$data);
        return $this->fetch('adminManage');
    }

    public function adminChangePasswordView(){
        $admin = new AdminModel();
        $admin_data = $admin->adminQuery(session('admin_id'));
        $this->assign('admin_data',$admin_data);
        return $this->fetch('adminChangePassword');
    }

    public function adminRoleManageView(){
        $admin = new AdminModel();
        $role_list = $admin->roleQuery();
        $user_list = $admin->userList();
        $this->assign('user_list',$user_list);
        $this->assign('role_list',$role_list);

        return $this->fetch('adminRoleManage');
    }

    public function depManageView(){
        $admin = new AdminModel();
        $dep_data = $admin->depList();
        $this->assign('dep_list',$dep_data);
        return $this->fetch('depManage');
    }
    public function adminWordManageView(){
        $admin = new AdminModel();
        $word_list = $admin->wordList();
        if($word_list == null){
            $word_list = [
                'word_name' => '空',
                'word_id' => '空',
                'word_place' => '空',
                'word_state' => '空',
                'word_introduction' => '空',
                'word_startTime' => '空'
            ];
        }
        $this->assign('word_list',$word_list);
        return $this->fetch('adminWordManage');
    }

    public function messageManageView(){
        $admin = new AdminModel();
        $user_list = $admin->userList();
        $this->assign('user_list',$user_list);
        return $this->fetch('messageManage');
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
                $this->success('添加成功', url('index\Admin\userManageView'));
            } else{
                $this->error('添加失败');
            }
        }
    }

    public function userDelete(){
        $get = Request::instance()->param();
//        var_dump($get);
        if(isset($get['user_id']) && $get['user_id'] != null){
            $admin = new AdminModel();
            $res = $admin->userDelete($get['user_id']);
            if(isset($res['state']) && $res['state'] == -1){
                $this->error($res['msg']);
            } else{
                $this->success('删除成功', url('index\Admin\userManageView'));
            }
        }else{
            $this->error('error');
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

    public function adminDelete(){
        $get = Request::instance()->param();
        if(isset($get['admin_id']) && $get['admin_id'] != null){
            $admin = new AdminModel();
            $res = $admin->adminDelete($get['admin_id']);
            if(isset($res['state']) && $res['state'] == -1){
                $this->error($res['msg']);
            } else{
                $this->success('删除成功', url('Admin\adminManageView'));
            }
        }else{
            $this->error('error');
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
        $this->success('修改成功',url('admin/adminChangePasswordView'));
    }

    public function adminWordDownload(){
        $get = Request::instance()->param();
        $name = $get['name'];
        $user = new AdminModel();
        $word = $user->wordQueryByName($name);
        $path = ROOT_PATH . 'public' . DS . 'uploads\\' .$word['word_place'];
        if(!file_exists($path)){
            $this->error('文件不存在');
        }
        header("Content-type: application/octet-stream");
        header('Content-Disposition: attachment; filename="'. $name .'"');
        header("Content-Length: ". filesize($path));
        readfile($path);
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

    public function adminWordEdit(){
        $file = Request::instance()->file();
        $file_info_1 = $file['file']->getInfo();
        $post = Request::instance()->post();
        if (empty($file)) {
            $this->error('请选择上传文件');
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads\\' . $file_info_1['name'];
        $admin = new AdminModel();
        $is_exists = $admin->wordQueryByName($file_info_1['name']);
        if($is_exists){
            $this->error('文件已存在');
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file['file']->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info) {
//                $this->success('文件上传成功');
//            var_dump($info->getSaveName());
            $word_place = $info->getSaveName();
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

        $file_info = [
            'word_name' => $file_info_1['name'],
            'word_place' => $word_place,
            'word_state' => $post['word_state'],
            'word_introduction' => $post['word_introduction'],
            'word_startTime' => date("Y-m-d-h:i")
        ];
        $res = $admin->wordUpload($file_info);
        if(!$res){
            $this->error("数据库错误");
        }else{
            $user_word = [
                'word_id' => $res,
                'user_id' => 0,
                'word_name' => $file_info_1['name']
            ];
            $res1 = $admin->wordUserJoin($user_word);
            if(!$res){
                $this->error("数据库错误1");
            }
            $this->success('文件上传成功');
        }
    }

    public function adminWordUpload(){
        // 获取表单上传文件
        $file = Request::instance()->file();
        $file_info_1 = $file['file']->getInfo();
        $post = Request::instance()->post();
        if (empty($file)) {
            $this->error('请选择上传文件');
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads\\' . $file_info_1['name'];
        $admin = new AdminModel();
        $is_exists = $admin->wordQueryByName($file_info_1['name']);
        if($is_exists){
            $this->error('文件已存在');
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file['file']->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info) {
//                $this->success('文件上传成功');
//            var_dump($info->getSaveName());
            $word_place = $info->getSaveName();
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

        $file_info = [
            'word_name' => $file_info_1['name'],
            'word_place' => $word_place,
            'word_state' => $post['word_state'],
            'word_introduction' => $post['word_introduction'],
            'word_startTime' => date("Y-m-d-h:i")
        ];
        $res = $admin->wordUpload($file_info);
        if(!$res){
            $this->error("数据库错误");
        }else{
            $user_word = [
                'word_id' => $res,
                'user_id' => 0,
                'word_name' => $file_info_1['name']
            ];
            $res1 = $admin->wordUserJoin($user_word);
            if(!$res){
                $this->error("数据库错误1");
            }
            $this->success('文件上传成功');
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

    public function roleAssign(){
        $post = Request::instance()->post();
//        $rule = [
//            'role_name' => "require",
//            'user_id' => 'require'
//        ];
//        $validate = new Validate($rule);
//        if(!$validate->check($post)){
//            $this->error($validate->getError());
//        }else{
//            $admin = new AdminModel();
//            $res = $admin->roleAdd($post);
//            if(!$res){
//                $this->error('失败');
//            }else{
//                $this->success('成功',url('index/admin/adminRoleManageView'));
//            }
//        }

//        var_dump($post);
    }

    public function depAdd(){
        $post = Request::instance()->post();
        $rule = [
            'dep_id' => 'require',
            'dep_name' => 'require',
            'dep_place' => 'require',
            'dep_leader' => 'require',
        ];
        $validate = new Validate($rule);
        if(!$validate->check($post)){
            $this->error($validate->getError());
        }else{
            $admin = new AdminModel();
            $res = $admin->depAdd($post);
            if(!$res){
                $this->error('数据库错误');
            }
            $this->success('添加成功',url("admin/depManageView"));
        }
    }

    public function depDel(){
        $get = Request::instance()->param();
        if(!isset($get['dep_id'])){
            $this->error('删除参数错误');
        }
        $dep_id = $get['dep_id'];
        $admin = new AdminModel();
        $res = $admin->depDelete($dep_id);
        if(isset($res['state']) && $res['state'] == -1){
            $this->error($res['msg']);
        }else{
            $this->success('删除成功',url('admin/depManageView'));
        }
    }

    public function depEdit(){
        $post = Request::instance()->post();
        $rule = [
            'dep_id' => 'require',
            'dep_name' => 'require',
            'dep_place' => 'require',
            'dep_leader' => 'require',
        ];
        $validate = new Validate($rule);
        if(!$validate->check($post)){
            $this->error($validate->getError());
        }else{
            $admin = new AdminModel();
            $res = $admin->depEdit($post);
            if(!$res){
                $this->error('修改失败');
            }else{
                $this->success('修改成功',url('admin/depManageView'));
            }
        }
    }

    public function messageAdd(){
        $post = Request::instance()->post();
        $rule = [
            'user_id' => 'require',
            'message' => 'require'
        ];
        $validate = new Validate($rule);
        if(!$validate->check($post)){
            $this->error($validate->getError());
        }
        $post['message'] = '消息发送时间:'.date('Y-m-d-h:i') .'  '.'消息内容:'. $post['message'];
        $admin = new AdminModel();
        $res = $admin->messageAdd($post);
        if(!$res){
            $this->error('推送失败');
        }else{
            $this->success('推送成功',url('admin/messageManageView'));
        }
    }

    public function exitLogin(){
        session('admin_id','');
        $this->success('注销成功',url("index/index/index"));
    }

    public function userDepEdit(){
        $post = Request::instance()->post();
//        var_dump($post);
        $rule = [
            'user_id' => 'require',
            'dep_id' => 'require'
        ];
        $validate = new Validate($rule);
        if(!$validate->check($post)){
            $this->error($validate->getError());
        }
        $admin = new AdminModel();
        $res = $admin->userDepEdit($post);
        if(isset($res['state']) && $res['state'] == -1){
            $this->error($res['msg']);
        }else{
            $this->success('修改成功',url('index/admin/userManageView'));
        }
    }
}