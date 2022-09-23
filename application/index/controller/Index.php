<?php
namespace app\index\controller;
use user;
use app\index\controller\Base;
use think\Request;
use app\index\model\Admin_info as AdminModel;
use app\index\model\Teacher_info as TeacherModel;
use app\index\model\Student_info as StudentModel;
use think\Session;

class Index extends Base
{
    public function index()
    {
		$this -> isLogin();
		$titleIndex="辅导员管理系统1.0v";
		$this -> view -> assign("title",$titleIndex);

		//登录等操作
		return $this -> view -> fetch();
	}

	public function login()
    {
		$this -> alreadyLogin();
		$titleLogin="登陆账号-辅导员管理系统1.0v";
		$this->assign("title1",$titleLogin);
		return $this -> view -> fetch();
	} 

	//验证登录  $this->validate($data,$rule,$msq)
	public function checkLogin(Request $request)
	{
	  //进来的时候账号密码和验证码都一定是存在的  不能为空  且长度符合  
	  //在前端JS
	  $status = 0;
	  $result = '';
	  $data =$request -> param();
	  $len=strlen($data['verify']);
	  $loginidlen=new \user\Loginidlen();
	  

	  //验证错误
	  if(!captcha_check($data['verify'])){
		
		return ['status'=>0,'message'=>'验证码错误'];
	  }


	  switch(strlen($data['name'])){
		  //管理员
	  	  case $loginidlen->getAdmin():
		  $info = ['adminId' => $data['name'],'password' => $data['passwd']];
		  $userAdmin = AdminModel::get($info);
		  if($userAdmin == null){
				$result ='账号或者密码错误，请检查';
		  } else {
		    $status = 0;
			$status = 1;
			$result = 'Success';
			//设置用户登录信息用：Session
			Session::set('user_id',$userAdmin->id);
			Session::set('user_info',$userAdmin ->getData());
		  }
		  break;
		  //教师
		  case $loginidlen->getTch():
		  $info = ['teacherId' => $data['name'],'password' => $data['passwd']];
		  $userTch = TeacherModel::get($info);
		  if($userTch == null){
				$status = 0;
				$result ='账号或者密码错误，请检查';
		  } else {
			$status = 1;
			$result = 'Success';
			//设置用户登录信息用：Session
			Session::set('user_id',$userTch->id);
			Session::set('user_info',$userTch ->getData());
		  }
		  break;
		  //学生
		  case $loginidlen->getStu():
		  $info = ['studentId' => $data['name'],'password' => $data['passwd']];
		  $userStu = StudentModel::get($info);
		  if($userStu == null){
				$status = 0;
				$result ='账号或者密码错误，请检查';
		  } else {
			$status = 1;
			$result = 'Success';
			//设置用户登录信息用：Session
			Session::set('user_id',$userStu->id);
			Session::set('user_info',$userStu ->getData());
		  }
		  break;
		  defalut:
		  $status=0;
		  $result='学号/工号不存在';
		  break;
	  }
	  return ['status'=>$status,'message'=>$result];
	}

	public function loginout()
	{
		Session::delete('user_id');
		Session::delete('user_info');
		$this -> success('注销登录，正在返回','index/login');
	}

	public function personalinfo()
	{
		$id=Session::get('user_id');
		$role=Session::get('user_info.role');
		//管理员
		if($role > 1){
		$List=AdminModel::Get($id);
		$this -> view -> assign('user_info',$List);
		return $this -> view -> fetch('admin_info');
		} else if($role == 1){
		$List=TeacherModel::Get($id);
		$this -> view -> assign('user_info',$List);
		return $this -> view -> fetch('teacher_info');
		}
		
	}

	//渲染修改密码界面
	public function changePassword()
	{
		$this->assign("title",'修改密码');
		return $this -> view -> fetch('password_edit');
	}
	
