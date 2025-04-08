require('./bootstrap');
import { createApp } from 'vue';
import ChatBot from './components/ChatBot.vue';
import HelloVueComponent from './components/HelloVueComponent.vue';

const app = createApp({});
app.component('chat-bot', ChatBot);
app.component('hello-vue-component', HelloVueComponent);
app.mount('#app');

