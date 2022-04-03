<?php

namespace App\Http\Controllers\Api;

use App\Models\Modules;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ResourcesBuildings;

class HomeController extends Controller
{
    public function get_home_data(Request $request)
    {

        $config_resources = ResourcesBuildings::get();

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

        $user = User::where('id', Auth::id())->first();
        $user->status = 'active';
        $user->save();


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
                    'position' => array(
                        'galaxy' => $module->position_z,
                        'solar_system' => $module->position_y,
                        'planet' => $module->position_x
                    ),
                    'resources' => array(
                        array(
                            'name' => $config_resources[0]->name,
                            'image' => $config_resources[0]->image_url,
                            'qty' => $module->resources_1,
                            'building_level' => $module->resources_building_lvl_1,
                            'generate_qty_minute' => $module->resources_building_lvl_1 * $config_resources[0]->generate_multiplier
                        ),
                        array(
                            'name' => $config_resources[1]->name,
                            'image' => $config_resources[1]->image_url,
                            'qty' => $module->resources_2,
                            'building_level' => $module->resources_building_lvl_2,
                            'generate_qty_minute' => $module->resources_building_lvl_2 * $config_resources[1]->generate_multiplier
                        ),
                        array(
                            'name' => $config_resources[2]->name,
                            'image' => $config_resources[2]->image_url,
                            'qty' => $module->resources_3,
                            'building_level' => $module->resources_building_lvl_3,
                            'generate_qty_minute' => $module->resources_building_lvl_3 * $config_resources[2]->generate_multiplier
                        ),
                        array(
                            'name' => 'Orbs',
                            'image' => 'https://videohive.img.customer.envatousercontent.com/files/226842041/OrbPlexusEnergyLogoReveal_PreviewImage.jpg?auto=compress%2Cformat&fit=crop&crop=top&max-h=8000&max-w=590&s=e900018fdcc367b6b4096eddfa811f0c',
                            'qty' => $user->paid_resource,
                            'building_level' => 0,
                            'generate_qty_minute' => 0
                        )
                    ),
                    'construction_space' => $module->construction_space,
                ),

            )
        ], 200);
    }
}
