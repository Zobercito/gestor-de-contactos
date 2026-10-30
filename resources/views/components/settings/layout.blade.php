<div class="flex items-start gap-8 max-md:flex-col">
    <div class="w-full pb-4 md:w-[220px]">
        <div class="ct-card p-2">
            <flux:navlist>
                <flux:navlist.item href="{{ route('settings.profile') }}" icon="user" wire:navigate>Perfil</flux:navlist.item>
                <flux:navlist.item href="{{ route('settings.password') }}" icon="key" wire:navigate>Contraseña</flux:navlist.item>
                <flux:navlist.item href="{{ route('settings.appearance') }}" icon="sun" wire:navigate>Apariencia</flux:navlist.item>
            </flux:navlist>
        </div>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading size="lg">{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-xl">
            {{ $slot }}
        </div>
    </div>
</div>
