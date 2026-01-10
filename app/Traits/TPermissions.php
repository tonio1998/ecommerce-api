<?php
namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FGHEMISAPI\HR\Permissions;

trait TPermissions
{
    public function getPermissions(&$data,$returnArrayKeyCodes=false,$userID=0){        
        $userID=Auth::user()->id;
        $employeeID=0;
        if(Auth::user()->user_type==1) $employeeID=Auth::user()->conn_id;
        $permissions=User::getUserAllPermissions($userID,false,$employeeID);
        if($permissions){
            if($returnArrayKeyCodes){
                $temp=[];
                foreach($permissions as $p){
                    if(!empty($p['code'])) $temp[$p['code']]=true;
                }
                $data['permissions']=$temp;
                return $temp;
            }else{
                return $permissions;
            }
        }
        return false;
    }

    public function addCustomPermission($name,$value){
        if($value!==false){
            $this->permissions[$name]=$value;
            $this->data['permissions']=$this->permissions;
        }
    }

    /**
     * hasPermission
     *
     * check if user has permission of any
     *
     * @param  [mixed]  $permission - array of permission codes,
     *                                  - array of permission ids
     *                                  - permission code
     *                                  - permission id
     * @param  [array] optional  $permissions - set permissions
     * @param  [int] optional $userID - pass a user ID when used in middleware
     * @return boolean
     */
    public function hasPermission($permission,$permissions=[],$userID=null){
        $userID=Auth::user()->id;
        $employeeID=0;
        $permissions=[];
        if(Auth::user()->user_type==1) $employeeID=Auth::user()->conn_id;       
        if(count($permissions)==0){
            $permissions=User::getUserAllPermissions($userID,false,$employeeID);
        }
        if($permissions){
            if(is_array($permission)){
                $countValidPermissions=0;
                foreach($permissions as $k=>$v){
                    foreach ($permission as $key => $value) {
                        if(is_string($value)){
                            if($v==$value) $countValidPermissions++;
                        }
                        if(is_int($value)){
                            if(array_key_exists($value, $permissions)) $countValidPermissions++;
                        }
                    }
                }
                if(count($permissions)==$countValidPermissions) return true;
            }elseif(is_string($permission)){
                foreach($permissions as $k=>$v){
                    if($v['code']==$permission){
                        return true;
                    }
                }
            }elseif(is_int($permission)){
                return array_key_exists($permission, $permissions);
            }else{
                return false;
            }
        }
        return false;
    }

    public function getPermissionsAsVariableToView(&$data){
        $employeeID=0;
        if(Auth::user()->user_type==1) $employeeID=Auth::user()->conn_id;
        $userPermissions=User::getUserAllPermissions(Auth::user()->id,false,$employeeID);        
        $permissions=[];
        if(count($userPermissions)>0 && is_array($userPermissions)){
            foreach($userPermissions as $v){
                $permissions[$v['code']]=true;
            }
        }
        if(count($permissions)>0){
            $data['permissions']=$permissions;
            return $permissions;
        }
        return false;
    }

    public function handle($request, Closure $next,$controller)
    {
        //check if user is an er user
        $erUser=$request->session()->get('erUser');
        if(!$erUser){
            return redirect('/logout');
        }
        //urls that dont need permission
        $urlsNoPerms=['reorder','counts','questions'];
        $urlsNoPermsWCat=['questions','reorder'];
        switch($request->segment(4)){
            case 'category':
                if($request->segment(5)=='add' || $request->segment(6)=='update'){
                    if(!PController::hasPermission('add_exam_category')){
                        return redirect('/classroom');
                    }
                }elseif($request->segment(5)=='remove'){
                    if(!PController::hasPermission('remove_exam_category')){
                        return redirect('/classroom');
                    }
                }elseif($request->segment(5)>0 && $request->segment(6)=='question'){
                    if(PController::hasPermission('input_questions_answers')
                        || PController::hasPermission('review_exam_questions')){
                        //able to view question for encoding or verification
                    }else{
                        return redirect('/classroom');
                    }
                }elseif($request->segment(5)>0 && in_array($request->segment(6),$urlsNoPermsWCat)){
                    //allow thisUsing $this when not in object context
                }elseif(in_array($request->segment(5),$urlsNoPerms)){
                    //allow them
                }else{
                    return redirect('/classroom');
                }
                break;
            case 'question':
                if($request->segment(5)=='submit'){
                    if(!PController::hasPermission('submit_for_verification')){
                        return redirect('/classroom');
                    }
                }elseif($request->segment(5)=='verify'){
                    if(!PController::hasPermission('verify_question')){
                        return redirect('/classroom');
                    }
                }else{
                    //allow
                }
                break;
            default: break;
        }
        $valid=$this->checkControllerPermissions($controller);
        if($valid){
            return $next($request);
        }else{
            return redirect('/logout');
        }
    }

    private function checkControllerPermissions($controller){
        if(!empty($controller)){
            switch ($controller) {
                case 'ExamPaymentsController':
                    if(!PController::hasPermission('verify_user_payment')){
                        return redirect('/classroom')->with(['error'=>'You do not have permission to visit this page']);
                    }
                    break;
                default:
                    break;
            }
        }
        return true;
    }
}
