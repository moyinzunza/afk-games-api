<?php

namespace App\Http\Controllers\Api;

use App\Models\ArmyLine;
use App\Models\Army;
use App\Models\UsersArmy;
use DateTime;

class ArmyLineController extends Controller
{
    public function process_army_line()
    {
        //runs every 5 seconds
        $now = new DateTime();
        $army_count = 0;
        $army_line = ArmyLine::where('start_at', '<=', $now)->get();
        $army_count += count($army_line);

        foreach ($army_line as $army) {

            $end_date = new DateTime($army->finish_at);
            $army_qty = $army->qty;
            $interval = $now->diff($end_date);
            $diffInMinutes = $interval->i;
            $time_per_unit = Army::where('id', $army->army_id)->first()->time;
            $total_time = $time_per_unit * $army_qty;
            $army_diff = $total_time - $diffInMinutes;
            
            $qty = (int)($army_diff/$time_per_unit);
            if($end_date <= $now){
                $qty = $army->qty;
            }
            echo($qty);

            if($army->type == 'army' && $qty > 0){
                $user_army = UsersArmy::where('module_id', $army->module_id)->where('army_id', $army->army_id)->first();
                $user_army->qty += $qty;
                $user_army->save();
                $army->qty -= $qty;
                $army->save();
            }

            if($army->qty <= 0){
                $army->delete();
            }
            
        }

        $data['status'] = "success";
        $data['msg'] = 'Army line processed: ' . $army_count;
        $data['data'] = array();
        return response()->json($data, 200);
    }

}