<?php

use Livewire\Component;

new class extends Component
{
    public function openOrder($id)
    {
        $this->dispatch('openTab', data: [
            'id' => 'order_' . $id,
            'title' => 'ЗН #' . $id,
            'component' => 'order-card',
            'params' => ['orderId' => $id]
        ]);
    }
};
?>

<div>
    <!-- Заголовок и кнопка -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">Заказ-наряды</h2>
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium transition-colors">
            + Добавить
        </button>
    </div>

    <!-- Таблица -->
    <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">№</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Клиент</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Автомобиль</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Номер</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
            <!-- Тестовая строка -->
            <tr class="hover:bg-gray-50 cursor-pointer" wire:click="openOrder(1)">
                <td class="px-3 py-2 whitespace-nowrap text-blue-600 font-medium">ЗН #1</td>
                <td class="px-3 py-2 whitespace-nowrap text-gray-900">Света Макарова</td>
                <td class="px-3 py-2 whitespace-nowrap text-gray-500">BMW Z4</td>
                <td class="px-3 py-2 whitespace-nowrap text-gray-500">А123ВС</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
