<?php

namespace App\Http\Controllers\Api;

use App\Models\Facilities;
use App\Models\UsersFacilities;
use App\Models\Modules;
use App\Models\Resources;
use App\Models\UpgradesLine;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\CalculatePricesTimeController;
use DateTime;

class FacilitiesController extends Controller
{

    public function get_module_facilities($module_id)
    {

        $facilities_config = Facilities::get();
        $config_resources = json_decode(Resources::get());

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();
        $facilities_arr = array();
        if (!empty($module)) {

            $building_upgrades_line = UpgradesLine::where('user_id', Auth::id())->where('type', 'facilities')->where('module_id', $module_id)->get();

            $upgrade_line = array();

            foreach ($building_upgrades_line as $upgrades_line) {

                $init_unix_date = new DateTime($upgrades_line->created_at);
                $init_unix_date = $init_unix_date->format('U');
                $end_unix_date = new DateTime($upgrades_line->finish_at);
                $end_unix_date = $end_unix_date->format('U');

                $total_time_minutes = ($end_unix_date - $init_unix_date) / 60;

                $next_level = 1;
                $current_facility = UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $upgrades_line->upgrade_id)->first();
                if (!empty($upgrade_facility)) {
                    $next_level = $current_facility->level + 1;
                }

                $single_upgrade = array(
                    'building_upgrade' => $facilities_config[$upgrades_line->upgrade_id]->name,
                    'next_level' => $next_level,
                    'total_time_minutes' => $total_time_minutes,
                    'date_init' => $init_unix_date,
                    'date_finish' => $end_unix_date
                );

                array_push($upgrade_line, $single_upgrade);
            }

            foreach ($facilities_config as $facility) {

                $user_facility = UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $facility->id)->first();
                if(empty($user_facility)){

                    UsersFacilities::create([
                        'user_id' => Auth::id(),
                        'module_id' => $module->id,
                        'facility_id' => $facility->id,
                        'level' => 0
                    ]);

                    $user_facility = UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $facility->id)->first();
                }

                $facility_arr = array(
                    'id' => 1,
                    'name' => $facility->name,
                    'image' => $facility->image_url,
                    'level' => $user_facility->level,
                    'next_level_price_time' => CalculatePricesTimeController::get_facility_single_price_time($facility->id, $user_facility->level+1)
                );

                array_push($facilities_arr, $facility_arr);
            }

            $module_info = array(
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'levels' => $facilities_arr,
                'upgrades_line' => $upgrade_line
            );

            $data['status'] = array("statusCode" => 200, "message" => 'facilities in module');
            $data['result'] = $module_info;
            return response()->json($data, 200);
        } else {
            $data['status'] = array("statusCode" => 400, "message" => 'no module found');
            return response()->json($data, 400);
        }
    }
}
