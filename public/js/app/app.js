navigator.serviceWorker.addEventListener('message', event => {

    if (event.data && event.data.sound === 'PLAY_NOTIFICATION_SOUND') {
        const audio = new Audio('/sounds/notification.mp3');
        audio.play().catch(err => {
            console.warn('Could not play notification sound:', err);
        });
        new FilamentNotification()
            .title('New Message')
            .success()
            .send()

    }

});

window.addEventListener('load', function () {
    if ('Notification' in window && Notification.permission === "default") {
        Notification.requestPermission().then(function(permission) {
            if (permission === "granted") {

                // می‌تونی test نوتیفیکیشن بزنی:
                new Notification("به سایت خوش آمدید!", {
                    body: "نوتیفیکیشن با موفقیت فعال شد 🎉",

                });
            } else {
                console.log("❌ کاربر نوتیفیکیشن رو نپذیرفت");
            }
        });
    }
});



