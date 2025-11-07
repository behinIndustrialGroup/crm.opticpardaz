@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش کلی فرآیندها و پرونده‌ها')
@section('subtitle', 'نمای کلی از وضعیت اجرای فرآیندها، دسته‌بندی‌ها و کاربران فعال')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;

        $totalInProgress = DB::table('wf_cases')->where('status', 'inProgress')->count();

        $avgSeconds = DB::table('wf_cases')
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_seconds')
            ->value('avg_seconds');
        $avgSeconds = $avgSeconds ? (int) round($avgSeconds) : 0;
        $avgReadable = $avgSeconds ? gmdate('H:i:s', $avgSeconds) : '00:00:00';

        $processCategories = DB::table('wf_process')
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $errorRates = DB::table('wf_process as p')
            ->leftJoin('wf_cases as c', 'c.process_id', '=', 'p.id')
            ->select(
                'p.id',
                'p.name',
                'p.number_of_error',
                DB::raw('COUNT(c.id) as total_cases'),
                DB::raw('IFNULL(p.number_of_error, 0) / NULLIF(COUNT(c.id), 0) as error_ratio')
            )
            ->groupBy('p.id', 'p.name', 'p.number_of_error')
            ->orderByDesc(DB::raw('IFNULL(p.number_of_error, 0) / NULLIF(COUNT(c.id), 0)'))
            ->limit(20)
            ->get();

        $topCreators = DB::table('wf_cases as cases')
            ->leftJoin('users', 'users.id', '=', 'cases.creator')
            ->select('cases.creator', 'users.name', DB::raw('COUNT(*) as active_cases'))
            ->where('cases.status', 'inProgress')
            ->groupBy('cases.creator', 'users.name')
            ->orderByDesc('active_cases')
            ->limit(20)
            ->get();
    @endphp
    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد کل فرآیندهای در حال اجرا</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($totalInProgress) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">میانگین زمان تکمیل پرونده‌ها</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $avgReadable }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ number_format($avgSeconds / 3600, 2) }} ساعت</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد دسته‌بندی‌های فرآیند</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ number_format($processCategories->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">بیشترین نرخ خطا</p>
                @php
                    $topError = $errorRates->first();
                    $errorPercent = $topError && $topError->error_ratio ? round($topError->error_ratio * 100, 2) : 0;
                @endphp
                <p class="mt-2 text-3xl font-bold text-rose-600">{{ $errorPercent }}<span class="text-base">%</span></p>
                <p class="mt-1 text-xs text-slate-400">{{ $topError->name ?? '---' }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">توزیع فرآیندها بر اساس دسته‌بندی</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">دسته‌بندی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">تعداد فرآیند</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($processCategories as $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $row->category ?? 'نامشخص' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">رکوردی یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">نرخ خطای فرآیندها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">فرآیند</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">تعداد پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">تعداد خطا</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">نرخ خطا</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($errorRates as $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $row->name ?? ('#' . $row->id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ number_format($row->total_cases) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ number_format($row->number_of_error ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-rose-600 font-semibold">{{ round(($row->error_ratio ?? 0) * 100, 2) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">رکوردی یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">کاربران با بیشترین پرونده فعال</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">کاربر/واحد</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">تعداد پرونده فعال</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($topCreators as $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $row->name ?? ('کاربر #' . $row->creator) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($row->active_cases) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">رکوردی وجود ندارد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
