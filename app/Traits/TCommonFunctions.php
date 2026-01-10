<?php
namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
trait TCommonFunctions
{
    public static function setCommonFields2(&$object, $hasArchived = true, $hasStatus = true)
    {
        if (is_object($object)) {
            $object->created_by = Auth::user()->id;
            $object->updated_by = Auth::user()->id;
            $object->created_at = date('Y-m-d H:i:s');
            if ($hasStatus) {
                $object->status = 'active';
            }
            if ($hasArchived) {
                $object->archived = 0;
            }

        } elseif (is_array($object)) {
            $object['created_by'] = Auth::user()->id;
            $object['updated_by'] = Auth::user()->id;
            $object['created_at'] = date('Y-m-d H:i:s');
            if ($hasStatus) {
                $object['status'] = 'active';
            }
            if ($hasArchived) {
                $object['archived'] = 0;
            }
        }
    }

    public function extractAcademicPeriod(Request $request)
    {
        return [
            'AYFrom' => $request->query('AYFrom'),
            'AYTo' => $request->query('AYTo'),
            'Semester' => $request->query('Semester'),
        ];
    }

    public function setCommonFields(&$object, $hasArchived = true, $hasStatus = true)
    {
        if (is_object($object)) {
            $object->created_by = Auth::user()->id;
            $object->updated_by = Auth::user()->id;
            $object->created_at = date('Y-m-d H:i:s');
            $object->updated_at = date('Y-m-d H:i:s');
            if ($hasStatus) {
                $object->status = 'active';
            }
            if ($hasArchived) {
                $object->archived = 0;
            }

        } elseif (is_array($object)) {
            $object['created_by'] = Auth::user()->id;
            $object['updated_by'] = Auth::user()->id;
            $object['created_at'] = date('Y-m-d H:i:s');
            $object['updated_at'] = date('Y-m-d H:i:s');
            if ($hasStatus) {
                $object['status'] = 'active';
            }
            if ($hasArchived) {
                $object['archived'] = 0;
            }
        }
    }

    public function setTerm(&$request, &$o)
    {
        if(null ==  Session::get('termFY')){
            Session::put('termFY', date('Y'));
        }

        if(null ==  Session::get('termMonth')){
            Session::put('termMonth', date('m'));
        }

        $o->termFY = $request->session()->get('termFY');
        $o->termMonth = $request->session()->get('termMonth');

        $o->AYFrom = Session::get('AYFrom');
        $o->AYTo = Session::get('AYTo');
        $o->Semester = Session::get('Semester');
    }

    public function setCommonFieldswoStatus(&$object, $hasArchived = true, $hasStatus = true)
    {
        if (is_object($object)) {
            $object->created_by = Auth::user()->id;
            $object->updated_by = Auth::user()->id;
            $object->created_at = date('Y-m-d H:i:s');
            $object->updated_at = date('Y-m-d H:i:s');
            if ($hasArchived) {
                $object->archived = 0;
            }

        } elseif (is_array($object)) {
            $object['created_by'] = Auth::user()->id;
            $object['updated_by'] = Auth::user()->id;
            $object['created_at'] = date('Y-m-d H:i:s');
            $object['updated_at'] = date('Y-m-d H:i:s');
            if ($hasArchived) {
                $object['archived'] = 0;
            }
        }
    }

    public function setUpdateFields(&$object)
    {
        if (is_object($object)) {
            $object->updated_by = Auth::user()->id;
        }
        if (is_array($object)) {
            $object['updated_by'] = Auth::user()->id;
        }
    }

    public function setEmployee(&$request, &$o)
    {
        $e = User::where('id', $o->userID)->first();
        if ($e) {
            $o->employeeID = $e->conn_id;
        }
    }


