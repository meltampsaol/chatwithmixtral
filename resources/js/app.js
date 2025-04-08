import { createApp } from 'vue';

const HelloVueComponent = {
    template: `<div>{{ message }}</div>`,
    data() {
        return { message: 'Hello Vue!' };
    },
};

const app = createApp({});
app.component('hello-vue-component', HelloVueComponent);
app.mount('#app');
