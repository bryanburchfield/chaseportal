<?php

namespace App\Services;

use App\Http\Controllers\AdminController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DemoClientService
{
    /**
     * Expire Demos
     * 
     * Called from cron.  Finds all expired demo users
     * and deletes their KPI recipients and automated reports
     * then sets user_type to 'expired'
     * 
     * @return void 
     * @throws mixed 
     */
    public static function expireDemos()
    {
        $controller = new AdminController();

        $request = new Request();

        $users = User::where('user_type', 'demo')
            ->whereDate('expiration', '<', Carbon::now()->toDateTimeString())
            ->get();

        foreach ($users as $user) {

            Log::info('Expiring demo user ' . $user->id . ': ' . $user->name);

            $request->merge(['id' => $user->id]);

            $controller->deleteUser($request, true);

            $user->user_type = 'expired';
            $user->save();

            // Delete any sessions which will force a logout if they happened to be logged in
            app('db')->table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        }
    }
}
