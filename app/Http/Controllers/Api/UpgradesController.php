<?php

namespace App\Http\Controllers\Api;

use App\Models\Modules;
use App\Models\UpgradesLine;
use App\Models\UsersFacilities;
use DateTime;

class UpgradesController extends Controller
{
    public function process_resources_buildings()
    {
        //runs every 5 seconds
        $now = new DateTime();
        $upgrades_count = 0;
        $upgrades = UpgradesLine::where('finish_at', '<' ,$now)->where('type', 'resources_building')->get();
        $upgrades_count += count($upgrades);

        foreach ($upgrades as $upgrade) {
            
            $module = Modules::where('id', $upgrade->module_id)->first();
            $module->{'resources_building_lvl_' . $upgrade->upgrade_id} += 1;
            $module->save();
            $upgrade->delete();
        }

        $upgrades = UpgradesLine::where('finish_at', '<' ,$now)->where('type', 'facilities')->get();
        $upgrades_count += count($upgrades);

        foreach ($upgrades as $upgrade) {
            
            $user_facility = UsersFacilities::where('module_id', $upgrade->module_id)->where('facility_id', $upgrade->upgrade_id)->first();
            $user_facility->level += 1;
            $user_facility->save();
            $upgrade->delete();
        }

        $data['status'] = "success";
        $data['msg'] = 'Upgrades Finished: ' . $upgrades_count;
        $data['data'] = array();
        return response()->json($data, 200);
    }

}
