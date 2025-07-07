import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Clusters/AccountSettings/**/*.php',
        './resources/views/filament/clusters/account-settings/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/namu/wirechat/resources/views/**/*.blade.php',
        './vendor/namu/wirechat/src/Livewire/**/*.php',
        './vendor/awcodes/filament-table-repeater/resources/**/*.blade.php',

    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Roboto'],
            },
        },
    },
}
