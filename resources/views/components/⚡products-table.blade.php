<?php

use App\Models\Product;
use App\Models\PartBrand;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    public $products = [];
    public $search = '';

    // Свойства формы
    public $showModal = false;
    public $editingId = null;

    public $part_brand_id = null;
    public $brand_search = '';
    public $article = '';
    public $name = '';
    public $oem_number = '';
    public $sale_price = ''; // В рублях

    public function mount()
    {
        $this->loadProducts();
    }

    public function loadProducts()
    {
        $this->products = Product::query()
            ->leftJoin('part_brands', 'products.part_brand_id', '=', 'part_brands.id')
            ->select('products.*')
            ->where(function($query) {
                $query->where('products.name', 'like', '%' . $this->search . '%')
                    ->orWhere('products.article', 'like', '%' . $this->search . '%')
                    ->orWhere('products.oem_number', 'like', '%' . $this->search . '%');
            })
            ->orderBy('part_brands.name', 'asc') // Сначала по бренду
            ->orderBy('products.article', 'asc') // Потом по артикулу
            ->limit(100)
            ->get();
    }

    public function updatedSearch()
    {
        $this->loadProducts();
    }

    // Автокомплит бренда
    #[Computed]
    public function brandSuggestions()
    {
        if (strlen($this->brand_search) < 2) {
            return collect();
        }
        return PartBrand::where('name', 'like', strtoupper($this->brand_search) . '%')
            ->limit(5)
            ->get();
    }

    public function selectBrand($id)
    {
        $brand = PartBrand::find($id);
        $this->part_brand_id = $brand->id;
        $this->brand_search = $brand->name;
    }

    public function create()
    {
        $this->reset(['editingId', 'part_brand_id', 'brand_search', 'article', 'name', 'oem_number', 'sale_price']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->editingId = $id;
        $this->part_brand_id = $product->part_brand_id;
        $this->brand_search = $product->partBrand->name ?? '';
        $this->article = $product->article;
        $this->name = $product->name;
        $this->oem_number = $product->oem_number;
        $this->sale_price = $product->sale_price / 100; // Переводим копейки в рубли для инпута
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'part_brand_id' => 'required',
            'article' => 'required',
            'name' => 'required',
            'sale_price' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'part_brand_id' => $this->part_brand_id,
            'article' => strtoupper($this->article),
            'name' => $this->name,
            'oem_number' => $this->oem_number,
            'sale_price' => $this->sale_price ? (int) round($this->sale_price * 100) : 0,
        ];

        if ($this->editingId) {
            Product::find($this->editingId)->update($data);
        } else {
            Product::create($data);
        }

        $this->showModal = false;
        $this->loadProducts();
    }

    public function delete($id)
    {
        Product::find($id)?->delete();
        $this->loadProducts();
    }
};
?>

<div class="w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">Справочник товаров</h2>
        <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">
            + Добавить товар
        </button>
    </div>

    <!-- Поиск -->
    <div class="mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Поиск по названию, артикулу или OEM..." class="w-full max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
    </div>

    <!-- Модалка -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $editingId ? 'Редактировать товар' : 'Новый товар' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-sm">
                    <!-- Автокомплит бренда -->
                    <div class="relative">
                        <label class="block text-gray-700 mb-1">Каталог (Бренд)</label>
                        <input type="text" wire:model.live.debounce.300ms="brand_search"
                               class="w-full border border-gray-300 rounded px-3 py-2 uppercase"
                               placeholder="Например, HEPU">
                        @if(strlen($brand_search) >= 2 && !$part_brand_id)
                            <div class="absolute z-10 w-full bg-white border border-gray-300 rounded mt-1 shadow-lg max-h-40 overflow-y-auto">
                                @forelse($this->brandSuggestions as $brand)
                                    <div wire:click="selectBrand({{ $brand->id }})" class="px-3 py-2 hover:bg-gray-100 cursor-pointer">
                                        {{ $brand->name }}
                                    </div>
                                @empty
                                    <div class="px-3 py-2 text-gray-500 text-xs">Не найдено</div>
                                @endforelse
                            </div>
                        @endif
                        @error('part_brand_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 mb-1">Артикул</label>
                            <input type="text" wire:model="article" class="w-full border border-gray-300 rounded px-3 py-2">
                            @error('article') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">OEM-номер</label>
                            <input type="text" wire:model="oem_number" class="w-full border border-gray-300 rounded px-3 py-2">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1">Название</label>
                        <input type="text" wire:model="name" class="w-full border border-gray-300 rounded px-3 py-2">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1">Цена продажи (руб.)</label>
                        <input type="number" step="0.01" wire:model="sale_price" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="0.00">
                    </div>
                </div>

                <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Отмена</button>
                    <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow-sm">Сохранить</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Таблица -->
    <div class="bg-white border border-gray-200 overflow-hidden">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2 border border-gray-300 text-left text-xs font-medium text-gray-500 uppercase">Каталог</th>
                <th class="px-3 py-2 border border-gray-300 text-left text-xs font-medium text-gray-500 uppercase">Артикул</th>
                <th class="px-3 py-2 border border-gray-300 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                <th class="px-3 py-2 border border-gray-300 text-left text-xs font-medium text-gray-500 uppercase">OEM</th>
                <th class="px-3 py-2 border border-gray-300 text-right text-xs font-medium text-gray-500 uppercase">Цена</th>
                <th class="px-3 py-2 border border-gray-300 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
            @forelse($products as $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 border border-gray-300 font-medium">{{ $p->partBrand->name ?? '—' }}</td>
                    <td class="px-3 py-2 border border-gray-300">{{ $p->article }}</td>
                    <td class="px-3 py-2 border border-gray-300">{{ $p->name }}</td>
                    <td class="px-3 py-2 border border-gray-300 text-gray-500 font-mono text-xs">{{ $p->oem_number ?? '—' }}</td>
                    <td class="px-3 py-2 border border-gray-300 text-right">{{ number_format($p->sale_price / 100, 2, ',', ' ') }} ₽</td>
                    <td class="px-3 py-2 border border-gray-300 text-right">
                        <button wire:click="edit({{ $p->id }})" class="text-gray-500 hover:text-blue-600 mr-3" title="Редактировать">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button wire:click="delete({{ $p->id }})" wire:confirm="Удалить товар?" class="text-gray-500 hover:text-red-600" title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-3 py-4 text-center text-gray-500 border border-gray-300">
                        Товаров пока нет.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
