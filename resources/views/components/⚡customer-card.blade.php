<?php

use App\Models\Customer;
use Livewire\Component;

new class extends Component
{
    public $customerId;
    public $customer;

    // Поля для редактирования
    public $name = '';
    public $phone = '';
    public $email = '';
    public $isEditing = false;

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->loadCustomer();
    }

    public function loadCustomer()
    {
        $this->customer = Customer::findOrFail($this->customerId);
        $this->name = $this->customer->name;
        $this->phone = $this->customer->phone;
        $this->email = $this->customer->email;
    }

    public function edit()
    {
        $this->isEditing = true;
    }

    public function cancel()
    {
        $this->isEditing = false;
        $this->loadCustomer(); // Сбрасываем изменения
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:2',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        $this->customer->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
        ]);

        $this->isEditing = false;
    }
};
?>

<div>
    <!-- Шапка карточки -->
    <div class="bg-white rounded shadow-sm border border-gray-200 p-5 mb-4">
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-xl font-bold text-gray-800">Карточка клиента</h2>
            @if(!$isEditing)
                <button wire:click="edit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded text-sm font-medium transition-colors">
                    Редактировать
                </button>
            @endif
        </div>

        @if($isEditing)
            <div class="grid grid-cols-2 gap-4 text-sm max-w-2xl">
                <div class="col-span-2">
                    <label class="block text-gray-700 mb-1">ФИО</label>
                    <input type="text" wire:model="name" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">Телефон</label>
                    <input type="text" wire:model="phone" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-gray-700 mb-1">Email</label>
                    <input type="email" wire:model="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <div class="col-span-2 flex space-x-3 mt-2">
                    <button wire:click="save" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">Сохранить</button>
                    <button wire:click="cancel" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm font-medium">Отмена</button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-2 gap-y-3 gap-x-6 text-sm">
                <div>
                    <span class="text-gray-500 block text-xs uppercase tracking-wider mb-1">ФИО</span>
                    <span class="font-medium text-gray-900">{{ $customer->name }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block text-xs uppercase tracking-wider mb-1">Телефон</span>
                    <span class="font-medium text-gray-900">{{ $customer->phone ?? '—' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-gray-500 block text-xs uppercase tracking-wider mb-1">Email</span>
                    <span class="text-gray-900">{{ $customer->email ?? '—' }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Блоки статистики (заглушки) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Машины -->
        <div class="bg-white rounded shadow-sm border border-gray-200 p-4">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Автомобили
            </h3>
            <p class="text-sm text-gray-500 italic">История машин появится после создания заказ-нарядов.</p>
        </div>

        <!-- Ремонты -->
        <div class="bg-white rounded shadow-sm border border-gray-200 p-4">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Заказ-наряды
            </h3>
            <p class="text-sm text-gray-500 italic">История ремонтов появится после создания заказ-нарядов.</p>
        </div>

        <!-- Платежи -->
        <div class="bg-white rounded shadow-sm border border-gray-200 p-4">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Платежи
            </h3>
            <p class="text-sm text-gray-500 italic">История платежей появится позже.</p>
        </div>
    </div>
</div>
