import preset from './vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './resources/**/*.blade.php',
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/namu/wirechat/resources/views/**/*.blade.php',
        './vendor/namu/wirechat/src/Livewire/**/*.php',

    ],
}
