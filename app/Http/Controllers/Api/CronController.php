<?php

namespace App\Http\Controllers\Api;

use App\Models\ArmyGroups;
use App\Models\UsersTechnologies;
use App\Models\Modules;
use App\Models\Users;
use App\Models\ArmyLine;
use App\Models\ArmyMovement;
use App\Models\PushNotificationTokens;
use App\Models\ResourcesBuildings;
use App\Models\UpgradesLine;
use App\Models\UsersArmy;
use App\Models\UsersDefense;
use App\Models\UsersFacilities;
use DateInterval;
use DateTime;

class CronController extends Controller
{
    public function update_resources()
    {
        //runs every minute

        $users = Users::where('status', 'active')->get();
        $config_resources = json_decode(ResourcesBuildings::get());

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
        Users::where('updated_at', '<', $date)->update([
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
            $time_per_unit = $army->time_per_unit;
            $total_time = $time_per_unit * $army_qty;

            $diffInSeconds = $end_date->getTimestamp() - $now->getTimestamp();
            $army_diff = ($total_time*60) - $diffInSeconds;

            $qty = (int)(floor($army_diff / ($time_per_unit*60)));
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

    public function process_army_movements()
    {
        //runs every 5 seconds
        $date = new DateTime();
        $army_movements = ArmyMovement::where('finish_at', '<', $date)->get();

        foreach ($army_movements as $army_movement) {

            if ($army_movement->type == 'go_back') {
                $module = Modules::where('id', $army_movement->module_id)->first();
                if (!empty($module)) {
                    $module->resources_1 += $army_movement->resources_1_carring;
                    $module->resources_2 += $army_movement->resources_2_carring;
                    $module->resources_3 += $army_movement->resources_3_carring;
                    $module->save();

                    $groups = ArmyGroups::where('group_id', $army_movement->army_group_id)->get();
                    foreach ($groups as $group) {
                        $user_army = UsersArmy::where('module_id', $army_movement->module_id)->where('army_id', $group->army_id)->first();
                        if (!empty($user_army)) {
                            $user_army->qty += $group->qty;
                            $user_army->save();
                        } else {
                            UsersArmy::create([
                                'user_id' => $module->user_id,
                                'army_id' => $group->army_id,
                                'module_id' => $module->id,
                                'qty' => $group->qty
                            ]);
                        }
                        $group->delete();
                    }
                }
                $army_movement->delete();

                //create notification
            }

            if ($army_movement->type == 'deploy') {
                $module = Modules::where('id', $army_movement->module_id_destination)->first();
                if (!empty($module)) {
                    $module->resources_1 += $army_movement->resources_1_carring;
                    $module->resources_2 += $army_movement->resources_2_carring;
                    $module->resources_3 += $army_movement->resources_3_carring;
                    $module->save();

                    $groups = ArmyGroups::where('group_id', $army_movement->army_group_id)->get();
                    foreach ($groups as $group) {
                        $user_army = UsersArmy::where('module_id', $army_movement->module_id_destination)->where('army_id', $group->army_id)->first();
                        if (!empty($user_army)) {
                            $user_army->qty += $group->qty;
                            $user_army->save();
                        } else {
                            UsersArmy::create([
                                'user_id' => $module->user_id,
                                'army_id' => $group->army_id,
                                'module_id' => $module->id,
                                'qty' => $group->qty
                            ]);
                        }
                        $group->delete();
                    }
                }
                $army_movement->delete();

                //create notification
            }

            if ($army_movement->type == 'transport') {
                $module = Modules::where('id', $army_movement->module_id_destination)->first();
                if (!empty($module)) {
                    $module->resources_1 += $army_movement->resources_1_carring;
                    $module->resources_2 += $army_movement->resources_2_carring;
                    $module->resources_3 += $army_movement->resources_3_carring;
                    $module->save();
                }

                $start_at = new DateTime($army_movement->start_at);
                $end_date = new DateTime($army_movement->finish_at);

                $interval = $start_at->diff($end_date);
                $diffInMinutes = $interval->days * 24 * 60;
                $diffInMinutes += $interval->h * 60;
                $diffInMinutes += $interval->i;

                $finish_time = new DateTime($army_movement->finish_at);
                $finish_time->add(new DateInterval('PT' . (int)($diffInMinutes) . 'M'));

                $army_movement->resources_1_carring = 0;
                $army_movement->resources_2_carring = 0;
                $army_movement->resources_3_carring = 0;
                $army_movement->type = 'go_back';
                $army_movement->start_at = $army_movement->finish_at;
                $army_movement->finish_at = $finish_time;
                $army_movement->save();
                //create notification
            }

            if ($army_movement->type == 'colonize') {
                //check if module exist, if exist go back
                $module = Modules::where('position_x', $army_movement->position_x)->where('position_y', $army_movement->position_y)->where('position_z', $army_movement->position_z)->first();
                if (!empty($module)) {
                    $start_at = new DateTime($army_movement->start_at);
                    $end_date = new DateTime($army_movement->finish_at);

                    $interval = $start_at->diff($end_date);
                    $diffInMinutes = $interval->days * 24 * 60;
                    $diffInMinutes += $interval->h * 60;
                    $diffInMinutes += $interval->i;

                    $finish_time = new DateTime($army_movement->finish_at);
                    $finish_time->add(new DateInterval('PT' . (int)($diffInMinutes) . 'M'));

                    $army_movement->type = 'go_back';
                    $army_movement->start_at = $army_movement->finish_at;
                    $army_movement->finish_at = $finish_time;
                    $army_movement->save();
                } else {

                    $groups = ArmyGroups::where('group_id', $army_movement->army_group_id)->get();
                    foreach ($groups as $group) {
                        $group->delete();
                    }
                    
                    Modules::create([
                        'user_id' => $army_movement->user_id,
                        'name' => 'colony',
                        'construction_space' => rand(150, 300),
                        'resources_1' => 500,
                        'resources_2' => 500,
                        'resources_3' => 500,
                        'resources_building_lvl_1' => 0,
                        'resources_building_lvl_2' => 0,
                        'resources_building_lvl_3' => 0,
                        'position_x' => $army_movement->position_x,
                        'position_y' => $army_movement->position_y,
                        'position_z' => $army_movement->position_z
                    ]);
                    $army_movement->delete();
                }
                //create notification
            }

            if ($army_movement->type == 'spy') {
                $module = Modules::where('id', $army_movement->module_id_destination)->first();

                $start_at = new DateTime($army_movement->start_at);
                $end_date = new DateTime($army_movement->finish_at);

                $interval = $start_at->diff($end_date);
                $diffInMinutes = $interval->days * 24 * 60;
                $diffInMinutes += $interval->h * 60;
                $diffInMinutes += $interval->i;

                $finish_time = new DateTime($army_movement->finish_at);
                $finish_time->add(new DateInterval('PT' . (int)($diffInMinutes) . 'M'));

                $army_movement->type = 'go_back';
                $army_movement->start_at = $army_movement->finish_at;
                $army_movement->finish_at = $finish_time;
                $army_movement->save();
                //create notification
                //generate report
            }

            if ($army_movement->type == 'attack') {

                exit;

                $module = Modules::where('id', $army_movement->module_id_destination)->first();

                $tech_attack_destination = UsersTechnologies::where('user_id', $army_movement->user_id_destination)->where('technology_id', 4)->first();
                $tech_attack_attacker = UsersTechnologies::where('user_id', $army_movement->user_id)->where('technology_id', 4)->first();

                $tech_defense_destination = UsersTechnologies::where('user_id', $army_movement->user_id_destination)->where('technology_id', 5)->first();
                $tech_defense_attacker = UsersTechnologies::where('user_id', $army_movement->user_id)->where('technology_id', 5)->first();


                $start_at = new DateTime($army_movement->start_at);
                $end_date = new DateTime($army_movement->finish_at);

                $interval = $start_at->diff($end_date);
                $diffInMinutes = $interval->days * 24 * 60;
                $diffInMinutes += $interval->h * 60;
                $diffInMinutes += $interval->i;

                $finish_time = new DateTime($army_movement->finish_at);
                $finish_time->add(new DateInterval('PT' . (int)($diffInMinutes) . 'M'));

                //calculate battle remain units 

                $army_movement->type = 'go_back';
                $army_movement->start_at = $army_movement->finish_at;
                $army_movement->finish_at = $finish_time;
                $army_movement->save();
                //create notification
                //generate report
            }
        }
    }
}
