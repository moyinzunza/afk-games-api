<?php

namespace App\Http\Controllers;

use App\Models\Modules;
use App\Models\ConfigResources;
use Illuminate\Support\Facades\Auth;


class ModulesController extends Controller
{

    public function get_modules()
    {
        $modules = Modules::where('user_id', Auth::id())->get();
        $config_resources = json_decode(ConfigResources::get());
        $modules_arr = array();

        foreach ($modules as $module) {

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'construction_space' => $module->construction_space,
                'position' => json_decode($module->position)
            );

            array_push($modules_arr, $module_info);
        }

        $data['status'] = array("statusCode" => 200, "message" => 'resources in all modules');
        $data['result'] = $modules_arr;
        return response()->json($data, 200);
    }
}




