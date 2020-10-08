<?php


namespace app\index\controller;


use app\index\model\UserModel;
use think\Controller;
use think\Request;
use think\Validate;

class UserController extends Controller
{
    public function index()
    {
        $user = new UserModel();
        $user_data = $user->userQuery(session('user_id'));
        $this->assign('user_data',$user_data);
        $html = $this->fetch();
        return $html;
    }

    public function passwordChange()
    {
        $post = Request::instance()->post();
        $rule = [
            'user_id' => 'require',
            'user_password' => 'require',
            'user_password_old' => 'require'
        ];
        $user = new UserModel();
        $validate = new Validate($rule);
        $result = $validate->check($post);
        if (!$result) {
            $this->error($validate->getError());
        } else {
            $res = $user->editPassword($post['user_id'], $post['user_password_old'], $post['user_password']);
            if (isset($res['state']) && $res['state'] == -1) {
                $this->error($res['msg']);
            } else {
                $this->success('操作成功', url('index\User\userChangePasswordView'));
            }
        }
    }

    public function userInfoEdit()
    {
        $post = Request::instance()->post();
        $rule = [
            'user_id' => 'require',
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
            var_dump($post);
            $res = $user->userEdit($post);
            if (isset($res['state']) && $res['state'] == -1) {
                $this->error($res['msg']);
            } else {
                $this->success('修改成功', url('User/userInfoEditView'));
            }
        }
    }

    public function userChangePasswordView()
    {
        $user = new UserModel();
        $user_info = $user->userQuery(session('user_id'));
        $this->assign('user_data',$user_info);
        return $this->fetch('changePassword');
    }

    public function userInfoEditView(){
        $user = new UserModel();
        $user_info = $user->userQuery(session('user_id'));
        $this->assign('user_data',$user_info);
        return $this->fetch('user_Info');
    }

    public function userWordView()
    {
        $user = new UserModel();
        $word_list = $user->userWordQuery(session('user_id'));
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
        for($i = 0;$i < count($word_list);$i++){
            $offset = strpos($word_list[$i]['word_place'],'\\');
            $word_list[$i]['word_place'] = substr($word_list[$i]['word_place'],0,$offset+1);
            $word_list[$i]['word_place'] = '\\'.$word_list[$i]['word_place'];
        }
        $user_info = $user->userQuery(session('user_id'));
        $this->assign('word_list', $word_list);
        $this->assign('user_data',$user_info);
//        $this->display("head");
        return $this->fetch('user_word');
    }

    public function userWordUploadView()
    {
        $user = new UserModel();
        $user_data = $user->userQuery(session('user_id'));
        $this->assign('user_data',$user_data);
        return $this->fetch('upload');
    }

    public function userIofoView()
    {
        $user = new UserModel();
        $user_data = $user->userQuery(session('user_id'));
        $this->assign('user_data', $user_data);
        return $this->fetch('user_info');
    }



    public function userWordUpload()
    {
        // 获取表单上传文件
        $file = Request::instance()->file();
        $file_info_1 = $file['file']->getInfo();
        $post = Request::instance()->post();
        if (empty($file)) {
            $this->error('请选择上传文件');
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads\\' . $file_info_1['name'];
        $user = new UserModel();
        $is_exists = $user->wordQueryByName($file_info_1['name']);
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
        $res = $user->wordUpload($file_info);
        if(!$res){
            $this->error("数据库错误");
        }else{
            $user_word = [
                'word_id' => $res,
                'user_id' => session('user_id'),
                'word_name' => $file_info_1['name']
            ];
            $res1 = $user->wordUserJoin($user_word);
            if(!$res){
                $this->error("数据库错误1");
            }
            $this->success('文件上传成功');
        }
    }

    public function userWordDownload(){
        $get = Request::instance()->param();
        $name = $get['name'];
        $user = new UserModel();
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

    public function uesrWordEdit(){
        $file = Request::instance()->file();
        $post = Request::instance()->post();
        $file_info_1 = $file['file']->getInfo();

        if (empty($file)) {
            $this->error('请选择上传文件');
        }
        $user = new UserModel();
        $is_exists = $user->wordQueryByName($file_info_1['name']);
        if(!$is_exists){
            $this->error('文件不存在');
        }
        $info1 = unlink(ROOT_PATH . 'public' . DS . 'uploads\\'.$is_exists['word_place']);
        if(!$info1){
            $this->error('删除失败');
        }
        $info = $file['file']->move(ROOT_PATH . 'public' . DS . 'uploads');
        if ($info) {
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
        $res = $user->wordUpload($file_info);
        if(!$res){
            $this->error("数据库错误");
        }else{
            $this->success("修改成功");
        }
    }

    public function messageRead(){
        $flag = Request::instance()->param();
        if(isset($flag['read']) && $flag['read'] == '1'){
            $user = new UserModel();
            $res = $user->messageRead(session('user_id'));
            if($res == null){
                $this->error('已读错误');
            }
            return $this->index();
        }
        else{
            var_dump($flag);
        }
    }

    public function exitLogin(){
        session('user_id','');
        $this->success('注销成功',url("index/index/index"));
    }
}