<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Modules;
use App\Models\Config;
use Illuminate\Http\Request;
use DB;


class ResourcesController extends Controller
{
    public function update_resources()
    {
        //runs every minute

        $users = User::where('status', 'active')->get();
        $config = Config::first();

        foreach ($users as $user) {

            $modules = Modules::where('user_id', $user->id)->get();

            foreach ($modules as $module) {

                Modules::where('id', $module->id)->update([
                    'resources_1' => ($module->resources_1 + ($module->resources_building_lvl_1 * $config->resources_building_1_multiplier)),
                    'resources_2' => ($module->resources_2 + ($module->resources_building_lvl_2 * $config->resources_building_2_multiplier)),
                    'resources_3' => ($module->resources_3 + ($module->resources_building_lvl_3 * $config->resources_building_3_multiplier)),
                    'resources_4' => ($module->resources_4 + ($module->resources_building_lvl_4 * $config->resources_building_4_multiplier)),
                ]);
            }
        }

        $data['status'] = "success";
        $data['msg'] = 'Users updated:' . count($users);
        $data['data'] = array();
        return response()->json($data, 200);
    }


    public function get_user_resources($user_id)
    {

        $modules = Modules::where('user_id', $user_id)->get();
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

        $data['status'] = "success";
        $data['msg'] = 'resources in all modules';
        $data['data'] = $modules_arr;
        return response()->json($data, 200);
    }

    public function get_module_resources($module_id)
    {

        $module = Modules::where('id', $module_id)->first();


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


        $data['status'] = "success";
        $data['msg'] = 'resources in module';
        $data['data'] = $module_info;
        return response()->json($data, 200);
    }

    public function get_module_lvl_resources($module_id)
    {
        $module = Modules::where('id', $module_id)->first();

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


        $data['status'] = "success";
        $data['msg'] = 'resources in module';
        $data['data'] = $module_info;
        return response()->json($data, 200);
    }
}
