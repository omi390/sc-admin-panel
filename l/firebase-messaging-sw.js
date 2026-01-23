importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
firebase.initializeApp({
    apiKey: "AIzaSyATwpBSYz69b5Y9ryQLELOJIHZSpJcXf7I",
    authDomain: "demancms.firebaseapp.com",
    projectId: "demancms",
    storageBucket: "demancms.appspot.com",
    messagingSenderId: "889759666168",
    appId: "1:889759666168:web:ab661cb341d3e47384d00d",
    measurementId: ""
});
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body ? payload.data.body : '',
        icon: payload.data.icon ? payload.data.icon : ''
    });
});