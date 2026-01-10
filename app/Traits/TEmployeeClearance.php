<?php
namespace App\Traits;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Es\Students;
use App\Es\EnrollmentSettings;
use App\Es\EnrollmentChecklist;
use App\Hris\Employees;
use App\Hris\EmployeeClearance;
use App\Models\Hris\EmployeeClearanceDetails;
use App\Models\Hris\EmployeeClearanceDesireTo;
use App\Hris\Roles;
use App\Es\views\xStudents;
use App\Es\Units;

trait TEmployeeClearance
{
    public function getClearanceDesireTo(){
        $res=EmployeeClearanceDesireTo::select('id','desire')->orderBy('id', 'asc')
            ->get();
        if($res) return $res;
        return [];
    }

    public function getEmployeeClearances($employeeUnitID){

        if(isset($employeeUnitID)):
            $getClearance=EmployeeClearance::select('employee_clearance.ClearanceID','employee_clearance.EmployeeID','employee_clearance.ApplyFor','employee_clearance.DesireTo','employee_clearance.OtherSpecify','employee_clearance_desire.desire','employee_clearance.AYFrom','employee_clearance.AYTo','employee_clearance.Semester','employees.EmployeeID','employees.FirstName','employees.LastName','employees.MiddleName')
                ->leftJoin('employees','employees.EmployeeID','=','employee_clearance.EmployeeID')
                ->leftJoin('employee_clearance_desire','employee_clearance_desire.id','=','employee_clearance.DesireTo')
                ->where('employees.UnitID', '=', $employeeUnitID)
                ->where('employee_clearance.AYFrom','=',$this->ayFrom)
                ->where('employee_clearance.AYTo','=',$this->ayTo)
                ->where('employee_clearance.Semester','=',$this->semester)
                ->where('employee_clearance.archived','=',0)
                ->get();
        else:

            $getClearance=EmployeeClearance::select('employee_clearance.ClearanceID','employee_clearance.EmployeeID','employee_clearance.ApplyFor','employee_clearance.DesireTo','employee_clearance.OtherSpecify','employee_clearance_desire.desire','employee_clearance.AYFrom','employee_clearance.AYTo','employee_clearance.Semester','employees.FirstName','employees.LastName','employees.MiddleName')
                ->leftJoin('employees','employees.EmployeeID','=','employee_clearance.EmployeeID')
                ->leftJoin('employee_clearance_desire','employee_clearance_desire.id','=','employee_clearance.DesireTo')
                ->where('employee_clearance.AYFrom','=',$this->ayFrom)
                ->where('employee_clearance.AYTo','=',$this->ayTo)
                ->where('employee_clearance.Semester','=',$this->semester)
                ->where('employee_clearance.archived','=',0)
                ->get();

        endif;

        return $getClearance;
    }

    public function getEmployeeClearancesForVP($employeeTypeID){

        if(isset($employeeTypeID)):

            $getClearance=EmployeeClearance::select('employee_clearance.ClearanceID','employee_clearance.EmployeeID','employee_clearance.ApplyFor','employee_clearance.DesireTo','employee_clearance.OtherSpecify','employee_clearance_desire.desire','employee_clearance.AYFrom','employee_clearance.AYTo','employee_clearance.Semester','employees.EmployeeID','employees.FirstName','employees.LastName','employees.MiddleName')
                ->leftJoin('employees','employees.EmployeeID','=','employee_clearance.EmployeeID')
                ->leftJoin('employee_clearance_desire','employee_clearance_desire.id','=','employee_clearance.DesireTo')
                ->whereIn('employees.EmployeeTypeID', $employeeTypeID)
                ->where('employee_clearance.AYFrom','=',$this->ayFrom)
                ->where('employee_clearance.AYTo','=',$this->ayTo)
                ->where('employee_clearance.Semester','=',$this->semester)
                ->where('employee_clearance.archived','=',0)
                ->get();
        else:

            $getClearance=EmployeeClearance::select('employee_clearance.ClearanceID','employee_clearance.EmployeeID','employee_clearance.ApplyFor','employee_clearance.DesireTo','employee_clearance.OtherSpecify','employee_clearance_desire.desire','employee_clearance.AYFrom','employee_clearance.AYTo','employee_clearance.Semester','employees.FirstName','employees.LastName','employees.MiddleName')
                ->leftJoin('employees','employees.EmployeeID','=','employee_clearance.EmployeeID')
                ->leftJoin('employee_clearance_desire','employee_clearance_desire.id','=','employee_clearance.DesireTo')
                /*->where('employee_clearance.AYFrom','=',$this->ayFrom)
                ->where('employee_clearance.AYTo','=',$this->ayTo)
                ->where('employee_clearance.Semester','=',$this->semester)*/
                ->where('employee_clearance.archived','=',0)
                ->get();

        endif;

        return $getClearance;
    }

