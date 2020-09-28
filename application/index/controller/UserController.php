<?php


namespace app\index\controller;


use think\Controller;
use think\Request;

class UserController extends Controller
{
    public function index(){
        $html = $this->fetch();
        return $html;
    }

    public function userEdit(){
        $post = Request::instance()->post();
    }
}