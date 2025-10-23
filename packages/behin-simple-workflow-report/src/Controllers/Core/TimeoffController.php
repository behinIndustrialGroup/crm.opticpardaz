<?php

namespace Behin\SimpleWorkflowReport\Controllers\Core;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Models\Entities\Timeoffs;
use BehinUserRoles\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class TimeoffController extends Controller
{
    private const HOURS_PER_DAY = 8;

    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);

        $baseQuery = Timeoffs::query();
        $this->applyFilters($baseQuery, $filters);

        $listQuery = clone $baseQuery;
        /** @var LengthAwarePaginator $rows */
        $rows = $listQuery->orderByDesc('start_timestamp')
            ->paginate($filters['per_page'])
            ->appends($request->query());

        $rows->getCollection()->transform(function ($row) {
            $row->normalized_duration = $this->normalizeDuration((float) $row->duration, $row->type);

            return $row;
        });

        $summary = $this->buildSummaryStatistics(clone $baseQuery);
        $perUserSummary = $this->buildPerUserSummary(clone $baseQuery);
        $monthlyBreakdown = $this->buildMonthlyBreakdown($filters);

        $users = User::query()->orderBy('number')->get(['id', 'name', 'number']);
        $userInfos = $this->resolveUsersInfo($rows, $perUserSummary);
        $topUsers = $perUserSummary->sortByDesc('total_hours')->take(5);

        return view('SimpleWorkflowReportView::Core.Timeoff.index', [
            'filters' => $filters,
            'rows' => $rows,
            'summary' => $summary,
            'perUserSummary' => $perUserSummary,
            'monthlyBreakdown' => $monthlyBreakdown,
            'users' => $users,
            'userInfos' => $userInfos,
            'topUsers' => $topUsers,
            'hoursPerDay' => self::HOURS_PER_DAY,
        ]);
    }

    public function update(Request $request)
    {
        $year = Jalalian::now()->format('%Y');
        // DB::table('wf_entity_timeoffs')->where('user', $request->userId)->where('request_year', $year)->update(['deleted_at' => Carbon::now()]);;
        $duration = $request->restBySystem - $request->restByUser;
        Timeoffs::create(
            [
                'user' => $request->userId,
                'type' => 'ساعتی',
                'duration' => $duration,
                'approved' => 1,
                'request_year' => $year,
                'start_year' => $year,
                'request_timestamp' => time(),
                'start_timestamp' => time(),
                'request_month' => Jalalian::now()->format('%m'),
                'start_month' => Jalalian::now()->format('%m'),
                'uniqueId' => 'به صورت دستی'
            ]
        );
        return redirect()->back();
    }

    public static function totalLeaves($userId = null)
    {
        $todayShamsi = Jalalian::now();

        $thisYear = $todayShamsi->getYear();
        $thisMonth = str_pad($todayShamsi->getMonth(), 2, '0', STR_PAD_LEFT);
        $startOfThisJalaliYear = Jalalian::fromFormat('Y-m-d', $thisYear . '-01-01')->toCarbon()->timestamp;
        $users = User::query();
        if ($userId) {
            $users = $users->where('id', $userId)->orderBy('number')->get();
        } else {
            $users = $users->orderBy('number')->get();
        }
        foreach ($users as $user) {
            $approvedLeaves = Timeoffs::select(
                DB::raw(
                    'COALESCE(SUM(CASE WHEN wf_entity_timeoffs.type = "ساعتی" THEN duration ELSE duration*8 END), 0) as total_leaves',
                ),
            )
                ->where('user', $user->id)
                ->where('start_timestamp', '>', $startOfThisJalaliYear)
                ->where('approved', 1)
                ->first()->total_leaves;
            $user->approvedLeaves = $approvedLeaves;
            $restLeaves = $thisMonth * 20 - $approvedLeaves;
            $user->restLeaves = $restLeaves;
        }
        return $users;
    }

    public static function items($userId)
    {
        $todayShamsi = Jalalian::now();
        $thisYear = $todayShamsi->getYear();
        $thisMonth = str_pad($todayShamsi->getMonth(), 2, '0', STR_PAD_LEFT);
        $startOfToday = Carbon::today()->timestamp;
        $thisYearTimestamp = Carbon::create($thisYear, 1, 1)->timestamp;
        $thisMonthTimestamp = Carbon::create($thisYear, $thisMonth, 1)->timestamp;
        if ($userId) {
            $items = Timeoffs::whereNot('uniqueId', 'به صورت دستی')
                ->where('start_timestamp', '>=', $thisYearTimestamp)
                // ->where('approved', 1)
                ->where('user', $userId)
                ->orderBy('start_timestamp', 'desc')
                ->get();
        } else {
            $items = Timeoffs::whereNot('uniqueId', 'به صورت دستی')->where('start_timestamp', '>=', $startOfToday)->where('approved', 1)->orderBy('start_timestamp', 'desc')->get();
        }
        return $items;
    }

    /**
     * @param null|Carbon $today
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function todayItems($today = null)
    {
        $todayShamsi = Jalalian::now();
        $thisYear = $todayShamsi->getYear();
        $thisMonth = str_pad($todayShamsi->getMonth(), 2, '0', STR_PAD_LEFT);
        $startOfToday = $today ? $today->timestamp : Carbon::today()->timestamp;
        $endOfToday = $today ? $today->endOfDay()->timestamp : Carbon::today()->endOfDay()->timestamp;
        $items = Timeoffs::whereNot('uniqueId', 'به صورت دستی')
        ->where('start_timestamp', '<=', $startOfToday)
        ->where('end_timestamp', '>=', $startOfToday)
        ->where('approved', 1)->orderBy('start_timestamp', 'desc')->get();
        return $items;
    }

    /**
     * @param null|Carbon $today
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function todayHourlyItems($today = null)
    {
        $todayShamsi = Jalalian::now();
        $thisYear = $todayShamsi->getYear();
        $thisMonth = str_pad($todayShamsi->getMonth(), 2, '0', STR_PAD_LEFT);
        $startOfToday = $today ? $today->timestamp : Carbon::today()->timestamp;
        $endOfToday = $today ? $today->endOfDay()->timestamp : Carbon::today()->endOfDay()->timestamp;
        $items = Timeoffs::whereNot('uniqueId', 'به صورت دستی')
        ->where('type', 'ساعتی')
        ->where('start_timestamp', '>=', $startOfToday)
        ->where('end_timestamp', '<=', $endOfToday)
        ->where('approved', 1)->orderBy('start_timestamp', 'desc')->get();
        return $items;
    }

    protected function extractFilters(Request $request): array
    {
        $defaultYear = Jalalian::now()->getYear();

        $filters = [
            'user_id' => $request->input('user_id'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'approved_by' => $request->input('approved_by'),
            'year' => $request->filled('year') ? $this->normalizeNumber($request->input('year')) : null,
            'month' => $request->filled('month') ? $this->normalizeNumber($request->input('month')) : null,
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'search' => $request->input('search'),
            'per_page' => (int) $request->input('per_page', 20),
        ];

        if ($filters['per_page'] <= 0 || $filters['per_page'] > 200) {
            $filters['per_page'] = 20;
        }

        if ($filters['year'] === null && $filters['month']) {
            $filters['year'] = $defaultYear;
        }

        return $filters;
    }

    protected function applyFilters(Builder $query, array $filters, bool $skipMonth = false): void
    {
        if (!empty($filters['user_id'])) {
            $query->where('user', $filters['user_id']);
        }

        if (!empty($filters['type'])) {
            $type = $filters['type'];
            if ($type === 'hourly' || $type === 'ساعتی') {
                $query->where('type', 'ساعتی');
            } elseif ($type === 'daily' || $type === 'روزانه') {
                $query->where('type', '!=', 'ساعتی');
            }
        }

        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'approved':
                case '1':
                    $query->where('approved', 1);
                    break;
                case 'rejected':
                case '0':
                    $query->where('approved', 0);
                    break;
                case 'pending':
                    $query->whereNull('approved');
                    break;
            }
        }

        if (!empty($filters['approved_by'])) {
            $query->where('approved_by', $filters['approved_by']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $inner) use ($search) {
                $inner->where('description', 'like', "%{$search}%")
                    ->orWhere('uniqueId', 'like', "%{$search}%");
            });
        }

        $fromTimestamp = $this->parseDateToTimestamp($filters['from_date'] ?? null);
        $toTimestamp = $this->parseDateToTimestamp($filters['to_date'] ?? null, true);
        if ($fromTimestamp) {
            $query->where('start_timestamp', '>=', $fromTimestamp);
        }
        if ($toTimestamp) {
            $query->where('start_timestamp', '<=', $toTimestamp);
        }

        if (!$skipMonth && !empty($filters['year']) && !empty($filters['month'])) {
            $this->applyMonthRange($query, (int) $filters['year'], (int) $filters['month']);
        } elseif (!$skipMonth && empty($filters['year']) && !empty($filters['month'])) {
            $this->applyMonthRange($query, Jalalian::now()->getYear(), (int) $filters['month']);
        }

        if (!empty($filters['year'])) {
            $this->applyYearRange($query, (int) $filters['year']);
        }
    }

    protected function buildSummaryStatistics(Builder $query): array
    {
        $durationExpression = DB::raw($this->normalizedDurationExpression());

        $totalRequests = (clone $query)->count();
        $totalHours = (clone $query)->sum($durationExpression);
        $approvedRequests = (clone $query)->where('approved', 1)->count();
        $approvedHours = (clone $query)->where('approved', 1)->sum($durationExpression);
        $pendingRequests = (clone $query)->whereNull('approved')->count();
        $pendingHours = (clone $query)->whereNull('approved')->sum($durationExpression);
        $rejectedRequests = (clone $query)->where('approved', 0)->count();
        $rejectedHours = (clone $query)->where('approved', 0)->sum($durationExpression);
        $dailyHours = (clone $query)->where('type', '!=', 'ساعتی')->sum(
            DB::raw('duration * ' . self::HOURS_PER_DAY)
        );
        $hourlyHours = (clone $query)->where('type', 'ساعتی')->sum('duration');

        return [
            'total_requests' => $totalRequests,
            'total_hours' => $totalHours,
            'total_days' => $this->convertHoursToDays($totalHours),
            'approved_requests' => $approvedRequests,
            'approved_hours' => $approvedHours,
            'approved_days' => $this->convertHoursToDays($approvedHours),
            'pending_requests' => $pendingRequests,
            'pending_hours' => $pendingHours,
            'pending_days' => $this->convertHoursToDays($pendingHours),
            'rejected_requests' => $rejectedRequests,
            'rejected_hours' => $rejectedHours,
            'rejected_days' => $this->convertHoursToDays($rejectedHours),
            'daily_hours' => $dailyHours,
            'daily_days' => $this->convertHoursToDays($dailyHours),
            'hourly_hours' => $hourlyHours,
            'average_duration' => $totalRequests > 0 ? round($totalHours / $totalRequests, 2) : 0,
        ];
    }

    protected function buildPerUserSummary(Builder $query): Collection
    {
        $normalizedExpression = $this->normalizedDurationExpression();

        return (clone $query)
            ->select(
                'user',
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('SUM(' . $normalizedExpression . ') as total_hours'),
                DB::raw('SUM(CASE WHEN type = "ساعتی" THEN duration ELSE 0 END) as hourly_hours'),
                DB::raw('SUM(CASE WHEN type != "ساعتی" THEN duration * ' . self::HOURS_PER_DAY . ' ELSE 0 END) as daily_hours'),
                DB::raw('SUM(CASE WHEN approved = 1 THEN ' . $normalizedExpression . ' ELSE 0 END) as approved_hours'),
                DB::raw('SUM(CASE WHEN approved = 0 THEN ' . $normalizedExpression . ' ELSE 0 END) as rejected_hours'),
                DB::raw('SUM(CASE WHEN approved IS NULL THEN ' . $normalizedExpression . ' ELSE 0 END) as pending_hours')
            )
            ->groupBy('user')
            ->orderByDesc('total_hours')
            ->get();
    }

    protected function buildMonthlyBreakdown(array $filters): Collection
    {
        $query = Timeoffs::query();
        $this->applyFilters($query, $filters, true);

        $records = $query->get(['duration', 'type', 'approved', 'request_timestamp', 'start_timestamp']);

        $grouped = $records->groupBy(function ($item) {
            $timestamp = $item->request_timestamp ?: $item->start_timestamp;

            if (!$timestamp) {
                return null;
            }

            $carbon = Carbon::createFromTimestamp($timestamp);
            $jalali = Jalalian::fromCarbon($carbon);

            return sprintf('%04d-%02d', $jalali->getYear(), $jalali->getMonth());
        })->filter();

        return $grouped
            ->map(function (Collection $items, string $key) {
                [$year, $month] = array_map('intval', explode('-', $key));

                $totalHours = $items->sum(function ($row) {
                    return $this->normalizeDuration((float) $row->duration, $row->type);
                });
                $approvedHours = $items->filter(function ($row) {
                    return (string) $row->approved === '1';
                })->sum(function ($row) {
                    return $this->normalizeDuration((float) $row->duration, $row->type);
                });
                $rejectedHours = $items->filter(function ($row) {
                    return (string) $row->approved === '0';
                })->sum(function ($row) {
                    return $this->normalizeDuration((float) $row->duration, $row->type);
                });
                $pendingHours = $items->filter(function ($row) {
                    return $row->approved === null;
                })->sum(function ($row) {
                    return $this->normalizeDuration((float) $row->duration, $row->type);
                });

                return (object) [
                    'year' => $year,
                    'month' => $month,
                    'total_requests' => $items->count(),
                    'total_hours' => $totalHours,
                    'approved_hours' => $approvedHours,
                    'rejected_hours' => $rejectedHours,
                    'pending_hours' => $pendingHours,
                ];
            })
            ->sortByDesc(function ($item) {
                return sprintf('%04d-%02d', $item->year, $item->month);
            })
            ->values();
    }

    protected function resolveUsersInfo(LengthAwarePaginator $rows, Collection $perUserSummary): Collection
    {
        $rowUsers = collect($rows->items())->pluck('user');
        $approvedByUsers = collect($rows->items())->pluck('approved_by');
        $summaryUsers = $perUserSummary->pluck('user');

        $allIds = $rowUsers->merge($approvedByUsers)->merge($summaryUsers)->filter()->unique();

        return $allIds->mapWithKeys(function ($id) {
            return [$id => getUserInfo($id)];
        });
    }

    protected function parseDateToTimestamp(?string $value, bool $endOfDay = false): ?int
    {
        if (!$value) {
            return null;
        }

        $normalized = $this->normalizeNumber($value);
        $normalized = str_replace('/', '-', $normalized);

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized)) {
                $jalali = Jalalian::fromFormat('Y-m-d', $normalized);
                $carbon = $jalali->toCarbon();
            } else {
                $carbon = Carbon::parse($value);
            }

            if ($endOfDay) {
                $carbon->endOfDay();
            } else {
                $carbon->startOfDay();
            }

            return $carbon->timestamp;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    protected function applyMonthRange(Builder $query, int $year, int $month): void
    {
        try {
            $jalali = Jalalian::fromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
            $carbon = $jalali->toCarbon();
            $start = $carbon->copy()->startOfMonth()->timestamp;
            $end = $carbon->copy()->endOfMonth()->timestamp;
            $query->whereBetween('start_timestamp', [$start, $end]);
        } catch (\Throwable $exception) {
            // ignore invalid month filter
        }
    }

    protected function applyYearRange(Builder $query, int $year): void
    {
        try {
            $jalali = Jalalian::fromFormat('Y-m-d', sprintf('%04d-01-01', $year));
            $carbon = $jalali->toCarbon();
            $start = $carbon->copy()->startOfYear()->timestamp;
            $end = $carbon->copy()->endOfYear()->timestamp;
            $query->whereBetween('start_timestamp', [$start, $end]);
        } catch (\Throwable $exception) {
            // ignore invalid year filter
        }
    }

    protected function normalizeDuration(float $duration, ?string $type): float
    {
        if ($type === 'ساعتی') {
            return $duration;
        }

        return $duration * self::HOURS_PER_DAY;
    }

    protected function normalizedDurationExpression(): string
    {
        return 'CASE WHEN type = "ساعتی" THEN duration ELSE duration * ' . self::HOURS_PER_DAY . ' END';
    }

    protected function convertHoursToDays($hours): float
    {
        if (!$hours) {
            return 0;
        }

        return round($hours / self::HOURS_PER_DAY, 2);
    }

    protected function normalizeNumber($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if (function_exists('convertPersianToEnglish')) {
            return convertPersianToEnglish($value);
        }

        return $value;
    }
}
