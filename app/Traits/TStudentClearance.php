<?php
namespace App\Traits;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Es\StudentClearanceOverride;
use App\Es\StudentClearance;
use App\Es\Students;
use App\Es\StudentCurriculums;
use App\Es\EnrollmentSettings;
use App\Es\EnrollmentChecklist;
use App\Hris\Employees;
use App\Hris\Roles;
use App\Es\views\xStudents;
use App\Es\Units;
use App\Es\StudentInfo;
use Illuminate\Support\Facades\Input;

trait TStudentClearance
{
    public function checkOverride($type,$studentID,$year,$id2)
    {
        $d=['override'=>false,'label'=>''];
        $sco = StudentClearanceOverride::select('employees.EmployeeID','employees.FirstName', 'employees.MiddleName', 'employees.LastName')
            ->leftJoin('employees', 'employees.EmployeeID', '=', 'student_clearance_override.override_by')
            ->where('studentID', '=', $studentID)->where('override_type', '=', $type)
            ->where('ayFrom', '=', $this->ayFrom)
            ->where('ayTo', '=', $this->ayTo)
            ->where('semester', '=', $this->semester)->first();
        if ($sco){
            $d['text'] = '<p>Overriden by: ' . $sco->LastName . ', ' . $sco->FirstName.'</p>';
            $d['name']=$sco->LastName . ' ' . $sco->FirstName;
            $d['override']=true;
            $r=$this->hasClearanceOverridePermission();

            if($r && $sco->EmployeeID==Auth::user()->conn_id){
                $d['text'].='<button id="rob'.$type.$year.$id2.'" class="btn btn-sm btn-primary" onclick="removeOverride(\''.$type.'\',this)" data-studentid="'.encrypt($studentID).'">Remove</button><br />';
            }
            $d['label'] =$d['text'];
        }else{
            $d['text'] = '<button id="ob'.$type.$year.$id2.'" class="btn btn-sm btn-primary" onclick="override(\''.$type.'\',this)" data-studentid="'.encrypt($studentID).'">Override</button><br />';
        }
        return $d;
    }

    public function getStudentClearances($student,$ayFrom,$ayTo,$semester,$deanName,$forSSG=false){
        $studentID=$student[0];
        $studentYear=$student[1];
        $studentID2=$student[2];
        $message='';
        $clearance=array_flip(StudentClearance::areas);
        if($forSSG) $clearance=['ssg'=>null];
        $accountantName = Employees::getEmployeeNameByRole(Roles::ACCOUNTANT);
        $librarianName = Employees::getEmployeeNameByRole(Roles::LIBRARIAN);
        $dsaName = Employees::getEmployeeNameByRole(Roles::DSA);
        $registrarName = Employees::getEmployeeNameByRole(Roles::REGISTRAR);
        //$ssgName=$this->getSSGName();
        //all clear
        foreach($clearance as $k=>$ct){
            $check = '<div id="label'.$k. $studentYear . $studentID2 . '">';
            $check.='<small><i class="fa fa-check"></i><br/>';
            switch ($k) {
                case 'registrar':
                    $check.=$registrarName;
                    $officerName=$registrarName;
                    break;
                case 'accounting':
                    $check.=$accountantName;
                    $officerName=$accountantName;
                    break;
                case 'ssg':
                    $check.=$ssgName;
                    $officerName=$ssgName;
                    break;
                case 'library':
                    $check.=$librarianName;
                    $officerName=$librarianName;
                    break;
                case 'dean':
                    $check.=$deanName;
                    $officerName=$deanName;
                    break;
                case 'dsa':
                    $check.=$dsaName;
                    $officerName=$dsaName;
                    break;
                default:
                    break;
            }
            $check.='<small></div>';
            $clearance[$k]=['html'=>$check,'cleared'=>true,'officer'=>$officerName,'message'=>$message];
        }
        //get student clearance
        $clearances=StudentClearance::getClearances($studentID,$ayFrom,$ayTo,$semester);

        //get uncleared areas
        if(count($clearances)>0){
            foreach($clearances as $k=>$c){
                if(!is_null($c['ClearanceType']) && array_key_exists($c['ClearanceType'],$clearance)){
                    $uncheck = '<div id="label'.$c['ClearanceType'] . $studentYear . $studentID2 . '">';
                    if(!is_null($c['Message'])){
                        $uncheck .= '<small><i class="fa fa-times"></i> <font color="red">NOT CLEARED ('.$c['Message'].')</font><br/>';
                    }
                    else{
                        $uncheck .= '<small><i class="fa fa-times"></i> <font color="red">NOT CLEARED</font><br/>';
                    }
                    $officerName='';
                    switch ($c['ClearanceType']) {
                        case 'registrar':
                            //check enrollment checklist
                            //check student grades based on his curriculum
                            //each subject check prerequisite to enroll
                            $uncheck.=$registrarName;
                            $officerName=$registrarName;
                            break;
                        case 'accounting':
                            $uncheck.=$accountantName;
                            $officerName=$accountantName;
                            break;
                        case 'ssg':
                            $uncheck.=$ssgName;
                            $officerName=$ssgName;
                            break;
                        case 'library':
                            $uncheck.=$librarianName;
                            $officerName=$librarianName;
                            break;
                        case 'dean':
                            $uncheck.=$deanName;
                            $officerName=$deanName;
                            break;
                        case 'dsa':
                            $uncheck.=$dsaName;
                            $officerName=$dsaName;
                            break;
                        default:
                            break;
                    }
                    $uncheck.='<small></div>';
                    $clearance[$c['ClearanceType']]=['html'=>$uncheck,'cleared'=>false,'officer'=>$officerName,'Message'=>$c['Message']];
                }
            }

            $clearance['registrar']['checklist']=$this->registrarCheckList($studentID);
        }
        return $clearance;
    }

