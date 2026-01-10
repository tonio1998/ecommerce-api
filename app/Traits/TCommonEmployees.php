<?php
namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\User;
use App\MessageBoards;
use App\Hris\Employees;

Trait TCommonEmployees
{
    function c_selectEmployees($request,$encrypted=false){
        $postData = Input::except(['_token']);
        $query = $postData['_type'];
        $data = [];
        $q=isset($postData['q'])?$postData['q']:'';
        $res = Employees::where('archived', '=', 0)
            ->where('LastName', 'like', '%'.$q.'%')
            ->orWhere('FirstName', 'like', '%'.$q.'%')
            ->where('archived', '=', 0)
            ->limit(10)->get();
        foreach ($res as $r) {
            $employeeID=$r->EmployeeID;
            if($encrypted)$employeeID =encrypt($r->EmployeeID);
            $data[] = ['id' => $employeeID, 'text' => $r->LastName.' - '.$r->FirstName];
        }
        $results['items'] = $data;

        return json_encode($results);
    }
}
