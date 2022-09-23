<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use think\Session;
use app\index\controller\Index;
use app\index\model\Grade;
use app\index\model\Major;
use app\index\model\Student_info as StudentModel;
use app\index\model\Attend as AttendModel;

use think\Loader;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use PHPExcel_Writer_Excel5;
use PHPExcel_Writer_Excel2007;
use user;

//admin-考勤管理
class Adattend extends Base
{
	//考勤管理-考勤管理
	public function attendList()
	{
	 $this -> view -> assign('title','考勤管理');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

	 $gradeList=Grade::where(['status'=>0])->order('yid','asc')->select();//所有

	 $yidlist=Grade::Distinct(true)->where(['status'=>0])->field('yid')->order('yid','asc')->select();//年

	 $this -> view -> assign('yidlist',$yidlist);
	 $this -> view -> assign('gradelist',$gradeList);
	 return $this -> view -> fetch('attend_list');
	}

	//考勤-渲染编辑界面
	public function attendEdit(Request $request)
	{
	 
	 /*$this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');


	 $cid = trim($request -> param('cid'));
	 $classInfo = trim($request -> param('classInfo'));
	 $this -> view -> assign('title',$classInfo);
	 
	 $List=AttendModel::where(['cid'=>$cid])->order('id','asc')->select();//所有
		
	 $this -> view -> assign('classInfo',$classInfo);
	 $this -> view -> assign('list',$List);
	 $this -> view -> assign('cid',$cid);    //将cid存储到当前地址
	 return $this ->view ->fetch('attend_edit');*/

	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

     $minDate = strtotime($request->param('datemin'));
     $max = strtotime($request->param('datemax'));
     $maxDate = strtotime("+1day",$max);
     $key = $request->param('key');
     $status = (int)$request->param('status');


     if ($minDate!='' and $maxDate!='' and $key!=''){
         if ($status == 0){
             $where['courseName'] = array('like','%'.$key.'%');
         }else{
             $where['userName'] = array('like','%'.$key.'%');
         }
         $where['date'] = array('between',array($minDate,$maxDate));
     }

	 $cid = trim($request -> param('cid'));
     $where['cid'] = array('eq',$cid);

	 $classInfo = trim($request -> param('classInfo'));
	 $this -> view -> assign('title',$classInfo);

	 $pageSize = 10;

	 $List = AttendModel::where($where)->order('id','asc')->paginate($pageSize);//所有

	 $this -> view -> assign('classInfo',$classInfo);

	 $this -> view -> assign('list',$List);
	 $this -> view -> assign('cid',$cid);

	 return $this ->view ->fetch('attend_edit');
	}

