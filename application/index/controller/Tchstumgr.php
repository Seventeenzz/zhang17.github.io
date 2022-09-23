<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use think\Session;
use app\index\controller\Index;
use app\index\model\Grade;
use app\index\model\Major;
use app\index\model\Student_info as StudentModel;
use app\index\model\Teacher_info as TeacherModel;
use app\index\model\Attend as AttendModel;

//teacher-学生管理
class Tchstumgr extends Base
{
	//学生管理-学生列表
	public function stuInfoList()
	{
	 $this -> view -> assign('title','学生列表');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

	 $id=Session::get('user_id');
	 $teachList = TeacherModel::get($id);
     $cidArr = $teachList['cid'];
	 $where['cid'] = array('in',$cidArr);
	 $List=StudentModel::where($where)->order('id','asc')->select();
	 $this -> view -> assign('list',$List);
	 return $this -> view -> fetch('stumgr_stulist');
	}

	//学生管理-详细
	public function stuDtl(Request $request)
	{
		$user_id = $request -> param('id');
		$result = StudentModel::get($user_id);

		$mr=Major::get($result['mid']);
		$result['major']=$mr['majorName'];

		$ge=Grade::get($result['cid']);
		$result['grade']=$ge['yid']."级".$ge['gradeName'];

		$this -> view -> assign('keywords', '12341');
		$this -> view -> assign('title','添加管理员');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		$this -> view -> user_info =$result->getData();
		return $this ->view ->fetch('stumgr_studtl');
	}

	//考勤管理-考勤列表
	public function attendList(Request $request)
	{
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');
	 $status = trim($request -> param('status'));
	 $this -> view -> assign('title',$status);
	 return $this ->view ->fetch('user_admin_add');

	}

	//渲染添加-个人考勤信息
	public function personalAdd(Request $request)
	{
		$this -> view -> assign('title','添加个人考勤信息');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');

		$cid = trim($request -> param('cid'));
		
		$info=Grade::get(['cid'=> $cid]);
		$yclass=$info['yid']."级".$info['gradeName'];

		$this -> view -> assign('yclass',$yclass);
		$this -> view -> assign('cid',$cid);    //将cid存储到当前地址


		return $this ->view ->fetch('attend_personaladd');
	}

	//提交-添加个人考勤信息 
    public function addPersonalInfo(Request $request)
    {
        $data = $request -> param();
        $status = 1;
        $message = '添加成功';

        $rule = [
            'studentId|学号' => "require|min:3|max:20",
			'courseName|课程名称' => "require|min:3|max:20",
        ];
		

        $result = $this -> validate($data, $rule);
		$data['date'] =strtotime($data['date']);
		//$data['date'] = $data['date'].replace('-','/');
		//$data['date'] =new Date($data['date']).getTime();

		if ($result === true) {
			$user= AttendModel::create($data);
			if ($user === null) {
            $status = 0;
            $message = '添加失败~~';
		 }
		 } else {
		 $status = 0;
		 $message = '添加失败11~~';
		 }

        return ['status'=>$status, 'message'=>$message];
    }




	//状态变更
	public function setStatus(Request $request)
	{
		$id = $request -> param('id');
		
		$result =AttendModel::get($id);
		if($result->getData('status') == 1){
			AttendModel::update(['status'=>0],['id'=>$id]);
		} else {
			AttendModel::update(['status'=>1],['id'=>$id]);
		}
	}

	//考勤编辑-删除
	public function deleteInfo(Request $request)
	{
		$id = $request -> param('id');
		$result = AttendModel::get($id);
		$result->delete(true);
	}



}
