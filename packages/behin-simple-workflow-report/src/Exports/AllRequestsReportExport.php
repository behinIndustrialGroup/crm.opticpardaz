<?php

namespace Behin\SimpleWorkflowReport\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AllRequestsReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'شماره پرونده',
            'نام مشتری',
            'موبایل مشتری',
            'نام دستگاه',
            'سریال دستگاه',
            'شماره پلاک دستگاه',
            'نوع تعمیر',
            'جزئیات نوع تعمیر',
            'تعمیرکار',
            'تاریخ شروع تعمیر',
            'تاریخ پایان تعمیر',
            'تایید اول تعمیرات',
            'تایید دوم تعمیرات',
            'تایید سوم تعمیرات',
            'دستیاران تعمیر',
            'هزینه تعیین شده',
            'هزینه‌های دریافت شده',
            'آخرین وضعیت',
        ];
    }

    public function map($row): array
    {
        if ($row instanceof Collection) {
            $row = $row->toArray();
        } elseif (is_object($row)) {
            $row = (array) $row;
        }

        return [
            $row['case_number'] ?? null,
            $row['customer_name'] ?? null,
            $row['customer_mobile'] ?? null,
            $row['device_name'] ?? null,
            $row['device_serial'] ?? null,
            $row['device_plaque'] ?? null,
            $row['repair_type'] ?? null,
            $row['repair_subtype'] ?? null,
            $row['repairman'] ?? null,
            $row['repair_start_at'] ?? null,
            $row['repair_end_at'] ?? null,
            $row['approval_first'] ?? null,
            $row['approval_second'] ?? null,
            $row['approval_third'] ?? null,
            $row['assistants'] ?? null,
            $row['repair_cost'] ?? null,
            $row['received_cost'] ?? null,
            $row['last_status'] ?? null,
        ];
    }
}
