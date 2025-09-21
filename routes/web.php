<?php

use Behin\SimpleWorkflow\Controllers\Core\PushNotifications;
use Behin\SimpleWorkflow\Jobs\SendPushNotification;
use Behin\SimpleWorkflow\Models\Core\Cases;
use Behin\SimpleWorkflow\Models\Core\Inbox;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflow\Models\Entities\Device_repair;
use Behin\SimpleWorkflow\Models\Entities\Devices;
use Behin\SimpleWorkflow\Models\Entities\Repair_cost;
use Behin\SimpleWorkflow\Models\Entities\Repair_incomes;
use Behin\SimpleWorkflow\Models\Entities\Case_customer;
use BehinInit\App\Http\Middleware\Access;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Mkhodroo\AgencyInfo\Controllers\GetAgencyController;
use UserProfile\Controllers\ChangePasswordController;
use UserProfile\Controllers\GetUserAgenciesController;
use UserProfile\Controllers\NationalIdController;
use UserProfile\Controllers\UserProfileController;
use Behin\SimpleWorkflow\Models\Entities\Pre_invoices;
use BehinUserRoles\Models\User;
use Carbon\Carbon;

Route::get('', function () {
    return view('auth.login');
});

require __DIR__ . '/auth.php';

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', Access::class])->group(function () {
    Route::get('', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});

Route::get('send-notification', function () {
    SendPushNotification::dispatch(Auth::user()->id, 'test', 'test', route('admin.dashboard'));
    return 'تا دقایقی دیگر باید نوتیفیکیشن دریافت کنید';
})->name('send-notification');

Route::get('/pusher/beams-auth', function (Request $request) {
    $beamsClient = new PushNotifications([
        'instanceId' => config('broadcasting.pusher.instanceId'),
        'secretKey' => config('broadcasting.pusher.secretKey')
    ]);
    $userId = auth()->user()->id;
    $beamsToken = $beamsClient->generateToken(config('broadcasting.pusher.prefix_user') . $userId);
    return response()->json($beamsToken);
})->middleware('auth');

Route::get('queue-work', function () {
    $limit = 5;
    $now = Carbon::now()->timestamp;

    $jobs = DB::table('jobs')
        ->where('available_at', '<=', $now)
        ->orderBy('id')
        ->limit($limit)
        ->get();

    foreach ($jobs as $job) {
        try {
            $payload = json_decode($job->payload, true);
            $command = unserialize($payload['data']['command']);

            $command->handle();

            DB::table('jobs')->where('id', $job->id)->delete();
        } catch (Exception $e) {
            DB::table('failed_jobs')->insert([
                'connection' => $job->connection ?? 'database',
                'queue' => $job->queue ?? 'default',
                'payload' => $job->payload,
                'exception' => (string) $e,
                'failed_at' => now()
            ]);

            DB::table('jobs')->where('id', $job->id)->delete();

            return 'Job failed: ' . $e->getMessage();
        }
    }

    return 'Jobs processed.';
});



Route::get('build-app', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('view:clear');
    Artisan::call('migrate');
    return redirect()->back();
});
