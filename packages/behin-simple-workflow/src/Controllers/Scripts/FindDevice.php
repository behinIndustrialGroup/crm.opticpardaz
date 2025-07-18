<?php

namespace Behin\SimpleWorkflow\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflow\Models\Core\Cases;

class FindDevice extends Controller
{
    /**
     * اجرای اسکریپت جستجوی دستگاه بر اساس شماره سریال، نام مشتری یا شماره پرونده
     *
     * @param Request|null $request
     * @return array
     */
    public function execute(Request $request = null)
    {
        $deviceSerial = trim($request->device_serial_no);
        $customer     = trim($request->customer_fullname);
        $caseNumber   = trim($request->case_number);

        $result = [];

        // مرحله ۱: استخراج کیس‌هایی که سریال دستگاه مطابق دارند
        $casesFromSerial = [];
        if ($deviceSerial) {
            $variables = Variable::where('key', 'device_serial_no')
                ->where('value', $deviceSerial)
                ->with('case')
                ->get();

            foreach ($variables as $var) {
                if ($var->case) {
                    $casesFromSerial[$var->case->id] = $var->case;
                }
            }
        }

        // مرحله ۲: فیلتر مشتری روی نتایج فعلی یا جستجوی جدید اگر چیزی قبلاً پیدا نشده باشد
        if ($customer) {
            $variables = Variable::where('key', 'customer_fullname')
                ->where('value', 'like', "%$customer%")
                ->with('case')
                ->get();

            $filteredCases = [];
            foreach ($variables as $var) {
                if ($var->case) {
                    if (!empty($casesFromSerial)) {
                        if (isset($casesFromSerial[$var->case->id])) {
                            $filteredCases[$var->case->id] = $var->case;
                        }
                    } else {
                        $filteredCases[$var->case->id] = $var->case;
                    }
                }
            }
            $casesFromSerial = $filteredCases;
        }

        // مرحله ۳: فیلتر شماره پرونده
        if ($caseNumber) {
            $filteredCases = [];
            foreach ($casesFromSerial as $case) {
                if ($case->number == $caseNumber) {
                    $filteredCases[$case->id] = $case;
                }
            }

            // اگر قبلاً داده‌ای پیدا نشده بود، باید از دیتابیس بگیریم
            if (empty($casesFromSerial)) {
                $case = Cases::where('number', $caseNumber)->first();
                if ($case) {
                    $casesFromSerial[$case->id] = $case;
                }
            } else {
                $casesFromSerial = $filteredCases;
            }
        }

        // مرحله ۴: نهایی‌سازی نتایج
        foreach ($casesFromSerial as $case) {
            $result[] = [
                'case_id'       => $case->id,
                'case_number'   => $case->number,
                'customer_name' => $case->getVariable('customer_fullname'),
                'device_name'   => $case->getVariable('device_name'),
                'receive_date'  => $case->getVariable('receive_date'),
            ];
        }

        return $result;
    }
}