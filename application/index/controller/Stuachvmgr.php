<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use think\Session;
use app\index\controller\Index;
use app\index\model\Grade;
use app\index\model\Student_info as StudentModel;
use app\index\model\Student_award as AwardModel;
use app\index\model\Student_score as StuscoModel;
use app\index\model\Student_eval as StuEvalModel;
use app\index\model\Student_post as StuPostModel;
use app\index\model\Eval_data as EvalDataModel;

//stu-成绩管理管理
class Stuachvmgr extends Base{
    //综合成绩单
    public function achvList(){
        $this -> view -> assign('title','成绩列表');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $stu_name=Session::get('user_info.userName');
        $stu_id=Session::get('user_info.studentId');
        $list = StuscoModel::where('studentId',$stu_id)->order('id','asc')->select();
        $evalCon = StuEvalModel::get(['status'=>0]);
        $this -> view -> count =count($list);
        $this -> view -> stuId =$stu_id;
        $this -> view -> name =$stu_name;
        $this -> view -> assign('list',$list);
        $this -> view -> assign('evalCon',$evalCon);
        return $this -> view -> fetch('stusco_list');
    }

    public function evalList(){
        $this -> view -> assign('title','综合测试');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $stu_name=Session::get('user_info.userName');
        $stu_id=Session::get('user_info.studentId');
        $list = StuscoModel::where('studentId',$stu_id)->order('id','asc')->select();
        $evalCon = StuPostModel::get(['status'=>0]);
        $this -> view -> count =count($list);
        $this -> view -> stuId =$stu_id;
        $this -> view -> name =$stu_name;
        $this -> view -> assign('list',$list);
        $this -> view -> assign('evalCon',$evalCon);
        return $this -> view -> fetch('stuEval_list');
    }

    /* 测评材料列表 */
    public function stuEvalData(){
        $this -> view -> assign('title','测评资料');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $stu_name=Session::get('user_info.userName');
        $stu_id=Session::get('user_info.studentId');

        $date = date('Y');
        $evalYear = array();
        for ($i=1;$i<4;$i++){
            $evalYear[$i] = ($date - $i).'-'.($date - ($i-1));
        }

        $where['studentId'] = $stu_id;
        $evalDataList = EvalDataModel::where($where)->select();
        $this -> view -> name = $stu_name;
        $this -> view -> studentId = $stu_id;
        $this -> view -> assign('evalList',$evalDataList);
        $this -> view -> assign('evalYear',$evalYear);

        return $this -> view -> fetch('evalData_list');
    }

    /* 测评材料图片查看 */
    public function checkPic(Request $request){
        $this -> view -> assign('title','查看证明');
        $this -> view -> assign('keywords','教学管理系统');
        $this -> view -> assign('desc','教学案例');

        $eval_id = $request -> param('id');
        $eval = EvalDataModel::get(['id'=>$eval_id]);
        $this -> view -> assign('evalPic',$eval['evalProve']);
        return $this ->view ->fetch('stu_checkPic');
    }

    /* 测评材料上传 */
    public function uploadEval(Request $request){
        //获取表单返回的数据
        $param = $request -> param();
        $file = request() ->file('evalPic');
        if($file){
            $info = $file->validate(['size'=>2000000,'ext'=>'jpg,png'])->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'evalData',true,false);
            if($info){
                $filename="/uploads/evalData\\".$info->getFilename();
            }else{
                return ['status'=>0, 'message'=>'图片上传失败'];
            }
        }else{
            $filename="";
        }

        if (empty($param['evalName'])){
            return ['status'=>0, 'message'=>'材料名称为空！'];
        }
        if(empty($filename)){
            return ['status'=>0, 'message'=>'材料证明为空！'];
        }

        $data=[
            'studentId'=>$param['evalStu'],
            'userName'=>$param['evalUser'],
            'evalName'=>$param['evalName'],
            'evalProve'=>$filename,
            'evalType'=>$param['evalType'],
            'evalYear'=>$param['evalYear'],
        ];

        $result = EvalDataModel::create($data);

        if (true == $result) {
            return ['status'=>1, 'message'=>'上传成功'];
        } else {
            return ['status'=>0, 'message'=>'上传失败'];
        }
    }

    /* 保研材料上传 */
    public function uploadAward(Request $request){
        //获取表单返回的数据
        $param = $request -> param();
        $file = request() ->file('awardPic');
        if($file){
            $info = $file->validate(['size'=>2000000,'ext'=>'jpg,png'])->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'awardPic',true,false);
            if($info){
                $filename="/uploads/awardPic\\".$info->getFilename();
            }else{
                return ['status'=>0, 'message'=>'图片上传失败'];
            }
        }else{
            $filename="";
        }

        if (empty($param['award'])){
            return ['status'=>0, 'message'=>'奖项名称为空！'];
        }
        if(empty($filename)){
            return ['status'=>0, 'message'=>'奖项证明为空！'];
        }
        if (empty($param['awardType'])){
            return ['status'=>0, 'message'=>'奖项类型为空！'];
        }

        $data=[
            'studentId'=>$param['awardStu'],
            'userName'=>$param['awardUser'],
            'award'=>$param['award'],
            'awardProve'=>$filename,
            'awardType'=>$param['awardType'],
        ];

        $result = AwardModel::create($data);

        if (true == $result) {
            return ['status'=>1, 'message'=>'上传成功'];
        } else {
            return ['status'=>0, 'message'=>'上传失败'];
        }
    }

}
