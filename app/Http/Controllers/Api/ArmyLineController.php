<?php

namespace App\Http\Controllers\Api;

use App\Models\ArmyLine;
use App\Models\UsersArmy;
use DateTime;

class ArmyLineController extends Controller
{
    public function process_army_line()
    {
        //runs every 5 seconds
        $now = new DateTime();
        $army_line = ArmyLine::where('start_at', '<=', $now)->get();

        foreach ($army_line as $army) {

            $now = new DateTime();
            $end_date = new DateTime($army->finish_at);

            $army_qty = $army->qty;
            $interval = $now->diff($end_date);
            $diffInMinutes = $interval->days * 24 * 60;
            $diffInMinutes += $interval->h * 60;
            $diffInMinutes += $interval->i;
            $time_per_unit = $army->time_per_unit;
            $total_time = $time_per_unit * $army_qty;
            $army_diff = $total_time - $diffInMinutes;

            $qty = (int)(floor($army_diff / $time_per_unit));
            if ($end_date <= $now) {
                $qty = $army->qty;
            }

            if ($army->type == 'army' && $qty > 0) {
                $user_army = UsersArmy::where('module_id', $army->module_id)->where('army_id', $army->army_id)->first();
                $user_army->qty += $qty;
                $user_army->save();
                $army->qty -= $qty;
                $army->save();
            }

            if ($army->qty <= 0) {
                $army->delete();
            }
        }

        $data['status'] = "success";
        $data['msg'] = 'Army line processed: ' . count($army_line);
        return response()->json($data, 200);
    }
}
