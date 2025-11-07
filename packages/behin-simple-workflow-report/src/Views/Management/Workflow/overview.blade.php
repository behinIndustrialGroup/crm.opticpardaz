@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش‌های گردش کار')
@section('subtitle', 'وظایف کارتابل، وضعیت‌ها و مسیر جریان کار')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Carbon;

        $inboxTasks = DB::table('wf_inbox')
            ->select('id', 'task_id', 'case_id', 'actor', 'status', 'created_at', 'updated_at')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $taskStatuses = DB::table('wf_inbox')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $taskDurations = DB::table('wf_inbox')
            ->select('task_id', DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_seconds'), DB::raw('COUNT(*) as total'))
            ->whereNotNull('updated_at')
            ->groupBy('task_id')
            ->orderByDesc('avg_seconds')
            ->limit(50)
            ->get();

        $taskRoles = DB::table('wf_task_actor as ta')
            ->leftJoin('wf_task as t', 't.id', '=', 'ta.task_id')
            ->leftJoin('behin_roles as roles', 'roles.id', '=', 'ta.role_id')
            ->select('ta.task_id', 't.name as task_name', 'roles.name as role_name')
            ->orderBy('ta.task_id')
            ->get();

        $taskJumps = DB::table('wf_task_jumps as j')
            ->leftJoin('wf_task as from_task', 'from_task.id', '=', 'j.task_id')
            ->leftJoin('wf_task as to_task', 'to_task.id', '=', 'j.next_task_id')
            ->select('j.task_id', 'j.next_task_id', 'from_task.name as from_name', 'to_task.name as to_name')
            ->get();

        $processGraph = DB::table('wf_task as t')
            ->leftJoin('wf_task_jumps as j', 'j.task_id', '=', 't.id')
            ->select('t.process_id', 't.id as task_id', 't.name as task_name', 'j.next_task_id as to_task_id')
            ->get()
            ->groupBy('process_id');

        $users = DB::table('users')->whereIn('id', $inboxTasks->pluck('actor')->filter())->pluck('name', 'id');
        $tasks = DB::table('wf_task')->whereIn('id', $inboxTasks->pluck('task_id')->merge($taskDurations->pluck('task_id'))->merge($taskRoles->pluck('task_id'))->merge($taskJumps->pluck('task_id'))->merge($taskJumps->pluck('next_task_id'))->filter())->pluck('name', 'id');
    @endphp

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">وظایف در صف کارتابل</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($inboxTasks->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">میانگین زمان صرف‌شده</p>
                @php
                    $overallDuration = $inboxTasks->whereNotNull('updated_at')->map(function ($task) {
                        return $task->updated_at && $task->created_at ? Carbon::parse($task->updated_at)->diffInSeconds(Carbon::parse($task->created_at)) : null;
                    })->filter();
                    $overallDurationValue = $overallDuration->count() ? (int) round($overallDuration->average()) : 0;
                    $overallDurationLabel = $overallDurationValue ? gmdate('H:i:s', $overallDurationValue) : '00:00:00';
                @endphp
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $overallDurationLabel }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">وضعیت‌های فعال</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ number_format($taskStatuses->count()) }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">وظایف کارتابل</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وظیفه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کاربر</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وضعیت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ ایجاد</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($inboxTasks as $task)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $tasks[$task->task_id] ?? ('وظیفه #' . $task->task_id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $task->case_id ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $users[$task->actor] ?? ($task->actor ? 'کاربر #' . $task->actor : '---') }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $task->status ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $task->created_at ? Carbon::parse($task->created_at)->format('Y-m-d H:i') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">وظیفه‌ای ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">وضعیت وظایف</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وضعیت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($taskStatuses as $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $row->status ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($row->total ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">وضعیتی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">میانگین زمان صرف شده در هر تسک</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وظیفه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">میانگین زمان</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد انجام شده</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($taskDurations as $row)
                            @php
                                $durationLabel = $row->avg_seconds ? gmdate('H:i:s', (int) $row->avg_seconds) : '00:00:00';
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $tasks[$row->task_id] ?? ('وظیفه #' . $row->task_id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ $durationLabel }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($row->total ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">داده‌ای موجود نیست.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">نقش‌های مرتبط با تسک‌ها</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وظیفه</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نقش</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($taskRoles as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $row->task_name ?? ($tasks[$row->task_id] ?? ('وظیفه #' . $row->task_id)) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $row->role_name ?? '---' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">نقشی ثبت نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">مسیر جریان کار بین تسک‌ها</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">از تسک</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">به تسک</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شرط</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($taskJumps as $jump)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $jump->from_name ?? ($tasks[$jump->task_id] ?? ('تسک #' . $jump->task_id)) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $jump->to_name ?? ($tasks[$jump->next_task_id] ?? ('تسک #' . $jump->next_task_id)) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">---</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">مسیر تعریف نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">نمودار فرآیند (خلاصه)</h2>
            <div class="space-y-4">
                @forelse($processGraph as $processId => $items)
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-slate-700 mb-2">فرآیند #{{ $processId }}</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($items->groupBy('task_id') as $taskId => $group)
                                <div class="bg-white border border-slate-200 rounded-md px-3 py-2 text-sm text-slate-700">
                                    <span class="font-semibold">{{ $tasks[$taskId] ?? ('تسک #' . $taskId) }}</span>
                                    @php
                                        $targets = $group->pluck('to_task_id')->filter()->unique()->map(function ($id) use ($tasks) {
                                            return $tasks[$id] ?? ('تسک #' . $id);
                                        });
                                    @endphp
                                    @if($targets->isNotEmpty())
                                        <span class="block text-xs text-slate-500 mt-1">↳ {{ $targets->implode('، ') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">برای نمایش نمودار فرآیند داده‌ای وجود ندارد.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
