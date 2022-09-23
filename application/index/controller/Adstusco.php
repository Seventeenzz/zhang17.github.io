<?php
namespace app\index\controller;
use app\index\controller\Base;
use app\index\model\Eval_data;
use think\Request;
use think\Session;
use app\index\controller\Index;
use app\index\model\Grade;
use app\index\model\Admin_info as AdminModel;
use app\index\model\Student_info as StudentModel;
use app\index\model\Student_award as AwardModel;
use app\index\model\Student_score as StuscoModel;
use app\index\model\Student_eval as StuEvalModel;
use app\index\model\Student_post as StuPostModel;
use app\index\model\Eval_data as EvalDataModel;

use think\Loader;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use PHPExcel_Writer_Excel5;
use PHPExcel_Writer_Excel2007;
use user;

//admin-学生成绩
class Adstusco extends Base
{
	//成绩列表
	public function stuscoList()
	{
	 $this -> view -> assign('title','成绩列表');
	 $this -> view -> assign('keywords','教学管理系统');
	 $this -> view -> assign('desc','教学案例');

	 $this -> view -> count =StuscoModel::count();
	 $list = StuscoModel::order('id','asc')->select();
	 $this -> view -> assign('list',$list);
	 return $this -> view -> fetch('stusco_list');
	}

	
	//渲染导入成绩单界面
	public function achvImp()
	{
		$this -> view -> assign('title','批量添加学生');
		$this -> view -> assign('keywords','教学管理系统');
		$this -> view -> assign('desc','教学案例');

		return $this ->view ->fetch('stusco_imp');
	}

    /* 学生测评年份 */
    public function evalYear(){
        $this -> view -> assign('title','学生测评');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $gradeList=Grade::where(['status'=>0])->order('yid','asc')->select();//所有

        $yidlist=Grade::Distinct(true)->where(['status'=>0])->field('yid')->order('yid','asc')->select();//年

        $date = date('Y');
        $evalYear = array();
        for ($i=1;$i<4;$i++){
            $evalYear[$i] = ($date - $i).'-'.($date - ($i-1));
        }

        $this -> view -> toYear = $date;
        $this -> view -> assign('yidlist',$yidlist);
        $this -> view -> assign('gradelist',$gradeList);
        $this -> view -> assign('evalYear',$evalYear);

        return $this -> view -> fetch('stusco_evalYear');
    }

    /* 学生测评 */
    public function evaluate(Request $request){
        $this -> view -> assign('title','学生测评');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $classInfo = trim($request -> param('classInfo'));
        $cid = trim($request -> param('cid'));
        $evalYear = trim($request -> param('evalDate'));
//        echo "<br>";
//        dump($evalDate);

        $where['cid'] = array('eq',$cid);

        $this -> view -> count =StuscoModel::count();
        $stuList = StudentModel::order('id','asc')->where($where)->select();
        $gradeList = Grade::column('cid,yid,gradeName','cid');
        $stuScoList = StuscoModel::order('id','asc')->select();
        $evalList = EvalDataModel::order('id','asc')->column('studentId,userName,evalYear,score','id');
        $evalCon = StuEvalModel::get(['status'=>0]);
//        $date = date('Y');
//        $evalYear = array();
//        for ($i=1;$i<4;$i++){
//            $evalYear[$i] = ($date - $i).'-'.($date - ($i-1));
//        }

//        dump($coureYear);
        $this -> view -> years = $evalYear;
        $this -> view -> assign('stuList',$stuList);
        $this -> view -> assign('gradeList',$gradeList);
        $this -> view -> assign('stuScoList',$stuScoList);
        $this -> view -> assign('evalList',$evalList);
        $this -> view -> assign('evalCon',$evalCon);
        return $this -> view -> fetch('stusco_evalList');
    }


	/* 保研测评 */
    public function postEval(){
        $this -> view -> assign('title','保研测评');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $this -> view -> count =StuscoModel::count();
        $stuList = StudentModel::order('id','asc')->select();
        $gradeList = Grade::column('cid,yid,gradeName','cid');
        $stuScoList = StuscoModel::order('id','asc')->select();
        $evalCon = StuPostModel::get(['status'=>0]);
        $this -> view -> assign('stuList',$stuList);
        $this -> view -> assign('gradeList',$gradeList);
        $this -> view -> assign('stuScoList',$stuScoList);
        $this -> view -> assign('evalCon',$evalCon);
        return $this -> view -> fetch('stusco_postEval');
    }

    /* 计算设置 */
    public function methodSet(){
        $this -> view -> assign('title','计算设置');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $list = StuPostModel::order('id','asc')->select();
        $this -> view -> assign('list',$list);
        return $this -> view -> fetch('stusco_methodSet');
    }

