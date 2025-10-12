<?php

namespace Behin\SimpleWorkflowReport\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Collection $customers)
    {
    }

    public function collection(): Collection
    {
        return $this->customers;
    }

    public function headings(): array
    {
        return [
            'نام و نام خانوادگی',
            'کد ملی',
            'شماره موبایل',
            'آدرس',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->fullname,
            $customer->national_id,
            $customer->mobile,
            $customer->address,
        ];
    }
}
