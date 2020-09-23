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
    protected function userQuery($user_id)
    {
        return Db::table("hit_user")->where("user_id", $user_id)->find();
    }

    /*
     * 用户添加
     * @param $data 添加的用户数据
     */
    protected function userAdd($data)
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
        $data_add = array(
            "user_id" => $data['user_id'],
            "user_name" => $data["user_name"],
            'user_password' => $data['user_password'],
            'user_msg' => $data['user_mag'],
            'user_msg_history' => $data['user_msg_history'],
            'user_sex' => $data['user_sex'],
            'user_email' => $data['user_email'],
            'user_phone' => $data['user_phone'],
            'user_age' => $data['user_age']
        );
        $data_add['user_password'] = $this->encryptPassword($data_add["user_password"]);
        return Db::table('hit_user')->insert($data_add);
    }

    /*
     * 用户删除
     * @param $user_id
     */
    protected function userDelete($user_id)
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
    protected function userEdit($user_data)
    {
        $is_exist = Db::table('user_id')->where('user_id', $user_data["user_id"])->find();
        if ($is_exist == null) {
            $this->error["state"] = -1;
            $this->error['msg'] = '要修改的用户不存在';
            return $this->error;
        }
        $data_edit = array(
            "user_id" => $user_data['user_id'],
            "user_name" => $user_data["user_name"],
            'user_password' => $user_data['user_password'],
            'user_msg' => $user_data['user_mag'],
            'user_msg_history' => $user_data['user_msg_history'],
            'user_sex' => $user_data['user_sex'],
            'user_email' => $user_data['user_email'],
            'user_phone' => $user_data['user_phone'],
            'user_age' => $user_data['user_age']
        );
        $data_edit["user_password"] = $this->encryptPassword($data_edit['user_password']);
        return Db::table('hit_user')->update($data_edit);
    }

    /*
     * 文档查询
     */
    protected function wordQuery($word_id)
    {
        return Db::table("hit_word")->where("word_id", $word_id)->find();
    }

    /*
     * 文档上传
     */
    protected function wordUpload($word_data)
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
    protected function wordEdit($word_data)
    {
        $data_update = array(
            'word_id' => $word_data['word_id'],
            'word_name' => $word_data['word_name'],
            'word_place' => $word_data['word_place'],
            'word_state' => $word_data['word_state'],
            'word_introduction' => $word_data['word_introduction'],
            'word_startTime' => $word_data['word_startTime']
        );
        return Db::table('hit_word_process')->update($data_update);
    }

    /*
     * 文档删除
     */
    protected function wordDelete($word_id)
    {
        return Db::table('hit_word_process')->where("word_id", $word_id)->delete();
    }
}