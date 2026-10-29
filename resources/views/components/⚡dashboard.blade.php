<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;

new class extends Component
{
    #[Session]
    public $tabs = [
        ['id' => 'welcome', 'title' => 'Добро пожаловать', 'component' => null, 'params' => []]
    ];

    #[Session]
    public $activeTab = 'welcome';

    protected $listeners = ['openTab'];

    #[On('openTab')]
    public function openTab($data)
    {
        $id = $data['id'];
        $exists = collect($this->tabs)->firstWhere('id', $id);

        if (!$exists) {
            $this->tabs[] = [
                'id' => $id,
                'title' => $data['title'],
                'component' => $data['component'],
                'params' => $data['params'] ?? []
            ];
        }
        $this->activeTab = $id;
    }

    public function setActiveTab($id)
    {
        $this->activeTab = $id;
    }

    public function closeTab($id)
    {
        if (count($this->tabs) <= 1) return;
        $this->tabs = array_values(array_filter($this->tabs, fn($tab) => $tab['id'] !== $id));
        if ($this->activeTab === $id) {
            $this->activeTab = $this->tabs[0]['id'] ?? null;
        }
    }

    #[On('close-tab')]
    public function handleCloseTab($id)
    {
        $this->closeTab($id);
    }

    public function getActiveTabDataProperty()
    {
        return collect($this->tabs)->firstWhere('id', $this->activeTab);
    }
};
?>

<div class="flex flex-col h-screen">
    <!-- Панель табов (фиксированная) -->
    <div class="flex items-center bg-white border-b border-gray-200 px-4 pt-2 overflow-x-auto min-h-[48px] shrink-0">
        @foreach($tabs as $tab)
            <div
                wire:click="setActiveTab('{{ $tab['id'] }}')"
                class="flex items-center cursor-pointer px-4 py-2 mr-1 rounded-t-lg border-b-2 transition-colors
                    {{ $activeTab === $tab['id'] ? 'bg-gray-50 border-blue-500 text-blue-600' : 'bg-white border-transparent text-gray-500 hover:bg-gray-50' }}"
            >
                <span class="text-sm font-medium">{{ $tab['title'] }}</span>
                @if($tab['id'] !== 'welcome')
                    <button wire:click.stop="closeTab('{{ $tab['id'] }}')" class="ml-3 text-gray-400 hover:text-red-500 focus:outline-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Контент активного таба (скроллится) -->
    <div class="flex-1 overflow-y-auto bg-gray-50 p-6 pb-10">
        @if($this->activeTabData && $this->activeTabData['component'])
            @livewire($this->activeTabData['component'], $this->activeTabData['params'], key($this->activeTab))
        @else
            <div class="flex flex-col items-center justify-center h-full text-center">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Добро пожаловать!</h1>
                <p class="text-gray-500 max-w-md">Выберите нужный раздел в левом меню.</p>
            </div>
        @endif
    </div>
</div>
