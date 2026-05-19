// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here. Other Firebase libraries
// are not available in the service worker.
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');
// Initialize the Firebase app in the service worker by passing in
// your app's Firebase config object.
// https://firebase.google.com/docs/web/setup#config-object
<?php $config = getConfgPWA(); ?>
firebase.initializeApp({
    apiKey: "<?php echo $config['apiKey'];?>",
    authDomain: "<?php echo $config['authDomain'];?>",
    projectId: "<?php echo $config['projectId'];?>",
    storageBucket: "<?php echo $config['storageBucket'];?>",
    messagingSenderId: "<?php echo $config['messagingSenderId'];?>",
    appId: "<?php echo $config['appId'];?>",
    measurementId: "<?php echo $config['measurementId'];?>"
});

self.addEventListener('notificationclick', function(event) {
    console.log('On notification click: ', event.notification);
    //console.log('On notification click: ', event.notification.data.FCM_MSG.data.url);
    var url = '';
    if (typeof event.notification.data.FCM_MSG !== 'undefined') {
        url = event.notification.data.FCM_MSG.data.url
    } else {
        url = event.notification.data.url
    }
    
    // Android doesn't close the notification when you click on it
    // See: http://crbug.com/463146
    event.notification.close();

    // This looks to see if the current is already open and
    // focuses if it is
    event.waitUntil(
    clients.matchAll({
        type: "window"
    })
    .then(function(clientList) {
        for (var i = 0; i < clientList.length; i++) {
        var client = clientList[i];
        if ('focus' in client)
            client.navigate(url)
            return client.focus();
        }
        if (clients.openWindow) {
          return clients.openWindow(url);
        }
    })
    );
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
});