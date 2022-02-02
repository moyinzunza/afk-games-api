<?php

namespace App\Http\Controllers;

use App\Models\UpgradesLine;
use App\Models\Modules;
use App\Models\ConfigResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use DateTime;
use DateInterval;


class ResourcesBuildingsController extends Controller
{

    public function get_resources_buildings_prices()
    {
        $config_resources = ConfigResources::get();
        $prices_arr = array();

        for ($i = 1; $i <= 30; $i++) {

            $buildings_arr = array(
                "level_" . $i => [
                    $config_resources[0]->name . "_building" => $this->get_single_price_time(1, $i),
                    $config_resources[1]->name . "_building" => $this->get_single_price_time(2, $i),
                    $config_resources[2]->name . "_building" => $this->get_single_price_time(3, $i)
                ]
            );

            array_push($prices_arr, $buildings_arr);
        }

        $data['status'] = "success";
        $data['buildings'] = $prices_arr;
        return response()->json($data, 200);
    }

    public function fibonacci($n)
    {
        $fibonacci  = [0, 1];

        for ($i = 2; $i <= $n; $i++) {
            $fibonacci[] = $fibonacci[$i - 1] + $fibonacci[$i - 2];
        }
        return $fibonacci[$n];
    }

    public function get_single_price_time($building_id, $building_level)
    {

        $config_resources = ConfigResources::get();
        $selected_resource = $config_resources[$building_id - 1];

        $multiplier = $this->fibonacci($building_level);

        return array(
            $config_resources[0]->name => (int)($selected_resource->resources1_price_multiplier * $multiplier),
            $config_resources[1]->name => (int)($selected_resource->resources2_price_multiplier * $multiplier),
            $config_resources[2]->name => (int)($selected_resource->resources3_price_multiplier * $multiplier),
            "time_minutes" => (int)($selected_resource->time_multiplier * $multiplier)
        );
    }

    public function get_module_resources($module_id)
    {
        $config_resources = json_decode(ConfigResources::get());
        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();
        if (!empty($module)) {

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'buildings_levels' => array(
                    $config_resources[0]->name => array(
                        'level' => $module->resources_building_lvl_1,
                        'next_level_price_time' => $this->get_single_price_time(1, $module->resources_building_lvl_1 + 1)
                    ),
                    $config_resources[1]->name => array(
                        'level' => $module->resources_building_lvl_2,
                        'next_level_price_time' => $this->get_single_price_time(2, $module->resources_building_lvl_2 + 1)
                    ),
                    $config_resources[2]->name => array(
                        'level' => $module->resources_building_lvl_3,
                        'next_level_price_time' => $this->get_single_price_time(3, $module->resources_building_lvl_3 + 1)
                    )
                ),
                'generate_qty_minute' => array(
                    $config_resources[0]->name . '_qty_minute' => $module->resources_building_lvl_1 * $config_resources[0]->generate_multiplier,
                    $config_resources[1]->name . '_qty_minute' => $module->resources_building_lvl_2 * $config_resources[1]->generate_multiplier,
                    $config_resources[2]->name . '_qty_minute' => $module->resources_building_lvl_3 * $config_resources[2]->generate_multiplier
                ),


            );

            $data['status'] = array("statusCode" => 200, "message" => 'resources in module');
            $data['result'] = $module_info;
            return response()->json($data, 200);
        } else {

            $data['status'] = array("statusCode" => 400, "message" => 'no module found');
            return response()->json($data, 400);
        }
    }

    public function upgrade_resources_building(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'building_id' => 'required|integer|between:1,3',
            'module_id' => 'required'
        ]);

        if ($validator->fails()) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'The given data was invalid.'
            );
            $data['result'] = array('errors' => $validator->errors());
            return response()->json($data, 400);
        }

        $module = Modules::where('id', $request->module_id)->where('user_id', Auth::id())->first();

        if (empty($module)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Module not found.'
            );
            return response()->json($data, 400);
        }

        $config_resources = ConfigResources::get();

        $next_lvl = $module->{'resources_building_lvl_' . $request->building_id} + 1;
        $next_lvl_price = $this->get_single_price_time($request->building_id, $next_lvl);
        $price_resources_1 = $next_lvl_price[$config_resources[0]->name];
        $price_resources_2 = $next_lvl_price[$config_resources[1]->name];
        $price_resources_3 = $next_lvl_price[$config_resources[2]->name];

        if (
            $price_resources_1 > $module->resources_1
            || $price_resources_2 > $module->resources_2
            || $price_resources_3 > $module->resources_3
        ) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'insufficient resources.',
                'result' => array(
                    'module' => array(
                        'name' => $module->name,
                        $config_resources[0]->name => $module->resources_1,
                        $config_resources[1]->name => $module->resources_2,
                        $config_resources[2]->name => $module->resources_3,
                        'building_upgrade' => $config_resources[$request->building_id - 1]->name,
                        'current_level' => $module->{'resources_building_lvl_' . $request->building_id}
                    ),
                    'next_lvl_price_time' => $next_lvl_price
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

        $upgrade_line = UpgradesLine::where('user_id', Auth::id())->where('module_id', $module->id)->where('upgrade_id', $request->building_id)->where('type', 'resources_building')->first();

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
            'upgrade_id' => $request->building_id,
            'type' => 'resources_building',
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
                    'building_upgrade' => $config_resources[$request->building_id - 1]->name,
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