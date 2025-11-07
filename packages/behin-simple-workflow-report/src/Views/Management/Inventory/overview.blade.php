@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش‌های انبار و محصولات')
@section('subtitle', 'پایش موجودی، حداقل و حداکثر، و فعالیت انبارها')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Carbon;

        $inventoryItems = DB::table('wf_entity_inventory_items')
            ->select('id', 'product_id', 'product_name', 'warehouse_id', 'warehouse_name', 'quantity', 'purchase_price', 'change_type', 'created_at')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $products = DB::table('wf_entity_products')
            ->select('id', 'name', 'sku', 'min_stock', 'max_stock')
            ->get();

        $productMap = $products->pluck('name', 'id');
        $productMin = $products->pluck('min_stock', 'id');
        $productMax = $products->pluck('max_stock', 'id');

        $productTotals = $inventoryItems->groupBy('product_id')->map(function ($rows) {
            $quantity = $rows->sum('quantity');
            $value = $rows->sum(function ($row) {
                $price = (float) str_replace(',', '', $row->purchase_price ?? 0);
                return $price * ($row->quantity ?? 0);
            });

            return [
                'quantity' => $quantity,
                'value' => $value,
            ];
        });

        $lowStock = $products->filter(function ($product) use ($productTotals, $productMin) {
            $current = $productTotals[$product->id]['quantity'] ?? 0;
            return !is_null($product->min_stock) && $current < $product->min_stock;
        });

        $overStock = $products->filter(function ($product) use ($productTotals) {
            $current = $productTotals[$product->id]['quantity'] ?? 0;
            return !is_null($product->max_stock) && $current > $product->max_stock;
        });

        $stockValue = $products->map(function ($product) use ($productTotals) {
            $totals = $productTotals[$product->id] ?? ['quantity' => 0, 'value' => 0];
            return [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $totals['quantity'],
                'avg_price' => ($totals['quantity'] ?? 0) ? $totals['value'] / max($totals['quantity'], 1) : 0,
                'total_value' => $totals['value'],
            ];
        })->filter();

        $warehouseActivity = DB::table('wf_entity_inventory_items')
            ->select('warehouse_id', 'warehouse_name', DB::raw('COUNT(*) as transactions'), DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('warehouse_id', 'warehouse_name')
            ->orderByDesc('transactions')
            ->get();

        $warehouses = DB::table('wf_entity_warehouses')
            ->select('id', 'name', 'manager')
            ->get()
            ->keyBy('id');
    @endphp

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد کالاهای ثبت شده</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($products->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">ارزش ریالی کل موجودی</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($stockValue->sum('total_value'), 0) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد گردش انبار ثبت شده</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ number_format($inventoryItems->count()) }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">موجودی فعلی انبار</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کالا</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کد کالا</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">موجودی فعلی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">حداقل</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">حداکثر</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($products as $product)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $product->name ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $product->sku ?? '---' }}</td>
                                @php
                                    $currentQuantity = $productTotals[$product->id]['quantity'] ?? 0;
                                @endphp
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($currentQuantity) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($product->min_stock ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($product->max_stock ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">اطلاعات موجودی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">گردش ورود و خروج کالا</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کالا</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">انبار</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نوع تغییر</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($inventoryItems as $item)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $item->product_name ?? ($productMap[$item->product_id] ?? ('کالا #' . $item->product_id)) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $item->warehouse_name ?? optional($warehouses[$item->warehouse_id] ?? null)->name ?? ('انبار #' . $item->warehouse_id) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($item->quantity ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $item->change_type ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d') : '---' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">گردش ثبت نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">کالاهای خارج از محدوده موجودی</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کالا</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">موجودی</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">حداقل</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">حداکثر</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وضعیت</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($lowStock as $item)
                                @php
                                    $currentQuantity = $productTotals[$item->id]['quantity'] ?? 0;
                                @endphp
                                <tr class="bg-rose-50">
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $item->name ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-rose-600 font-semibold">{{ number_format($currentQuantity) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($item->min_stock ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($item->max_stock ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-rose-600 font-semibold">کمتر از حداقل</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">کالایی زیر حداقل موجودی نیست.</td>
                                </tr>
                            @endforelse
                            @foreach($overStock as $item)
                                @php
                                    $currentQuantity = $productTotals[$item->id]['quantity'] ?? 0;
                                @endphp
                                <tr class="bg-amber-50">
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $item->name ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-amber-600 font-semibold">{{ number_format($currentQuantity) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($item->min_stock ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($item->max_stock ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-amber-600 font-semibold">بیشتر از حداکثر</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">ارزش ریالی موجودی</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کالا</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">میانگین قیمت خرید</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">ارزش کل</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($stockValue as $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $row['name'] ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($row['quantity'] ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($row['avg_price'] ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($row['total_value'] ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">اطلاعاتی وجود ندارد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">فعالیت انبارها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">انبار</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مدیر</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد تراکنش</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">جمع تعداد</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($warehouseActivity as $activity)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $activity->warehouse_name ?? optional($warehouses[$activity->warehouse_id] ?? null)->name ?? ('انبار #' . $activity->warehouse_id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ optional($warehouses[$activity->warehouse_id] ?? null)->manager ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ number_format($activity->transactions ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($activity->total_quantity ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">هیچ فعالیتی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
