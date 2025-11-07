@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش‌های پیکربندی و فرآیندها')
@section('subtitle', 'فرم‌ها، اسکریپت‌ها، نقش‌ها و متغیرهای پویا')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Str;
        use Illuminate\Support\Carbon;

        $forms = DB::table('wf_forms')
            ->select('id', 'name', 'executive_file', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $scripts = DB::table('wf_scripts')
            ->select('id', 'name', 'executive_file', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $roleFormLinks = DB::table('wf_process_role_form_control as prf')
            ->leftJoin('behin_roles as roles', 'roles.id', '=', 'prf.role_id')
            ->leftJoin('wf_process', 'wf_process.id', '=', 'prf.process_id')
            ->leftJoin('wf_forms', 'wf_forms.id', '=', 'prf.summary_form_id')
            ->select(
                'prf.id',
                'roles.name as role_name',
                'wf_process.name as process_name',
                'wf_forms.name as form_name',
                'prf.created_at'
            )
            ->orderByDesc('prf.created_at')
            ->limit(100)
            ->get();

        $variables = DB::table('wf_variables')
            ->select('id', 'process_id', 'case_id', 'key', 'value', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $pmVariables = DB::table('pm_vars')
            ->select('id', 'process_id', 'case_id', 'key', 'value', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        $configChanges = DB::table('wf_entity_configs')
            ->select('id', 'key', 'value', 'updated_at')
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();
    @endphp

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد فرم‌های ثبت شده</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($forms->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد اسکریپت‌ها</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($scripts->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">آخرین تغییرات پیکربندی</p>
                @php
                    $latestChange = $configChanges->first();
                    $latestChangeLabel = ($latestChange && $latestChange->updated_at)
                        ? Carbon::parse($latestChange->updated_at)->format('Y-m-d')
                        : '---';
                @endphp
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $latestChangeLabel }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">موجودی فرم‌ها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شناسه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نام</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">فایل اجرایی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ ایجاد</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($forms as $form)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $form->id }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $form->name ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">
                                    <span class="block truncate max-w-xs" title="{{ $form->executive_file }}">{{
                                        $form->executive_file ? Str::limit($form->executive_file, 80) : '---'
                                    }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $form->created_at ? Carbon::parse($form->created_at)->format('Y-m-d') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">فرمی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">موجودی اسکریپت‌ها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شناسه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نام</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نوع</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ ایجاد</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($scripts as $script)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $script->id }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $script->name ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">
                                    <span class="block truncate max-w-xs" title="{{ $script->executive_file }}">{{
                                        $script->executive_file ? Str::limit($script->executive_file, 80) : '---'
                                    }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $script->created_at ? Carbon::parse($script->created_at)->format('Y-m-d') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">اسکریپتی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">ارتباط نقش‌ها با فرم‌ها و فرآیندها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نقش</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">فرآیند</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">فرم خلاصه</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($roleFormLinks as $link)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $link->role_name ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $link->process_name ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $link->form_name ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">ارتباطی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">متغیرهای پویا (wf_variables)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کلید</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">فرآیند</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مقدار</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($variables as $variable)
                                <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $variable->key ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $variable->case_id ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $variable->process_id ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">
                                        <span class="block truncate max-w-xs" title="{{ $variable->value }}">{{ Str::limit($variable->value, 80) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">متغیری ثبت نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">متغیرهای فرآیندی (pm_vars)</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کلید</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">فرآیند</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">آخرین به‌روزرسانی</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($pmVariables as $variable)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $variable->key ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $variable->case_id ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $variable->process_id ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $variable->updated_at ? Carbon::parse($variable->updated_at)->format('Y-m-d H:i') : '---' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">داده‌ای ثبت نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">تغییرات تنظیمات سیستم</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کلید</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مقدار</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">آخرین تغییر</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($configChanges as $config)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $config->key ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">
                                    <span class="block truncate max-w-md" title="{{ $config->value }}">{{ Str::limit($config->value, 120) }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $config->updated_at ? Carbon::parse($config->updated_at)->format('Y-m-d H:i') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">تغییری ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
