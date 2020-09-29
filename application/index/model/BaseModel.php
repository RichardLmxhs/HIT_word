<?php

namespace app\index\model;

use think\Db;
use think\Model;

class BaseModel extends Model
{
    protected $error = array();

    /*
     * 密码散列函数
     */
    protected function encryptPassword($password)
    {
        return hash("sha256", $password . "hit_word");
    }

    /*
     * 用户查询
     * @param $user_id
     * @return array
     */
    public function userQuery($user_id)
    {
        return Db::table("hit_user")->where("user_id", $user_id)->find();
    }


    /*
     * 用户添加
     * @param $data 添加的用户数据
     */
    public function userAdd($data)
    {
        if ($data == null) {
            $this->error['state'] = -1;
            $this->error['msg'] = '添加用户数据为空，错误';
            return $this->error;
        }
        $user_id = Db::table("hit_user")->where("user_id", $data['user_id'])->find();
        if ($user_id !== null) {
            $this->error['state'] = -1;
            $this->error['msg'] = '用户id已存在';
            return $this->error;
        }
        $data['user_password'] = $this->encryptPassword($data["user_password"]);
        return Db::table('hit_user')->insert($data);
    }

    /*
     * 用户删除
     * @param $user_id
     */
    public function userDelete($user_id)
    {
        $is_exist = Db::table('hit_user')->where("user_id", $user_id)->find();
        if ($is_exist == null) {
            $this->error['state'] = -1;
            $this->error['msg'] = '用户不存在';
            return $this->error;
        } else {
            $res_role = Db::table('hit_role')->where('user_id',$user_id)->find();
            if($res_role != null){
                $this->error['state'] = -1;
                $this->error['msg'] = '有属于该用户的角色，无法进行删除';
                return $this->error;
            }
            $res_word = Db::table("user_word")->where("user_id", $user_id)->find();
            if ($res_word != null) {
                $this->error['state'] = -1;
                $this->error['msg'] = '有属于该用户的文档，无法进行删除';
                return $this->error;
            } else {
                $res_user = Db::table('hit_user')->where("user_id", $user_id)->delete();
                $res_dep = Db::table('user_deparment')->where('user_id', $user_id)->delete();
                $res_word_del = Db::table('user_word')->where("user_id", $user_id)->delete();
                if ($res_user != null && $res_dep != null && $res_word_del != null) {
                    return true;
                } else {
                    $this->error['state'] = -1;
                    $this->error['msg'] = '数据库错误';
                    return $this->error;
                }
            }
        }
    }

    /*
     * 用户修改
     * @param $user_data
     */
    public function userEdit($user_data)
    {
        $is_exist = Db::table('user_id')->where('user_id', $user_data["user_id"])->find();
        if ($is_exist == null) {
            $this->error["state"] = -1;
            $this->error['msg'] = '要修改的用户不存在';
            return $this->error;
        }
        $user_data["user_password"] = $this->encryptPassword($user_data['user_password']);
        return Db::table('hit_user')->update($user_data);
    }

    /*
     * 文档查询
     */
    public function wordQuery($word_id)
    {
        return Db::table("hit_word")->where("word_id", $word_id)->find();
    }

    /*
     * 文档上传
     */
    public function wordUpload($word_data)
    {
        $data_ins = array(
            'word_id' => $word_data['word_id'],
            'word_name' => $word_data['word_name'],
            'word_place' => $word_data['word_place'],
            'word_state' => $word_data['word_state'],
            'word_introduction' => $word_data['word_introduction'],
            'word_startTime' => $word_data['word_startTime']
        );
        return Db::table("hit_word_process")->insert($data_ins);
    }

    /*
     * 文档修改
     */
    public function wordEdit($word_data)
    {
//        $data_update = array(
//            'word_id' => $word_data['word_id'],
//            'word_name' => $word_data['word_name'],
//            'word_place' => $word_data['word_place'],
//            'word_state' => $word_data['word_state'],
//            'word_introduction' => $word_data['word_introduction'],
//            'word_startTime' => $word_data['word_startTime']
//        );
        return Db::table('hit_word_process')->update($word_data);
    }

    /*
     * 文档删除
     */
    public function wordDelete($word_id)
    {
        return Db::table('hit_word_process')->where("word_id", $word_id)->delete();
    }
}