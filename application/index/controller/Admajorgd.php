<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use app\index\model\Major;
use app\index\model\Grade;
use think\Session;
use app\index\model\Admin_info as AdminModel;
use app\index\controller\Index;
use app\index\model\Student_info as StudentModel;
use app\index\model\Teacher_info as TeacherModel;

//admin-专业与班级
class Admajorgd extends Base
{
	//专业与班级-专业列表
	public function majorList()
	{
		
	 $this -> view -> assign('title','专业列表');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

	 $this -> view -> count =Major::count();

	 $role=Session::get('user_info.role');
	 if($role > 1){
	 $List=Major::order('mid','asc')->select();


	 $this -> view -> assign('list',$List);
	 return $this -> view -> fetch('major_list');
	 }
	 
	}

	//专业与班级-班级列表
	public function gradeList()
	{
	 $this -> view -> assign('title','班级列表');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');
	 $this -> view -> count =Grade::count();
	 $role=Session::get('user_info.role');
	 if($role > 1){
	 $List=Grade::order('cid','asc')->select();
	 

	 foreach ($List as &$v){
			$lt=StudentModel::all(['cid'=>$v['cid']]);
			$v['num']=count($lt);
			//查询辅导员
			$v['tchName']="-";
			$tch_info=TeacherModel::all();
			foreach ($tch_info as &$t){
			if($t['cid']==null)continue;
			$cids= explode(',',$t['cid']);
			for($index=0;$index<count($cids);$index++)
			{
			   if($v['cid']==$cids[$index]){
			   $v['tchName']=$t['userName'];
			   break;
			   }
			}
			}

			//查询专业
			$v['major']="-";
			$item_m=Major::get($v['mid']);
			if($item_m){
			$v['major']=$item_m['majorName'];
			}
        }
	 $this -> view -> assign('list',$List);
	 return $this -> view -> fetch('grade_list');
	 }
	}


	//渲染添加-专业
	public function majorAdd(Request $request)
	{
		$this -> view -> assign('title','添加专业');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		return $this ->view ->fetch('major_add');
	}
	//渲染添加-班级界面
	public function gradeAdd(Request $request)
	{
		$this -> view -> assign('title','添加专业');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		$majors=Major::all();
		$this ->view ->assign('majors',$majors);
		return $this ->view ->fetch('grade_add');
	}
	
	//检测专业名称是否可用
    public function checkMajorName(Request $request)
    {
        $majorName = trim($request -> param('majorName'));
        $status = 1;
        $message = '专业名称可用';
        if (Major::get(['majorName'=> $majorName])) {
            //如果在表中查询到该用户名
            $status = 0;
            $message = '专业名称重复,请重新输入~~';
        }
        return ['status'=>$status, 'message'=>$message];
    }

	//专业添加操作
    public function addMajor(Request $request)
    {
        $data = $request -> param();
        $status = 0;
        $message = '添加成功';

        $rule = [
            'majorName|专业' => "require|min:3|max:18"
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true) {
            $user= Major::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
        } else {
		  $message =  '添加失败，长度不符合~~';
		}

        return ['status'=>$status, 'message'=>$message];
    }
	
	//专业班级操作
    public function addGrade(Request $request)
    {
        $data = $request -> param();
        $status = 1;
        $message = '添加成功';

        $rule = [
            'yid|年份' => "require|min:3|max:18",
			'gradeName|班级名称' => "require|min:3|max:18",
        ];

        $result = $this -> validate($data, $rule);
		if ($result === true) {
		 if (Grade::get(['gradeName'=> $data['gradeName'],'yid'=> $data['yid']])) {
                $message = '已经存在该班级,添加失败~~';
		 } else {
				$user= Grade::create($request->param());
				if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
		 }
		 } else {
		 $message = '添加失败~~';
		 }

        return ['status'=>$status, 'message'=>$message];
    }

	//专业-删除
	public function deleteMajor(Request $request)
	{
		$mid = $request -> param('mid');
		$result = Major::get($mid);
		$result->delete(true);
	}

	//专业-删除
	public function deleteGrade(Request $request)
	{
		$cid = $request -> param('cid');
		$result = Grade::get($cid);
		$result->delete(true);
	}

	//渲染-编辑专业界面
	public function majorEdit(Request $request)
	{
		$mid = $request -> param('mid');
		$result = Major::get($mid);
		$this -> view -> assign('title','编辑专业');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		$this -> view -> major_info =$result->getData();
		return $this ->view ->fetch('major_edit');
	}

	//渲染-编辑班级界面
	public function gradeEdit(Request $request)
	{
		$cid = $request -> param('cid');
		$result = Grade::get($cid);
		$this -> view -> assign('title','编辑专业');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');
		$this -> view -> grade_info =$result->getData();
		return $this ->view ->fetch('grade_edit');
	}

	//更新专业数据操作
    public function editMajor(Request $request)
    {
        //获取表单返回的数据
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)){  //避免选择器出现问题 
                $data[$key] = $value;
            }
        }

        $condition = ['mid'=>$data['mid']] ;
        $result = Major::update($data, $condition);

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败'];
        }
    }

	//更新专业数据操作
    public function editGrade(Request $request)
    {
        //获取表单返回的数据
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)){  //避免选择器出现问题 
                $data[$key] = $value;
            }
        }

        $condition = ['cid'=>$data['cid']] ;
        $result = Grade::update($data, $condition);

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败'];
        }
    }

	//状态变更
	public function setStatus(Request $request)
	{
		$cid = $request -> param('cid');
		
		$result =Grade::get($cid);
		if($result->getData('status') == 1){
			Grade::update(['status'=>0],['cid'=>$cid]);
		} else {
			Grade::update(['status'=>1],['cid'=>$cid]);
		}
	}

	
	public function classInfo(Request $request)
	{
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

     $cid = $request->param('cid');
	 $where = ['cid'=>$cid] ;
	 $List=StudentModel::where($where)->select();
	 foreach($List as &$l)
	 {
		$i=Major::Get($l['mid']);
		$l['mor']=$i['majorName'];
	 }

	 $this -> view -> assign('list',$List);

	 $List=Grade::Get($cid);
     $List['class']=$List['yid']."级".$List['gradeName'];
	 $this -> view -> assign('clsName',$List['class']);
	 return $this ->view ->fetch('grade_class');
	}





}
