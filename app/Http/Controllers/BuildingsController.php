<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Modules;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BuildingsController extends Controller
{
    public function upgrade_resources_building(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'building_id' => 'required|integer|between:1,4',
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
        $next_lvl_price = $this->get_single_price($request->building_id, $next_lvl);
        $price_resources_1 = $next_lvl_price['resources_1'];
        $price_resources_2 = $next_lvl_price['resources_2'];
        $price_resources_3 = $next_lvl_price['resources_3'];
        $price_resources_4 = $next_lvl_price['resources_4'];

        if (
            $price_resources_1 > $module->resources_1
            || $price_resources_2 > $module->resources_2
            || $price_resources_3 > $module->resources_3
            || $price_resources_4 > $module->resources_4
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

    public function get_single_price($building_id, $building_level)
    {

        $config = Config::first();
        ${'resources_building_' . $building_id . '_price_multiplier'} = json_decode($config->{'resources_building_' . $building_id . '_price_multiplier'});

        $multiplier = $this->fibonacci($building_level);

        return array(
            "resources_1" => (int)(${'resources_building_' . $building_id . '_price_multiplier'}->resources_1 * $multiplier),
            "resources_2" => (int)(${'resources_building_' . $building_id . '_price_multiplier'}->resources_2 * $multiplier),
            "resources_3" => (int)(${'resources_building_' . $building_id . '_price_multiplier'}->resources_3 * $multiplier),
            "resources_4" => (int)(${'resources_building_' . $building_id . '_price_multiplier'}->resources_4 * $multiplier)
        );
    }

    public function get_resources_buildings_prices()
    {
        $prices_arr = array();

        for ($i = 1; $i <= 30; $i++) {

            $buildings_arr = array(
                "level_" . $i => [
                    "building_1" => $this->get_single_price(1, $i),
                    "building_2" => $this->get_single_price(2, $i),
                    "building_3" => $this->get_single_price(3, $i),
                    "building_4" => $this->get_single_price(4, $i),
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
