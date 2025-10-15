<?php

namespace Behin\SimpleWorkflowReport\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Models\User;
use Behin\Ami\Services\CallHistoryService;
use Behin\SimpleWorkflow\Models\Core\ViewModel;
use Behin\SimpleWorkflowReport\Exports\AllRequestsReportExport;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AllRequestsReportController extends Controller
{
    /**
     * @return View|JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->except('page');
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = $perPage > 0 ? $perPage : 15;
        $currentPage = max((int) $request->input('page', 1), 1);
        $filters = Arr::where($filters, fn($value) => $value !== null && $value !== '');

        $query = $this->applyFilters($this->baseQuery(), $filters);
        /** @var LengthAwarePaginator $rows */
        $rows = $query->paginate($perPage, ['*'], 'page', $currentPage);
        $rows->appends($filters);
        $preparedRows = $this->prepareRows($rows->getCollection());
        $rows->setCollection($preparedRows);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $preparedRows->values(),
                'pagination' => [
                    'total' => $rows->total(),
                    'per_page' => $rows->perPage(),
                    'current_page' => $rows->currentPage(),
                    'last_page' => $rows->lastPage(),
                ],
                'filters' => $filters,
            ]);
        }

        return view('SimpleWorkflowReportView::Core.AllRequests.index', [
            'rows' => $rows,
            'filters' => $filters,
            'perPage' => $perPage,
        ]);
    }

    protected function baseQuery(): Builder
    {
        $latestDevice = DB::table('wf_entity_devices as d')
            ->select('d.case_number', 'd.name', 'd.serial')
            ->joinSub(
                DB::table('wf_entity_devices as inner_d')
                    ->select('inner_d.case_number', DB::raw('MAX(inner_d.id) as latest_id'))
                    ->whereNull('inner_d.deleted_at')
                    ->groupBy('inner_d.case_number'),
                'latest_device',
                function ($join) {
                    $join->on('latest_device.case_number', '=', 'd.case_number')
                        ->on('latest_device.latest_id', '=', 'd.id');
                }
            )
            ->whereNull('d.deleted_at');

        $latestRepair = DB::table('wf_entity_device_repair as dr')
            ->select(
                'dr.case_number',
                'dr.device_id',
                'dr.repairman',
                'dr.repair_type',
                'dr.repair_subtype',
                'dr.repair_start_timestamp',
                'dr.repair_start_date_alt',
                'dr.repair_end_date_alt',
                'dr.updated_at',
                'dr.repairman_assitant',
                'dr.repair_is_approved',
                'dr.repair_is_approved_by',
                'dr.repair_is_approved_2',
                'dr.repair_is_approved_by_2',
                'dr.repair_is_approved_3',
                'dr.repair_is_approved_by_3',
                'dr.repair_report'
            )
            ->joinSub(
                DB::table('wf_entity_device_repair as inner_dr')
                    ->select('inner_dr.case_number', DB::raw('MAX(inner_dr.id) as latest_id'))
                    ->whereNull('inner_dr.deleted_at')
                    ->groupBy('inner_dr.case_number'),
                'latest_repair',
                function ($join) {
                    $join->on('latest_repair.case_number', '=', 'dr.case_number')
                        ->on('latest_repair.latest_id', '=', 'dr.id');
                }
            )
            ->whereNull('dr.deleted_at');

        $latestCost = DB::table('wf_entity_repair_cost as rc')
            ->select('rc.case_number', 'rc.cost')
            ->joinSub(
                DB::table('wf_entity_repair_cost as inner_rc')
                    ->select('inner_rc.case_number', DB::raw('MAX(inner_rc.id) as latest_id'))
                    ->whereNull('inner_rc.deleted_at')
                    ->groupBy('inner_rc.case_number'),
                'latest_cost',
                function ($join) {
                    $join->on('latest_cost.case_number', '=', 'rc.case_number')
                        ->on('latest_cost.latest_id', '=', 'rc.id');
                }
            )
            ->whereNull('rc.deleted_at');

        $totalIncome = DB::table('wf_entity_repair_incomes as ri')
            ->select('ri.case_number', DB::raw('SUM(ri.payment_amount) as total_received'))
            ->whereNull('ri.deleted_at')
            ->groupBy('ri.case_number');

        $lastStatus = DB::table('wf_inbox as wi')
            ->select('wi.case_id', 'wt.name as last_status')
            ->joinSub(
                DB::table('wf_inbox as inner_wi')
                    ->select('inner_wi.case_id', DB::raw('MAX(inner_wi.id) as latest_id'))
                    ->whereNotIn('inner_wi.status', ['done', 'doneByOther', 'canceled'])
                    ->groupBy('inner_wi.case_id'),
                'latest_inbox',
                function ($join) {
                    $join->on('latest_inbox.case_id', '=', 'wi.case_id')
                        ->on('latest_inbox.latest_id', '=', 'wi.id');
                }
            )
            ->join('wf_task as wt', 'wt.id', '=', 'wi.task_id');

        $devicePlaque = DB::table('wf_variables as v')
            ->select(
                'v.case_id',
                DB::raw("MAX(CASE WHEN v.key IN ('device_plaque_number', 'device-plaque-number', 'device_plaque', 'device_plaque_code') THEN v.value END) as device_plaque")
            )
            ->whereIn('v.key', [
                'device_plaque_number',
                'device-plaque-number',
                'device_plaque',
                'device_plaque_code',
            ])
            ->groupBy('v.case_id');

        return DB::table('wf_cases as c')
            ->leftJoin('wf_entity_case_customer as cc', function ($join) {
                $join->on('cc.case_number', '=', 'c.number')
                    ->whereNull('cc.deleted_at');
            })
            ->leftJoinSub($latestDevice, 'd', 'd.case_number', '=', 'c.number')
            ->leftJoinSub($latestRepair, 'dr', 'dr.case_number', '=', 'c.number')
            ->leftJoin('users as repairman', 'repairman.id', '=', 'dr.repairman')
            ->leftJoinSub($latestCost, 'rc', 'rc.case_number', '=', 'c.number')
            ->leftJoinSub($totalIncome, 'ri', 'ri.case_number', '=', 'c.number')
            ->leftJoinSub($lastStatus, 'ls', 'ls.case_id', '=', 'c.id')
            ->leftJoinSub($devicePlaque, 'vp', 'vp.case_id', '=', 'c.id')
            ->select([
                'c.id',
                'c.number',
                'cc.fullname as customer_name',
                'cc.mobile as customer_mobile',
                'd.name as device_name',
                'd.serial as device_serial',
                'vp.device_plaque',
                'dr.repair_type',
                'dr.repair_subtype',
                'repairman.name as repairman_name',
                'dr.repairman as repairman_id',
                'dr.repair_start_timestamp',
                'dr.repair_start_date_alt',
                'dr.updated_at as repair_end_timestamp',
                'dr.repair_end_date_alt',
                'dr.repairman_assitant',
                'dr.repair_is_approved',
                'dr.repair_is_approved_by',
                'dr.repair_is_approved_2',
                'dr.repair_is_approved_by_2',
                'dr.repair_is_approved_3',
                'dr.repair_is_approved_by_3',
                'dr.repair_report',
                'rc.cost as repair_cost',
                'ri.total_received',
                'ls.last_status',
            ])
            ->whereNull('c.deleted_at')
            ->where('c.process_id', '879e001c-59d5-4afb-958c-15ec7ff269d1')
            ->whereNotNull('c.number')
            ->orderByDesc('c.number')
            ->groupBy('c.number');
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $filters = Arr::where($filters, fn($value) => $value !== null && $value !== '');

        if (!empty($filters['case_number'])) {
            $query->where('c.number', 'like', '%' . $filters['case_number'] . '%');
        }

        if (!empty($filters['customer_name'])) {
            $query->where('cc.fullname', 'like', '%' . $filters['customer_name'] . '%');
        }

        if (!empty($filters['customer_mobile'])) {
            $query->where('cc.mobile', 'like', '%' . $filters['customer_mobile'] . '%');
        }

        if (!empty($filters['device_name'])) {
            $query->where('d.name', 'like', '%' . $filters['device_name'] . '%');
        }

        if (!empty($filters['device_serial'])) {
            $query->where('d.serial', 'like', '%' . $filters['device_serial'] . '%');
        }

        if (!empty($filters['device_plaque'])) {
            $query->where('vp.device_plaque', 'like', '%' . $filters['device_plaque'] . '%');
        }

        if (!empty($filters['repair_type'])) {
            $query->where('dr.repair_type', 'like', '%' . $filters['repair_type'] . '%');
        }

        if (!empty($filters['repair_subtype'])) {
            $query->where('dr.repair_subtype', 'like', '%' . $filters['repair_subtype'] . '%');
        }

        if (!empty($filters['repairman'])) {
            $query->where('repairman.name', 'like', '%' . $filters['repairman'] . '%');
        }

        if (!empty($filters['repair_start_from'])) {
            $query->whereDate('dr.repair_start_timestamp', '>=', $filters['repair_start_from']);
        }

        if (!empty($filters['repair_start_to'])) {
            $query->whereDate('dr.repair_start_timestamp', '<=', $filters['repair_start_to']);
        }

        if (!empty($filters['repair_end_from'])) {
            $query->whereDate('dr.updated_at', '>=', $filters['repair_end_from']);
        }

        if (!empty($filters['repair_end_to'])) {
            $query->whereDate('dr.updated_at', '<=', $filters['repair_end_to']);
        }

        if (!empty($filters['approval_first'])) {
            $this->applyApprovalFilter($query, 'dr.repair_is_approved', $filters['approval_first']);
        }

        if (!empty($filters['approval_second'])) {
            $this->applyApprovalFilter($query, 'dr.repair_is_approved_2', $filters['approval_second']);
        }

        if (!empty($filters['approval_third'])) {
            $this->applyApprovalFilter($query, 'dr.repair_is_approved_3', $filters['approval_third']);
        }

        if (!empty($filters['cost_min'])) {
            $query->whereRaw('CAST(rc.cost AS DECIMAL(18,2)) >= ?', [$filters['cost_min']]);
        }

        if (!empty($filters['cost_max'])) {
            $query->whereRaw('CAST(rc.cost AS DECIMAL(18,2)) <= ?', [$filters['cost_max']]);
        }

        if (!empty($filters['income_min'])) {
            $query->whereRaw('CAST(IFNULL(ri.total_received, 0) AS DECIMAL(18,2)) >= ?', [$filters['income_min']]);
        }

        if (!empty($filters['income_max'])) {
            $query->whereRaw('CAST(IFNULL(ri.total_received, 0) AS DECIMAL(18,2)) <= ?', [$filters['income_max']]);
        }

        if (!empty($filters['last_status'])) {
            $query->where('ls.last_status', 'like', '%' . $filters['last_status'] . '%');
        }

        return $query;
    }

    protected function applyApprovalFilter(Builder $query, string $column, string $value): void
    {
        $value = strtolower($value);

        if ($value === 'approved') {
            $query->where(function ($q) use ($column) {
                $q->where($column, 1)
                    ->orWhere($column, '1')
                    ->orWhere($column, 'true')
                    ->orWhere($column, 'yes')
                    ->orWhere($column, 'on')
                    ->orWhere($column, 'approved')
                    ->orWhere($column, 'تایید')
                    ->orWhere($column, 'تایید شده')
                    ->orWhere($column, 'بله');
            });
        } elseif ($value === 'rejected') {
            $query->where(function ($q) use ($column) {
                $q->where($column, 0)
                    ->orWhere($column, '0')
                    ->orWhere($column, 'false')
                    ->orWhere($column, 'no')
                    ->orWhere($column, 'off')
                    ->orWhere($column, 'rejected')
                    ->orWhere($column, 'declined')
                    ->orWhere($column, 'رد')
                    ->orWhere($column, 'عدم تایید');
            });
        } elseif ($value === 'pending') {
            $query->whereNull($column);
        }
    }

    protected function prepareRows($rows): Collection
    {
        $rows = $rows instanceof Collection ? $rows : collect($rows);

        if ($rows->isEmpty()) {
            return collect();
        }

        $assistantIds = $rows->flatMap(fn($row) => $this->extractIds($row->repairman_assitant ?? null));
        $approverIds = $rows->flatMap(function ($row) {
            return collect([
                $row->repair_is_approved_by ?? null,
                $row->repair_is_approved_by_2 ?? null,
                $row->repair_is_approved_by_3 ?? null,
            ]);
        })->filter();

        $userIds = $assistantIds->merge($approverIds)->unique()->filter();
        $userNames = $userIds->isNotEmpty()
            ? User::whereIn('id', $userIds->all())->pluck('name', 'id')
            : collect();

        return $rows->map(function ($row) use ($userNames) {
            $repairTypes = $this->normalizeList($row->repair_type ?? null);
            $repairSubTypes = $this->normalizeList($row->repair_subtype ?? null);
            $assistantIds = $this->extractIds($row->repairman_assitant ?? null);
            $assistantNames = $assistantIds->map(fn($id) => $userNames->get($id, (string) $id))->filter();

            $repairCost = $this->normalizeAmount($row->repair_cost ?? null);
            $receivedCost = $this->normalizeAmount($row->total_received ?? null);

            $startAt = $this->formatDate($row->repair_start_date_alt ?? null);
            $endAt = $this->formatDate($row->repair_end_date_alt ?? null);

            return (object) [
                'id' => $row->id,
                'case_number' => $row->number,
                'customer_name' => $row->customer_name,
                'customer_mobile' => $row->customer_mobile,
                'device_name' => $row->device_name,
                'device_serial' => $row->device_serial,
                'device_plaque' => $row->device_plaque,
                'repair_type' => $repairTypes->implode(', '),
                'repair_subtype' => $repairSubTypes->implode(', '),
                'repairman' => $row->repairman_name,
                'repair_start_at' => $startAt,
                'repair_end_at' => $endAt,
                'approval_first' => $this->formatApproval($row->repair_is_approved ?? null, $userNames->get($row->repair_is_approved_by ?? null)),
                'approval_second' => $this->formatApproval($row->repair_is_approved_2 ?? null, $userNames->get($row->repair_is_approved_by_2 ?? null)),
                'approval_third' => $this->formatApproval($row->repair_is_approved_3 ?? null, $userNames->get($row->repair_is_approved_by_3 ?? null)),
                'assistants' => $assistantNames->implode(', '),
                'repair_cost' => $repairCost,
                'repair_cost_formatted' => $repairCost !== null ? number_format($repairCost) : null,
                'received_cost' => $receivedCost,
                'received_cost_formatted' => $receivedCost !== null ? number_format($receivedCost) : null,
                'last_status' => $row->last_status,
                'customer_mobile_raw' => $row->customer_mobile,
                'repair_report' => $row->repair_report,
            ];
        });
    }

    protected function normalizeList($value): Collection
    {
        if ($value instanceof Collection) {
            return $value->filter(fn($item) => $item !== null && $item !== '')->values();
        }

        if (is_array($value)) {
            return collect($value)->filter(fn($item) => $item !== null && $item !== '')->values();
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $this->normalizeList($decoded);
            }

            return collect(array_map('trim', array_filter(explode(',', $value))))
                ->filter(fn($item) => $item !== '')
                ->values();
        }

        return collect();
    }

    protected function extractIds($value): Collection
    {
        $values = $this->normalizeList($value);
        return $values->map(fn($item) => (string) $item);
    }

    protected function formatApproval($status, ?string $approverName): ?string
    {
        $normalized = $this->normalizeApproval($status);

        if ($normalized === null && empty($approverName)) {
            return null;
        }

        $label = $normalized ?? (is_string($status) ? $status : 'نامشخص');

        if (!empty($approverName)) {
            $label .= ' - ' . $approverName;
        }

        return $label;
    }

    protected function normalizeApproval($status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        $value = mb_strtolower(trim((string) $status));

        $approved = ['1', 'true', 'yes', 'approved', 'on', 'تایید', 'تایید شده', 'بله'];
        $rejected = ['0', 'false', 'no', 'rejected', 'off', 'declined', 'رد', 'عدم تایید'];
        $pending = ['pending', 'wait', 'waiting', 'در انتظار', 'نامشخص'];

        if (in_array($value, $approved, true)) {
            return 'تایید شده';
        }

        if (in_array($value, $rejected, true)) {
            return 'رد شده';
        }

        if (in_array($value, $pending, true)) {
            return 'در انتظار';
        }

        return $status;
    }

    protected function normalizeAmount($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = str_replace([',', '٬', ' '], '', (string) $value);
        $clean = preg_replace('/[^\d.\-]/u', '', $clean);

        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }

    protected function formatDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return toJalali((int)$value/1000);
            return Carbon::parse($value)->format('Y/m/d H:i');
        } catch (\Throwable $exception) {
            return is_string($value) ? $value : null;
        }
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->except('page');
        $filters = Arr::where($filters, fn($value) => $value !== null && $value !== '');

        $rows = $this->applyFilters($this->baseQuery(), $filters)->get();
        $prepared = $this->prepareRows($rows)->map(fn($row) => (array) $row);

        $filename = 'requests_export_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AllRequestsReportExport($prepared), $filename);
    }

    public function show(string $caseNumber): View
    {
        $row = $this->baseQuery()
            ->where('c.number', $caseNumber)
            ->first();

        if (!$row) {
            abort(404);
        }

        $preparedRow = $this->prepareRows(collect([$row]))->first();

        /** @var CallHistoryService $callHistoryService */
        $callHistoryService = app(CallHistoryService::class);

        $callRecords = collect();
        $callRecordsError = null;
        $searchedNumbers = [];

        if (!empty($preparedRow->customer_mobile_raw)) {
            $callRecords = $callHistoryService->getCallsByPhone($preparedRow->customer_mobile_raw);
            $callRecordsError = $callHistoryService->getLastError();
            $searchedNumbers = $callHistoryService->getLastSearchNumbers();
        }

        return view('SimpleWorkflowReportView::Core.AllRequests.show', [
            'requestRow' => $preparedRow,
            'conversationViewModel' => ViewModel::find('912880ce-7acf-4735-9170-cbc34b39362b'),
            'callRecords' => $callRecords,
            'callRecordsError' => $callRecordsError,
            'callRecordsSearchedNumbers' => $searchedNumbers,
        ]);
    }
}
