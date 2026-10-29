<?php

use App\Models\Doc;
use App\Models\Counter;
use Livewire\Component;

new class extends Component
{
    public $type;
    public $docs = [];

    public function mount($type = 'receipt')
    {
        $this->type = $type;
        $this->loadDocs();
    }

    public function loadDocs()
    {
        $this->docs = Doc::with(['warehouse', 'customer'])
            ->where('type', $this->type)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function createDoc()
    {
        // Логика создания черновика (та, что раньше была в Sidebar)
        $number = \Illuminate\Support\Facades\DB::transaction(function () {
            $counterName = $this->type . '_number';
            $counter = Counter::where('name', $counterName)->lockForUpdate()->first();

            if (!$counter) {
                $counter = Counter::create(['name' => $counterName, 'value' => 0]);
            }

            $counter->increment('value');
            $prefix = $this->type === 'receipt' ? 'ПН' : 'РН';
            return $prefix . '-' . str_pad($counter->value, 6, '0', STR_PAD_LEFT);
        });

        $doc = Doc::create([
            'number' => $number,
            'type' => $this->type,
            'user_id' => 1,
            'is_confirmed' => false,
        ]);

        // Открываем новый таб с формой
        $this->dispatch('openTab', data: [
            'id' => 'doc_' . $doc->id,
            'title' => $number,
            'component' => $this->type === 'receipt' ? 'docs.income-form' : 'docs.sale-form',
            'params' => ['docId' => $doc->id]
        ]);
    }

    public function unconfirmDoc($id)
    {
        $doc = Doc::findOrFail($id);

        // Меняем статус документа
        $doc->update(['is_confirmed' => false, 'confirmed_at' => null]);

        // Меняем статус всех транзакций этого документа
        \App\Models\Transaction::where('doc_id', $id)->update([
            'is_confirmed' => false,
            'confirmed_at' => null
        ]);

        $this->loadDocs(); // Обновляем список
    }

};
?>

<div>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">
            {{ $type === 'receipt' ? 'Поступление товаров' : 'Реализация товаров' }}
        </h2>
        <button wire:click="createDoc" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">
            + Создать накладную
        </button>
    </div>

    <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Номер</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Дата</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Клиент / Поставщик</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Склад</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Оператор</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
            @forelse($docs as $doc)
                <tr class="hover:bg-gray-50 cursor-pointer" wire:click="$dispatch('openTab', { data: { id: 'doc_{{ $doc->id }}', title: '{{ $doc->number }}', component: 'docs.income-form', params: { docId: {{ $doc->id }} } } })">
                    <td class="px-3 py-2 whitespace-nowrap text-blue-600 font-medium">
                        {{ $doc->number }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                        {{ $doc->created_at->format('d.m.Y H:i') }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                        {{ $doc->customer->name ?? '—' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                        {{ $doc->warehouse->name ?? '—' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                        — <!-- Сюда потом придет имя пользователя, пока заглушка -->
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        @if($doc->is_confirmed)
                            <span class="text-green-600">Проведен</span>
                        @else
                            <span class="text-gray-500">Черновик</span>
                        @endif
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-right">
                        @if($doc->is_confirmed)
                            <!-- Кнопка отмены проводки -->
                            <button wire:click="unconfirmDoc({{ $doc->id }})" wire:confirm="Отменить проводку документа? Товары вернутся на склад как черновик." class="text-gray-500 hover:text-yellow-600 mr-3" title="Отменить проводку">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                            </button>
                        @endif

                        <!-- Кнопка удаления -->
                        <button wire:click="deleteDoc({{ $doc->id }})" wire:confirm="Удалить документ?" class="text-gray-400 hover:text-red-600" title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">Накладных пока нет.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
