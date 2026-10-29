<?php

use App\Models\Warehouse;
use Livewire\Component;

new class extends Component
{
    public $warehouses = [];

    // Свойства формы
    public $showModal = false;
    public $editingId = null;
    public $name = '';
    public $can_receive = true;
    public $can_issue = true;

    public function mount()
    {
        $this->loadWarehouses();
    }

    public function loadWarehouses()
    {
        $this->warehouses = Warehouse::orderBy('id', 'desc')->get();
    }

    public function create()
    {
        $this->reset(['editingId', 'name', 'can_receive', 'can_issue']);
        $this->can_receive = true;
        $this->can_issue = true;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $wh = Warehouse::findOrFail($id);
        $this->editingId = $id;
        $this->name = $wh->name;
        $this->can_receive = $wh->can_receive;
        $this->can_issue = $wh->can_issue;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:2',
        ]);

        if ($this->editingId) {
            Warehouse::find($this->editingId)->update([
                'name' => $this->name,
                'can_receive' => $this->can_receive,
                'can_issue' => $this->can_issue,
            ]);
        } else {
            Warehouse::create([
                'name' => $this->name,
                'can_receive' => $this->can_receive,
                'can_issue' => $this->can_issue,
            ]);
        }

        $this->reset(['showModal', 'editingId', 'name']);
        $this->loadWarehouses();
    }

    public function delete($id)
    {
        Warehouse::find($id)?->delete();
        $this->loadWarehouses();
    }
};
?>

<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">Склады</h2>
        <button wire:click="create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">
            + Добавить склад
        </button>
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $editingId ? 'Редактировать склад' : 'Новый склад' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-sm">
                    <div>
                        <label class="block text-gray-700 mb-1">Название склада</label>
                        <input type="text" wire:model="name" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" wire:model="can_receive" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="text-gray-700">Разрешить приход товара</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" wire:model="can_issue" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="text-gray-700">Разрешить продажу / выдачу</span>
                        </label>
                    </div>
                </div>

                <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Отмена</button>
                    <button wire:click="save" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow-sm">Сохранить</button>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Приход</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Продажа</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
            @forelse($warehouses as $wh)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 whitespace-nowrap text-gray-900 font-medium">
                        {{ $wh->name }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-center">
                        @if($wh->can_receive)
                            <span class="text-green-600">✔</span>
                        @else
                            <span class="text-red-400">✖</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-center">
                        @if($wh->can_issue)
                            <span class="text-green-600">✔</span>
                        @else
                            <span class="text-red-400">✖</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-right">
                        <button wire:click="edit({{ $wh->id }})" class="text-gray-500 hover:text-blue-600 mr-3" title="Редактировать">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button wire:click="delete({{ $wh->id }})" wire:confirm="Удалить склад?" class="text-gray-500 hover:text-red-600" title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">Складов пока нет.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
