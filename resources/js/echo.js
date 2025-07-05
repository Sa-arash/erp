import Echo from 'laravel-echo';
import 'pusher-js';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws'],
});

window.Echo.channel('pr.requested')
    .listen('pr.requested', (e) => {
        alert('Event received!');
        const audio = new Audio('/sounds/notification.mp3');
        audio.play();

        window.Livewire.dispatch('pr-requested');
    });
