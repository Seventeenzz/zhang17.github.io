<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use think\Session;
use app\index\controller\Index;
use app\index\model\Grade;
use app\index\model\Student_info as StudentModel;
use app\index\model\Teacher_info as TeacherModel;
use app\index\model\Attend as AttendModel;

//teacher-考勤管理
class Tchattendmgr extends Base
{
	//考勤管理-考勤列表
	public function attendList()
	{
	 $this -> view -> assign('title','考勤管理');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

    $id=Session::get('user_id');
    $teachList = TeacherModel::get($id);
    $cidArr = $teachList['cid'];
    $where['cid'] = array('in',$cidArr);
    $where['status'] = 0;

	 $gradeList=Grade::where($where)->order('yid','asc')->select();//所有

	 $yidlist=Grade::Distinct(true)->where($where)->field('yid')->order('yid','asc')->select();//年
	 $this -> view -> assign('yidlist',$yidlist);
	 $this -> view -> assign('gradelist',$gradeList);
	 return $this -> view -> fetch('attendmgr_attendlist');

	}

	//考勤-渲染编辑界面
	public function attendEdit(Request $request)
	{
	 
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

	 $cid = trim($request -> param('cid'));
	 $classInfo = trim($request -> param('classInfo'));
	 $this -> view -> assign('title',$classInfo);
	 
	 $List=AttendModel::where(['cid'=>$cid])->order('id','asc')->select();//所有
		
	 $this -> view -> assign('classInfo',$classInfo);
	 $this -> view -> assign('list',$List);
	 $this -> view -> assign('cid',$cid);    //将cid存储到当前地址
	 return $this ->view ->fetch('attendmgr_edit');
	}



}