    public function getAllStudentClearances($student,$deanName){
        $clearances=[];
        for($sy=2020;$sy<=date('Y');$sy++){
            for($s=4;$s>=1;$s--){
                //check if student is enrolled in this semester
                if(StudentCurriculums::isEnrolled($student[0],$sy,($sy+1),$s)==true){
                    $slabel=$sy.'-'.($sy+1);
                    $clearances[$slabel][$this->setYearLevelSuffix($s)]=$this->getStudentClearances($student,$sy,($sy+1),$s,$deanName);
                }
            }
        }
        return $clearances;
    }

    public function getAllStudentClearancesWithSemester($student,$deanName,$AYFro,$AYTo,$Semester){
        $clearances=[];
        if(StudentCurriculums::isEnrolled($student[0],$AYFro,$AYTo,$Semester)==true){
            $slabel=$AYFro.'-'.$AYTo;
            $clearances[$slabel][$this->setYearLevelSuffix($Semester)]=$this->getStudentClearances($student,$AYFro,$AYTo,$Semester,$deanName);
        }
        return $clearances;
    }



    public function getSSGName(){
        $student=EnrollmentSettings::getSSGSetting($this->ayFrom,$this->ayTo,$this->semester);
        if($student){
            $i=explode('-',$student);
            $s=Students::getStudentInfoByYearID2($i[0],$i[1]);
            if($s) return $s->LastName.', '.$s->FirstName.' '.substr($s->MiddleName,0,1).' '.$s->NameExtension;
        }
        return '';
    }

    public function getSSGStudentID(){
        $student=EnrollmentSettings::getSSGSetting($this->ayFrom,$this->ayTo,$this->semester);
        if($student){
            $i=explode('-',$student);
            $s=Students::getStudentIDByYearID2($i[0],$i[1]);
            if($s) return $s;
        }
        return '';
    }

    public function isStudentSSG($studentID){
        return $this->getSSGStudentID()==$studentID?true:false;
    }

    public function getClearanceRoleName($role,$studentUnitID=false){
        $name='';
        switch ($role) {
            case 'registrar':
                $name.=Employees::getEmployeeNameByRole(Roles::REGISTRAR);
                break;
            case 'accounting':
                $name=Employees::getEmployeeNameByRole(Roles::ACCOUNTANT);
                break;
            case 'ssg':
                $name=$this->getSSGName();
                break;
            case 'library':
                $name=Employees::getEmployeeNameByRole(Roles::LIBRARIAN);
                break;
            case 'dean':
                if($studentUnitID) $name=Units::getDeanName($studentUnitID);
                break;
            case 'dsa':
                $name=Employees::getEmployeeNameByRole(Roles::DSA);
                break;
            default:
                break;
        }
        return $name;
    }

