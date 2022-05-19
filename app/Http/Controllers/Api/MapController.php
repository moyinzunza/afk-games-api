<?php

namespace App\Http\Controllers\Api;

use App\Models\Modules;
use App\Models\Users;
use Illuminate\Http\Request;

class MapController extends Controller
{

    public function get_map($module_id, Request $request)
    {
        if(!empty($request->position_y) || !empty($request->position_z)){
            $position_y = $request->position_y;
            $position_z = $request->position_z;
        }else{
            $module = Modules::where('id', $module_id)->first();
            $position_y = $module->position_y;
            $position_z = $module->position_z;
        }
        $modules = Modules::where('position_y', $position_y)->where('position_z', $position_z)->get();
        $modules_arr = array();

        foreach ($modules as $module) {

            $user_data = Users::where('id', $module->user_id)->first();
            $module_info = array(
                'id' => $module->id,
                'image_url' => $module->image_url,
                'name' => $module->name,
                "positionPlanet" => $module->position_x,
                'position' => array(
                    'galaxy' => $module->position_z,
                    'solar_system' => $module->position_y,
                    'planet' => $module->position_x
                ),
                'user_data' => array(
                    'id' => $user_data->id,
                    'username' => $user_data->username,
                    'status' => $user_data->status
                )

            );
            array_push($modules_arr, $module_info);
        }

        $data['status'] = array("statusCode" => 200, "message" => 'map data');
        $data['result'] = $modules_arr;
        return response()->json($data, 200);
    }
}
