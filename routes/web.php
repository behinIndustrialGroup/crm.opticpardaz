<?php

use Behin\SimpleWorkflow\Controllers\Core\PushNotifications;
use Behin\SimpleWorkflow\Jobs\SendPushNotification;
use Behin\SimpleWorkflow\Models\Core\Cases;
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
    $limit = 5; // تعداد jobهای پردازش شده در هر درخواست
    $jobs = DB::table('jobs')->orderBy('id')->limit($limit)->get();

    foreach ($jobs as $job) {
        try {
            // دیکد کردن محتوای job
            $payload = json_decode($job->payload, true);
            $command = unserialize($payload['data']['command']);

            // اجرای job
            $command->handle();

            // حذف job پس از اجرا
            DB::table('jobs')->where('id', $job->id)->delete();

            // return 'Job processed: ' . $job->id;
        } catch (Exception $e) {
            // در صورت خطا، job را به جدول failed_jobs منتقل کنید
            DB::table('failed_jobs')->insert([
                'connection' => $job->connection,
                'queue' => $job->queue,
                'payload' => $job->payload,
                'exception' => (string) $e,
                'failed_at' => now()
            ]);

            DB::table('jobs')->where('id', $job->id)->delete();

            return 'Job failed: ' . $e->getMessage();
        }
    }
});



Route::get('build-app', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('view:clear');
    Artisan::call('migrate');
    return redirect()->back();
});

Route::get('test', function(){
    $cases = Cases::where('process_id', '879e001c-59d5-4afb-958c-15ec7ff269d1')->groupBy('id')->get();
    echo "<table>";
    foreach($cases as $case){
        try {
            // Customer info
            if ($case->getVariable('customer_fullname')) {
                if (!Case_customer::where('case_id', $case->id)->exists()) {
                    Case_customer::create([
                        'case_id' => $case->id,
                        'case_number' => $case->number ?? '',
                        'fullname' => $case->getVariable('customer_fullname') ?? '',
                        'mobile' => $case->getVariable('customer_mobile') ?? '',
                        'address' => $case->getVariable('customer_address') ?? '',
                    ]);
                }
            }

            // Device info
            $device = null;
            if ($case->getVariable('device_name')) {
                if (!Devices::where('case_id', $case->id)->where('name', $case->getVariable('device_name'))->exists()) {
                    $device = Devices::create([
                        'case_id' => $case->id,
                        'case_number' => $case->number ?? '',
                        'name' => $case->getVariable('device_name') ?? '',
                        'brand' => $case->getVariable('device_brand') ?? '',
                        'power' => $case->getVariable('device_power'),
                        'serial' => $case->getVariable('device_serial_no') ?? '',
                        'initial_pic' => $case->getVariable('initial_device_pic'),
                        'plaque_pic' => $case->getVariable('device_plaque_image'),
                        'specifications' => $case->getVariable('device_specifications'),
                    ]);
                } else {
                    $device = Devices::where('case_id', $case->id)->where('name', $case->getVariable('device_name'))->first();
                }
            }

            // Repair info
            if ($case->getVariable('repair_report') && $device) {
                if (!Device_repair::where('case_id', $case->id)->where('device_id', $device->id)->exists()) {
                    $deviceRepair = Device_repair::create([
                        'case_id' => $case->id,
                        'case_number' => $case->number ?? '',
                        'device_id' => $device->id,
                        'repairman' => $case->getVariable('repairman'),
                        'repair_type' => $case->getVariable('repair_type'),
                        'repair_subtype' => $case->getVariable('repair_subtype'),
                        'repair_pic' => $case->getVariable('device_pic'),
                        'repairman_assitant' => $case->getVariable('repairman_assitant'),
                        'repair_report' => $case->getVariable('repair_report'),
                    ]);

                    if ($case->getVariable('repair_start_date')) {
                        $deviceRepair->repair_start_timestamp = convertPersianDateToTimestamp($case->getVariable('repair_start_date'));
                    }

                    if ($case->getVariable('repair_is_approved')) {
                        $deviceRepair->repair_is_approved = $case->getVariable('repair_is_approved');
                        $deviceRepair->repair_is_approved_by = 3;
                        $deviceRepair->repair_is_approved_description = $case->getVariable('repair_is_approved_description');
                    }

                    if ($case->getVariable('repair_is_approved_2')) {
                        $deviceRepair->repair_is_approved_2 = $case->getVariable('repair_is_approved_2');
                        $deviceRepair->repair_is_approved_by_2 = 8;
                        $deviceRepair->repair_is_approved_description_2 = $case->getVariable('repair_is_approved_description_2');
                    }

                    if ($case->getVariable('repair_is_approved_3')) {
                        $deviceRepair->repair_is_approved_3 = $case->getVariable('repair_is_approved_3');
                        $deviceRepair->repair_is_approved_by_3 = 4;
                        $deviceRepair->repair_is_approved_description_3 = $case->getVariable('repair_is_approved_description_3');
                    }

                    $deviceRepair->save();
                }
            }

            // Repair cost
            if ($case->getVariable('repair_cost')) {
                if (!Repair_cost::where('case_id', $case->id)->exists()) {
                    Repair_cost::create([
                        'case_id' => $case->id,
                        'case_number' => $case->number,
                        'cost' => convertPersianToEnglish($case->getVariable('repair_cost')),
                        'description' => $case->getVariable('repair_cost_description'),
                        'pre_invoice' => $case->getVariable('pre_invoice'),
                        'pre_invoice_has_been_sended_to_customer' => $case->getVariable('pre_invoice_has_been_sended_to_customer'),
                    ]);
                }
            }

            // Income / payment
            if ($case->getVariable('payment_amount')) {
                if (!Repair_incomes::where('case_id', $case->id)->where('payment_amount', $case->getVariable('payment_amount'))->exists()) {
                    Repair_incomes::create([
                        'case_id' => $case->id,
                        'case_number' => $case->number,
                        'payment_method' => $case->getVariable('payment_method'),
                        'payment_receipt' => $case->getVariable('payment_receipt'),
                        'payment_date' => $case->getVariable('payment_date'),
                        'payment_amount' => $case->getVariable('payment_amount'),
                        'payment_description' => $case->getVariable('payment_description'),
                        'transaction_number' => $case->getVariable('transaction_number'),
                        'cheque_number' => $case->getVariable('cheque_number'),
                        'cheque_due_date' => $case->getVariable('cheque_due_date'),
                        'customer_account_status_image' => $case->getVariable('customer_account_status_image'),
                        'cheque_image' => $case->getVariable('cheque_image')
                    ]);
                }
            }

        } catch (Exception $e) {
            echo $case->id .  " Error: " . $e->getMessage() . '<br>';
        }
    }

});
