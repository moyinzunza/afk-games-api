<?php

namespace App\Http\Controllers\Api;

use App\Models\Modules;
use Illuminate\Support\Facades\Auth;


class ModulesController extends Controller
{

    public function get_modules()
    {
        $modules = Modules::where('user_id', Auth::id())->get();
        $modules_arr = array();

        foreach ($modules as $module) {

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'construction_space' => $module->construction_space,
                'position' => array(
                    'galaxy' => $module->position_z,
                    'solar_system' => $module->position_y,
                    'planet' => $module->position_x
                )
            );

            array_push($modules_arr, $module_info);
        }

        $data['status'] = array("statusCode" => 200, "message" => 'resources in all modules');
        $data['result'] = $modules_arr;
        return response()->json($data, 200);
    }
}
