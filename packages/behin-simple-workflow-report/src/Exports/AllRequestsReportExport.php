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
            'نام',
            'نام خانوادگی',
            'شناسه قبض برق',
            'نوع نیروگاه',
            'استان محل نیروگاه',
            'ظرفیت درخواستی',
            'نتیجه اولین تماس',
            'سود تسهیلات',
            'مبلغ اولیه',
            'امکان‌سنجی',
            'نتیجه تماس رابط مالی با متقاضی',
            'آخرین وضعیت درخواست',
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
            $row['user_firstname'] ?? null,
            $row['user_lastname'] ?? null,
            $row['electricity_bill_id'] ?? null,
            $row['powerhouse_type'] ?? null,
            $row['powerhouse_place_info_province'] ?? null,
            $row['requested_capacity_of_powerhouse'] ?? null,
            $row['first_call_result'] ?? null,
            $row['loan_interest'] ?? null,
            $row['initial_amount'] ?? null,
            $row['feasibility_study'] ?? null,
            $row['fin_interface_call_result'] ?? null,
            $row['last_status'] ?? null,
        ];
    }
}
