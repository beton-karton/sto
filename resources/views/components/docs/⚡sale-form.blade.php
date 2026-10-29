<?php

use App\Models\Customer;
use App\Models\Doc;
use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public $docId;
    public $doc;
    public $order_number = '';

    // Шапка
    public $customer_id = '';
    public $notes = '';

    // Модалка
    public $showSearchModal = false;
    public $activeTab = 'customers'; // customers или orders

    // Поиск клиентов
    public $customerSearch = '';
    public $foundCustomers = [];

    // Создание нового клиента
    public $isCreatingCustomer = false;
    public $new_customer_name = '';
    public $new_customer_phone = '';

    // Поиск ЗН
    public $orderSearch = '';
    public $foundOrders = [];

    public function mount($docId)
    {
        $this->docId = $docId;
        $this->doc = Doc::findOrFail($docId);
        $this->customer_id = $this->doc->customer_id;
        $this->notes = $this->doc->notes;
    }

    public function saveHeader()
    {
        $this->doc->update([
            'customer_id' => $this->customer_id ?: null,
            'notes' => $this->notes,
        ]);
        session()->flash('message', 'Шапка документа сохранена.');
    }

    // --- ЛОГИКА МОДАЛКИ ---
    public function openSearchModal()
    {
        $this->reset(['customerSearch', 'foundCustomers', 'isCreatingCustomer', 'new_customer_name', 'new_customer_phone', 'orderSearch', 'foundOrders']);
        $this->activeTab = 'customers';
        $this->showSearchModal = true;
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;

        // Если переключились на ЗН и список пуст — загружаем последние
        if ($tab === 'orders' && empty($this->orderSearch)) {
            $this->loadInitialOrders();
        }
    }

    public function loadInitialOrders()
    {
        $this->foundOrders = Order::with(['customer', 'vehicle'])
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();
    }

    // --- ПОИСК КЛИЕНТА ---
    public function searchCustomers()
    {
        if (strlen($this->customerSearch) < 2) {
            $this->foundCustomers = [];
            return;
        }

        $searchTerm = mb_strtolower($this->customerSearch);

        $this->foundCustomers = Customer::whereRaw('LOWER(name) LIKE ?', ['%' . $searchTerm . '%'])
            ->orWhere('phone', 'like', '%' . $this->customerSearch . '%')
            ->limit(10)
            ->get();
    }

    public function selectCustomer($id)
    {
        $this->customer_id = $id;
        $this->showSearchModal = false;
    }

    public function createNewCustomer()
    {
        $this->isCreatingCustomer = true;
        $this->new_customer_name = $this->customerSearch; // Подставляем то, что искали
    }

    public function saveNewCustomer()
    {
        $this->validate([
            'new_customer_name' => 'required|min:2',
        ]);

        $customer = Customer::create([
            'name' => $this->new_customer_name,
            'phone' => $this->new_customer_phone,
        ]);

        $this->customer_id = $customer->id;
        $this->showSearchModal = false;
    }

    // --- ПОИСК ЗН ---
    public function searchOrders()
    {
        if (strlen($this->orderSearch) < 2) {
            $this->foundOrders = [];
            return;
        }

        $this->foundOrders = Order::with(['customer', 'vehicle'])
            ->where('status', '!=', 'completed')
            ->where(function($query) {
                $query->where('id', 'like', '%' . $this->orderSearch . '%')
                    ->orWhere('license_plate', 'like', '%' . $this->orderSearch . '%');
            })
            ->limit(10)
            ->get();
    }

    public function selectOrder($id)
    {
        $order = Order::find($id);
        $this->customer_id = $order->customer_id;
        $this->order_number = $order->number; // <--- Сохраняем номер ЗН
        $this->showSearchModal = false;
    }
};
?>