	//修改密码
	public function changepd(Request $request)
	{
		$data =$request -> param();
		$id=Session::get('user_id');
		$user_info=Session::get('user_info');

		$message="修改密码成功";
		$status=1;
		$condition = ['id'=>$user_info['id']] ;
		$cdata=['password'=>$data['npassword1']];
		$result=false;

		//检测密码正确与否
		if($data['npassword1']==$data['npassword2']&&$data['npassword2']!=""){
			if($data['password']==$user_info['password']){
				switch($user_info['role']){
				case 3:
				case 2:$result = AdminModel::update($cdata, $condition);break;
				case 1:$result = TeacherModel::update($cdata, $condition);break;
				case 0:$result = StudentModel::update($cdata, $condition);break;
				defalut:break;
				}
			}else {
			  $status=0;
			  $message="修改失败，原密码不正确";
			}
		} else {
			$status=0;
			$message="修改失败，请检测";
		}
        if (true == $result) {
            return ['status'=>1, 'message'=>'修改成功'];
        } else {
            return ['status'=>$status, 'message'=>$message];
        }
	}

	//修改教师信息
	public function changeTchInfo(Request $request)
	{
		$data =$request -> param();
		$id=Session::get('user_id');
		$user_info=Session::get('user_info');

		$condition = ['id'=>$user_info['id']] ;
		$cdata=['job'=>$data['work'],'email'=>$data['em'],'contact'=>$data['ct']];

		$result = TeacherModel::update($cdata, $condition);
		if (true == $result) {
            return ['status'=>1, 'message'=>'修改成功'];
        } else {
            return ['status'=>0, 'message'=>'修改失败'];
        }
        
	}
	//jump stu info 
	public function jumpStuInfo()
	{
		$id=Session::get('user_id');
		$List=StudentModel::Get($id);
		$this -> view -> assign('user_info',$List);
		return $this -> view -> fetch('student_info');
	}

	//渲染-学生修改界面
	public function studentEdit(Request $request)
	{
		$user_id = Session::get('user_id');
		$result = StudentModel::get($user_id);
		$this -> view -> assign('keywords', '12341');
		$this -> view -> assign('title','添加管理员');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		$this -> view -> user_info =$result->getData();
		return $this ->view ->fetch('student_edit');
	}

	//更新学生数据操作
    public function editStudent(Request $request)
    {
        //获取表单返回的数据
        $param = $request -> param();
		$file = request() ->file('image');
		if($file){
        $info = $file->validate(['size'=>2000000,'ext'=>'jpg,png'])->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'userImg',true,false);
        if($info){
           $filename="/uploads/userImg\\".$info->getFilename();
        }else{
            return ['status'=>0, 'message'=>'更新失败,图像验证错误'];
        }
		}else{
            $filename="";
        }

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)||$key=='gender'){  //避免选择器出现问题 
                $data[$key] = $value;
            }
        }
		//没有图片
		if(empty($filename)){
			$update=[
				'gender'=>$data['gender'],
				'nation'=>$data['nt'],
				'birthPlace'=>$data['bp'],
				'polity'=>$data['py'],
				//'major'=>$data['major'],
				'contact'=>$data['ct'],
				'identity'=>$data['iy'],
				'email'=>$data['em'],
				'address'=>$data['address'],
			];
		} 
		//有图
		else {
			$update=[
				'gender'=>$data['gender'],
				'nation'=>$data['nt'],
				'birthPlace'=>$data['bp'],
				'polity'=>$data['py'],
				//'major'=>$data['major'],
				'contact'=>$data['ct'],
				'identity'=>$data['iy'],
				'email'=>$data['em'],
				'address'=>$data['address'],
				'picture'=>$filename,
			];
		}

		$user_id = Session::get('user_id');
        $condition = ['id'=>$user_id] ;
        $result = StudentModel::update($update, $condition);

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,检查数据正确与否'];
        }
    }

    public function welcome(){
        return $this ->view ->fetch('welcome');
    }

}
