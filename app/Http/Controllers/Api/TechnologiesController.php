<?php

namespace App\Http\Controllers\Api;

use App\Models\Technologies;
use App\Models\TechnologiesConditions;
use App\Models\UsersTechnologies;
use App\Models\Modules;
use App\Models\Resources;
use App\Models\UpgradesLine;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\CalculatePricesTimeController;
use App\Models\Facilities;
use App\Models\UsersFacilities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DateInterval;
use DateTime;

class TechnologiesController extends Controller
{
    public function get_module_technologies($module_id)
    {

        $technologies_config = Technologies::get();
        $config_resources = json_decode(Resources::get());

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();
        $technologies_arr = array();
        if (!empty($module)) {

            $technologies_upgrades_line = UpgradesLine::where('user_id', Auth::id())->where('type', 'technologies')->where('module_id', $module_id)->get();

            $upgrade_line = array();

            foreach ($technologies_upgrades_line as $upgrades_line) {

                $init_unix_date = new DateTime($upgrades_line->created_at);
                $init_unix_date = $init_unix_date->format('U');
                $end_unix_date = new DateTime($upgrades_line->finish_at);
                $end_unix_date = $end_unix_date->format('U');

                $total_time_minutes = ($end_unix_date - $init_unix_date) / 60;

                $next_level = 1;
                $current_technology = UsersTechnologies::where('user_id', Auth::id())->where('technology_id', $upgrades_line->upgrade_id)->first();
                if (!empty($current_technology)) {
                    $next_level = $current_technology->level + 1;
                }

                $single_upgrade = array(
                    'technology_id' => $upgrades_line->upgrade_id,
                    'technology_upgrade' => $technologies_config[$upgrades_line->upgrade_id - 1]->name,
                    'next_level' => $next_level,
                    'total_time_minutes' => $total_time_minutes,
                    'date_init' => $init_unix_date,
                    'date_finish' => $end_unix_date
                );

                array_push($upgrade_line, $single_upgrade);
            }

            foreach ($technologies_config as $technolgy) {

                $user_technology = UsersTechnologies::where('user_id', Auth::id())->where('technology_id', $technolgy->id)->first();
                if (empty($user_technology)) {

                    UsersTechnologies::create([
                        'user_id' => Auth::id(),
                        'technology_id' => $technolgy->id,
                        'level' => 0
                    ]);

                    $user_technology = UsersTechnologies::where('user_id', Auth::id())->where('technology_id', $technolgy->id)->first();
                }

                $conditions_array = array();
                $conditions_arr = TechnologiesConditions::where('technology_id', $technolgy->id)->get();
                foreach ($conditions_arr as $condition) {

                    $name = "";
                    $fulfilled = false;
                    if ($condition->type == 'facility') {
                        $name = Facilities::where('id', $condition->type_id)->first()->name;
                        if(!empty(UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $condition->type_id)->where('level', '>=', $condition->min_level)->first())){
                            $fulfilled = true;
                        }
                    } else if ($condition->type == 'technology') {
                        $name = Technologies::where('id', $condition->type_id)->first()->name;
                        if(!empty(UsersTechnologies::where('user_id', Auth::id())->where('technology_id', $condition->type_id)->where('level', '>=', $condition->min_level)->first())){
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

                $technology_arr = array(
                    'id' => $technolgy->id,
                    'name' => $technolgy->name,
                    'image' => $technolgy->image_url,
                    'level' => $user_technology->level,
                    'next_level_price_time' => CalculatePricesTimeController::get_technology_single_price_time($technolgy->id, $user_technology->level + 1),
                    'require' => $conditions_array
                );

                array_push($technologies_arr, $technology_arr);
            }

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'levels' => $technologies_arr,
                'upgrades_line' => $upgrade_line
            );

            $data['status'] = array("statusCode" => 200, "message" => 'technologies in module');
            $data['result'] = $module_info;
            return response()->json($data, 200);
        } else {
            $data['status'] = array("statusCode" => 400, "message" => 'no module found');
            return response()->json($data, 400);
        }
    }

    public function upgrade_technology(Request $request, $module_id)
    {
        $technologies_config = Technologies::get();
        $validator = Validator::make($request->all(), [
            'technology_id' => 'required|integer|between:1,' . count($technologies_config)
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
        $user_technology = UsersTechnologies::where('user_id', Auth::id())->where('technology_id', $request->technology_id)->first();
        if (empty($user_technology)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'technology not found.'
            );
            return response()->json($data, 400);
        }

        $next_lvl = $user_technology->level + 1;
        $next_lvl_price = CalculatePricesTimeController::get_technology_single_price_time($request->technology_id, $next_lvl);
        $price_resources_1 = $next_lvl_price[$config_resources[0]->name];
        $price_resources_2 = $next_lvl_price[$config_resources[1]->name];
        $price_resources_3 = $next_lvl_price[$config_resources[2]->name];


        //Conditions
        $conditions_array = array();
        $conditions_arr = TechnologiesConditions::where('technology_id', $request->technology_id)->get();
        foreach ($conditions_arr as $condition) {

            $name = "";
            $fulfilled = false;
            if ($condition->type == 'facility') {
                $name = Facilities::where('id', $condition->type_id)->first()->name;
                if(!empty(UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $condition->type_id)->where('level', '>=', $condition->min_level)->first())){
                    $fulfilled = true;
                }
            } else if ($condition->type == 'technology') {
                $name = Technologies::where('id', $condition->type_id)->first()->name;
                if(!empty(UsersTechnologies::where('user_id', Auth::id())->where('technology_id', $condition->type_id)->where('level', '>=', $condition->min_level)->first())){
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
                        $config_resources[2]->name => $module->resources_3,
                        'technology_upgrade' => $technologies_config[$request->technology_id - 1]->name,
                        'current_level' => $user_technology->level
                    ),
                    'next_lvl_price_time' => $next_lvl_price,
                    'require' => $conditions_array
                )
            );
            return response()->json($data, 400);
        }


        $module->resources_1 -= $next_lvl_price[$config_resources[0]->name];
        $module->resources_2 -= $next_lvl_price[$config_resources[1]->name];
        $module->resources_3 -= $next_lvl_price[$config_resources[2]->name];
        $module->save();

        $init_time = new DateTime();
        $finish_time = new DateTime();
        $finish_time->add(new DateInterval('PT' . $next_lvl_price['time_minutes'] . 'M'));

        $upgrade_line = UpgradesLine::where('user_id', Auth::id())->where('module_id', $module->id)->where('upgrade_id', $request->technology_id)->where('type', 'technologies')->first();

        if (!empty($upgrade_line)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Upgrade already in progress.'
            );
            return response()->json($data, 400);
        }

        UpgradesLine::create([
            'user_id' => Auth::id(),
            'module_id' => $module->id,
            'upgrade_id' => $request->technology_id,
            'type' => 'technologies',
            'finish_at' => $finish_time
        ]);

        $data['status'] = array(
            'statusCode' => 200,
            'message' => 'Upgrade in progress',
            'result' => array(
                'module' => array(
                    'name' => $module->name,
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3,
                    'technology_upgrade' => $technologies_config[$request->technology_id - 1]->name,
                    'next_level' => $next_lvl
                ),
                'total_time_minutes' => $next_lvl_price['time_minutes'],
                'date_init' => $init_time->format('U'),
                'date_finish' => $finish_time->format('U')
            )
        );
        return response()->json($data, 200);
    }
}
