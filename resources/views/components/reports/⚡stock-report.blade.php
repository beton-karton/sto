<?php

use Livewire\Component;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $stocks = [];

    public function mount()
    {
        // Сложный SQL-запрос для расчета остатков
        $this->stocks = DB::table('products')
            ->leftJoin('transactions', 'products.id', '=', 'transactions.product_id')
            ->leftJoin('part_brands', 'products.part_brand_id', '=', 'part_brands.id')
            ->select(
                'products.id',
                'products.name as product_name',
                'products.article',
                'part_brands.name as brand_name',
                // Суммируем количество, умноженное на знак (приход +, расход -)
                // Если транзакций нет, COALESCE вернет 0
                DB::raw('COALESCE(SUM(transactions.amount * transactions.sign), 0) as total_stock')
            )
            ->groupBy('products.id', 'products.name', 'products.article', 'part_brands.name')
            ->orderBy('part_brands.name')
            ->orderBy('products.name')
            ->get();
    }
};
?>

<div class="w-full">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Остатки товаров на складе</h2>

    <div class="bg-white border border-gray-200 overflow-hidden">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2 border border-gray-300 text-left text-xs font-medium text-gray-500 uppercase">№</th>
                <th class="px-3 py-2 border border-gray-300 text-left text-xs font-medium text-gray-500 uppercase">Каталог</th>
                <th class="px-3 py-2 border border-gray-300 text-left text-xs font-medium text-gray-500 uppercase">Артикул</th>
                <th class="px-3 py-2 border border-gray-300 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                <th class="px-3 py-2 border border-gray-300 text-right text-xs font-medium text-gray-500 uppercase">Остаток</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
            @forelse($stocks as $index => $stock)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 border border-gray-300">{{ $index + 1 }}</td>
                    <td class="px-3 py-2 border border-gray-300 font-medium">{{ $stock->brand_name ?? '—' }}</td>
                    <td class="px-3 py-2 border border-gray-300">{{ $stock->article ?? '—' }}</td>
                    <td class="px-3 py-2 border border-gray-300">{{ $stock->product_name ?? '—' }}</td>
                    <td class="px-3 py-2 border border-gray-300 text-right font-bold
                            {{ $stock->total_stock > 0 ? 'text-green-600' : ($stock->total_stock < 0 ? 'text-red-600' : 'text-gray-500') }}">
                        {{ $stock->total_stock }} шт.
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-3 py-4 text-center text-gray-500 border border-gray-300">
                        На складе пока нет товаров.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
