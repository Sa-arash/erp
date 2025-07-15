self.addEventListener('message', event => {
    if (event.data.type === 'SHOW_NOTIFICATION') {
        event.waitUntil(
            self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clients => {
                const isChatOpen = clients.some(client => client.url.includes('chat') && 'focus' in client);
                if (!isChatOpen) {
                    return self.registration.showNotification(event.data.title, event.data.options)
                        .then(() => {
                            clients.forEach(client => {
                                client.postMessage({ sound: 'PLAY_NOTIFICATION_SOUND' });
                            });
                        })
                        .catch(error => {
                            console.error('Wirechat Show Notification failed:', error);
                        });
                }
            })
        );
    }
    if (event.data.type === 'CLOSE_NOTIFICATION') {
        event.waitUntil(
            self.registration.getNotifications({ tag: event.data.tag })
                .then(notifications => {
                    notifications.forEach(notification => notification.close());
                })
                .catch(error => {
                    console.error('Wirechat Close notifications failed:', error);
                })
        );
    }
});
