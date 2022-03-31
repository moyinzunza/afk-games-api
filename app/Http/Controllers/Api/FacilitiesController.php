<?php

namespace App\Http\Controllers\Api;

use App\Models\Facilities;
use App\Models\UsersFacilities;
use App\Models\Modules;
use App\Models\Resources;
use App\Models\UpgradesLine;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\CalculatePricesTimeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DateInterval;
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

                $init_unix_date = new DateTime($upgrades_line->start_at);
                $init_unix_date = $init_unix_date->format('U');
                $end_unix_date = new DateTime($upgrades_line->finish_at);
                $end_unix_date = $end_unix_date->format('U');

                $total_time_minutes = ($end_unix_date - $init_unix_date) / 60;

                $next_level = 1;
                $current_facility = UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $upgrades_line->upgrade_id)->first();
                if (!empty($current_facility)) {
                    $next_level = $current_facility->level + 1;
                }

                $single_upgrade = array(
                    'id_line' => $upgrades_line->id,
                    'image' => $facilities_config[$upgrades_line->upgrade_id-1]->image_url,
                    'id' => $upgrades_line->upgrade_id,
                    'name' => $facilities_config[$upgrades_line->upgrade_id -1]->name,
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
                    'id' => $facility->id,
                    'name' => $facility->name,
                    'image' => $facility->image_url,
                    'level' => $user_facility->level,
                    'price_time' => CalculatePricesTimeController::get_facility_single_price_time($facility->id, $user_facility->level+1)
                );

                array_push($facilities_arr, $facility_arr);
            }

            $module_info = array(
                'image' => 'https://media.wired.com/photos/593387807965e75f5f3c8847/master/w_2560%2Cc_limit/Multi-dome_base_being_constructed.jpg',
                'id' => $module->id,
                'name' => $module->name,
                'resources' => array(
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3
                ),
                'items' => $facilities_arr,
                'items_line' => $upgrade_line
            );

            $data['status'] = array("statusCode" => 200, "message" => 'facilities in module');
            $data['result'] = $module_info;
            return response()->json($data, 200);
        } else {
            $data['status'] = array("statusCode" => 400, "message" => 'no module found');
            return response()->json($data, 400);
        }
    }

    public function upgrade_facility(Request $request, $module_id)
    {
        $facilities_config = Facilities::get();
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|between:1,'.count($facilities_config)
        ]);

        if ($validator->fails()) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'The given data was invalid.'
            );
            $data['result'] = array('errors' => $validator->errors());
            return response()->json($data, 400);
        }

        $module = Modules::where('id', $module_id)->where('user_id', Auth::id())->first();

        if (empty($module)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Module not found.'
            );
            return response()->json($data, 400);
        }

        $config_resources = Resources::get();
        $user_facility = UsersFacilities::where('module_id', $module_id)->where('user_id', Auth::id())->where('facility_id', $request->id)->first();
        if (empty($user_facility)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'facility not found.'
            );
            return response()->json($data, 400);
        }

        $next_lvl = $user_facility->level + 1;
        $next_lvl_price = CalculatePricesTimeController::get_facility_single_price_time($request->id, $next_lvl);
        $price_resources_1 = $next_lvl_price[$config_resources[0]->name];
        $price_resources_2 = $next_lvl_price[$config_resources[1]->name];
        $price_resources_3 = $next_lvl_price[$config_resources[2]->name];

        if (
            $price_resources_1 > $module->resources_1
            || $price_resources_2 > $module->resources_2
            || $price_resources_3 > $module->resources_3
        ) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'insufficient resources.',
                'result' => array(
                    'module' => array(
                        'name' => $module->name,
                        $config_resources[0]->name => $module->resources_1,
                        $config_resources[1]->name => $module->resources_2,
                        $config_resources[2]->name => $module->resources_3,
                        'building_upgrade' => $facilities_config[$request->id - 1]->name,
                        'current_level' => $user_facility->level
                    ),
                    'next_lvl_price_time' => $next_lvl_price
                )
            );
            return response()->json($data, 400);
        }

        $upgrade_line = UpgradesLine::where('user_id', Auth::id())->where('module_id', $module->id)->where('upgrade_id', $request->id)->where('type', 'facilities')->first();

        if (!empty($upgrade_line)) {
            $data['status'] = array(
                'statusCode' => 400,
                'message' => 'Upgrade already in progress.'
            );
            return response()->json($data, 400);
        }

        $module->resources_1 -= $next_lvl_price[$config_resources[0]->name];
        $module->resources_2 -= $next_lvl_price[$config_resources[1]->name];
        $module->resources_3 -= $next_lvl_price[$config_resources[2]->name];
        $module->save();

        $init_time = new DateTime();
        $finish_time = new DateTime();
        $last_facility_line = UpgradesLine::where('module_id', $module_id)->where('user_id', Auth::id())->where('type', 'facilities')->orderBy('id', 'DESC')->first();
        if(!empty($last_facility_line)){
            $init_time = new DateTime($last_facility_line->finish_at);
            $finish_time = new DateTime($last_facility_line->finish_at);;
        }

        $finish_time->add(new DateInterval('PT' . $next_lvl_price['time_minutes'] . 'M'));

        UpgradesLine::create([
            'user_id' => Auth::id(),
            'module_id' => $module->id,
            'upgrade_id' => $request->id,
            'type' => 'facilities',
            'start_at' => $init_time,
            'finish_at' => $finish_time
        ]);

        $data['status'] = array(
            'statusCode' => 200,
            'message' => 'Upgrade in progress',
            'result' => array(
                'module' => array(
                    'name' => $module->name,
                    $config_resources[0]->name => $module->resources_1,
                    $config_resources[1]->name => $module->resources_2,
                    $config_resources[2]->name => $module->resources_3,
                    'building_upgrade' => $facilities_config[$request->id -1]->name,
                    'next_level' => $next_lvl
                ),
                'total_time_minutes' => $next_lvl_price['time_minutes'],
                'date_init' => $init_time->format('U'),
                'date_finish' => $finish_time->format('U')
            )
        );
        return response()->json($data, 200);
    }
}
