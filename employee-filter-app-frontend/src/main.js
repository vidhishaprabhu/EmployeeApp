import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'; 
import "bootstrap/dist/css/bootstrap.min.css";
// createApp(App).app.use(router).mount('#app');
const app = createApp(App);
app.use(router);
app.mount('#app');
