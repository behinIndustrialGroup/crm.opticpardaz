<?php

namespace Behin\SimpleWorkflowReport\Controllers\Core;

use App\Http\Controllers\Controller;
use Behin\Ami\Services\CallHistoryService;
use Behin\SimpleWorkflow\Models\Core\Cases;
use Behin\SimpleWorkflow\Models\Core\Inbox;
use Behin\SimpleWorkflowReport\Exports\AllRequestsReportExport;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Behin\SimpleWorkflow\Models\Core\ViewModel;
use Behin\SimpleWorkflow\Models\Entities\Case_customer;
use Behin\SimpleWorkflow\Models\Entities\Customers;
use Behin\SimpleWorkflow\Models\Entities\Device_repair;
use Behin\SimpleWorkflow\Models\Entities\Devices;
use Behin\SimpleWorkflow\Models\Entities\Repair_cost;
use Behin\SimpleWorkflow\Models\Entities\Repair_incomes;
use Behin\SimpleWorkflow\Models\Entities\Transactions;

class AllRequestsReportController extends Controller
{
    protected array $openStatuses = ['new', 'opened', 'inProgress', 'draft'];

    public function index(Request $request)
    {
        $filters = $request->except('page');
        $perPage = (int) ($filters['per_page'] ?? 15);
        $cases = Cases::get()->each(function($row){
            $row->customer = Case_customer::where('case_number', $row->number)->first();
            $row->device = Devices::where('case_number', $row->number)->first();
            $row->device_repair = Device_repair::where('case_number', $row->number)->first();
            $row->repair_cost = Repair_cost::where('case_number', $row->number)->first();
            $row->repair_incomes = Repair_incomes::where('case_number', $row->number)->get();
            $row->transactions = Transactions::where('case_number', $row->number)->get();
        });
        return $cases;
        $filters = Arr::where($filters, fn($value, $key) => $value !== null && $value !== '');
        $query = $this->baseQuery();
        $query = $this->applyFilters($query, $filters);
        $rows = $query->paginate($perPage);
        $rows->appends($filters);
        $rows->getCollection()->transform(function ($row) {
            $row->last_status = Inbox::where('case_id', $row->id)
                ->whereNotIn('status', ['done', 'doneByOther', 'canceled'])
                ->get();
            return $row;
        });

        // return $rows;
        return view('SimpleWorkflowReportView::Core.AllRequests.index', [
            'rows' => $rows,
            'filters' => $filters,
            'perPage' => $perPage,
        ]);
    }

