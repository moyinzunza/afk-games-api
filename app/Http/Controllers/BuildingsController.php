<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Modules;
use App\Models\ConfigResources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BuildingsController extends Controller
{
    public function upgrade_resources_building(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'building_id' => 'required|integer|between:1,3',
            'module_id' => 'required',
            'user_id' => 'required',
            'token' => 'required'
        ]);

        if ($validator->fails()) {

            $data['status'] = "error";
            $data['msg'] = "params missing";
            $data['data'] = $validator->errors();
            return response()->json($data, 200);
        }

        $user = User::where('id', $request->user_id)->first();
        $module = Modules::where('id', $request->module_id)->where('user_id', $request->user_id)->first();
        if (empty($user)) {
            $data['status'] = "error";
            $data['msg'] = "user don't exist";
            return response()->json($data, 200);
        }
        if (empty($module)) {
            $data['status'] = "error";
            $data['msg'] = "module don't exist";
            return response()->json($data, 200);
        }
        if ($request->token != $user->remember_token) {
            $data['status'] = "error";
            $data['msg'] = "invalid token";
            return response()->json($data, 200);
        }

        $next_lvl = $module->{'resources_building_lvl_' . $request->building_id} + 1;
        $next_lvl_price = $this->get_single_price_time($request->building_id, $next_lvl);
        $price_resources_1 = $next_lvl_price['resources_1'];
        $price_resources_2 = $next_lvl_price['resources_2'];
        $price_resources_3 = $next_lvl_price['resources_3'];

        if (
            $price_resources_1 > $module->resources_1
            || $price_resources_2 > $module->resources_2
            || $price_resources_3 > $module->resources_3
        ) {
            $data['module_info'] = $module;
            $data['next_lvl_price'] = $next_lvl_price;
            $data['status'] = "error";
            $data['msg'] = "insufficient resources";
            return response()->json($data, 200);
        }

        $data['status'] = "success";
        $data['next_lvl_price'] = $next_lvl_price;
        $data['msg'] = "login successfully";
        $data['token'] = $request->token;
        return response()->json($data, 200);
    }

    public function get_single_price_time($building_id, $building_level)
    {

        $config_resources = ConfigResources::get();
        $selected_resource = $config_resources[$building_id-1];

        $multiplier = $this->fibonacci($building_level);

        return array(
            $config_resources[0]->name => (int)($selected_resource->resources1_price_multiplier * $multiplier),
            $config_resources[1]->name => (int)($selected_resource->resources1_price_multiplier * $multiplier),
            $config_resources[2]->name => (int)($selected_resource->resources1_price_multiplier * $multiplier),
            "time_minutes" => (int)($selected_resource->time_multiplier * $multiplier) 
        );
    }

    public function get_resources_buildings_prices()
    {
        $config_resources = ConfigResources::get();
        $prices_arr = array();

        for ($i = 1; $i <= 30; $i++) {

            $buildings_arr = array(
                "level_" . $i => [
                    $config_resources[0]->name."_building" => $this->get_single_price_time(1, $i),
                    $config_resources[1]->name."_building" => $this->get_single_price_time(2, $i),
                    $config_resources[2]->name."_building" => $this->get_single_price_time(3, $i)
                ]
            );

            array_push($prices_arr, $buildings_arr);
        }

        $data['status'] = "success";
        $data['buildings'] = $prices_arr;
        return response()->json($data, 200);
    }

    public function fibonacci($n)
    {
        $fibonacci  = [0, 1];

        for ($i = 2; $i <= $n; $i++) {
            $fibonacci[] = $fibonacci[$i - 1] + $fibonacci[$i - 2];
        }
        return $fibonacci[$n];
    }
}
