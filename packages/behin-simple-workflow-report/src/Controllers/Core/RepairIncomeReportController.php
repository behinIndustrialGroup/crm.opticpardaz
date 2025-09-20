<?php

namespace Behin\SimpleWorkflowReport\Controllers\Core;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Models\Core\Cases;
use Behin\SimpleWorkflow\Models\Entities\Repair_incomes;

class RepairIncomeReportController extends Controller
{
    public function index()
    {
        $incomes = Repair_incomes::orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($income) {
                $income->normalized_amount = $this->normalizeAmount($income->payment_amount);

                return $income;
            })
            ->filter(function ($income) {
                return !empty($income->case_id) || !empty($income->case_number);
            });

        $caseSummaries = $incomes->groupBy(function ($income) {
            return $income->case_id ?: $income->case_number ?: $income->id;
        })->map(function ($payments) {
            $first = $payments->first();
            $sortedPayments = $payments->sortByDesc(function ($payment) {
                return $payment->payment_date ?? $payment->created_at;
            })->values();

            return [
                'case_id' => $first->case_id,
                'case_number' => $first->case_number,
                'total_amount' => $payments->sum('normalized_amount'),
                'payments_count' => $payments->count(),
                'payments' => $sortedPayments,
            ];
        })->sortByDesc('total_amount')->values();

        $caseDetails = Cases::whereIn('id', $caseSummaries->pluck('case_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $caseSummaries = $caseSummaries->map(function ($summary) use ($caseDetails) {
            $case = $summary['case_id'] ? $caseDetails->get($summary['case_id']) : null;

            $summary['case'] = $case;
            $summary['customer_name'] = $case ? $case->getVariable('customer_fullname') : null;

            return $summary;
        });

        $overallTotal = $caseSummaries->sum('total_amount');

        return view('SimpleWorkflowReportView::Core.RepairIncome.index', [
            'caseSummaries' => $caseSummaries,
            'overallTotal' => $overallTotal,
        ]);
    }

    private function normalizeAmount($amount): int
    {
        if (is_null($amount)) {
            return 0;
        }

        if (is_numeric($amount)) {
            return (int) $amount;
        }

        $amount = trim($amount);

        $amount = str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $amount
        );

        $amount = str_replace(['ریال', 'ريال', 'تومان', 'IRR', 'IRT', ',', '٬', '٫', '،', ' ', '‌', '/', '\\'], '', $amount);

        $amount = preg_replace('/[^\d\-]/u', '', $amount);

        if ($amount === '' || $amount === '-' || $amount === null) {
            return 0;
        }

        return (int) $amount;
    }
}
