<?php
namespace app\index\controller;
use think\Controller;
use think\Session;

class Base extends Controller
{
    protected function _initialize()
	{
	  parent::_initialize();
	  define('USER_ID',Session::get('user_id'));
	}

	//判断用户是否登录
	protected function isLogin()
	{
		if(is_null(USER_ID)){
			$this->error('用户未登陆，无权访问',url('index/login'));
			//$this->redirect(url('index/login'));重定向
		}
	}

	protected function alreadyLogin()
	{
		if(!is_null(USER_ID)){
			$this ->error('用户已经登陆，请勿重复登录',url('index/index'));
		}
	}
}
