<?php

namespace App\Http\Controllers\Api;

use App\Models\Modules;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Resources;


class HomeController extends Controller
{
    public function get_home_data(Request $request)
    {

        $config_resources = Resources::get();

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

        User::where('id', Auth::id())->update([
            'status' => 'active'
        ]);

        return response()->json([
            'status' => array(
                'statusCode' => 200,
                'message' => 'Successfully get info'
            ),
            'result' => array(
                'user' => $request->user(),
                'module' => array(
                    'id' => $module->id,
                    'name' => $module->name,
                    'position' => json_decode($module->position),
                    'resources' => array(
                        $config_resources[0]->name => array(
                            'qty' => $module->resources_1,
                            'building_level' => $module->resources_building_lvl_1,
                            'generate_qty_minute' => $module->resources_building_lvl_1 * $config_resources[0]->generate_multiplier
                        ),
                        $config_resources[1]->name => array(
                            'qty' => $module->resources_2,
                            'building_level' => $module->resources_building_lvl_2,
                            'generate_qty_minute' => $module->resources_building_lvl_2 * $config_resources[1]->generate_multiplier
                        ),
                        $config_resources[2]->name => array(
                            'qty' => $module->resources_3,
                            'building_level' => $module->resources_building_lvl_3,
                            'generate_qty_minute' => $module->resources_building_lvl_3 * $config_resources[2]->generate_multiplier
                        ),
                    ),
                    'construction_space' => $module->construction_space,
                ),

            )
        ], 200);
    }
}
