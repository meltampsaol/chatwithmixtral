require('./bootstrap');
import Vue from 'vue';
import ChatBot from './components/ChatBot.vue';

const app = new Vue({
    el: '#app',
    components: {
        ChatBot
    }
});

//const app = createApp({});


app.mount('#app');
