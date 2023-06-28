<?php

namespace App\Http\Controllers\Api;

use App\Models\Technologies;
use App\Models\UsersTechnologies;
use App\Models\Modules;
use Illuminate\Support\Facades\Auth;
use App\Models\Army;
use App\Models\ArmyConditions;
use App\Models\ArmyLine;
use App\Models\Facilities;
use App\Models\ResourcesBuildings;
use App\Models\UsersArmy;
use App\Models\UsersFacilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DateInterval;
use DateTime;

class ArmyController extends Controller
{
    public function get_module_army($module_id)
    {

        $army_config = Army::orderBy('atack_points', 'ASC')->get();
        $config_resources = json_decode(ResourcesBuildings::get());

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();
        $army_arr = array();
        if (!empty($module)) {

            //Army production line

            $army_line = ArmyLine::where('user_id', Auth::id())->where('module_id', $module_id)->where('type', 'army')->get();

            $production_line = array();

            foreach ($army_line as $arm_line) {

                $init_unix_date = new DateTime($arm_line->start_at);
                $init_unix_date = $init_unix_date->format('U');
                $end_unix_date = new DateTime($arm_line->finish_at);
                $end_unix_date = $end_unix_date->format('U');

                $total_time_minutes = ($end_unix_date - $init_unix_date) / 60;

                $single_army = array(
                    'id_line' => $arm_line->id,
                    'image' => $army_config[$arm_line->army_id - 1]->image_url,
                    'id' => $arm_line->army_id,
                    'name' => $army_config[$arm_line->army_id - 1]->name,
                    'qty' => $arm_line->qty,
                    'total_time_minutes' => $total_time_minutes,
                    'date_init' => $init_unix_date,
                    'date_finish' => $end_unix_date
                );

                array_push($production_line, $single_army);
            }


            foreach ($army_config as $army) {

                $user_army = UsersArmy::where('user_id', Auth::id())->where('module_id', $module_id)->where('army_id', $army->id)->first();
                if (empty($user_army)) {

                    UsersArmy::create([
                        'user_id' => Auth::id(),
                        'army_id' => $army->id,
                        'qty' => 0,
                        'module_id' => $module_id
                    ]);

                    $user_army = UsersArmy::where('user_id', Auth::id())->where('module_id', $module_id)->where('army_id', $army->id)->first();
                }

                $conditions_array = array();
                $conditions_arr = ArmyConditions::where('army_id', $army->id)->get();
                $all_conditions_fullfilled = true;
                foreach ($conditions_arr as $condition) {

                    $name = "";
                    $fulfilled = false;
                    if ($condition->type == 'facility') {
                        $facility = Facilities::where('id', $condition->type_id)->first();
                        $name = $facility->name;
                        $image = $facility->image_url;
                        if (!empty(UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $condition->type_id)->where('level', '>=', $condition->min_level)->first())) {
                            $fulfilled = true;
                        }
                    } else if ($condition->type == 'technology') {
                        $technologies = Technologies::where('id', $condition->type_id)->first();
                        $name = $technologies->name;
                        $image = $technologies->image_url;
                        if (!empty(UsersTechnologies::where('user_id', Auth::id())->where('technology_id', $condition->type_id)->where('level', '>=', $condition->min_level)->first())) {
                            $fulfilled = true;
                        }
                    }

                    if (!$fulfilled) {
                        $all_conditions_fullfilled = false;
                    }

                    array_push($conditions_array, [
                        'image' => $image,
                        'type' => $condition->type,
                        'level' => $condition->min_level,
                        'name' => $name,
                        'fulfilled' => $fulfilled
                    ]);
                }

                $arm_arr = array(
                    'id' => $army->id,
                    'name' => $army->name,
                    'image' => $army->image_url,
                    'qty' => $user_army->qty,
                    'price_time' => array(
                        $config_resources[0]->name => (int)($army->resources1_price),
                        $config_resources[1]->name => (int)($army->resources2_price),
                        $config_resources[2]->name => (int)($army->resources3_price),
                        "time_minutes" => (int)($army->time)
                    ),
                    'all_conditions_fullfilled' => $all_conditions_fullfilled,
                    'require' => $conditions_array
                );

                array_push($army_arr, $arm_arr);
            }

            $module_info = array(
                'image' => 'http://universe.artificialrevenge.com/assets/army/army_factory.jpg',
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'items' => $army_arr,
                'items_line' => $production_line
            );

            $data['status'] = array("statusCode" => 200, "message" => 'army in module');
            $data['result'] = $module_info;
            return response()->json($data, 200);
        } else {
            $data['status'] = array("statusCode" => 400, "message" => 'no module found');
            return response()->json($data, 200);
        }
    }

    public function create_army(Request $request, $module_id)
    {
        $army_config = Army::get();
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|between:1,' . count($army_config),
            'qty' => 'required|integer|gt:0'
        ]);

        if ($validator->fails()) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'The given data was invalid.'
            );
            $data['result'] = array('errors' => $validator->errors());
            return response()->json($data, 200);
        }

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();

        if (empty($module)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Module not found.'
            );
            return response()->json($data, 200);
        }

        $config_resources = ResourcesBuildings::get();
        $user_army = UsersArmy::where('user_id', Auth::id())->where('army_id', $request->id)->first();
        if (empty($user_army)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'army not found.'
            );
            return response()->json($data, 200);
        }

        $army_config = Army::where('id', $request->id)->first();

        $price_resources_1 = $army_config->resources1_price * $request->qty;
        $price_resources_2 = $army_config->resources2_price * $request->qty;
        $price_resources_3 = $army_config->resources3_price * $request->qty;


        //Conditions
        $conditions_array = array();
        $conditions_arr = ArmyConditions::where('army_id', $request->id)->get();
        foreach ($conditions_arr as $condition) {

            $name = "";
            $fulfilled = false;
            if ($condition->type == 'facility') {
                $name = Facilities::where('id', $condition->type_id)->first()->name;
                if (!empty(UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $condition->type_id)->where('level', '>=', $condition->min_level)->first())) {
                    $fulfilled = true;
                }
            } else if ($condition->type == 'technology') {
                $name = Technologies::where('id', $condition->type_id)->first()->name;
                if (!empty(UsersTechnologies::where('user_id', Auth::id())->where('technology_id', $condition->type_id)->where('level', '>=', $condition->min_level)->first())) {
                    $fulfilled = true;
                }
            }

            if (!$fulfilled) {
                array_push($conditions_array, [
                    'type' => $condition->type,
                    'level' => $condition->min_level,
                    'name' => $name
                ]);
            }
        }
        //end conditions



        if (
            $price_resources_1 > $module->resources_1
            || $price_resources_2 > $module->resources_2
            || $price_resources_3 > $module->resources_3
            || count($conditions_array) > 0
        ) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'insufficient requirements',
                'result' => array(
                    'module' => array(
                        'name' => $module->name,
                        $config_resources[0]->name => $module->resources_1,
                        $config_resources[1]->name => $module->resources_2,
                        $config_resources[2]->name => $module->resources_3
                    ),
                    'army_name' => $army_config->name,
                    'price_time' => array(
                        $config_resources[0]->name => (int)($army_config->resources1_price * $request->qty),
                        $config_resources[1]->name => (int)($army_config->resources2_price * $request->qty),
                        $config_resources[2]->name => (int)($army_config->resources3_price * $request->qty),
                        "time_minutes" => (int)($army_config->time * $request->qty)
                    ),
                    'qty' => $request->qty,
                    'require' => $conditions_array
                )
            );
            return response()->json($data, 200);
        }

        $module->resources_1 -= $army_config->resources1_price * $request->qty;
        $module->resources_2 -= $army_config->resources2_price * $request->qty;
        $module->resources_3 -= $army_config->resources3_price * $request->qty;
        $module->save();

        $init_time = new DateTime();
        $finish_time = new DateTime();
        $last_army_line = ArmyLine::where('module_id', $module_id)->where('user_id', Auth::id())->where('type', 'army')->orderBy('id', 'DESC')->first();
        if (!empty($last_army_line)) {
            $init_time = new DateTime($last_army_line->finish_at);
            $finish_time = new DateTime($last_army_line->finish_at);;
        }

        $finish_time->add(new DateInterval('PT' . (int)($army_config->time * $request->qty) . 'M'));

        ArmyLine::create([
            'user_id' => Auth::id(),
            'module_id' => $module->id,
            'army_id' => $request->id,
            'qty' => $request->qty,
            'time_per_unit' => $army_config->time,
            'type' => 'army',
            'finish_at' => $finish_time,
            'start_at' => $init_time
        ]);

        $data['status'] = array(
            'statusCode' => 200,
            'message' => 'army in progress',
            'result' => array(
                'module' => array(
                    'name' => $module->name,
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'army_name' => $army_config->name,
                'qty' => $request->qty,
                'total_time_minutes' => (int)($army_config->time * $request->qty),
                'date_init' => $init_time->format('U'),
                'date_finish' => $finish_time->format('U')
            )
        );
        return response()->json($data, 200);
    }
}
