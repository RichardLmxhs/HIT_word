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
            $res = $user->editPassword($post['user_id'], $post['user_password'], $post['user_password_old']);
            if (isset($res['state']) && $res['state'] == -1) {
                $this->error($res['msg']);
            } else {
                $this->success('操作成功', url('User\index'));
            }
        }
    }

    public function userInfoEdit()
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
            $res = $user->userEdit($post);
            if (isset($res['state']) && $res['state'] == -1) {
                $this->error($res['msg']);
            } else {
                $this->success('修改成功', url('User/index'));
            }
        }
    }

    public function userChangePasswordView()
    {
        return $this->fetch('changePassword');
    }

    public function userInfoEditView(){
        $user = new UserModel();
        $user_info = $user->userQuery(session('user_id'));
        $this->assign('user_info',$user_info);
        return $this->fetch('userInfoEdit');
    }

    public function userWordView()
    {
        $user = new UserModel();
        $word_list = $user->userWordQuery(session('user_id'));
        $this->assign('word_list', $word_list);
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

        if(file_exists($path)){
            $this->error('文件已存在');
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $file_info = [
            'word_name' => $file_info_1['name'],
            'word_place' => ROOT_PATH . 'public' . DS . 'uploads',
            'word_state' => $post['file_state'],
            'word_introduction' => $post['file_introduction'],
            'word_startTime' => date("Y-M-D h:i")
        ];
        $user = new UserModel();
        $res = $user->wordUpload($file_info);
        if(!$res){
            $this->error("数据库错误");
        }else{

            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) {
                $this->success('文件上传成功');
                echo $info->getFilename();
            } else {
                // 上传失败获取错误信息
                $this->error($file->getError());
            }
        }
    }

    public function userWordDownload(){
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
}