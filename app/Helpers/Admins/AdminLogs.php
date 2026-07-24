<?php

namespace App\Helpers\Admins;

use App\Models\Users\Admins\Logs\AdminActionLogs;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminLogs
{
    /*
    * Log Action
    */
    public static function log($action, $type, $data = null, $summary = null)
    {
        //Add summary
        if (is_null($summary)) {
            $summary = str_replace('.', ' ', $type);
            $summary = str_replace('-', ' ', $summary);
            $summary = "$action: $summary";
            $summary = ucwords($summary);
        }

        //Set log
        $log = [
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'type' => $type,
            'data' => $data,
            'summary' => $summary,
        ];

        //Get last same log
        $last_log = AdminActionLogs::where($log)
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->orderBy('created_at', 'desc')
            ->first();

        //Add log
        if (!$last_log) {
            AdminActionLogs::create($log);
        }
    }
}
