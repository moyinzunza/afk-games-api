<?php

namespace App\Http\Controllers\Api;

use App\Models\Modules;
use App\Models\UpgradesLine;
use DateTime;

class UpgradesController extends Controller
{
    public function process_resources_buildings()
    {
        //runs every 5 seconds
        $now = new DateTime();
        $upgrades = UpgradesLine::where('finish_at', '<' ,$now)->where('type', 'resources_building')->get();

        foreach ($upgrades as $upgrade) {
            
            $module = Modules::where('id', $upgrade->module_id)->first();
            $module->{'resources_building_lvl_' . $upgrade->upgrade_id} += 1;
            $module->save();

            $upgrade->delete();
        }

        $data['status'] = "success";
        $data['msg'] = 'Upgrades Finished: ' . count($upgrades);
        $data['data'] = array();
        return response()->json($data, 200);
    }

}
