navigator.serviceWorker.addEventListener('message', event => {

    if (event.data && event.data.sound === 'PLAY_NOTIFICATION_SOUND') {
        const audio = new Audio('/sounds/notification.mp3');
        audio.play().catch(err => {
            console.warn('Could not play notification sound:', err);
        });
        // new FilamentNotification()
        //     .title('New Message')
        //     .success()
        //     .send()

    }

});

window.addEventListener('load', function () {
    if ('Notification' in window && Notification.permission === "default") {
        Notification.requestPermission().then(function(permission) {
            if (permission === "granted") {
                new Notification("Welcome", {
                    body: "Welcome🎉",
                });
            } else {
                console.log("❌ Welcome");
            }
        });
    }
});



