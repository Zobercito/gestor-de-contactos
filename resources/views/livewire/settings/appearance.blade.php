<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="flex flex-col items-start">
    @include('partials.settings-heading')

    <x-settings.layout heading="Apariencia" subheading="Elige cómo se ve Gestor de contactos en este navegador">
        <div class="ct-card p-6">
            <p class="ct-label mb-3">Tema</p>
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                <flux:radio value="light" icon="sun">Claro</flux:radio>
                <flux:radio value="dark" icon="moon">Oscuro</flux:radio>
                <flux:radio value="system" icon="computer-desktop">Sistema</flux:radio>
            </flux:radio.group>
            <p class="mt-4 text-sm text-ink-400">
                La preferencia se guarda en este navegador y no afecta a otras personas del equipo.
            </p>
        </div>
    </x-settings.layout>
</div>
