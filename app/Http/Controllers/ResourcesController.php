<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Modules;
use App\Models\ConfigResources;

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

}