    public function check_avatar($r): string
    {
        $profile_avatar = '';
        $arra = ['success','primary','danger','info','warning'];
        $randomElement = $arra[array_rand($arra)];
        if (empty($r['avatar'])) {
            $firstLetterFirstName = strtoupper(substr($r['FirstName'], 0, 1));
            $firstLetterLastName = strtoupper(substr($r['LastName'], 0, 1));
            $concatenated = $firstLetterFirstName . $firstLetterLastName;

            //$profile_avatar = '<div class="d-flex justify-content-start align-items-center user-name"><div class="avatar-wrapper"><div class="avatar me-3"><span  data-bs-toggle="tooltip" data-bs-placement="top"  title="BiometricID: '.$r['EmployeeCode'].'<br>EmployeeID: '.$r['EmployeeID'].'" class="avatar-initial rounded-circle bg-label-'.$randomElement.'"></span></div></div><div class="d-flex flex-column"><a href="#" class="text-body text-truncate" data-bs-toggle="tooltip" data-bs-placement="top"  data-bs-html="true"  title="BiometricID: '.$r['EmployeeCode'].'<br>EmployeeID: '.$r['EmployeeID'].' '.(isset($r['EmployeeStatus']) ?  ('<br>'.$r['EmployeeStatus']) : '').'"><span class="fw-medium"><b>'.strtoupper($r['LastName']).'</b>, '.ucfirst(strtolower($r['FirstName'])).' '.ucfirst(strtolower($r['MiddleName'])).' '.$r['NameExtension'].'</span></a><small class="text-muted">'.$r['email'].'</small></div></div>';

            $profile_avatar = '
            <div class="d-flex justify-content-start align-items-center user-name">
                <div class="avatar-wrapper">
                    <div class="avatar me-3">
                        <img
                            src="/ux/img/org_logo.png"
                            style="width: 50px;"
                            class="rounded-circle"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-html="true"
                            title="BiometricID: ' . $r['EmployeeCode'] . '<br>
                                   EmployeeID: ' . $r['EmployeeID'] . ' ' .
                            (isset($r['EmployeeStatus']) ? '<br>' . $r['EmployeeStatus'] : '') . '"
                        >
                    </div>
                </div>

                <div class="d-flex flex-column">
                    <a
                        href="#"
                        class="text-body text-truncate"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        data-bs-html="true"
                        title="BiometricID: ' . $r['EmployeeCode'] . '<br>EmployeeID: ' . $r['EmployeeID'] . ' ' .
                            (isset($r['EmployeeStatus']) ? '<br>' . $r['EmployeeStatus'] : '') . '"
                    >
                        <span class="fw-medium">
                            '.$this->getEmployeeFullName($r).'
                        </span>
                    </a>

                    <small class="text-muted">' . $r['email'] . '</small>
                </div>
            </div>';


        } else {
            //$profile_avatar = '<div class="d-flex justify-content-start align-items-center user-name"><div class="avatar-wrapper"><div class="avatar me-3"><img style="width: 50px;" src="'.$r['avatar'].'"   data-bs-toggle="tooltip" data-bs-placement="top"  data-bs-html="true"  title="BiometricID: '.$r['EmployeeCode'].'<br>EmployeeID: '.$r['EmployeeID'].' '.(isset($r['EmployeeStatus']) ?  ('<br>'.$r['EmployeeStatus']) : '').'" class="rounded-circle"></div></div><div class="d-flex flex-column"><a href="#" class="text-body text-truncate" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"  title="BiometricID: '.$r['EmployeeCode'].' EmployeeID: '.$r['EmployeeID'].' '.(isset($r['EmployeeStatus']) ?  ('<br>'.$r['EmployeeStatus']) : '').'"><span class="fw-medium"><b>'.strtoupper($r['LastName']).'</b>, '.ucfirst(strtolower($r['FirstName'])).' '.ucfirst(strtolower($r['MiddleName'])).' '.$r['NameExtension'].'</span></a><small class="text-muted">'.$r['email'].'</small></div></div>';

            $profile_avatar = '
<div class="d-flex justify-content-start align-items-center user-name">
    <div class="avatar-wrapper">
        <div class="avatar me-3">
            <img
                src="' . $r['avatar'] . '"
                style="width: 50px;"
                class="rounded-circle"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-html="true"
                title="BiometricID: ' . $r['EmployeeCode'] . '<br>
                       EmployeeID: ' . $r['EmployeeID'] . ' ' .
                (isset($r['EmployeeStatus']) ? '<br>' . $r['EmployeeStatus'] : '') . '"
            >
        </div>
    </div>

    <div class="d-flex flex-column">
        <a
            href="#"
            class="text-body text-truncate"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-html="true"
            title="BiometricID: ' . $r['EmployeeCode'] . '
                   <br>EmployeeID: ' . $r['EmployeeID'] . ' ' .
                (isset($r['EmployeeStatus']) ? '<br>' . $r['EmployeeStatus'] : '') . '"
        >
            <span class="fw-medium">
                          '.$this->getEmployeeFullName($r).'
            </span>
        </a>

        <small class="text-muted">' . $r['email'] . '</small>
    </div>
</div>';
        }
        return $profile_avatar;
    }


    public function getSemesterName($num)
    {
        switch ($num) {
            case 1:
                $term = '1st Semester';
                break;
            case 2:
                $term = '2nd Semester';
                break;
            case 3:
                $term = 'Summer';
                break;
            case 4:
                $term = '2nd Summer';
                break;
            case 5:
                $term = '3rd Semester';
                break;
            default:
                $term = '';
                break;
        }
        return $term;
    }

    public function setYearLevelName($year)
    {
        switch ($year) {
            case 1:
                $year = 'First Year';
                break;
            case 2:
                $year = 'Second Year';
                break;
            case 3:
                $year = 'Third Year';
                break;
            case 4:
                $year = 'Fourth Year';
                break;
            case 5:
                $year = 'Fifth Year';
                break;
            case 7:
                $year = 'G-12';
                break;
            default:
                $year = '';
                break;
        }
        return $year;
    }

    public function setSemesterName($num)
    {
        switch ($num) {
            case 1:
                $num = '1st Semester';
                break;
            case 2:
                $num = '2nd Semester';
                break;
            case 3:
                $num = 'Summer';
                break;
            case 4:
                $num = '2nd Summer';
                break;
            case 5:
                $num = '3rd Semester';
                break;
            default:
                $num = '';
                break;
        }
        return $num;
    }


    public function setYearLevelSuffix($year)
    {
        switch ($year) {
            case 1:
                $year .= 'st';
                break;
            case 2:
                $year .= 'nd';
                break;
            case 3:
                $year .= 'rd';
                break;
            case 4:
            case 5:
                $year .= 'th';
                break;
            default:
                $year .= '';
                break;
        }
        return $year;
    }


    function getEmployeeFullName(array $employee): string
    {
        // Helper: Proper case per word (handles spaces & hyphens)
        $properCase = function (?string $value): string {
            if (empty($value)) {
                return '';
            }

            return mb_convert_case(
                mb_strtolower(trim($value), 'UTF-8'),
                MB_CASE_TITLE,
                'UTF-8'
            );
        };

        $lastName = strtoupper($employee['LastName'] ?? '');
        $first    = $properCase($employee['FirstName'] ?? '');
        $middle   = $properCase($employee['MiddleName'] ?? '');
        $ext      = $properCase($employee['NameExtension'] ?? '');

        $fullName = '<b>'.$lastName . '</b>, ' . $first;

        if ($ext !== '') {
            $fullName .= ' ' . $ext;
        }

        if ($middle !== '') {
            $fullName .= ' ' . $middle;
        }

        return trim($fullName);
    }


}