    public function getStudentClearancePagination($request,$forSSG=false,$studentID=null,$override=false,$unitCode='',$registrar=false){
        $d = $request->input();
        if($override){
            $override=false;
            $areas=StudentClearance::areas;
            foreach($areas as $a){
                if(isset($this->permissions['student_clearance_override_'.$a])){
                    $override=true;
                    break;
                }
            }
        }

        $data = ['draw' => 0, 'recordsFiltered' => 0, 'recordsTotal' => 0, 'data' => []];
        $data['draw'] = $d['draw'];
        $offset = $d['start'];
        $limit = $d['length'];
        $columns = [0 => 'LastName', 1 => 'LastName', 2 => 'BirthDate', 3 => 'Gender', 4 => 'ProgramCode', 5 => 'YearLevel', 6 => 'UnitCode', 7 => 'StudentTypeCode', 8 => 'StudentYear'];
        $orderBy = '';
        $orderType = '';
        if (isset($d['order'][0]['column'])) {
            $orderBy = $columns[$d['order'][0]['column']];
            $orderType = $d['order'][0]['dir'];
        }
        $filter = $d['search']['value'];
        $status = 0;
        $employeeUnitID = null;

        $res = xStudents::select('xstudents.*', 'xstudents.StudentID as StudID')
            ->where('xstudents.archived', '=', 0)
            ->where('xstudents.ayFrom', '=', $this->ayFrom)
            ->where('xstudents.ayTo', '=', $this->ayTo)
            ->where('xstudents.Semester', '=', $this->semester)
            ->where(function ($res) use ($filter) {
                if (!empty($filter)) {
                    $res->where('FirstName', 'like', '%' . $filter . '%');
                    $res->orWhere('LastName', 'like', '%' . $filter . '%');
                    $res->orwhere('StudentID2', 'like', '%' . $filter . '%');
                    $res->orwhere('Gender', 'like', '%' . $filter . '%');
                    $res->orwhere('ProgramCode', 'like', '%' . $filter . '%');
                    $res->orwhere('StudentTypeCode', 'like', '%' . $filter . '%');
                    $res->orwhere('UnitCode', 'like', '%' . $filter . '%');
                }
            })
            ->skip($offset)
            ->take($limit)
            ->orderBy('UnitCode')
            ->orderBy('ProgramCode')
            ->orderBy('YearLevel')
            ->orderBy('Lastname')
            ->orderBy('FirstName');
        if(!empty($unitCode)) $res->where('UnitCode','=',$unitCode);
        $res = $res->get();
        $c = $res->count();
        $students = [];

        if ($c > 0) {
            $records = $res->toArray();
            //get student program then get program head get employee
            $defaultNote = '<br><font color="red">NOT CLEARED</font>';

            foreach ($records as $r) {
                //get program head
                $deanName = Units::getDeanName($r['UnitID']);
                $YearLevel = $r['YearLevel'];
                $cleared = false;
                $gender = ucfirst($r['Gender']);
                $studentCode = str_pad($r['StudentID2'], 5, '0', STR_PAD_LEFT);
                $studentType = $r['StudentTypeCode'];

                $clearances=$this->getStudentClearances([$r['StudID'],$r['StudentYear'],$r['StudentID2']],
                    $this->ayFrom,$this->ayTo,$this->semester,$deanName,$forSSG);
                $actions='';
                $cleared=false;
                $sActions = false;
                $message='';
                foreach($clearances as $k=>$c){
                    if (isset($this->data['permissions']['clearance_'.$k]) || ($forSSG==true && !is_null($studentID)) && $this->isStudentSSG($studentID)){
                        $sActions=true;
                        if($clearances[$k]['cleared']==true) $cleared=true;
                    }
                    if($override && $clearances[$k]['cleared']==false && $forSSG==false && $studentID==null){
                        $o=$this->checkOverride($k,$r['StudID'],$r['StudentYear'],$r['StudentID2']);
                        $clearances[$k]['html']=$o['text'].$clearances[$k]['html'];
                    }
                    if(isset($this->permissions['student_clearance_override_display'])){
                        $o=$this->checkOverride($k,$r['StudID'],$r['StudentYear'],$r['StudentID2']);
                        $clearances[$k]['html'].=$o['label'];
                    }
                    //get message
                }
                $messageShow='none';
                if($sActions){
                    if($cleared){
                        $messageShow='block';
                        $actions = '<br><div id="button' . $r['StudentYear'] . $r['StudentID2'] . '"><button type="button" data-id="' . encrypt($r['StudID']) . '" onclick="approveClearance(this,2)" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Set CLEARED     </button></div>';
                        $actions .= '<div id="button' . $r['StudentYear'] . $r['StudentID2'] . '"><button type="button" data-id="' . encrypt($r['StudID']) . '" onclick="approveClearance(this,1)" class="btn btn-warning btn-xs"><i class="fa fa-times"></i> Set NOT CLEARED</button></div>';

                    }else{
                        $messageShow='block';
                        $actions = '<br><div id="button' . $r['StudentYear'] . $r['StudentID2'] . '"><button type="button" data-id="' . encrypt($r['StudID']) . '" onclick="approveClearance(this,2)" class="btn btn-success btn-xs"><i class="fa fa-check"></i> Set CLEARED</button></div>';
                        $actions .= '<div id="button' . $r['StudentYear'] . $r['StudentID2'] . '"><button type="button" data-id="' . encrypt($r['StudID']) . '" onclick="approveClearance(this,1)" class="btn btn-warning btn-xs"><i class="fa fa-times"></i> Set NOT CLEARED</button></div>';

                    }
                }
                if($registrar){
                    $actions.= '<a href="'.route('printStudentClearance',encrypt($r['StudID'])).'" target="_blank" class="btn btn-success btn-xs"><i class="fa fa-address-card-o"></i> Print Clearance</a></div>';
                }
                $studentName=$r['LastName'] . ', ' . title_case($r['FirstName'] . ' ' . $r['MiddleName'] . ' ' . $r['NameExtension']);
                $actions.= '<div id="msgBtn' . $r['StudentYear'] . $r['StudentID2'].'" style="display:'.$messageShow.'"><button " data-name="'.$studentName.'" data-id="'.encrypt($r['StudentID']). '" type="button" data-id="' . encrypt($r['StudID']) . '" onclick="setMessage(this)" class="btn btn-primary btn-xs">Remarks</button></div>';

                $rec = [$r['StudentYear'] . '-' . $studentCode,$studentName,
                    $gender, '('.$this->setYearLevelSuffix($YearLevel).')'.$r['ProgramCode']];

                foreach($clearances as $k=>$c){
                    $rec[]=$c['html'];
                }
                $rec[]=$actions;
                $students[]=$rec;
            }
            $tcount = xStudents::where('archived', '=', 0)
                ->where('xstudents.ayFrom', '=', $this->ayFrom)
                ->where('xstudents.ayTo', '=', $this->ayTo)
                ->where('xstudents.Semester', '=', $this->semester)
                ->where(function ($res) use ($filter) {
                    if (!empty($filter)) {
                        $res->where('FirstName', 'like', '%' . $filter . '%');
                        $res->orWhere('LastName', 'like', '%' . $filter . '%');
                        $res->orwhere('StudentID2', 'like', '%' . $filter . '%');
                        $res->orwhere('Gender', 'like', '%' . $filter . '%');
                        $res->orwhere('ProgramCode', 'like', '%' . $filter . '%');
                        $res->orwhere('StudentTypeCode', 'like', '%' . $filter . '%');
                        $res->orwhere('UnitCode', 'like', '%' . $filter . '%');
                    }
                })
                ->orderBy('UnitCode')
                ->orderBy('ProgramCode')
                ->orderBy('YearLevel')
                ->orderBy('Lastname')
                ->orderBy('FirstName');
            if (isset($this->data['permissions']['clearance_dean']) && !is_null($employeeUnitID)) {
                $tcount->where('xstudents.UnitID', '=', $employeeUnitID);
            }
            if(!empty($unitCode)) $tcount->where('UnitCode','=',$unitCode);
            $totals = $tcount->count();
            $data['recordsTotal'] = $totals;
            $data['recordsFiltered'] = $totals;
        }
        $term = $this->setSemesterName($this->semester) . ', AY ' . $this->ayFrom . '-' . $this->ayTo;
        $data['term'] = $term;
        $data['data'] = $students;

        return json_encode($data);
    }

