<?php

use App\Models\Vehicle;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $cars = [];

    // Свойства формы
    public $showModal = false;
    public $editingId = null;
    public $isVinLocked = false;

    public $selected_brand = '';
    public $selected_model = '';
    public $year = '';
    public $color = '';
    public $vin = '';

    public function mount()
    {
        $this->loadCars();
    }

    public function loadCars()
    {
        $this->cars = Vehicle::orderBy('id', 'desc')->get();
    }

    #[Computed]
    public function brands()
    {
        return DB::connection('central')->table('brands')->orderBy('name')->get();
    }

    #[Computed]
    public function models()
    {
        if (!$this->selected_brand) {
            return collect();
        }
        return DB::connection('central')->table('models')
            ->where('brand_id', $this->selected_brand)
            ->orderBy('name')
            ->get();
    }

    public function updatedSelectedBrand()
    {
        $this->selected_model = '';
    }

    public function openCar($id)
    {
        $this->dispatch('openTab', data: [
            'id' => 'car_' . $id,
            'title' => 'Автомобиль #' . $id,
            'component' => 'car-card',
            'params' => ['carId' => $id]
        ]);
    }

    public function editCar($id)
    {
        $car = Vehicle::findOrFail($id);
        $this->editingId = $id;
        $this->selected_model = $car->model_id;
        $this->year = $car->year;
        $this->color = $car->color;
        $this->vin = $car->vin;

        // Проверяем, есть ли у машины заказы (если да, VIN менять нельзя)
        $this->isVinLocked = $car->orders()->exists();

        // Подтягиваем марку для селекта
        $model = DB::connection('central')->table('models')->where('id', $car->model_id)->first();
        $this->selected_brand = $model->brand_id ?? '';

        $this->showModal = true;
    }

    public function deleteCar($id)
    {
        Vehicle::find($id)?->delete();
        $this->loadCars();
    }

    public function closeModal()
    {
        $this->reset(['showModal', 'editingId', 'isVinLocked', 'selected_brand', 'selected_model', 'year', 'vin', 'color']);
    }

    public function saveCar()
    {
        $rules = [
            'selected_model' => 'required',
        ];

        // Если VIN не заблокирован, проверяем его уникальность и формат
        if (!$this->isVinLocked) {
            $rules['vin'] = 'required|regex:/^[A-HJ-NPR-Z0-9]{17}$/i|unique:vehicles,vin,' . $this->editingId;
        }

        $this->validate($rules, [
            'vin.regex' => 'VIN должен состоять ровно из 17 символов (только латинские буквы и цифры).',
            'vin.unique' => 'Автомобиль с таким VIN уже зарегистрирован в системе.',
        ]);

        if ($this->editingId) {
            $car = Vehicle::findOrFail($this->editingId);
            $car->update([
                'model_id' => $this->selected_model,
                'year' => $this->year,
                'vin' => $this->isVinLocked ? $car->vin : $this->vin,
                'color' => $this->color,
            ]);
        } else {
            Vehicle::create([
                'model_id' => $this->selected_model,
                'year' => $this->year,
                'vin' => $this->vin,
                'color' => $this->color,
            ]);
        }

        $this->closeModal();
        $this->loadCars();
    }
};
?>

<div>
    <!-- Заголовок и кнопка -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">Справочник автомобилей</h2>
        <button wire:click="$set('showModal', true)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow-sm text-sm font-medium">
            + Добавить авто
        </button>
    </div>

    <!-- Модальное окно -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $editingId ? 'Редактировать автомобиль' : 'Новый автомобиль' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-5 space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-1">Марка</label>
                            <select wire:model.live="selected_brand" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">Выберите марку</option>
                                @foreach($this->brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Модель</label>
                            <select wire:model="selected_model" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" {{ !$selected_brand ? 'disabled' : '' }}>
                                <option value="">Выберите модель</option>
                                @foreach($this->models as $model)
                                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                                @endforeach
                            </select>
                            @error('selected_model') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Год выпуска</label>
                            <input type="number" wire:model="year" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Цвет</label>
                            <input type="text" wire:model="color" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-gray-700 mb-1">VIN (17 символов)</label>
                            <input type="text" wire:model="vin" maxlength="17" oninput="this.value = this.value.toUpperCase()"
                                   class="w-full border border-gray-300 rounded px-3 py-2 font-mono uppercase focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                          {{ $isVinLocked ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}"
                                {{ $isVinLocked ? 'disabled' : '' }}>
                            @error('vin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            @if($isVinLocked)
                                <span class="text-gray-500 text-xs mt-1 block">VIN нельзя изменить, так как по машине уже есть заказ-наряды.</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="px-5 py-3 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="closeModal" class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Отмена</button>
                    <button wire:click="saveCar" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow-sm">Сохранить</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Таблица -->
    <div class="bg-white rounded shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">VIN</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Марка / Модель</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Год</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Цвет</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Владелец</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Госномер</th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Действия</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
            @forelse($cars as $car)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 whitespace-nowrap">
                        <a href="#" wire:click.prevent="openCar({{ $car->id }})" class="text-blue-600 font-mono font-medium hover:underline">
                            {{ $car->vin ?? '—' }}
                        </a>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                        {{ $car->display_name }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                        {{ $car->year ?? '—' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                        {{ $car->color ?? '—' }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-400">
                        —
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-gray-400">
                        —
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap text-right">
                        <button wire:click="editCar({{ $car->id }})" class="text-gray-500 hover:text-blue-600 mr-3" title="Редактировать">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>
                        <button wire:click="deleteCar({{ $car->id }})" wire:confirm="Вы уверены, что хотите удалить этот автомобиль?" class="text-gray-500 hover:text-red-600" title="Удалить">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-3 py-4 text-center text-gray-500">
                        В базе пока нет автомобилей.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
