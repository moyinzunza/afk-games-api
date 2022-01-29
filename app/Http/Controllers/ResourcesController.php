<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Modules;
use App\Models\Config;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;


class ResourcesController extends Controller
{
    public function update_resources()
    {
        //runs every minute

        $users = User::where('status', 'active')->get();
        $generate_resources_buildings_multiplier = json_decode(Config::first()->generate_resources_buildings_multiplier);

        foreach ($users as $user) {

            $modules = Modules::where('user_id', $user->id)->get();

            foreach ($modules as $module) {

                Modules::where('id', $module->id)->update([
                    'resources_1' => ($module->resources_1 + ($module->resources_building_lvl_1 * $generate_resources_buildings_multiplier->generate_resources_1)),
                    'resources_2' => ($module->resources_2 + ($module->resources_building_lvl_2 * $generate_resources_buildings_multiplier->generate_resources_2)),
                    'resources_3' => ($module->resources_3 + ($module->resources_building_lvl_3 * $generate_resources_buildings_multiplier->generate_resources_3)),
                    'resources_4' => ($module->resources_4 + ($module->resources_building_lvl_4 * $generate_resources_buildings_multiplier->generate_resources_4)),
                ]);
            }
        }

        $data['status'] = "success";
        $data['msg'] = 'Users updated:' . count($users);
        $data['data'] = array();
        return response()->json($data, 200);
    }


    public function get_user_resources()
    {
        $modules = Modules::where('user_id', Auth::id())->get();
        $modules_arr = array();

        foreach ($modules as $module) {

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    'resources_1' => $module->resources_1,
                    'resources_2' => $module->resources_2,
                    'resources_3' => $module->resources_3,
                    'resources_4' => $module->resources_4,
                )
            );

            array_push($modules_arr, $module_info);
        }

        $data['status'] = array("statusCode" => 200, "message" => 'resources in all modules');
        $data['result'] = $modules_arr;
        return response()->json($data, 200);
    }

    public function get_module_resources($module_id)
    {

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();
        if (!empty($module)) {

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    'resources_1' => $module->resources_1,
                    'resources_2' => $module->resources_2,
                    'resources_3' => $module->resources_3,
                    'resources_4' => $module->resources_4,
                )
            );

            $data['status'] = array("statusCode" => 200, "message" => 'resources in module');
            $data['result'] = $module_info;
            return response()->json($data, 200);
        } else {

            $data['status'] = array("statusCode" => 400, "message" => 'no module found');
            return response()->json($data, 400);
        }
    }

    public function get_module_lvl_resources($module_id)
    {
        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();
        if (!empty($module)) {

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    'resources_building_lvl_1' => $module->resources_building_lvl_1,
                    'resources_building_lvl_2' => $module->resources_building_lvl_2,
                    'resources_building_lvl_3' => $module->resources_building_lvl_3,
                    'resources_building_lvl_4' => $module->resources_building_lvl_4,
                )
            );
            $data['status'] = array("statusCode" => 200, "message" => 'resources in module');
            $data['result'] = $module_info;
            return response()->json($data, 200);
        } else {
            $data['status'] = array("statusCode" => 400, "message" => 'no module found');
            return response()->json($data, 400);
        }
    }
}