    /*public static function getEmployeeClearancesCount($employeeUnitID,$AYFrom,$AYTo,$Semester) {

        $records=EmployeeClearance::select('employee_clearance.ClearanceID')
            ->leftJoin('employees','employees.EmployeeID','=','employee_clearance.EmployeeID')
            ->where('employees.UnitID', '=', $employeeUnitID)
            ->where('employee_clearance.AYFrom','=',$AYFrom)
            ->where('employee_clearance.AYTo','=',$AYTo)
            ->where('employee_clearance.Semester','=',$Semester)
            //->where('employee_clearance.status','=','active')
            ->count();

        if($records>0) {
            return $records;
        } else {
            return false;
        }
    }*/

    public function getClearanceSignature($ClearanceID,$SignatoryID) {
        $getClearance = EmployeeClearanceDetails::select('employees.Signature')
            ->leftJoin('employees','employees.EmployeeID','=','employee_clearance_details.EmployeeIDSignature')
            ->where('employee_clearance_details.ClearanceID','=',$ClearanceID)
            ->where('employee_clearance_details.SignatoryID','=',$SignatoryID)
            ->first();

        if($getClearance) return $getClearance['Signature'];
        return false;
    }

    public function getClearanceSignatureDate($ClearanceID,$SignatoryID) {
        $getClearanceDate = EmployeeClearanceDetails::select('employee_clearance_details.created_at')
            ->leftJoin('employees','employees.EmployeeID','=','employee_clearance_details.EmployeeIDSignature')
            ->where('employee_clearance_details.ClearanceID','=',$ClearanceID)
            ->where('employee_clearance_details.SignatoryID','=',$SignatoryID)
            ->first();
        if($getClearanceDate) return $getClearanceDate['created_at'];
        return false;
    }

    public function getClearanceSignatureName($ClearanceID,$SignatoryID) {
        $res = EmployeeClearanceDetails::select('employees.LastName','employees.FirstName','employees.MiddleName')
            ->leftJoin('employees','employees.EmployeeID','=','employee_clearance_details.EmployeeIDSignature')
            ->where('employee_clearance_details.ClearanceID','=',$ClearanceID)
            ->where('employee_clearance_details.SignatoryID','=',$SignatoryID)
            ->first();

        if($res) return $res['LastName'].', '.$res['FirstName'].' '.substr($res['MiddleName'],0,1);
        return false;
    }

    public function excludeUnitID($getEmpUnitID,$EmployeeID) {

        $deansUnit = ['3','4','5','6','7'];

        if (($key = array_search($getEmpUnitID, $deansUnit)) !== false) {
            unset($deansUnit[$key]); //remove EmployeeUnitID
            $excludeUnitID = $deansUnit; //exclude UnitID for College Deans
        } else {
            $excludeUnitID = $deansUnit;
        }
        $getEmpType = Employees::getEmployeeType($EmployeeID);
        $VPAcadUnitID=Units::getUnitID('VPAcad');
        $VPAdminUnitID=Units::getUnitID('VPAdmin');

        //echo $getEmpUnitID; die();

        if($getEmpType==1 || $getEmpType==2){
            array_push($excludeUnitID,$VPAdminUnitID);
        } else {
            array_push($excludeUnitID,$VPAcadUnitID);
        }
        //echo count($excludeUnitID); die();

        return $excludeUnitID;
    }
}