<div class="w-full p-4">
    <h2 class="text-xl font-bold text-gray-800 mb-4">
        Реализация товаров №{{ $doc->number }}
    </h2>

    @if(session()->has('message'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4 text-sm">
            {{ session('message') }}
        </div>
    @endif

    <!-- КОМПАКТНАЯ ШАПКА -->
    <div class="border border-gray-300 rounded p-3 mb-6 bg-gray-50 flex flex-col gap-3 text-sm">

        <div class="flex items-end gap-4">
            <!-- Поле клиента + поиск (6/12) -->
            <div class="w-1/2">
                <label class="block text-gray-700 mb-1">Клиент (Покупатель)</label>
                <div class="flex gap-1">
                    <input type="text" readonly value="
                        @if($order_number)
                            {{ $order_number }} — {{ \App\Models\Customer::find($customer_id)?->name }}
                        @else
                            {{ \App\Models\Customer::find($customer_id)?->name ?? 'Розничный покупатель' }}
                        @endif
                    " class="flex-1 border border-gray-300 rounded px-3 py-1.5 bg-gray-100 text-gray-700 cursor-not-allowed focus:outline-none">

                    <button wire:click="openSearchModal" type="button" class="bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 px-2 py-1.5 rounded shadow-sm" title="Найти клиента или ЗН">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Поле примечания (6/12) -->
            <div class="w-1/2">
                <label class="block text-gray-700 mb-1">Примечание</label>
                <input type="text" wire:model="notes" class="w-full border border-gray-300 rounded px-3 py-1.5">
            </div>
        </div>

        <!-- Кнопка Сохранить снизу -->
        <div class="flex justify-end">
            <button wire:click="saveHeader" class="bg-white border border-gray-300 hover:bg-gray-100 text-gray-700 px-4 py-1.5 rounded shadow-sm font-medium">
                Сохранить
            </button>
        </div>
    </div>

    <!-- ЗАГЛУШКА ДЛЯ ТАБЛИЧНОЙ ЧАСТИ -->
    <div class="bg-white border border-gray-200 rounded p-10 text-center text-gray-500">
        Механизм добавления запчастей в разработке.
    </div>

    <!-- МОДАЛКА ПОИСКА -->
    @if($showSearchModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col" style="height: 400px;">

                <!-- Табы -->
                <div class="flex border-b border-gray-200 bg-gray-50 shrink-0">
                    <button wire:click="switchTab('customers')" class="px-6 py-3 text-sm font-medium {{ $activeTab === 'customers' ? 'text-blue-600 border-b-2 border-blue-600 bg-white' : 'text-gray-500 hover:text-gray-700' }}">
                        Поиск клиента
                    </button>
                    <button wire:click="switchTab('orders')" class="px-6 py-3 text-sm font-medium {{ $activeTab === 'orders' ? 'text-blue-600 border-b-2 border-blue-600 bg-white' : 'text-gray-500 hover:text-gray-700' }}">
                        Поиск заказ-наряда
                    </button>
                    <div class="flex-1"></div>
                    <button wire:click="$set('showSearchModal', false)" class="px-4 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <!-- Контент -->
                <div class="p-5 overflow-y-auto flex-1">
                    @if($activeTab === 'customers')
                        @if(!$isCreatingCustomer)
                            <div class="flex gap-2 mb-4">
                                <input type="text" wire:model.live.debounce.300ms="customerSearch" wire:keydown.enter="searchCustomers" class="flex-1 border border-gray-300 rounded px-3 py-2" placeholder="Введите ФИО или телефон...">
                                <button wire:click="searchCustomers" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Найти</button>
                            </div>

                            <div class="border rounded overflow-y-auto max-h-[250px]">
                                @if(count($foundCustomers) > 0)
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                        @foreach($foundCustomers as $c)
                                            <tr class="hover:bg-gray-50 cursor-pointer" wire:click="selectCustomer({{ $c->id }})">
                                                <td class="px-3 py-2 font-medium">{{ $c->name }}</td>
                                                <td class="px-3 py-2 text-gray-500">{{ $c->phone ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-center py-6 text-gray-500 text-sm">
                                        @if(strlen($customerSearch) >= 2)
                                            Ничего не найдено.
                                            <button wire:click="createNewCustomer" class="text-blue-600 hover:underline ml-1">Создать нового клиента</button>
                                        @else
                                            Введите ФИО или телефон для поиска.
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Создание нового клиента -->
                            <h4 class="font-bold text-gray-800 mb-3">Новый клиент</h4>
                            <div class="space-y-3 text-sm mb-4">
                                <div>
                                    <label class="block text-gray-700 mb-1">ФИО</label>
                                    <input type="text" wire:model="new_customer_name" class="w-full border border-gray-300 rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-1">Телефон</label>
                                    <input type="text" wire:model="new_customer_phone" class="w-full border border-gray-300 rounded px-3 py-2">
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="saveNewCustomer" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Сохранить и выбрать</button>
                                <button wire:click="$set('isCreatingCustomer', false)" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">Отмена</button>
                            </div>
                        @endif

                    @else
                        <!-- Поиск ЗН -->
                        <div class="flex gap-2 mb-4">
                            <input type="text" wire:model.live.debounce.300ms="orderSearch" wire:keydown.enter="searchOrders" class="flex-1 border border-gray-300 rounded px-3 py-2" placeholder="Введите номер ЗН или госномер...">
                            <button wire:click="searchOrders" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Найти</button>
                        </div>

                        <div class="border rounded overflow-y-auto max-h-[250px]">
                            @if(count($foundOrders) > 0)
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach($foundOrders as $o)
                                        <tr class="hover:bg-gray-50 cursor-pointer" wire:click="selectOrder({{ $o->id }})">
                                            <td class="px-3 py-2 font-medium">ЗН #{{ $o->id }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ $o->customer->name ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ $o->license_plate ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="text-center py-6 text-gray-500 text-sm">
                                    Заказ-наряды не найдены.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
