<?php

use App\Models\Doc;
use App\Models\Warehouse;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public $docId;
    public $doc;

    public $warehouse_id = '';
    public $customer_id = '';
    public $notes = '';

    public $showProductModal = false;
    public $search = '';
    public $foundProducts = [];
    public $selectedProduct = null;

    public $isCreatingNew = false;
    public $new_brand_search = '';
    public $new_part_brand_id = null;
    public $new_article = '';
    public $new_name = '';
    public $new_oem = '';

    public $amount = 1;
    public $purchase_price = null;

    public function mount($docId)
    {
        $this->docId = $docId;
        $this->doc = Doc::with('transactions.product')->findOrFail($docId);
        $this->warehouse_id = $this->doc->warehouse_id;
        $this->customer_id = $this->doc->customer_id;
        $this->notes = $this->doc->notes;
    }

    public function saveHeader()
    {
        $this->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $this->doc->update([
            'warehouse_id' => $this->warehouse_id,
            'customer_id' => $this->customer_id,
            'notes' => $this->notes,
        ]);

        session()->flash('message', 'Шапка документа сохранена.');
    }

    public function openProductModal()
    {
        $this->reset(['search', 'foundProducts', 'selectedProduct', 'isCreatingNew', 'new_brand_search', 'new_part_brand_id', 'new_article', 'new_name', 'new_oem', 'amount', 'purchase_price']);
        $this->showProductModal = true;
    }

    public function searchProduct()
    {
        if (strlen($this->search) < 2) {
            $this->foundProducts = [];
            return;
        }

        $this->foundProducts = Product::where('article', 'like', '%' . $this->search . '%')
            ->orWhere('oem_number', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->limit(10)
            ->get();

        $this->isCreatingNew = false;
    }

    public function selectProduct($id)
    {
        $this->selectedProduct = Product::find($id);
        $this->foundProducts = [];
    }

    public function createNewProduct()
    {
        $this->isCreatingNew = true;
        $this->selectedProduct = null;
        $this->foundProducts = [];
    }

    public function saveNewProduct()
    {
        $this->validate([
            'new_part_brand_id' => 'required',
            'new_article' => 'required',
            'new_name' => 'required',
        ]);

        $product = Product::create([
            'part_brand_id' => $this->new_part_brand_id,
            'article' => $this->new_article,
            'name' => $this->new_name,
            'oem_number' => $this->new_oem,
            'sale_price' => 0,
            'average_price' => 0,
        ]);

        $this->selectedProduct = $product;
        $this->isCreatingNew = false;
    }

    public function addLine()
    {
        if (!$this->selectedProduct) return;

        $this->validate([
            'amount' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0.01',
        ]);

        $operationType = \App\Models\OperationType::where('direction', 'income')->first();

        Transaction::create([
            'warehouse_id' => $this->warehouse_id ?? 1,
            'product_id' => $this->selectedProduct->id,
            'operation_type_id' => $operationType->id,
            'user_id' => 1,
            'customer_id' => $this->customer_id ?? 1,
            'doc_id' => $this->docId,
            'amount' => $this->amount,
            'purchase_price' => (int) round($this->purchase_price * 100),
            'sale_price' => 0,
            'sign' => 1,
            'is_confirmed' => false,
        ]);

        $this->showProductModal = false;
        $this->reset(['selectedProduct', 'amount', 'purchase_price', 'search', 'foundProducts']);
        $this->doc->refresh();
    }

    public function confirmDoc()
    {
        // 1. Проверяем, что заполнена шапка
        if (!$this->warehouse_id || !$this->customer_id) {
            $this->addError('general', 'Заполните склад и поставщика перед проведением.');
            return;
        }

        // 2. Проверяем, что есть товары
        if ($this->doc->transactions->count() === 0) {
            $this->addError('general', 'Добавьте хотя бы один товар в накладную.');
            return;
        }

        // 3. Меняем статус документа
        $this->doc->update([
            'is_confirmed' => true,
            'confirmed_at' => now()
        ]);

        // 4. Меняем статус всех строк документа
        Transaction::where('doc_id', $this->docId)->update([
            'is_confirmed' => true,
            'confirmed_at' => now()
        ]);

        // 5. Закрываем таб
        $this->dispatch('close-tab', id: 'doc_' . $this->docId);
    }

    #[Computed]
    public function brandSuggestions()
    {
        if (strlen($this->new_brand_search) < 2) {
            return collect();
        }
        return \App\Models\PartBrand::where('name', 'like', strtoupper($this->new_brand_search) . '%')
            ->limit(5)
            ->get();
    }

    public function selectBrand($id)
    {
        $brand = \App\Models\PartBrand::find($id);
        $this->new_part_brand_id = $brand->id;
        $this->new_brand_search = $brand->name;
    }

    public function createBrand()
    {
        $name = strtoupper(trim($this->new_brand_search));
        if (empty($name)) return;

        if (!preg_match('/^[A-Z0-9\s\-]+$/', $name)) {
            $this->addError('new_brand_search', 'Бренд может содержать только заглавные латинские буквы, цифры, пробелы и дефисы.');
            return;
        }

        $exists = \App\Models\PartBrand::where('name', $name)->first();
        if ($exists) {
            $this->selectBrand($exists->id);
            return;
        }

        $brand = \App\Models\PartBrand::create(['name' => $name]);
        $this->selectBrand($brand->id);
    }
};
?>

