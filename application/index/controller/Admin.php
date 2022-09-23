<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use app\index\model\Admin_info as AdminModel;
use app\index\model\Teacher_info as TeacherModel;
use app\index\model\Student_info as StudentModel;
use app\index\model\Grade;
use app\index\model\Major;
use think\Session;
use think\Loader;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use PHPExcel_Writer_Excel5;
use PHPExcel_Writer_Excel2007;

//admin-用户管理
class Admin extends Base
{

	//用户管理-管理员列表
	public function adminList()
	{
	 $this -> view -> assign('title','管理员列表');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

	 $this -> view -> count =AdminModel::count();

	 $role=Session::get('user_info.role');
	 $adminId=Session::get('user_info.adminId');
	 
	 if($role == 3){
	 	 $List=AdminModel::order('id','asc')->select();
	 } else {
	 	 $List=AdminModel::all(['adminId' => $adminId]);
	 }	
	 $this -> view -> assign('list',$List);
	 return $this -> view -> fetch('user_admin_list');
	}

	//用户管理-教师列表
	public function teacherList()
	{
	 $this -> view -> assign('title','教师列表');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

	 $this -> view -> count =TeacherModel::count();

	 $List=TeacherModel::order('id','asc')->select();

	 foreach ($List as &$v){
			if($v['cid']==null)continue;
			$cids= explode(',',$v['cid']);
			$info="";
			for($index=0;$index<count($cids);$index++)
			{
			   $item_cls = Grade::get($cids[$index]);
			   $info=$info.$item_cls['yid']."级".$item_cls['gradeName']."，";
			}
			$info=substr($info, 0, -3);
			$v['cid']=$info;
     }
	 $this -> view -> assign('list',$List);
	 return $this -> view -> fetch('user_teacher_list');
	}
	
	//用户管理-学生列表
	public function studentList(Request $request)
	{

	 $this -> view -> assign('title','学生列表');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

	 $status=$request -> param('status');
	 $keyw=$request -> param('key');


	 $List=StudentModel::order('id','asc')->select();
	 foreach($List as &$l){
			$info=Grade::get($l['cid']);
			$l['cid']=$info['yid']."级".$info['gradeName'];
			if($l['cid']==null)$l['cid']="-";
			$info=Major::get($l['mid']);
			$l['mid']=$info['majorName'];
			if($l['mid']==null)$l['mid']="-";
	 }
	  //$List=StudentModel::order('id','asc')->select();
	 $this -> view -> assign('list',$List);
	 return $this -> view -> fetch('user_student_list');
	}



	//状态变更
	public function setStatus(Request $request)
	{
		$user_id = $request -> param('id');
		$role = $request -> param('role');

		if($role >1){//管理员
		$result =AdminModel::get($user_id);
		if($result->getData('status') == 1){
			AdminModel::update(['status'=>0],['id'=>$user_id]);
		} else {
			AdminModel::update(['status'=>1],['id'=>$user_id]);
		}
		} else if($role == 1){//教师
		$result =TeacherModel::get($user_id);
		if($result->getData('status') == 1){
			TeacherModel::update(['status'=>0],['id'=>$user_id]);
		} else {
			TeacherModel::update(['status'=>1],['id'=>$user_id]);
		}
		} else {//学生
		$result =StudentModel::get($user_id);
		if($result->getData('status') == 1){
			StudentModel::update(['status'=>0],['id'=>$user_id]);
		} else {
			StudentModel::update(['status'=>1],['id'=>$user_id]);
		}
		}
		
	}

	//渲染-编辑管理员界面
	public function adminEdit(Request $request)
	{
		$user_id = $request -> param('id');
		$result = AdminModel::get($user_id);
		$this -> view -> assign('keywords', '12341');
		$this -> view -> assign('title','添加管理员');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		$this -> view -> user_info =$result->getData();
		return $this ->view ->fetch('user_admin_edit');
	}
	//渲染-编辑教师界面
	public function teacherEdit(Request $request)
	{
		$user_id = $request -> param('id');
		$result = TeacherModel::get($user_id);
		$this -> view -> assign('keywords', '12341');
		$this -> view -> assign('title','添加管理员');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		$gradeList=Grade::where(['status'=>0])->order('yid','asc')->select();//所有
        $teachCid = explode(',', $result['cid']);
		foreach ($gradeList as &$v){
            $v['class']=$v['yid']."级".$v['gradeName'];
        }

		$this -> view -> assign('gradeList',$gradeList);
		$this -> view -> assign('teachCid',$teachCid);
		$this -> view -> user_info =$result->getData();
		return $this ->view ->fetch('user_teacher_edit');
	}

