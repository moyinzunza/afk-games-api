<?php

namespace App\Http\Controllers\Api;

use App\Models\Technologies;
use App\Models\UsersTechnologies;
use App\Models\Modules;
use App\Models\Resources;
use Illuminate\Support\Facades\Auth;
use App\Models\ArmyLine;
use App\Models\Defense;
use App\Models\DefenseConditions;
use App\Models\Facilities;
use App\Models\UsersDefense;
use App\Models\UsersFacilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DateInterval;
use DateTime;

class DefenseController extends Controller
{
    public function get_module_defense($module_id)
    {

        $defense_config = Defense::get();
        $config_resources = json_decode(Resources::get());

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();
        $defense_arr = array();
        if (!empty($module)) {

            $army_line = ArmyLine::where('user_id', Auth::id())->where('module_id', $module_id)->where('type', 'defense')->get();

            $production_line = array();

            foreach ($army_line as $arm_line) {

                $init_unix_date = new DateTime($arm_line->created_at);
                $init_unix_date = $init_unix_date->format('U');
                $end_unix_date = new DateTime($arm_line->finish_at);
                $end_unix_date = $end_unix_date->format('U');

                $total_time_minutes = ($end_unix_date - $init_unix_date) / 60;

                $single_army = array(
                    'image' => $defense_config[$arm_line->army_id - 1]->image_url,
                    'id' => $arm_line->army_id,
                    'name' => $defense_config[$arm_line->army_id - 1]->name,
                    'qty' => $arm_line->qty,
                    'total_time_minutes' => $total_time_minutes,
                    'date_init' => $init_unix_date,
                    'date_finish' => $end_unix_date
                );

                array_push($production_line, $single_army);
            }


            foreach ($defense_config as $defense) {

                $user_defense = UsersDefense::where('user_id', Auth::id())->where('module_id', $module_id)->where('defense_id', $defense->id)->first();
                if (empty($user_defense)) {

                    UsersDefense::create([
                        'user_id' => Auth::id(),
                        'defense_id' => $defense->id,
                        'qty' => 0,
                        'module_id' => $module_id
                    ]);

                    $user_defense = UsersDefense::where('user_id', Auth::id())->where('module_id', $module_id)->where('defense_id', $defense->id)->first();
                }

                $conditions_array = array();
                $conditions_arr = DefenseConditions::where('defense_id', $defense->id)->get();
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

                $def_arr = array(
                    'id' => $defense->id,
                    'name' => $defense->name,
                    'image' => $defense->image_url,
                    'qty' => $user_defense->qty,
                    'price_time' => array(
                        $config_resources[0]->name => (int)($defense->resources1_price),
                        $config_resources[1]->name => (int)($defense->resources2_price),
                        $config_resources[2]->name => (int)($defense->resources3_price),
                        "time_minutes" => (int)($defense->time)
                    ),
                    'require' => $conditions_array
                );

                array_push($defense_arr, $def_arr);
            }

            $module_info = array(
                'image' => 'https://vid.alarabiya.net/images/2022/03/09/144ccf47-49f6-403c-9475-6fbd5a3cb0e2/144ccf47-49f6-403c-9475-6fbd5a3cb0e2_16x9_1200x676.jpg?width=1138',
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'items' => $defense_arr,
                'items_line' => $production_line
            );

            $data['status'] = array("statusCode" => 200, "message" => 'defense in module');
            $data['result'] = $module_info;
            return response()->json($data, 200);
        } else {
            $data['status'] = array("statusCode" => 400, "message" => 'no module found');
            return response()->json($data, 400);
        }
    }

    public function create_defense(Request $request, $module_id)
    {
        $defense_config = Defense::get();
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|between:1,' . count($defense_config),
            'qty' => 'required|integer'
        ]);

        if ($validator->fails()) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'The given data was invalid.'
            );
            $data['result'] = array('errors' => $validator->errors());
            return response()->json($data, 400);
        }

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();

        if (empty($module)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Module not found.'
            );
            return response()->json($data, 400);
        }

        $config_resources = Resources::get();
        $user_defense = UsersDefense::where('user_id', Auth::id())->where('defense_id', $request->id)->first();
        if (empty($user_defense)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'defense not found.'
            );
            return response()->json($data, 400);
        }

        $defense_config = Defense::where('id', $request->id)->first();

        $price_resources_1 = $defense_config->resources1_price*$request->qty;
        $price_resources_2 = $defense_config->resources2_price*$request->qty;
        $price_resources_3 = $defense_config->resources3_price*$request->qty;


        //Conditions
        $conditions_array = array();
        $conditions_arr = DefenseConditions::where('defense_id', $request->id)->get();
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
                    'defense_name' => $defense_config->name,
                    'price_time' => array(
                        $config_resources[0]->name => (int)($defense_config->resources1_price*$request->qty),
                        $config_resources[1]->name => (int)($defense_config->resources2_price*$request->qty),
                        $config_resources[2]->name => (int)($defense_config->resources3_price*$request->qty),
                        "time_minutes" => (int)($defense_config->time*$request->qty)
                    ),
                    'qty' => $request->qty,
                    'require' => $conditions_array
                )
            );
            return response()->json($data, 400);
        }

        $module->resources_1 -= $defense_config->resources1_price*$request->qty;
        $module->resources_2 -= $defense_config->resources2_price*$request->qty;
        $module->resources_3 -= $defense_config->resources3_price*$request->qty;
        $module->save();

        $init_time = new DateTime();
        $last_army_line = ArmyLine::where('module_id', $module_id)->where('user_id', Auth::id())->where('type', 'defense')->orderBy('id', 'DESC')->first();
        if(!empty($last_army_line)){
            $init_time = new DateTime($last_army_line->finish_at);
        }

        $finish_time = new DateTime();
        $finish_time->add(new DateInterval('PT' . (int)($defense_config->time*$request->qty) . 'M'));


        ArmyLine::create([
            'user_id' => Auth::id(),
            'module_id' => $module->id,
            'army_id' => $request->id,
            'qty' => $request->qty,
            'time_per_unit' => $defense_config->time,
            'type' => 'defense',
            'finish_at' => $finish_time,
            'start_at' => $init_time
        ]);

        $data['status'] = array(
            'statusCode' => 200,
            'message' => 'defense in progress',
            'result' => array(
                'module' => array(
                    'name' => $module->name,
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'defense_name' => $defense_config->name,
                'qty' => $request->qty,
                'total_time_minutes' => (int)($defense_config->time*$request->qty),
                'date_init' => $init_time->format('U'),
                'date_finish' => $finish_time->format('U')
            )
        );
        return response()->json($data, 200);
    }

}
