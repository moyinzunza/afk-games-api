<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Modules;
use App\Models\ConfigResources;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;


class ResourcesController extends Controller
{
    public function update_resources()
    {
        //runs every minute

        $users = User::where('status', 'active')->get();
        $config_resources = json_decode(ConfigResources::get());

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


    public function get_user_resources()
    {
        $modules = Modules::where('user_id', Auth::id())->get();
        $config_resources = json_decode(ConfigResources::get());
        $modules_arr = array();

        foreach ($modules as $module) {

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
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
        $config_resources = json_decode(ConfigResources::get());
        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();

        if (!empty($module)) {

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    $config_resources[0]->name.'_building_lvl' => $module->resources_building_lvl_1,
                    $config_resources[1]->name.'_building_lvl' => $module->resources_building_lvl_2,
                    $config_resources[2]->name.'_building_lvl' => $module->resources_building_lvl_3
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
