<?php
namespace App\Traits;
use App\User;
use App\Hris\Employees;
use App\Hris\EmployeeSchedule;
use App\Es\Units;

trait TSchedule
{
    public function getScheduleTimes(){
        $l = 12;
        $t = 'AM';
        $times = [];
        $mc = 5;
        for ($i = 5; $i <= $l; $i++) {
            if ($i == 12) {
                $n = 1;
                $p = 'PM';
                $t = 'PM';
            }
            $p = $t;
            $v = $i . ':00 ' . $t . ' - ' . $i . ':30 ' . $p;
            $mcLabel = sprintf("%02d", $mc);
            $times[$mcLabel . ':00:00-' . $mcLabel . ':30:00'] = $v;
            $n = $i + 1;
            if ($i + 1 == 12 && $t == 'AM') {
                $p = 'PM';
            }

            if ($i == 12) {
                $n = 1;
                $p = 'PM';
            }
            if ($i + 1 == 12 && $t == 'PM') {
                $p = 'AM';
            }

            $v = $i . ':30 ' . $t . ' - ' . $n . ':00 ' . $p;
            $mcLabel2 = sprintf("%02d", ($mc + 1));
            $times[$mcLabel . ':30:00-' . $mcLabel2 . ':00:00'] = $v;
            if ($i == 11 && $t == 'PM') {
                break;
            }

            if ($i == 12) {
                $i = 0;
                $l = 11;
                $t = $p = 'PM';
            }
            $mc++;

        }
        return $times;
    }

    public function getScheduleColors(){    
        return $this->getLightScheduleColors();
    }

