importScripts("https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging.js");

firebase.initializeApp({
    apiKey: "c705c2357ea80bce791beb41874acf80003debff",
    authDomain: "your-project-id.firebaseapp.com",
    projectId: "baocaocongviec-60294",
    storageBucket: "your-project-id.appspot.com",
    messagingSenderId: "your-sender-id",
    appId: "your-app-id",
    measurementId: "your-measurement-id",
});

const messaging = firebase.messaging();