    protected function baseQuery()
    {
        return DB::table('wf_cases as c')
            ->leftJoin('wf_variables as v', 'c.id', '=', 'v.case_id')
            ->select(
                'c.id',
                'c.number',
                DB::raw("MAX(CASE WHEN v.key IN ('user-firstname', 'user_firstname') THEN v.value END) as user_firstname"),
                DB::raw("MAX(CASE WHEN v.key = 'user-lastname' THEN v.value END) as user_lastname"),
                DB::raw("MAX(CASE WHEN `key` IN ('electricity_bill_id') THEN value END) as electricity_bill_id"),
                DB::raw("MAX(CASE WHEN `key` IN ('powerhouse_type', 'powerhouse-type') THEN value END) as powerhouse_type"),
                DB::raw("MAX(CASE WHEN `key` IN ('powerhouse_place_info-id', 'powerhouse_place_info_id') THEN value END) as powerhouse_place_info_id"),
                DB::raw("MAX(CASE WHEN `key` = 'powerhouse_place_info-province' THEN value END) as powerhouse_place_info_province"),
                DB::raw("MAX(CASE WHEN `key` IN ('requested_capacity_of_powerhouse', 'requested-capacity-of-powerhouse') THEN value END) as requested_capacity_of_powerhouse"),
                DB::raw("MAX(CASE WHEN `key` IN ('first_call_result', 'first-call-result') THEN value END) as first_call_result"),
                DB::raw("MAX(CASE WHEN `key` IN ('loan_interest', 'loan-interest') THEN value END) as loan_interest"),
                DB::raw("MAX(CASE WHEN `key` IN ('initial_amount', 'initial-amount') THEN value END) as initial_amount"),
                DB::raw("MAX(CASE WHEN `key` IN ('feasibility_study', 'feasibility-study') THEN value END) as feasibility_study"),
                DB::raw("MAX(CASE WHEN `key` IN ('mobile', 'user-mobile', 'user_mobile') THEN value END) as mobile"),
                DB::raw("MAX(CASE WHEN `key` IN ('user-national_id', 'user_national_id', 'national_id') THEN value END) as user_national_id"),
                DB::raw("MAX(CASE WHEN `key` IN ('powerhouse_place_info-postal_code', 'powerhouse_place_info_postal_code') THEN value END) as powerhouse_place_info_postal_code"),
                DB::raw("MAX(CASE WHEN `key` IN ('powerhouse_place_info-address', 'powerhouse_place_info_address') THEN value END) as powerhouse_place_info_address"),
                DB::raw("MAX(CASE WHEN `key` IN ('fin_interface_call_result') THEN value END) as fin_interface_call_result")
            )
            ->groupBy('c.id', 'c.number');
    }

    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                unset($filters[$key]);
            }
        }

        if (!empty($filters['case_number'])) {
            $query->having('number', 'like', '%' . $filters['case_number'] . '%');
        }

        if (!empty($filters['user_firstname'])) {
            $query->having('user_firstname', 'like', '%' . $filters['user_firstname'] . '%');
        }

        if (!empty($filters['user_lastname'])) {
            $query->having('user_lastname', 'like', '%' . $filters['user_lastname'] . '%');
        }

        if (!empty($filters['electricity_bill_id'])) {
            $query->having('electricity_bill_id', 'like', '%' . $filters['electricity_bill_id'] . '%');
        }

        if (!empty($filters['powerhouse_type'])) {
            $query->having('powerhouse_type', 'like', '%' . $filters['powerhouse_type'] . '%');
        }

        if (!empty($filters['requested_capacity_of_powerhouse'])) {
            $query->having('requested_capacity_of_powerhouse', 'like', '%' . $filters['requested_capacity_of_powerhouse'] . '%');
        }

        if (!empty($filters['first_call_result'])) {
            $query->having('first_call_result', 'like', '%' . $filters['first_call_result'] . '%');
        }

        if (!empty($filters['loan_interest'])) {
            $query->having('loan_interest', 'like', '%' . $filters['loan_interest'] . '%');
        }

        if (!empty($filters['initial_amount'])) {
            $query->having('initial_amount', 'like', '%' . $filters['initial_amount'] . '%');
        }

        if (!empty($filters['feasibility_study'])) {
            $query->having('feasibility_study', 'like', '%' . $filters['feasibility_study'] . '%');
        }

        if (!empty($filters['fin_interface_call_result'])) {
            $query->having('fin_interface_call_result', 'like', '%' . $filters['fin_interface_call_result'] . '%');
        }

        if (!empty($filters['last_status'])) {
            $query->whereExists(function ($subQuery) use ($filters) {
                $subQuery->select(DB::raw(1))
                    ->from('wf_inbox as wi')
                    ->join('wf_task as wt', 'wt.id', '=', 'wi.task_id')
                    ->whereColumn('wi.case_id', 'c.id')
                    ->whereNotIn('wi.status', ['done', 'doneByOther', 'canceled'])
                    ->where('wt.name', 'like', '%' . $filters['last_status'] . '%');
            });
        }


        return $query;
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->except('page');
        $perPage = (int) ($filters['per_page'] ?? 15);
        $filters = Arr::where($filters, fn($value, $key) => $value !== null && $value !== '');
        $query = $this->baseQuery();
        $query = $this->applyFilters($query, $filters);

        $rows = $query->get();
        $rows->each(function ($row) {
            $statuses = Inbox::where('case_id', $row->id)
                ->whereNotIn('status', ['done', 'doneByOther', 'canceled'])
                ->with('task') // 👈 برای بارگذاری رابطه task
                ->get()
                ->map(fn($inbox) => $inbox->task?->name) // 👈 استخراج نام تسک
                ->filter() // حذف nullها
                ->toArray();

            $row->last_status = implode(', ', $statuses);
            return $row;
        });
        // حالا خروجی اکسل ساده
        $filename = 'requests_export_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AllRequestsReportExport($rows), $filename);
    }

    public function show(string $caseNumber): View
    {
        $row = $this->baseQuery()
            ->where('c.number', $caseNumber)
            ->first();

        if (!$row) {
            abort(404);
        }

        // $row = $this->prepareRows(collect([$row]))->first();

        /** @var CallHistoryService $callHistoryService */
        $callHistoryService = app(CallHistoryService::class);

        $callRecords = collect();
        $callRecordsError = null;
        $searchedNumbers = [];

        if (!empty($row->mobile)) {
            $callRecords = $callHistoryService->getCallsByPhone($row->mobile);
            $callRecordsError = $callHistoryService->getLastError();
            $searchedNumbers = $callHistoryService->getLastSearchNumbers();
        }

        return view('SimpleWorkflowReportView::Core.AllRequests.show', [
            'requestRow' => $row,
            'conversationViewModel' => ViewModel::find('912880ce-7acf-4735-9170-cbc34b39362b'),
            'callRecords' => $callRecords,
            'callRecordsError' => $callRecordsError,
            'callRecordsSearchedNumbers' => $searchedNumbers,
        ]);
    }
}
