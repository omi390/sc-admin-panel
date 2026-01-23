importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
firebase.initializeApp({
    apiKey: "AIzaSyDCo97yiLR-FWTjGxFshq8EQ4z_jqDbGhk",
    authDomain: "servicecaart-7f63b.firebaseapp.com",
    projectId: "servicecaart-7f63b",
    storageBucket: "servicecaart-7f63b.firebasestorage.app",
    messagingSenderId: "1076861846682",
    appId: "1:1076861846682:android:48f0365b65ec2c8cdd8b23",
    measurementId: "G-BH6K1Q5SB7"
});
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body ? payload.data.body : '',
        icon: payload.data.icon ? payload.data.icon : ''
    });
});