    public function getLightScheduleColors(){    
      $colors=["#33cc33", "#6699ff", "#ffdb4d", "#ff3385", "#ff704d",
               "#53f953", "#ff4d4d", "#ffbf80", "#9966ff", "#EE82EE",
               "#ff1a40", "#FAEBD7", "#ffb84d", "#FFDEAD", "#FF00FF", 
               "#feff72", "#4ac7c2", "#ffa500", "#ade78f", "#aeff62", 
               "#FF5733", "#99860D", "#0D6F47", "#2D2ADE", "#6C079E", 
               "#8C1258", "#B6370E", "#53f953", "#ff4d4d", "#ffbf80",
               "#53f953", "#ff4d4d", "#ffbf80", "#9966ff", "#EE82EE",
               "#ff1a40", "#FAEBD7", "#ffb84d", "#FFDEAD", "#FF00FF", 
               "#feff72", "#4ac7c2", "#ffa500", "#ade78f", "#aeff62", 
               "#FF5733", "#99860D", "#0D6F47", "#2D2ADE", "#6C079E", 
               "#8C1258", "#B6370E", "#53f953", "#ff4d4d", "#ffbf80", 
               "#33cc33", "#6699ff", "#ffdb4d", "#ff3385", "#ff704d",
               "#53f953", "#ff4d4d", "#ffbf80", "#9966ff", "#EE82EE",
               "#ff1a40", "#FAEBD7", "#ffb84d", "#FFDEAD", "#FF00FF", 
               "#feff72", "#4ac7c2", "#ffa500", "#ade78f", "#aeff62", 
               "#FF5733", "#99860D", "#0D6F47", "#2D2ADE", "#6C079E", 
               "#8C1258", "#B6370E", "#53f953", "#ff4d4d", "#ffbf80",
               "#53f953", "#ff4d4d", "#ffbf80", "#9966ff", "#EE82EE",
               "#ff1a40", "#FAEBD7", "#ffb84d", "#FFDEAD", "#FF00FF", 
               "#feff72", "#4ac7c2", "#ffa500", "#ade78f", "#aeff62", 
               "#FF5733", "#99860D", "#0D6F47", "#2D2ADE", "#6C079E", 
               "#8C1258", "#B6370E", "#53f953", "#ff4d4d", "#ffbf80", 
               "#33cc33", "#6699ff", "#ffdb4d", "#ff3385", "#ff704d",
               "#53f953", "#ff4d4d", "#ffbf80", "#9966ff", "#EE82EE",
               "#ff1a40", "#FAEBD7", "#ffb84d", "#FFDEAD", "#FF00FF", 
               "#feff72", "#4ac7c2", "#ffa500", "#ade78f", "#aeff62", 
               "#FF5733", "#99860D", "#0D6F47", "#2D2ADE", "#6C079E", 
               "#8C1258", "#B6370E", "#53f953", "#ff4d4d", "#ffbf80",
               "#53f953", "#ff4d4d", "#ffbf80", "#9966ff", "#EE82EE",
               "#ff1a40", "#FAEBD7", "#ffb84d", "#FFDEAD", "#FF00FF", 
               "#feff72", "#4ac7c2", "#ffa500", "#ade78f", "#aeff62", 
               "#FF5733", "#99860D", "#0D6F47", "#2D2ADE", "#6C079E", 
               "#8C1258", "#B6370E", "#53f953", "#ff4d4d", "#ffbf80", 
               "#9966ff", "#EE82EE",];
      return $colors;
    }
    # employee logged schedule
    public function viewLoggedSchedule($employeeID,$month){
        $this->data['scheduleLabel']='Log Schedule';
        $e = Employees::where('EmployeeID', '=', $employeeID)->first();
        if($e){
            $types=EmployeeSchedule::getEmployeeSchedTypes($employeeID,$month);
            $scheds=[];
            $times=[];
            $efromto=[];
            if (count($types)>0) {
                $scheds = [];
                $colors = $this->getScheduleColors();
                $i = 0;
                $courseColors = [];

                foreach ($types as $k=>$type) {
                    if (!array_key_exists($k, $courseColors)) {
                        $courseColors[$k] = $i;
                        $i++;
                    }

                    $text = ucfirst($k);
                    $schedules = EmployeeSchedule::getEmployeeSchedule($employeeID,$k,false);
                    $efromto[$k]=$schedules->toArray();
                    if ($schedules) {
                        foreach ($schedules as $s) {
                            $origText = $text;
                            //check what time and divid
                            $timeFrom = $s->TimeFrom;
                            $timeTo = $s->TimeTo;
                            $start_date = new \DateTime('2019-05-22 ' . $timeFrom);
                            $timeDiff = $start_date->diff(new \DateTime('2019-05-22 ' . $timeTo));
                            $m = ($timeDiff->i > 0) ? 1 : 0;
                            $h = ($timeDiff->h > 0) ? ($timeDiff->h * 2) : 0;
                            $totalTime = $h + $m;
                            if ($totalTime > 0) {
                                $halfStartUp = strpos($s->TimeFrom, '30') ? true : false;
                                $halfEndUp = strpos($s->TimeTo, '30') ? true : false;

                                $timeFrom = preg_replace('/ /', '', $s->TimeFrom);
                                $timeTo = '';

                                $t2 = explode(':', $s->TimeTo);
                                $t = explode(':', $s->TimeFrom);
                                if ($halfStartUp) {
                                    $timeTo = ($t[0] + 1) . ':00:00';
                                } else {
                                    $timeTo = $t[0] . ':30:00';
                                }
                                $key = $timeFrom . '-' . $timeTo;
                                $weeks=['Mon'=>'M','Tue'=>'T','Wed'=>'W','Thu'=>'Th','Fri'=>'F','Sat'=>'S','Sun'=>'Su'];
                                $scheds[$weeks[$s->ScheduleDays]][$key] = ['text' => $origText, 'color' => $colors[$courseColors[$k]] ?? $colors[0], 'rowspan' => $totalTime, 'halfStartUp' => $halfStartUp, 'halfEndUp' => $halfEndUp];
                            }
                        }
                    }
                    $i++;
                }
                $scheds = $scheds;
            }            
            $times= $this->getScheduleTimes();
            return [$scheds,$times,$efromto];
        }
    }
    # employee schedule types
    public function employeeScheduleTypes($employeeID,$month){
        $types=EmployeeSchedule::getEmployeeSchedTypes($employeeID,$month);
        return count($types)?$types:[];
    }
}