	//考勤-渲染编辑界面
	public function searchInfo(Request $request)
	{

	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');
	 $status = trim($request -> param('status'));
	 $this -> view -> assign('title',$status);
	 return $this ->view ->fetch('user_admin_add');

	 /*
	 $classInfo = trim($request -> param('classInfo'));

	 $cid = trim($request -> param('cid'));
	 //$data = $request -> param();
	 $keyid=trim($request -> param('status'));
	 $datemin=strtotime(trim($request -> param('datemin')));
	 $datemax=strtotime(trim($request -> param('datemax')));
	 $courseName=trim($request -> param('key'));


	 //课程名称
	 if($keyid=="0"){
		$courseName=trim($request -> param('key'));
		$List=AttendModel::where(['cid'=>$cid,'courseName'=>$courseName])->order('id','asc')->select();//所有

	 } else {
		$userName=trim($request -> param('key'));
	 }
	 $List=AttendModel::where(['cid'=>$cid])->order('id','asc')->select();//所有
	 //$List=AttendModel::where(['cid'=>$cid,'courseName'=>$courseName])->order('id','asc')->select();//所有
	 $this -> view -> assign('title',$classInfo);
	 $this -> view -> assign('classInfo',$classInfo);
	 $this -> view -> assign('list',$List);
	 $this -> view -> assign('cid',$cid);    //将cid存储到当前地址
	 return $this ->view ->fetch('attend_edit');
	 */
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

	//excel导入函数
	public function exportExcel($columName, $list, $setTitle='Sheet1', $fileName='demo')
	{
    if ( empty($columName) || empty($list) ) {
        return '列名或者内容不能为空';
    }
    
    if ( count($list[0]) != count($columName) ) {
        return '列名跟数据的列不一致';
    }
    Loader::import('PHPExcel.PHPExcel');
    Loader::import('PHPExcel.PHPExcel.PHPExcel_IOFactory');
    Loader::import('PHPExcel.PHPExcel.PHPExcel_Cell');
    $PHPExcel    =    new \PHPExcel();
	
    //获得当前sheet对象
    $PHPSheet    =    $PHPExcel-> getActiveSheet();
    $PHPSheet->setTitle($setTitle);
    $letter        =    [
        'A','B','C','D','E','F','G','H','I','J','K','L','M',
        'N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
    ];
    for ($i=0; $i < count($list[0]); $i++) {
        //$letter[$i]1 = A1 B1 C1  $letter[$i] = 列1 列2 列3
        $PHPSheet->setCellValue("$letter[$i]1","$columName[$i]");
    }
    //内容第2行开始
    foreach ($list as $key => $val) {
        //array_values 把一维数组的键转为0 1 2 3 ..
        foreach (array_values($val) as $key2 => $val2) {
            //$letter[$key2].($key+2) = A2 B2 C2 ……
            $PHPSheet->setCellValue($letter[$key2].($key+2),$val2);
        }
    }
	//居中 
	for($i=1;$i<count($list)+2;$i++){
	$PHPExcel->getActiveSheet()->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
	}
	$styleArray = array(  
        'borders' => array(  
            'allborders' => array(  
                //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的  
                'style' => \PHPExcel_Style_Border::BORDER_THIN,//细边框  
                //'color' => array('argb' => 'FFFF0000'),  
            ),  
        ),  
    );  
	$PHPExcel->setActiveSheetIndex(0);
	$activeSheet = $PHPExcel->getActiveSheet();
	for($i=1;$i<count($list)+2;$i++){
	$activeSheet->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->applyFromArray($styleArray);
	if($i==1){
			$PHPExcel->getActiveSheet()->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->getFont()->setName('Candara');
			//$PHPExcel->getActiveSheet()->getStyle( 'A1')->getFont()->setColor( new \PHPExcel_Style_Color(\PHPExcel_Style_Color::COLOR_YELLOW ) );
			$PHPExcel->getActiveSheet()->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->getFont()->setSize(14);
			$PHPExcel->getActiveSheet()->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->getFont()->setBold(true);
			$PHPExcel->getActiveSheet()->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
			$PHPExcel->getActiveSheet()->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->getFill()->getStartColor()->setARGB('FFC6EF6E');
	}else {
	if($i%2!=0){
		$PHPExcel->getActiveSheet()->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
		$PHPExcel->getActiveSheet()->getStyle("A".$i.":".$letter[count($list[0])-1].$i)->getFill()->getStartColor()->setARGB('FFCDCDCD');
	}else {
		continue;
	}
	}
	}
	//列宽
	$PHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
	$PHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(13);
	$PHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
	$PHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(35);
	$PHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	//$PHPExcel->getActiveSheet()->getStyle('A2:N2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);//文字居中
    //生成2007版本的xlsx
    $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
	//$PHPWriter=PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$fileName.'.xlsx');
    header('Cache-Control: max-age=0');
    $PHPWriter->save("php://output");
	}

	public function importExcel(Request $request)
	{
		$data=$request ->param();
		if(count($data)==0)
		{
			echo "<script>
								alert(\"无数据导出，请检查\");
								setTimeout(function () {window.history.back(-1);}, 1000);
							</script>";
				return ;
		}
		$tot=array();
		for($index=0;$index<count($data);$index++)
		{
		   $col= explode(',',$data[$index]);
		   $tot[]=$col;
		}
		 $columName    =    ['学号','姓名','日期','课程名称','状态'];
		 $list=$tot;
		 $this->exportExcel($columName,$list,'Sheet1',user\ExcelName::IMP_OUT);
	}

	 //添加大量-学生
	public function attendImt(Request $request)
	{
		$data=$request->param();
		$this -> view -> assign('title','批量添加学生');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');

		//$gradeList=Grade::where(['status'=>0])->order('yid','asc')->select();//所有
		//foreach ($gradeList as &$v){
       //     $v['class']=$v['yid']."级".$v['gradeName'];
        //}
		$this -> view -> assign('gradeList',$data['classInfo']);
		$this -> view -> assign('cid',$data['cid']);
		return $this ->view ->fetch('attend_excelImt');
	}

	//excel import
    public function excelAttend(Request $request)
    {

        $cid = (int)$request -> param('cid');
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
            $keys = array( 'studentId', 'userName','date','courseName','status');
            foreach ($re as $i => $vals) {
                $re[$i] = array_combine($keys, $vals);
                $re[$i]['cid'] = $cid;
				$time = \PHPExcel_Shared_Date::ExcelToPHP($re[$i]['date']);
				//str_replace("/","-",$re[$i]['date']);
				$re[$i]['date']=$time;
				//转int
            }
            //遍历数组写入数据库
			$t=count($re);
			$l=0;
            for ($i = 1; $i < count($re)+1; $i++) {
                $data = $re[$i];
                $res = AttendModel::create($data);
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
