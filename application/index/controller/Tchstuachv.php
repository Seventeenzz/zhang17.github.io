<?php
namespace app\index\controller;
use app\index\controller\Base;
use app\index\model\Student_eval;
use think\Request;
use think\Session;
use app\index\controller\Index;
use app\index\model\Grade;
use app\index\model\Student_score as StuscoModel;
use app\index\model\Student_info as StudentModel;
use app\index\model\Teacher_info as TeacherModel;
use app\index\model\Student_eval as StuEvalModel;

use app\index\model\Attend as AttendModel;

//teacher-学生成绩
class Tchstuachv extends Base
{
	//学生成绩-成绩列表
	public function achvList(){
        $this -> view -> assign('title','成绩列表');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');


        $id=Session::get('user_id');
        $teachList = TeacherModel::get($id);
        $where['cid'] = array('in',$teachList['cid']);

        $this -> view -> count =StuscoModel::count();
        $stuList = StudentModel::where($where)->order('id','asc')->select();
        $list = StuscoModel::order('id','asc')->select();
        $this -> view -> assign('list',$list);
        $this -> view -> assign('stuList',$stuList);
        return $this -> view -> fetch('achv_list');
	}

	/* 教师端--学生综测 */
	public function evalList(){
        $this -> view -> assign('title','综测列表');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $id=Session::get('user_id');
        $teachList = TeacherModel::get($id);
        $where['cid'] = array('in',$teachList['cid']);

        $stuList = StudentModel::where($where)->order('id','asc')->select();
        $stuScoList = StuscoModel::order('id','asc')->select();
        $gradeList = Grade::where($where)->column('cid,yid,gradeName','cid');
        $evalCon = StuEvalModel::get(['status'=>0]);


        $this -> view -> assign('stuList',$stuList);
        $this -> view -> assign('stuScoList',$stuScoList);
        $this -> view -> assign('gradeList',$gradeList);
        $this -> view -> assign('evalCon',$evalCon);
        return $this -> view -> fetch('eval_list');
    }

	



}
