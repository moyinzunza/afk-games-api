<?php

namespace App\Http\Controllers\Api;

use App\Models\UsersTechnologies;
use App\Models\Modules;
use App\Models\Resources;
use App\Models\User;
use App\Models\ArmyLine;
use App\Models\PushNotificationTokens;
use App\Models\UpgradesLine;
use App\Models\UsersArmy;
use App\Models\UsersDefense;
use App\Models\UsersFacilities;
use DateTime;

class CronController extends Controller
{

    public function update_resources()
    {
        //runs every minute

        $users = User::where('status', 'active')->get();
        $config_resources = json_decode(Resources::get());

        foreach ($users as $user) {

            $modules = Modules::where('user_id', $user->id)->get();

            foreach ($modules as $module) {

                Modules::where('id', $module->id)->update([
                    'resources_1' => ($module->resources_1 + ($module->resources_building_lvl_1 * $config_resources[0]->generate_multiplier)),
                    'resources_2' => ($module->resources_2 + ($module->resources_building_lvl_2 * $config_resources[1]->generate_multiplier)),
                    'resources_3' => ($module->resources_3 + ($module->resources_building_lvl_3 * $config_resources[2]->generate_multiplier))
                ]);
            }
        }

        $data['status'] = "success";
        $data['msg'] = 'Users updated:' . count($users);
        $data['data'] = array();
        return response()->json($data, 200);
    }

    public function clean_db_tokens()
    {

        $date = new DateTime();
        $date->modify("-30 day");
        PushNotificationTokens::where('updated_at', '<', $date)->delete();
    }

    public function inactivate_accounts()
    {
        $date = new DateTime();
        $date->modify("-7 day");
        User::where('updated_at', '<', $date)->update([
            'status' => 'inactive'
        ]);
    }

    public function process_upgrades()
    {
        //runs every 5 seconds
        $now = new DateTime();
        $upgrades_count = 0;
        $upgrades = UpgradesLine::where('finish_at', '<', $now)->get();
        $upgrades_count += count($upgrades);

        foreach ($upgrades as $upgrade) {
            if ($upgrade->type == 'resources_building') {
                $module = Modules::where('id', $upgrade->module_id)->first();
                $module->{'resources_building_lvl_' . $upgrade->upgrade_id} += 1;
                $module->save();
                $upgrade->delete();
            } else if ($upgrade->type == 'facilities') {
                $user_facility = UsersFacilities::where('module_id', $upgrade->module_id)->where('facility_id', $upgrade->upgrade_id)->first();
                $user_facility->level += 1;
                $user_facility->save();
                $upgrade->delete();
            } else if ($upgrade->type == 'technologies') {
                $user_technologies = UsersTechnologies::where('user_id', $upgrade->user_id)->where('technology_id', $upgrade->upgrade_id)->first();
                $user_technologies->level += 1;
                $user_technologies->save();
                $upgrade->delete();
            }
        }

        $data['status'] = "success";
        $data['msg'] = 'Upgrades Finished: ' . $upgrades_count;
        $data['data'] = array();
        return response()->json($data, 200);
    }

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
            } else if ($army->type == 'defense' && $qty > 0) {
                $user_army = UsersDefense::where('module_id', $army->module_id)->where('defense_id', $army->army_id)->first();
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
