<?php

namespace App\Http\Controllers\Api;

use App\Models\Facilities;
use App\Models\Resources;

class CalculatePricesTimeController extends Controller
{
    
    public static function fibonacci($n)
    {
        $fibonacci  = [0, 1];

        for ($i = 2; $i <= $n; $i++) {
            $fibonacci[] = $fibonacci[$i - 1] + $fibonacci[$i - 2];
        }
        return $fibonacci[$n];
    }

    public static function get_single_price_time($building_id, $building_level)
    {

        $config_resources = Resources::get();
        $selected_resource = $config_resources[$building_id - 1];

        $multiplier = CalculatePricesTimeController::fibonacci($building_level);

        return array(
            $config_resources[0]->name => (int)($selected_resource->resources1_price_multiplier * $multiplier),
            $config_resources[1]->name => (int)($selected_resource->resources2_price_multiplier * $multiplier),
            $config_resources[2]->name => (int)($selected_resource->resources3_price_multiplier * $multiplier),
            "time_minutes" => (int)($selected_resource->time_multiplier * $multiplier),
            "next_level_generate_qty" => (int)($building_level*$selected_resource->generate_multiplier)
        );
    }

    public static function get_facility_single_price_time($building_id, $building_level)
    {

        $config_resources = Resources::get();
        $selected_facility = Facilities::where('id', $building_id)->first();

        $multiplier = CalculatePricesTimeController::fibonacci($building_level);

        return array(
            $config_resources[0]->name => (int)($selected_facility->resources1_price_multiplier * $multiplier),
            $config_resources[1]->name => (int)($selected_facility->resources2_price_multiplier * $multiplier),
            $config_resources[2]->name => (int)($selected_facility->resources3_price_multiplier * $multiplier),
            "time_minutes" => (int)($selected_facility->time_multiplier * $multiplier)
        );
    }

}





