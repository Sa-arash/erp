<x-filament-panels::page.simple style="padding:0!important;margin:0!important">


    <div style="margin: 0;padding: 0"
         class="w-full overflow-hidden overflow-x-hidden  flex flex-col md:flex-row h-[100dvh]">
        <div class="w-full hidden md:flex items-center justify-center bg-cover bg-center"
             style="background-image: url('{{asset('img/erp.png')}}');background-position: right;background-size: cover;background-repeat: no-repeat;width: 170%">
            <div class="text-white text-left max-w-sm p-6 /50 rounded">

            </div>
        </div>

        <div class="w-full md:w-1/2 p-6  flex flex-col justify-center" style="padding: 5%">
            <div style="text-align: center;font-weight: bold"><h1>AREA TARGET GENERAL TRADING L.L.C</h1></div>
            @if (filament()->hasRegistration())
                <x-slot name="subheading">
                    {{ __('filament-panels::pages/auth/login.actions.register.before') }}
                    {{ $this->registerAction }}
                </x-slot>
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

            <x-filament-panels::form id="form" wire:submit="authenticate">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
        </div>
    </div>

</x-filament-panels::page.simple>