    /* 计算设置存库 */
    public function methodSave(Request $request){
        $data = $request -> param();
        $stuEval = new StuPostModel;
        $maxID = $stuEval->max('id');
        $res = $stuEval->where('id','<=',$maxID)->update(['status'=>'1']);

        if ($res){
            $result = $stuEval->save($data);
            if ($result){
                return ['status'=>1, 'message'=>'设置成功'];
            }else{
                return ['status'=>0, 'message'=>'设置失败'];
            }
        }else{
            return ['status'=>0, 'message'=>'设置失败'];
        }
    }
    /* 删方法 */
    public function evalDel(Request $request){
        $eval_id = $request -> param('id');
        StuPostModel::destroy($eval_id);

    }

    /* 学生奖惩列表 */
    public function adAward(){
        $this -> view -> assign('title','学生奖惩');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $awardList = AwardModel::order('id','asc')->select();

        $this -> view -> assign('awardList',$awardList);
        return $this -> view -> fetch('stusco_awardList');
    }

    /* 学生奖惩审核页面 */
    public function awardCheck(Request $request){
        $id = $request -> param('id');
        $awCheck = AwardModel::get($id);

        $this -> view -> assign('awCheck',$awCheck);
        return $this -> view -> fetch('stusco_awardCheck');
    }

    /* 学生奖惩审核 */
    public function awardCheckSave(Request $request){
        $data = $request -> param();

//        if ($data['status'] == 2){
//            AwardModel::update(0,'score');
//        }

        $update = [
            'score' => $data['score'],
            'status' => $data['status'],
        ];

        $condition = ['id' => $data['id']];

        $result = AwardModel::update($update, $condition);

        if (true == $result) {
            return ['status'=>1, 'message'=>'提交成功'];
        } else {
            return ['status'=>0, 'message'=>'提交失败,检查数据正确与否'];
        }

    }

    /* 学生综测资料列表 */
    public function evalDateList(){
        $this -> view -> assign('title','学生奖惩');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $evalDataList = EvalDataModel::order('id','asc')->select();

        $this -> view -> assign('evalDataList',$evalDataList);
        return $this -> view -> fetch('stusco_evalDataList');
    }

    /* 学生综测资料审核页面 */
    public function evalCheck(Request $request){
        $id = $request -> param('id');
        $evalCheck = EvalDataModel::get($id);

        $this -> view -> assign('evalCheck',$evalCheck);
        return $this -> view -> fetch('stusco_evalCheck');
    }

    /* 学生综测资料审核 */
    public function evalCheckSave(Request $request){
        $data = $request -> param();

//        if ($data['status'] == 2){
//            AwardModel::update(0,'score');
//        }

        $update = [
            'score' => $data['score'],
            'status' => $data['status'],
        ];

        $condition = ['id' => $data['id']];

        $result = EvalDataModel::update($update, $condition);

        if (true == $result) {
            return ['status'=>1, 'message'=>'提交成功'];
        } else {
            return ['status'=>0, 'message'=>'提交失败,检查数据正确与否'];
        }

    }

    /* 查看证明 */
    public function checkPic(Request $request){
        $this -> view -> assign('title','查看证明');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $evalData_id = $request -> param('id');
        $evalData = EvalDataModel::get(['id'=>$evalData_id]);
        $this -> view -> assign('evalProve',$evalData['evalProve']);
        return $this ->view ->fetch('admins_checkProve');
    }



	//excel import
    public function excelStuAchv(Request $request)
    {
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
            $keys = array( 'studentId', 'userName','courseNum','courseName',
			'coursePlat','coursePro','courseYear','term','assess','examSco',
			'credit','coursePoint','status','ordSco','makeupSco','restudySco');

            foreach ($re as $i => $vals) {
                $re[$i] = array_combine($keys, $vals);
				//$time = \PHPExcel_Shared_Date::ExcelToPHP($re[$i]['date']);
                if ($re[$i]['coursePlat']=='T通识素质教育'){
                    $re[$i]['coursePlat']='T';
                }elseif($re[$i]['coursePlat']=='Z专业拓展教育'){
                    $re[$i]['coursePlat']='Z';
                }elseif($re[$i]['coursePlat']=='K学科专业教育'){
                    $re[$i]['coursePlat']='K';
                }else{
                    $re[$i]['coursePlat']='S';
                }
                $re[$i]['coursePro']=='B必修' ? $re[$i]['coursePro']='B' : $re[$i]['coursePro']='X';
				$re[$i]['term']=="春季" ? $re[$i]['term']=0:$re[$i]['term']=1; 
				$re[$i]['assess']=="考察" ? $re[$i]['assess']=0:$re[$i]['assess']=1; 
				$re[$i]['status']=="正常" ? $re[$i]['status']=0:$re[$i]['status']=1; 
            }
            //遍历数组写入数据库
			$t=count($re);
			$l=0;
            for ($i = 1; $i < count($re)+1; $i++) {
                $data = $re[$i];
                $res = StuscoModel::create($data);
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