    public function hasClearanceOverridePermission(){
        $valid=false;
        $areas=StudentClearance::areas;
        foreach($areas as $a){
            if(isset($this->permissions['student_clearance_override_'.$a])){
                $valid=true;
                break;
            }
        }
        return $valid;
    }

    public function registrarCheckList($studentID){
        //check student CheckList
        $e=EnrollmentChecklist::select('*')->where('StudentID','=',$studentID)->first();
        $missing=[];
        if($e){
            switch($e->StudentType){
                case 'Freshmen':
                    if($e->f137==0) $missing[]='Form 137';
                    if($e->f138==0) $missing[]='Form 138';
                    if($e->psa==0) $missing[]='PSA';
                    break;
                case 'Transferee':
                    if($e->psa==0) $missing[]='PSA';
                    if($e->hd==0) $missing[]='HD';
                    if($e->tori==0) $missing[]='TORI';
                    if($e->toro==0) $missing[]='TORO';
                    break;
                default:
                    break;
            }
        }
        return $missing;
    }

    public function studentClearancePrint($studentID,$ayFrom,$ayTo,$semester,$status){
        $data['student']=Students::getStudentInfo($studentID,$ayFrom,$ayTo,$semester);
        $data['student']->FullName=$this->formatStudentName($data['student']);
        $data['ayFrom']=$ayFrom;
        $data['ayTo']=$ayTo;
        $data['status']=$status;
        $data['semester'.$semester]='<i class="fa fa-check"></i>';

        return view('teacher.print_clearance', $data);
    }

