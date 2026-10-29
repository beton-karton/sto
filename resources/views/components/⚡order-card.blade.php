<?php
new class extends Livewire\Component {
    public $orderId;
    public function mount($orderId) { $this->orderId = $orderId; }
};
?>
<div class="bg-white p-6 rounded shadow-sm border border-gray-200">
    <h2 class="text-xl font-bold">Карточка заказа #{{ $orderId }}</h2>
    <p class="text-gray-500 mt-2">Здесь будет детальная информация по заказу.</p>
</div>