	//渲染-编辑学生界面
	public function studentEdit(Request $request)
	{
		$user_id = $request -> param('id');
		$result = StudentModel::get($user_id);
		$result['cls']="-";
		$result['cls']="111";
		$info=Major::get($result['mid']);
		$result['cls']=$info['majorName'];
		if($result['mid']==null)$result['cls']="-";
		$this -> view -> assign('keywords', '12341');
		$this -> view -> assign('title','添加管理员');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		$this -> view -> assign('user_info',$result);
		return $this ->view ->fetch('user_student_edit');
	}
	//管理员-删除
	public function deleteUser(Request $request)
	{
		$user_id = $request -> param('id');
		//$res=AdminModel::destroy($user_id);
		$result = AdminModel::get($user_id);
		$result->delete(true);
	}

	//教师-删除
	public function deleteTeacherUser(Request $request)
	{
		$user_id = $request -> param('id');
		$result = TeacherModel::get($user_id);
		$result->delete(true);
	}

	//学生-删除
	public function deleteStudentUser(Request $request)
	{
		$user_id = $request -> param('id');
		$result = StudentModel::get($user_id);
		$result->delete(true);
	}
	//添加-管理员
	public function adminAdd(Request $request)
	{
		$this -> view -> assign('title','添加管理员');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		return $this ->view ->fetch('user_admin_add');
	}

	//添加-教师
	public function teacherAdd(Request $request)
	{
		$this -> view -> assign('title','添加管理员');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		return $this ->view ->fetch('user_teacher_add');
	}
	//添加-学生
	public function studentAdd(Request $request)
	{
		$this -> view -> assign('title','添加学生');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		return $this ->view ->fetch('user_student_add');
	}
	
