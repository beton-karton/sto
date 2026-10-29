<?php

new class extends Livewire\Component {
    public $carId;

    public function mount($carId) {
        $this->carId = $carId;
    }
};
?>

<div class="bg-white p-4 rounded shadow-sm border border-gray-200">
    <h2 class="text-xl font-bold mb-2">Карточка автомобиля #{{ $carId }}</h2>
    <p class="text-gray-500">Здесь будет детальная информация, VIN и история ремонтов.</p>
</div>
