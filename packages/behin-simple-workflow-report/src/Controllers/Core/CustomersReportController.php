<?php

namespace Behin\SimpleWorkflowReport\Controllers\Core;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Models\Entities\Customers;
use Behin\SimpleWorkflowReport\Exports\CustomersExport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomersReportController extends Controller
{
    public function index(): View
    {
        $customers = Customers::query()
            ->orderBy('fullname')
            ->get();

        return view('SimpleWorkflowReportView::Core.Customers.index', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCustomer($request);

        Customers::create($data);

        return redirect()
            ->route('simpleWorkflowReport.customers.index')
            ->with('success', 'مشتری با موفقیت ثبت شد.');
    }

    public function update(Request $request, Customers $customer): RedirectResponse
    {
        $data = $this->validateCustomer($request);

        $customer->update($data);

        return redirect()
            ->route('simpleWorkflowReport.customers.index')
            ->with('success', 'اطلاعات مشتری با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Customers $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('simpleWorkflowReport.customers.index')
            ->with('success', 'مشتری با موفقیت حذف شد.');
    }

    public function export(): BinaryFileResponse
    {
        $customers = Customers::query()
            ->orderBy('fullname')
            ->get();

        return Excel::download(new CustomersExport($customers), 'customers.xlsx');
    }

    protected function validateCustomer(Request $request): array
    {
        return $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ], [], [
            'fullname' => 'نام و نام خانوادگی',
            'national_id' => 'کد ملی',
            'mobile' => 'شماره موبایل',
            'address' => 'آدرس',
        ]);
    }
}