	//添加大量-学生
	public function studentBatchAdd(Request $request)
	{
		$this -> view -> assign('title','批量添加学生');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');

		$gradeList=Grade::where(['status'=>0])->order('yid','asc')->select();//所有
		foreach ($gradeList as &$v){
            $v['class']=$v['yid']."级".$v['gradeName'];
        }
		$this -> view -> assign('gradeList',$gradeList);

		return $this ->view ->fetch('user_student_excelImport');
	}
	//更新教师数据操作
    public function editTeacher(Request $request)
    {
        //获取表单返回的数据
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)||$key=='status'){  //避免选择器出现问题 
                $data[$key] = $value;
            }
        }
		unset($data['gradew']);
        $condition = ['id'=>$data['id']] ;
        $result = TeacherModel::update($data, $condition);

        if (true == $result) {
            return ['status'=>1, 'message'=>"更新成功"];
        } else {
            return ['status'=>0, 'message'=>'更新失败'];
        }
    }

	//更新管理员数据操作
    public function editUser(Request $request)
    {
        //获取表单返回的数据
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)||$key=='status'||$key=='role'){  //避免选择器出现问题 
                $data[$key] = $value;
            }
        }

        $condition = ['id'=>$data['id']] ;
        $result = AdminModel::update($data, $condition);

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败'];
        }
    }

	//更新学生数据操作
    public function editStudent(Request $request)
    {
        //获取表单返回的数据
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)||$key=='status'||$key=='gender'){  //避免选择器出现问题 
                $data[$key] = $value;
            }
        }

        $condition = ['id'=>$data['id']] ;
        $result = StudentModel::update($data, $condition);

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败'];
        }
    }

	//检测管理员工号是否可用
    public function checkUserName(Request $request)
    {
        $adminId = trim($request -> param('adminId'));
        $status = 1;
        $message = '工号可用';
        if (AdminModel::get(['adminId'=> $adminId])) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '工号重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

	//检测教师工号是否可用
    public function checkTeacherName(Request $request)
    {
        $teacherId = trim($request -> param('teacherId'));
        $status = 1;
        $message = '工号可用';
        if (TeacherModel::get(['teacherId'=> $teacherId])) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '工号重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

	//检测学生学号是否可用
    public function checkStudentName(Request $request)
    {
        $studentId = trim($request -> param('studentId'));
        $status = 1;
        $message = '学号可用';
        if (StudentModel::get(['studentId'=> $studentId])) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '学号重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }


	//管理员添加操作
    public function addUser(Request $request)
    {
        $data = $request -> param();
        $status = 0;
        $message = '添加成功';

        $rule = [
            'adminId|工号' => "require|min:3|max:10",
            'password|密码' => "require|min:6|max:10"
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true) {
            $user= AdminModel::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
        }


        return ['status'=>$status, 'message'=>$message];
    }

	//教师添加操作
    public function addTeacher(Request $request)
    {
        $data = $request -> param();
        $status = 0;
        $message = '添加成功';

        $rule = [
            'teacherId|教师工号' => "require|min:3|max:10",
            'password|密码' => "require|min:6|max:10"
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true) {
            $user= TeacherModel::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
        }else {
		  $message =  '添加失败，长度不符合~~';
		}
        return ['status'=>$status, 'message'=>$message];
    }

	//学生添加操作
    public function addStudent(Request $request)
    {
        $data = $request -> param();
        $status = 0;
        $message = '添加成功';

        $rule = [
            'studentId|学生学号' => "require|min:3|max:12",
            'password|密码' => "require|min:6|max:10"
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true) {
            $user= StudentModel::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
        } else {
                $message = '添加失败~~';
		}

        return ['status'=>$status, 'message'=>$message];
    }
	//学生大量删除
    public function batchDStu(Request $request)
    {
        $data = $request -> param();
        $status = 0;
		if($data['data'][0]=="学号"){
			unset($data['data'][0]);
		}

		$condition = 'studentId in('.implode(',',$data['data']).')';  
		$list=StudentModel::where($condition)->delete();
		if($list!==false) {
			$message="成功删除{$list}条！";
			$status=1;
		}else{   
			$message="删除失败！"; 
		} 
        return ['status'=>$status, 'message'=>$message];
    }


	//excel import
    public function excelAddStudent(Request $request)
    {

        $cid = (int)$request -> param('cid');
		$inf=Grade::get($cid);
		$mid = $inf['mid'];
        $file = $request->file('excel');
        Loader::import('PHPExcel.PHPExcel');
        Loader::import('PHPExcel.PHPExcel.PHPExcel_IOFactory');
        Loader::import('PHPExcel.PHPExcel.PHPExcel_Cell');
        //实例化PHPExcel
        $objPHPExcel = new \PHPExcel();

        if ($file) {

            $file_types = explode(".", $_FILES ['excel'] ['name']); // ["name"] => string(25) "excel文件名.xls"
            $file_type = $file_types [count($file_types) - 1];//xls后缀
            $file_name = $file_types [count($file_types) - 2];//xls去后缀的文件名
            /*判别是不是.xls文件，判别是不是excel文件*/
            if (strtolower($file_type) != "xls" && strtolower($file_type) != "xlsx") {
                echo '不是Excel文件，重新上传';
                die;
            }

            $info = $file->move(ROOT_PATH . 'public' . DS . 'excel');//上传位置
            $path = ROOT_PATH . 'public' . DS . 'excel' . DS;
            $file_path = $path . $info->getSaveName();//上传后的EXCEL路径
            //echo $file_path;//文件路径

            //获取上传的excel表格的数据，形成数组
            $re = $this->actionRead($file_path, 'utf-8');
            array_splice($re, 1, 0);
            unset($re[0]);
            //dump($re); //查看数组

            /*将数组的键改为自定义名称*/
            $keys = array( 'studentId', 'userName');
            foreach ($re as $i => $vals) {
                $re[$i] = array_combine($keys, $vals);
                $re[$i]['cid'] = $cid;
				$re[$i]['mid'] = $mid;
            }
            //遍历数组写入数据库
			$t=count($re);
			$l=0;
            for ($i = 1; $i < count($re)+1; $i++) {
                $data = $re[$i];
				if(StudentModel::get(['studentId'=>$re[$i]['studentId']])){
					$l++;
					continue;
				}
                $res = StudentModel::create($data);
            }
			$s=$t-$l;
			if(isset($res)){
				if ($res){
					echo "<script>
								alert(\"导入数据：\"+$t+\"，成功：\"+$s+\"，失败：\"+$l+\"。\");
								setTimeout(function () {parent.window.location.reload();}, 400);
							</script>";
				}
			}else {
					
				echo "<script>
                            setTimeout(function () {parent.window.location.reload();}, 400);
							alert(\"导入数据：\"+$t+\"，成功：\"+$s+\"，失败：\"+$l+\"。\");
                        </script>";
			}
        }
    }



	public function actionRead($filename,$encode='utf-8'){
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        //return $highestRow;
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        //var_dump($highestColumnIndex);
        $excelData = array();
        for($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][]=(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }
	

}
