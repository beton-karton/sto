<?php

use Livewire\Component;
use App\Models\Doc;
use App\Models\Counter;

new class extends Component
{
    public function createReceipt()
    {
        // 1. Получаем следующий номер атомарно (в транзакции)
        $number = \Illuminate\Support\Facades\DB::transaction(function () {
            $counter = Counter::where('name', 'receipt_number')->lockForUpdate()->first();

            if (!$counter) {
                $counter = Counter::create(['name' => 'receipt_number', 'value' => 0]);
            }

            $counter->increment('value');
            return 'ПН-' . str_pad($counter->value, 6, '0', STR_PAD_LEFT);
        });

        //dd($number);
        // 2. Создаем пустой документ
        $doc = Doc::create([
            'number' => $number,
            'type' => 'receipt',
            'user_id' => 1, // Заглушка
            'is_confirmed' => false,
        ]);

        // 3. Открываем таб с этим документом
        $this->dispatch('openTab', data: [
            'id' => 'doc_' . $doc->id,
            'title' => $number,
            'component' => 'docs.income-form',
            'params' => ['docId' => $doc->id]
        ]);
    }

    public function openReceipts()
    {
        $this->dispatch('openTab', data: [
            'id' => 'docs_receipt_list',
            'title' => 'Поступление товаров',
            'component' => 'docs.docs-table',
            'params' => ['type' => 'receipt']
        ]);
    }

    public function openOrders()
    {
        // Отправляем данные как один массив
        $this->dispatch('openTab', data: [
            'id' => 'orders_list',
            'title' => 'Заказ-наряды',
            'component' => 'orders-table'
        ]);
    }

    public function openCars()
    {
        $this->dispatch('openTab', data: [
            'id' => 'cars_list',
            'title' => 'Автомобили',
            'component' => 'cars-table'
        ]);
    }

    public function openCustomers()
    {
        $this->dispatch('openTab', data: [
            'id' => 'customers_list',
            'title' => 'Клиенты',
            'component' => 'customers-table'
        ]);
    }

    public function openDocs()
    {
        $this->dispatch('openTab', data: [
            'id' => 'new_doc',
            'title' => 'Новый документ',
            'component' => 'docs.doc-form'
        ]);
    }

    public function openStockReport()
    {
        $this->dispatch('openTab', data: [
            'id' => 'stock_report',
            'title' => 'Остатки товаров',
            'component' => 'reports.stock-report'
        ]);
    }

    public function openProducts()
    {
        $this->dispatch('openTab', data: [
            'id' => 'products_list',
            'title' => 'Товары',
            'component' => 'products-table'
        ]);
    }

    public function openIssues()
    {
        $this->dispatch('openTab', data: [
            'id' => 'docs_issue_list',
            'title' => 'Реализация товаров',
            'component' => 'docs.docs-table',
            'params' => ['type' => 'issue']
        ]);
    }

};
?>

<div class="p-4">
    <!-- РАЗДЕЛ 1: Справочники -->
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4">Справочники</h3>
    <ul class="space-y-1 mb-6">
        <li>
            <button wire:click="openCars" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors flex items-center">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Автомобили
            </button>
        </li>
        <li>
            <button wire:click="openCustomers" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors flex items-center">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Клиенты
            </button>
        </li>
        <li>
            <button wire:click="openProducts" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors flex items-center">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Товары
            </button>
        </li>
    </ul>

    <!-- РАЗДЕЛ 2: Документы -->
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4">Документы</h3>
    <ul class="space-y-1 mb-6">
        <li>
            <button wire:click="openReceipts" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors flex items-center">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Поступление товаров
            </button>
        </li>
        <li>
            <button wire:click="openIssues" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors flex items-center">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Реализация товаров
            </button>
        </li>
        <li>
            <button wire:click="openOrders" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors flex items-center">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Заказ-наряды
            </button>
        </li>
    </ul>

    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4">Отчеты</h3>
    <ul class="space-y-1 mb-6">
        <li>
            <button wire:click="openStockReport" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors flex items-center">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Остатки товаров
            </button>
        </li>
    </ul>

    <!-- РАЗДЕЛ 3: Настройки -->
    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-4">Настройки</h3>
    <ul class="space-y-1">
        <li>
            <button wire:click="$dispatch('openTab', { data: { id: 'warehouses_list', title: 'Склады', component: 'warehouses-table' } })" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md transition-colors flex items-center">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Склады
            </button>
        </li>
    </ul>
</div>
