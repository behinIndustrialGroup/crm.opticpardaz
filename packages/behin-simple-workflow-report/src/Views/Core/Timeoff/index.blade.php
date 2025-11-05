@extends('behin-layouts.app')

@section('title', 'گزارش مرخصی‌ها')

@section('content')
    @php
        $monthNames = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];
        $statusOptions = [
            '' => 'همه وضعیت‌ها',
            'approved' => 'تایید شده',
            'pending' => 'در انتظار',
            'rejected' => 'رد شده',
        ];
        $typeOptions = [
            '' => 'همه انواع',
            'daily' => 'روزانه',
            'hourly' => 'ساعتی',
        ];
    @endphp

    <div class="container-fluid py-3">
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">کل درخواست‌ها</p>
                                <h4 class="fw-bold mb-0">{{ number_format($summary['total_requests']) }}</h4>
                            </div>
                            <span class="material-icons text-primary" style="font-size: 36px;">event</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">مرخصی‌های تایید شده</p>
                                <h4 class="fw-bold text-success mb-0">{{ number_format($summary['approved_requests']) }}</h4>
                            </div>
                            <span class="material-icons text-success" style="font-size: 36px;">check_circle</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">مرخصی‌های در انتظار</p>
                                <h4 class="fw-bold text-warning mb-0">{{ number_format($summary['pending_requests']) }}</h4>
                            </div>
                            <span class="material-icons text-warning" style="font-size: 36px;">schedule</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">مرخصی‌های رد شده</p>
                                <h4 class="fw-bold text-danger mb-0">{{ number_format($summary['rejected_requests']) }}</h4>
                            </div>
                            <span class="material-icons text-danger" style="font-size: 36px;">cancel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">کل ساعات مرخصی</p>
                        <h5 class="fw-bold">{{ number_format($summary['total_hours'], 1) }} ساعت</h5>
                        <p class="text-secondary mb-0">معادل {{ number_format($summary['total_days'], 2) }} روز کاری</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">ساعات تایید شده</p>
                        <h5 class="fw-bold text-success">{{ number_format($summary['approved_hours'], 1) }} ساعت</h5>
                        <p class="text-secondary mb-0">{{ number_format($summary['approved_days'], 2) }} روز تایید شده</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">میانگین هر درخواست</p>
                        <h5 class="fw-bold">{{ number_format($summary['average_duration'], 2) }} ساعت</h5>
                        <p class="text-secondary mb-0">{{ number_format($summary['average_duration'] / $hoursPerDay, 2) }} روز در هر درخواست</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">فیلتر گزارش</h5>
                        <span class="badge bg-light text-primary">{{ number_format($rows->total()) }} درخواست</span>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">کاربر</label>
                                <select name="user_id" class="form-select">
                                    <option value="">همه کاربران</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->number ? $user->number . ' | ' : '' }}{{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">نوع مرخصی</label>
                                <select name="type" class="form-select">
                                    @foreach($typeOptions as $value => $label)
                                        <option value="{{ $value }}" {{ ($filters['type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">وضعیت</label>
                                <select name="status" class="form-select">
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">سال (شمسی)</label>
                                <input type="text" name="year" value="{{ $filters['year'] ?? '' }}" class="form-control" placeholder="مثال: ۱۴۰۲">
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">ماه</label>
                                <select name="month" class="form-select">
                                    <option value="">همه ماه‌ها</option>
                                    @foreach($monthNames as $key => $label)
                                        <option value="{{ $key }}" {{ (int)($filters['month'] ?? 0) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">تاریخ شروع از</label>
                                <input type="text" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="form-control" placeholder="مثال: ۱۴۰۲-۰۱-۰۱">
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">تاریخ شروع تا</label>
                                <input type="text" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="form-control" placeholder="مثال: ۱۴۰۲-۰۱-۳۱">
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">شناسه تاییدکننده</label>
                                <input type="text" name="approved_by" value="{{ $filters['approved_by'] ?? '' }}" class="form-control" placeholder="شناسه کاربر">
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">جست‌وجو</label>
                                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="شرح یا شناسه یکتا">
                            </div>
                            <div class="col-12 col-md-6 col-xl-4">
                                <label class="form-label">تعداد نمایش</label>
                                <select name="per_page" class="form-select">
                                    @foreach([10, 20, 30, 50, 100, 200] as $size)
                                        <option value="{{ $size }}" {{ ($filters['per_page'] ?? 20) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                <a href="{{ route('simpleWorkflowReport.timeoff-report.index') }}" class="btn btn-light">پاکسازی</a>
                                <button type="submit" class="btn btn-primary">اعمال فیلتر</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">تفکیک نوع مرخصی</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">ساعتی</span>
                            <span class="fw-bold">{{ number_format($summary['hourly_hours'], 1) }} ساعت</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">روزانه</span>
                            <span class="fw-bold">{{ number_format($summary['daily_hours'], 1) }} ساعت ({{ number_format($summary['daily_days'], 2) }} روز)</span>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">کاربران با بیشترین مرخصی</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        @forelse($topUsers as $rank => $item)
                            @php
                                $user = $userInfos[$item->user] ?? null;
                                $userName = $user->name ?? 'کاربر #' . $item->user;
                                $userNumber = $user->number ?? null;
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $userName }}</div>
                                    @if($userNumber)
                                        <small class="text-muted">شماره کارمند: {{ $userNumber }}</small>
                                    @endif
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ number_format($item->total_hours, 1) }} ساعت</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">داده‌ای برای نمایش وجود ندارد.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        @if($perUserSummary->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">گزارش کلی مرخصی کاربران</h5>
                    <span class="badge bg-light text-primary">{{ number_format($perUserSummary->count()) }} کاربر</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>کاربر</th>
                                <th>کل ساعات</th>
                                <th>معادل روز</th>
                                <th>ساعات تایید شده</th>
                                <th>در انتظار</th>
                                <th>رد شده</th>
                                <th>ساعتی</th>
                                <th>روزانه</th>
                                <th>تعداد درخواست</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($perUserSummary as $item)
                                @php
                                    $user = $userInfos[$item->user] ?? null;
                                    $userName = $user->name ?? 'کاربر #' . $item->user;
                                    $userNumber = $user->number ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $userName }}</div>
                                        @if($userNumber)
                                            <small class="text-muted">{{ $userNumber }}</small>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->total_hours, 1) }} ساعت</td>
                                    <td>{{ number_format($item->total_hours / $hoursPerDay, 2) }} روز</td>
                                    <td class="text-success">{{ number_format($item->approved_hours, 1) }} ساعت</td>
                                    <td class="text-warning">{{ number_format($item->pending_hours, 1) }} ساعت</td>
                                    <td class="text-danger">{{ number_format($item->rejected_hours, 1) }} ساعت</td>
                                    <td>{{ number_format($item->hourly_hours, 1) }} ساعت</td>
                                    <td>{{ number_format($item->daily_hours, 1) }} ساعت</td>
                                    <td>{{ number_format($item->total_requests) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">تفکیک ماهانه</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ماه</th>
                                    <th>تعداد</th>
                                    <th>کل ساعات</th>
                                    <th>تایید شده</th>
                                    <th>در انتظار</th>
                                    <th>رد شده</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyBreakdown as $month)
                                    @php
                                        $monthIndex = (int) $month->month;
                                        $monthLabel = ($monthNames[$monthIndex] ?? $monthIndex) . ' ' . $month->year;
                                    @endphp
                                    <tr>
                                        <td>{{ $monthLabel }}</td>
                                        <td>{{ number_format($month->total_requests) }}</td>
                                        <td>{{ number_format($month->total_hours, 1) }} ساعت</td>
                                        <td class="text-success">{{ number_format($month->approved_hours, 1) }}</td>
                                        <td class="text-warning">{{ number_format($month->pending_hours, 1) }}</td>
                                        <td class="text-danger">{{ number_format($month->rejected_hours, 1) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">داده‌ای برای نمایش وجود ندارد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">نمودار وضعیت درخواست‌ها</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>تایید شده</span>
                                <span class="badge bg-success rounded-pill">{{ number_format($summary['approved_hours'], 1) }} ساعت</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>در انتظار</span>
                                <span class="badge bg-warning text-dark rounded-pill">{{ number_format($summary['pending_hours'], 1) }} ساعت</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>رد شده</span>
                                <span class="badge bg-danger rounded-pill">{{ number_format($summary['rejected_hours'], 1) }} ساعت</span>
                            </li>
                        </ul>
                        <p class="text-muted small mt-3 mb-0">هر روز معادل {{ $hoursPerDay }} ساعت در نظر گرفته شده است.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">لیست درخواست‌های مرخصی</h5>
                <span class="badge bg-light text-primary">{{ number_format($rows->total()) }} نتیجه</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>کاربر</th>
                            <th>نوع</th>
                            <th>مدت</th>
                            <th>وضعیت</th>
                            <th>تاریخ شروع</th>
                            <th>تاریخ پایان</th>
                            <th>تاریخ درخواست</th>
                            <th>تایید کننده</th>
                            <th>شناسه</th>
                            <th>توضیحات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $user = $userInfos[$row->user] ?? null;
                                $approvedBy = $row->approved_by ? ($userInfos[$row->approved_by] ?? getUserInfo($row->approved_by)) : null;
                                $statusLabel = 'در انتظار';
                                $statusClass = 'bg-warning text-dark';
                                if ($row->approved === 1) {
                                    $statusLabel = 'تایید شده';
                                    $statusClass = 'bg-success';
                                } elseif ($row->approved === 0) {
                                    $statusLabel = 'رد شده';
                                    $statusClass = 'bg-danger';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $user->name ?? 'کاربر #' . $row->user }}</div>
                                    @if(($user->number ?? null))
                                        <small class="text-muted">{{ $user->number }}</small>
                                    @endif
                                </td>
                                <td>{{ $row->type ?? '---' }}</td>
                                <td>
                                    {{ number_format($row->normalized_duration ?? $row->duration, 2) }} ساعت
                                    <div class="text-muted small">{{ number_format(($row->normalized_duration ?? $row->duration) / $hoursPerDay, 2) }} روز</div>
                                </td>
                                <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                <td>{{ $row->start_timestamp ? toJalali((int)$row->start_timestamp)->format('Y/m/d H:i') : '---' }}</td>
                                <td>{{ $row->end_timestamp ? toJalali((int)$row->end_timestamp)->format('Y/m/d H:i') : '---' }}</td>
                                <td>{{ $row->request_timestamp ? toJalali((int)$row->request_timestamp)->format('Y/m/d H:i') : '---' }}</td>
                                <td>{{ $approvedBy->name ?? ($row->approved_by ? 'کاربر #' . $row->approved_by : '---') }}</td>
                                <td>{{ $row->uniqueId ?? '---' }}</td>
                                <td style="max-width: 220px;">
                                    <span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $row->description }}">
                                        {{ $row->description ?? '---' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">درخواستی برای نمایش وجود ندارد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
@endsection
