<?php

namespace Behin\SimpleWorkflowReport\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StageReportExport implements FromCollection, WithHeadings, WithMapping
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
            'شماره درخواست',
            'نام مشتری',
            'موبایل مشتری',
            'کدملی',
            'استان',
            'پیمانکار',
            'شناسه قبض',
            'کدپستی',
            'آدرس',
            'مرحله جاری',
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
            $row['customer_national_id'] ?? null,
            $row['province'] ?? null,
            $row['contractor'] ?? null,
            $row['bill_identifier'] ?? null,
            $row['postal_code'] ?? null,
            $row['address'] ?? null,
            $row['current_stage'] ?? null,
        ];
    }
}