    public function getStudentClearancePaginationOld($request,$forSSG=false,$studentID=null,$override=false,$unitCode='',$registrar=false){
        $d = $request->input();
        if($override){
            $override=false;
            $areas=StudentClearance::areas;
            foreach($areas as $a){
                if(isset($this->permissions['student_clearance_override_'.$a])){
                    $override=true;
                    break;
                }
            }
        }

        $data = ['draw' => 0, 'recordsFiltered' => 0, 'recordsTotal' => 0, 'data' => []];
        $data['draw'] = $d['draw'];
        $offset = $d['start'];
        $limit = $d['length'];
        $columns = [0 => 'LastName', 1 => 'LastName', 2 => 'BirthDate', 3 => 'Gender', 4 => 'ProgramCode', 5 => 'YearLevel', 6 => 'UnitCode', 7 => 'StudentTypeCode', 8 => 'StudentYear'];
        $orderBy = '';
        $orderType = '';
        if (isset($d['order'][0]['column'])) {
            $orderBy = $columns[$d['order'][0]['column']];
            $orderType = $d['order'][0]['dir'];
        }
        $filter = $d['search']['value'];
        $status = 0;
        $employeeUnitID = null;

        $res = StudentClearance::distinct()->select('student_clearance.*','xstudents.LastName','xstudents.FirstName','xstudents.MiddleName',
            'xstudents.NameExtension','xstudents.StudentYear','xstudents.StudentID2','xstudents.ProgramCode',
            'xstudents.Gender','xstudents.YearLevel','xstudents.StudentTypeCode','xstudents.UnitID','xstudents.StudentID as StudID')
            ->leftJoin('xstudents','xstudents.StudentID','=','student_clearance.StudentID')
            ->where('xstudents.archived', '=', 0)
            ->where('student_clearance.ayFrom', '<', $this->ayFrom)
            ->where('student_clearance.ayTo', '<', $this->ayTo)
            ->where(function ($res) use ($filter) {
                if (!empty($filter)) {
                    $res->where('FirstName', 'like', '%' . $filter);
                    $res->orWhere('LastName', 'like', '%' . $filter);
                    $res->orwhere('StudentID2', 'like', '%' . $filter);
                    $res->orwhere('Gender', 'like', '%' . $filter);
                    $res->orwhere('ProgramCode', 'like', '%' . $filter);
                    $res->orwhere('StudentTypeCode', 'like', '%' . $filter);
                    $res->orwhere('UnitCode', 'like', '%' . $filter);
                }
            })
            ->skip($offset)
            ->take($limit)
            ->orderBy('UnitCode')
            ->orderBy('ProgramCode')
            ->orderBy('YearLevel')
            ->orderBy('Lastname')
            ->orderBy('FirstName');
        if(!empty($unitCode)) $res->where('UnitCode','=',$unitCode);
        $res = $res->get();
        $c = $res->count();
        $students = [];

        if ($c > 0) {
            $records = $res->toArray();
            //get student program then get program head get employee
            $defaultNote = '<br><font color="red">NOT CLEARED</font>';

            foreach ($records as $r) {
                //get program head
                $deanName = Units::getDeanName($r['UnitID']);
                $YearLevel = $r['YearLevel'];
                $cleared = false;
                $gender = ucfirst($r['Gender']);
                $studentCode = str_pad($r['StudentID2'], 5, '0', STR_PAD_LEFT);
                $studentType = $r['StudentTypeCode'];
                $actions='';
                $cleared=false;
                $sActions = false;

                $rec = [$r['StudentYear'] . '-' . $studentCode, $r['LastName'] . ', ' . title_case($r['FirstName'] . ' ' . $r['MiddleName'] . ' ' . $r['NameExtension']),
                    $gender, $this->setYearLevelSuffix($YearLevel).' - '.$r['ProgramCode'],strtoupper($r['ClearanceType']),$r['AYFrom'],$r['AYTo'],$this->setSemesterName($r['Semester'])];
                $students[]=$rec;
            }
            $tcount = StudentClearance::distinct()->select('student_clearance.*')->leftJoin('xstudents','xstudents.StudentID','=','student_clearance.StudentID')
                ->where('xstudents.archived', '=', 0)
                ->where('student_clearance.ayFrom', '<', $this->ayFrom)
                ->where('student_clearance.ayTo', '<', $this->ayTo)
                ->where(function ($res) use ($filter) {
                    if (!empty($filter)) {
                        $res->where('FirstName', 'like', '%' . $filter);
                        $res->orWhere('LastName', 'like', '%' . $filter);
                        $res->orwhere('StudentID2', 'like', '%' . $filter);
                        $res->orwhere('Gender', 'like', '%' . $filter);
                        $res->orwhere('ProgramCode', 'like', '%' . $filter);
                        $res->orwhere('StudentTypeCode', 'like', '%' . $filter);
                        $res->orwhere('UnitCode', 'like', '%' . $filter);
                    }
                });
            if (isset($this->data['permissions']['clearance_dean']) && !is_null($employeeUnitID)) {
                $tcount->where('xstudents.UnitID', '=', $employeeUnitID);
            }
            if(!empty($unitCode)) $tcount->where('UnitCode','=',$unitCode);
            $totals = $tcount->count();
            $data['recordsTotal'] = $totals;
            $data['recordsFiltered'] = $totals;
        }
        $term = $this->setSemesterName($this->semester) . ', AY ' . $this->ayFrom . '-' . $this->ayTo;
        $data['term'] = $term;
        $data['data'] = $students;
        return json_encode($data);
    }

