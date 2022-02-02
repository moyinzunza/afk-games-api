<?php

namespace App\Http\Controllers;

use App\Models\Modules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ConfigResources;


class HomeController extends Controller
{
    public function get_home_data(Request $request)
    {

        $config_resources = ConfigResources::get();

        if (empty($request->module)) {
            $module = Modules::where('user_id', Auth::id())->first();
        } else {
            $module = Modules::where('id', $request->module)->where('user_id', Auth::id())->first();
        }

        if (empty($module)) {
            return response()->json(array(
                'status' => array(
                    'statusCode' => 400,
                    'message' => 'no module found'
                )
            ), 400);
        }

        return response()->json([
            'status' => array(
                'statusCode' => 200,
                'message' => 'Successfully get info'
            ),
            'result' => array(
                'user' => $request->user(),
                'module' => array(
                    'name' => $module->name,
                    'position' => json_decode($module->position),
                    'resources' => array(
                        $config_resources[0]->name => $module->resources_1,
                        $config_resources[1]->name => $module->resources_2,
                        $config_resources[2]->name => $module->resources_3,
                    ),
                    'construction_space' => $module->construction_space,
                    'buildings_levels' => array(
                        $config_resources[0]->name . '_level' => $module->resources_building_lvl_1,
                        $config_resources[1]->name . '_level' => $module->resources_building_lvl_2,
                        $config_resources[2]->name . '_level' => $module->resources_building_lvl_3
                    ),
                    'generate_qty_minute' => array(
                        $config_resources[0]->name . '_qty_minute' => $module->resources_building_lvl_1 * $config_resources[0]->generate_multiplier,
                        $config_resources[1]->name . '_qty_minute' => $module->resources_building_lvl_2 * $config_resources[1]->generate_multiplier,
                        $config_resources[2]->name . '_qty_minute' => $module->resources_building_lvl_3 * $config_resources[2]->generate_multiplier
                    )
                ),

            )
        ], 200);
    }
}
