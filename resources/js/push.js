/**
 * Subscribe for push notifications.
 */
export function subscribe() {
    navigator.serviceWorker.ready.then(registration => {
        const options = { userVisibleOnly: true };
        const vapidPublicKey = process.env.MIX_VAPID_PUBLIC_KEY;

        if (vapidPublicKey) {
            options.applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);
        }

        registration.pushManager.subscribe(options)
            .then(subscription => {
                updateSubscription(subscription);
            })
            .catch(e => {
                if (Notification.permission === 'denied') {
                    console.log('Permission for Notifications was denied');
                } else {
                    console.log('Unable to subscribe to push.', e);
                }
            });
    });
}

/**
* Unsubscribe from push notifications.
*/
export function unsubscribe() {
    navigator.serviceWorker.ready.then(registration => {
        registration.pushManager.getSubscription()
            .then(subscription => {
                if (!subscription) {
                    return;
                }

                subscription.unsubscribe()
                    .then(() => {
                        deleteSubscription(subscription);
                    })
                    .catch(e => {
                        console.log('Unsubscription error: ', e);
                    });
            })
            .catch(e => {
                console.log('Error thrown while unsubscribing.', e);
            })
    })
}

/**
 * Send a request to the server to update user's subscription.
 *
 * @param {PushSubscription} subscription
 */
export function updateSubscription(subscription) {
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');
    const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
    const data = {
        endpoint: subscription.endpoint,
        publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
        authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
        contentEncoding
    };

    axios.post('/subscriptions', data);
}

/**
 * Send a requst to the server to delete user's subscription.
 *
 * @param {PushSubscription} subscription
 */
export function deleteSubscription(subscription) {
    axios.post('/subscriptions/delete', { endpoint: subscription.endpoint });
}

/**
 * https://github.com/Minishlink/physbook/blob/02a0d5d7ca0d5d2cc6d308a3a9b81244c63b3f14/app/Resources/public/js/app.js#L177
 *
 * @param  {String} base64String
 * @return {Uint8Array}
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}
