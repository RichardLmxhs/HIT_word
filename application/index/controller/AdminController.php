<?php


namespace app\index\controller;

use think\Controller;
class AdminController extends Controller
{
    public function loginView(){
        return $this->fetch("login");
    }

    public function index(){
        return $this->fetch();
    }


}