    public function setClearanceMessage($request){
        $result = ['result' => false, 'message' => 'Unable to process your request'];
        $postData = Input::except(['_token']);
        $studentID = decrypt($postData['StudentID']);
        $student=StudentInfo::where('id','=',$studentID)->first();
        if ($student){
            $areas=StudentClearance::areas;
            foreach($areas as $ctype){
                if(isset($this->data['permissions']['clearance_'.$ctype])){
                    $message=$postData['message'];
                    $sc=StudentClearance::where('StudentID','=',$studentID)
                        ->where('AYFrom','=',$this->ayFrom)
                        ->where('ayTo','=',$this->ayTo)
                        ->where('Semester','=',$this->semester)
                        ->where('ClearanceType','=',$ctype)->update(['Message'=>$message]);
                    if($sc){
                        $result['result']=true;
                        $result['message']='Message has been set.';
                    }
                }
            }
        }
        return json_encode($result);
    }

    public function getClearanceMessage($request){
        $return = ['result' => false, 'message' => 'Unable to process your request','scMessage'=>''];
        $postData = Input::except(['_token']);
        $studentID = decrypt($postData['StudentID']);
        $student=StudentInfo::where('id','=',$studentID)->first();
        if ($student){
            $areas=StudentClearance::areas;
            foreach($areas as $ctype){
                if(isset($this->data['permissions']['clearance_'.$ctype])){
                    $sc=StudentClearance::where('StudentID','=',$studentID)->where('AYFrom','=',$this->ayFrom)
                        ->where('ayTo','=',$this->ayTo)->where('Semester','=',$this->semester)
                        ->where('ClearanceType','=',$ctype)->first();
                    if($sc){
                        $return['scMessage']=$sc->Message;
                    }
                }
            }
            $return['result']=true;
        } else {
            $return['message']='Student must filled up student info.';
            $return['result']=false;
        }
        return json_encode($return);
    }
}
