<?php

use App\Models\Customer;
use Livewire\Component;

new class extends Component
{
    public $search = '';
    public $customers = [];

    // Свойства формы
    public $showModal = false;
    public $name = '';
    public $phone = '';
    public $email = '';

    public function mount()
    {
        $this->loadCustomers();
    }

    public function loadCustomers()
    {
        $this->customers = Customer::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->get();
    }

    // При изменении поиска перезагружаем список
    public function updatedSearch()
    {
        $this->loadCustomers();
    }

    public function openCustomer($id)
    {
        $this->dispatch('openTab', data: [
            'id' => 'customer_' . $id,
            'title' => 'Клиент #' . $id,
            'component' => 'customer-card',
            'params' => ['customerId' => $id]
        ]);
    }

    public function saveCustomer()
    {
        $this->validate([
            'name' => 'required|min:2',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
        ]);

        Customer::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
        ]);

        $this->reset(['name', 'phone', 'email', 'showModal']);
        $this->loadCustomers(); // Обновляем список
    }

    public function deleteCustomer($id)
    {
        Customer::find($id)?->delete();
        $this->loadCustomers(); // Обновляем список
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">Клиенты</h2>
        <button wire:click="$set('showModal', true)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">
            + Добавить клиента
        </button>
    </div>

    <!-- Поиск -->
    <div class="mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Поиск по имени или телефону..." class="w-full max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
    </div>

    <!-- Модалка -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Новый клиент</h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-5 space-y-4 text-sm">
                    <div>
                        <label class="block text-gray-700 mb-1">ФИО</label>
                        <input type="text" wire:model="name" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @error('name') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Телефон</label>
                        <input type="text" wire:model="phone" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @error('phone') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Email</label>
                        <input type="email" wire:model="email" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @error('email') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Отмена</button>
                    <button wire:click="saveCustomer" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow-sm">Сохранить</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Таблица -->
    <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ФИО</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Телефон</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
            @forelse($this->customers as $customer)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 whitespace-nowrap">
                        <a href="#" wire:click.prevent="openCustomer({{ $customer->id }})" class="text-blue-600 font-medium hover:underline">
                            {{ $customer->name }}
                        </a>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-900">{{ $customer->phone ?? '—' }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">{{ $customer->email ?? '—' }}</td>
                    <td class="px-3 py-2 whitespace-nowrap text-right">
                        <button wire:click="deleteCustomer({{ $customer->id }})" wire:confirm="Удалить клиента?" class="text-gray-500 hover:text-red-600" title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">Клиенты не найдены.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
