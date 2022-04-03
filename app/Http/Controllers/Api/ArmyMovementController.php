<?php

namespace App\Http\Controllers\Api;

use App\Models\Technologies;
use App\Models\UsersTechnologies;
use App\Models\Modules;
use Illuminate\Support\Facades\Auth;
use App\Models\Army;
use App\Models\ArmyGroups;
use App\Models\ArmyMovement;
use App\Models\ResourcesBuildings;
use App\Models\UsersArmy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DateInterval;
use DateTime;

class ArmyMovementController extends Controller
{
    public function move_army(Request $request, $module_id)
    {
        $army_config = Army::get();
        $validator = Validator::make($request->all(), [
            'army' => 'required|array',
            'army.*.id' => 'required|integer|between:1,' . count($army_config),
            'army.*.qty' => 'required|integer',
            'position_x' => 'required|integer|between:1,11',
            'position_y' => 'required|integer|between:1,300',
            'position_z' => 'required|integer|between:1,10',
            'resources_1_carring' => 'required|integer',
            'resources_2_carring' => 'required|integer',
            'resources_3_carring' => 'required|integer',
            'type' => 'required|in:attack,spy,deploy,transport,colonize',
        ]);

        if ($validator->fails()) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'The given data was invalid.'
            );
            $data['result'] = array('errors' => $validator->errors());
            return response()->json($data, 200);
        }

        $module = Modules::where('user_id', Auth::id())->where('id', $module_id)->first();
        if (empty($module)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Not module found.'
            );
            return response()->json($data, 200);
        }

        $user_id_destination = 0;
        $module_id_destination = 0;

        $module_destination = Modules::where('position_x', $request->position_x)->where('position_y', $request->position_y)->where('position_z', $request->position_z)->first();
        if (empty($module_destination) && $request->type != 'colonize') {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Not module destination found.'
            );
            return response()->json($data, 200);
        }
        if (!empty($module_destination)) {
            $user_id_destination = $module_destination->user_id;
            $module_id_destination = $module_destination->module_id;
        }
        if (empty($module_destination) && $request->type == 'colonize') {

            $army_count = 0;
            $army_id = 0;
            foreach ($request->army as $units) {
                $army_id = $units['id'];
                $army_count += $units['qty'];
            }
            if ($army_count == 1) {
                $colonizer = Army::where('id', $army_id)->first();
                if (!empty($colonizer)) {
                    if ($colonizer->type != 'colonize') {
                        $data['status'] = array(
                            'statusCode' => 400,
                            'message' => 'Colonizer needed.'
                        );
                        return response()->json($data, 200);
                    }
                } else {
                    $data['status'] = array(
                        'statusCode' => 400,
                        'message' => 'Unit dont exist.'
                    );
                    return response()->json($data, 200);
                }
            } else {
                $data['status'] = array(
                    'statusCode' => 400,
                    'message' => 'You only can send 1 colonizer.'
                );
                return response()->json($data, 200);
            }
        } else {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Module exist cant colonize.'
            );
            return response()->json($data, 200);
        }

        if ($request->type == 'deploy' && $module_destination->user_id != Auth::id()) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'You only can deploy to your own modules.'
            );
            return response()->json($data, 200);
        }

        if ($request->type == 'attack' && $module_destination->user_id == Auth::id()) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'You cant attack your own modules.'
            );
            return response()->json($data, 200);
        }

        if (
            $request->resources_1_carring > $module->resources_1 ||
            $request->resources_2_carring > $module->resources_2 ||
            $request->resources_3_carring > $module->resources_3
        ) {
            $config_resources = json_decode(ResourcesBuildings::get());
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Not enough resources in module.'
            );
            $data['result'] = array('module_resources' => array(
                $config_resources[0]->name => $module->resources_1,
                $config_resources[1]->name => $module->resources_2,
                $config_resources[2]->name => $module->resources_3
            ));
            return response()->json($data, 200);
        }

        $total_carrying_capacity = 0;
        $minim_speed = 0;
        $valid_army_array = true;
        foreach ($request->army as $units) {
            $user_army = UsersArmy::where('user_id', Auth::id())->where('module_id', $module_id)->where('army_id', $units['id'])->where('qty', '>=', $units['qty'])->first();
            $army_config_unit = Army::where('id', $units['id'])->first();
            $total_carrying_capacity += ($army_config_unit->carrying_capacity * $units['qty']);
            if ($minim_speed == 0 || $army_config_unit->speed < $minim_speed) {
                $minim_speed = $army_config_unit->speed;
            }
            if (empty($user_army)) {
                $valid_army_array = false;
            }
        }

        if (!$valid_army_array) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Not enough units.'
            );
            return response()->json($data, 200);
        }

        if ($total_carrying_capacity < ($request->resources_1_carring + $request->resources_2_carring + $request->resources_3_carring)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Not enough space for transport resources.'
            );
            $data['result'] = array('carrying_capacity' => $total_carrying_capacity);
            return response()->json($data, 200);
        }

        $distance_between_modules = sqrt(pow($module->position_x - $request->position_x, 2) + pow($module->position_y - $request->position_y, 2) + pow($module->position_z - $request->position_z, 2));
        $distance_between_modules *= 10000;
        $distance_between_modules = floor($distance_between_modules);

        $minutes_to_reach_objetive = $distance_between_modules / $minim_speed;

        $now = new DateTime();
        $army_group_id = $module_id . $now->format('U') . $module_id;


        foreach ($request->army as $units) {

            $user_army = UsersArmy::where('user_id', Auth::id())->where('module_id', $module_id)->where('army_id', $units['id'])->where('qty', '>=', $units['qty'])->first();
            $user_army->qty -= $units['qty'];
            $user_army->save();

            ArmyGroups::create([
                'user_id' => Auth::id(),
                'group_id' => $army_group_id,
                'army_id' => $units['id'],
                'qty' => $units['qty']
            ]);
        }

        $finish_time = new DateTime();
        $finish_time->add(new DateInterval('PT' . (int)($minutes_to_reach_objetive) . 'M'));
        ArmyMovement::create([
            'user_id' => Auth::id(),
            'user_id_destination' => $user_id_destination,
            'module_id' => $module_id,
            'module_id_destination' => $module_id_destination,
            'position_x' => $request->position_x,
            'position_y' => $request->position_y,
            'position_z' => $request->position_z,
            'army_group_id' => $army_group_id,
            'type' => $request->type,
            'resources_1_carring' => $request->resources_1_carring,
            'resources_2_carring' => $request->resources_2_carring,
            'resources_3_carring' => $request->resources_3_carring,
            'start_at' => $now,
            'finish_at' => $finish_time
        ]);
        $module->resources_1 -= $request->resources_1_carring;
        $module->resources_2 -= $request->resources_2_carring;
        $module->resources_3 -= $request->resources_3_carring;

        $module->save();


        $config_resources = json_decode(ResourcesBuildings::get());
        $data['status'] = array(
            'statusCode' => 200,
            'message' => 'army send',
            'result' => array(
                'module' => array(
                    'name' => $module->name,
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'total_time_minutes' => (int)($minutes_to_reach_objetive),
                'date_init' => $now->format('U'),
                'date_finish' => $finish_time->format('U')
            )
        );
        return response()->json($data, 200);
    }

    public function get_army_movement($module_id)
    {
        $config_resources = json_decode(ResourcesBuildings::get());
        $army_movement = ArmyMovement::where('user_id', Auth::id())->orWhere('user_id_destination', Auth::id())->get();

        $army_movement_array = array();

        foreach ($army_movement as $movement) {

            $group = ArmyGroups::where('group_id', $movement->army_group_id)->get();
            $group_array = array();
            $total_group_qty = 0;

            foreach ($group as $single_group) {

                array_push($group_array, array(
                    'name' => Army::where('id', $single_group->army_id)->first()->name,
                    'qty' => $single_group->qty
                ));

                $total_group_qty += $single_group->qty;
            }

            $init_unix_date = new DateTime($movement->start_at);
            $init_unix_date = $init_unix_date->format('U');
            $end_unix_date = new DateTime($movement->finish_at);
            $end_unix_date = $end_unix_date->format('U');

            $module_origin = Modules::where('id', $movement->module_id)->first();
            $module_destination = Modules::where('id', $movement->module_id_destination)->first();
            $module_destination_name = '';
            if (!empty($module_destination)) {
                $module_destination_name = $module_destination->name;
            }

            $single_army = array(
                'id' => $movement->id,
                'user_id' => $movement->user_id,
                'user_id_destination' => $movement->user_id_destination,
                'module_origin' => array(
                    'name' => $module_origin->name,
                    'position' => array(
                        'galaxy' => $module_origin->position_z,
                        'solar_system' => $module_origin->position_y,
                        'planet' => $module_origin->position_x
                    )
                ),
                'module_destination' => array(
                    'name' => $module_destination_name,
                    'position' => array(
                        'galaxy' => $movement->position_z,
                        'solar_system' => $movement->position_y,
                        'planet' => $movement->position_x
                    )
                ),
                'army_group_id' => $movement->army_group_id,
                'type' => $movement->type,
                $config_resources[0]->name => $movement->resources_1_carring,
                $config_resources[1]->name => $movement->resources_2_carring,
                $config_resources[2]->name => $movement->resources_3_carring,
                'total_group_qty' => $total_group_qty,
                'date_init' => $init_unix_date,
                'date_finish' => $end_unix_date,
                'group' => $group_array
            );

            array_push($army_movement_array, $single_army);
        }

        $data['status'] = array(
            'statusCode' => 200,
            'message' => 'Army Movement',
            'result' => $army_movement_array
        );
        return response()->json($data, 200);
    }
}