<div class="w-full pb-12">
        <!-- Заголовок и шапка (фиксированные) -->

            <h2 class="text-xl font-bold text-gray-800 mb-4">
                Приходная накладная № {{ $doc->number }}
            </h2>

            @if(session()->has('message'))
                <div class="bg-green-100 text-green-800 p-2 rounded mb-4 text-sm">
                    {{ session('message') }}
                </div>
            @endif

            <div class="border border-gray-300 rounded p-4 bg-gray-50 ">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-bold text-gray-700">Шапка документа</h3>
                    <button wire:click="saveHeader" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 px-3 py-1.5 rounded shadow-sm text-sm font-medium">
                        Сохранить шапку
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block text-gray-700 mb-1">Склад</label>
                        <select wire:model="warehouse_id" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Выберите склад</option>
                            @foreach(Warehouse::all() as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1">Поставщик</label>
                        <select wire:model="customer_id" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Выберите контрагента</option>
                            @foreach(Customer::all() as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-span-2">
                        <label class="block text-gray-700 mb-1">Примечание</label>
                        <input type="text" wire:model="notes" class="w-full border border-gray-300 rounded px-3 py-2">
                    </div>
                </div>
            </div>


        <!-- Список товаров (скроллится) -->
        <div class="flex-1 overflow-y-auto pt-8 pb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold text-gray-800">Список товаров</h3>
                <button wire:click="openProductModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">
                    Добавить
                </button>
            </div>

            <div class="bg-white border border-gray-200 mb-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">№</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Каталог</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Артикул</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Кол-во</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Цена</th>
                        <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">Сумма</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                    @forelse($doc->transactions as $index => $t)
                        <tr>
                            <td class="px-2 py-2">{{ $index + 1 }}</td>
                            <td class="px-2 py-2">{{ $t->product->partBrand->name ?? '—' }}</td>
                            <td class="px-2 py-2">{{ $t->product->article ?? '—' }}</td>
                            <td class="px-2 py-2">{{ $t->product->name ?? '—' }}</td>
                            <td class="px-2 py-2">{{ $t->amount }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format($t->purchase_price / 100, 2, ',', ' ') }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format(($t->amount * $t->purchase_price) / 100, 2, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-2 py-4 text-center text-gray-500">Товаров пока нет.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Итоги и кнопка проведения (фиксированные) -->
        <div class="p-5 border border-gray-200 shrink-0 bg-white pb-10">
            <div class="flex justify-between items-end">
                <div class="bg-gray-50 border border-gray-200 rounded px-4 py-2 flex space-x-6 text-sm">
                    <div>
                        <span class="text-gray-500">Всего позиций:</span>
                        <span class="font-bold text-gray-800">{{ $doc->transactions->count() }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Сумма:</span>
                        <span class="font-bold text-gray-800">
                            {{ number_format($doc->transactions->sum(fn($t) => $t->amount * $t->purchase_price) / 100, 2, ',', ' ') }}
                        </span>
                    </div>
                </div>

                <button wire:click="confirmDoc" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow-sm font-medium">
                    Провести накладную
                </button>
            </div>
            @if($errors->has('general'))
                <div class="text-red-500 text-sm mt-2 text-right">{{ $errors->first('general') }}</div>
            @endif
        </div>


    <!-- МОДАЛКА ДОБАВЛЕНИЯ ТОВАРА -->
    @if($showProductModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Добавление товара</h3>
                    <button wire:click="$set('showProductModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-5 text-sm">
                    @if(!$selectedProduct && !$isCreatingNew)
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2 font-medium">Поиск товара (артикул, OEM или название)</label>
                            <div class="flex gap-2">
                                <input type="text" wire:model="search" wire:keydown.enter="searchProduct" class="flex-1 border border-gray-300 rounded px-3 py-2" placeholder="Например, P527 или 11517545201">
                                <button wire:click="searchProduct" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Найти</button>
                            </div>
                        </div>

                        @if(count($foundProducts) > 0)
                            <div class="border rounded overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($foundProducts as $p)
                                        <tr class="hover:bg-gray-50 cursor-pointer" wire:click="selectProduct({{ $p->id }})">
                                            <td class="px-3 py-2 font-medium">{{ $p->partBrand->name ?? '—' }} {{ $p->article }}</td>
                                            <td class="px-3 py-2 text-gray-500">{{ $p->name }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif(strlen($search) >= 2)
                            <div class="text-center py-4 text-gray-500">
                                Ничего не найдено.
                                <button wire:click="createNewProduct" class="text-blue-600 hover:underline ml-1">Создать новый товар</button>
                            </div>
                        @endif

                    @elseif($isCreatingNew)
                        <h4 class="font-bold text-gray-800 mb-3">Новый товар</h4>
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="relative col-span-2">
                                <label class="block text-gray-700 mb-1">Каталог (Бренд)</label>
                                <input type="text" wire:model.live.debounce.300ms="new_brand_search"
                                       class="w-full border border-gray-300 rounded px-3 py-2 uppercase"
                                       placeholder="Начните вводить бренд (например, LEM)">

                                @error('new_brand_search') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror

                                @if(strlen($new_brand_search) >= 2 && !$new_part_brand_id)
                                    <div class="absolute z-10 w-full bg-white border border-gray-300 rounded mt-1 shadow-lg max-h-40 overflow-y-auto">
                                        @forelse($this->brandSuggestions as $brand)
                                            <div wire:click="selectBrand({{ $brand->id }})" class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                                                {{ $brand->name }}
                                            </div>
                                        @empty
                                            <div class="px-3 py-2 text-gray-500 text-xs">
                                                Не найдено.
                                                <button wire:click="createBrand" class="text-blue-600 hover:underline">Создать "{{ strtoupper($new_brand_search) }}"</button>
                                            </div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>

                            <div><label class="block text-gray-700 mb-1">Артикул</label><input type="text" wire:model="new_article" class="w-full border border-gray-300 rounded px-3 py-2"></div>
                            <div class="col-span-2"><label class="block text-gray-700 mb-1">Название</label><input type="text" wire:model="new_name" class="w-full border border-gray-300 rounded px-3 py-2"></div>
                            <div class="col-span-2"><label class="block text-gray-700 mb-1">OEM-номер</label><input type="text" wire:model="new_oem" class="w-full border border-gray-300 rounded px-3 py-2"></div>
                        </div>
                        <button wire:click="saveNewProduct" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-4">Создать и продолжить</button>

                    @else
                        <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-4">
                            <div class="font-bold text-blue-800">{{ $selectedProduct->partBrand->name ?? '—' }} {{ $selectedProduct->article }}</div>
                            <div class="text-blue-600">{{ $selectedProduct->name }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 mb-1">Количество</label>
                                <input type="number" wire:model="amount" class="w-full border border-gray-300 rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1">Цена закупки (руб.)</label>
                                <input type="number" step="0.01" wire:model="purchase_price" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="0.00">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="$set('showProductModal', false)" class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Отмена</button>
                    @if($selectedProduct)
                        <button wire:click="addLine" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 shadow-sm">Добавить в накладную</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="h-12"></div>

</div>
