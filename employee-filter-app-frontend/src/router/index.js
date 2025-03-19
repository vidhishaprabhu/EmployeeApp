import { createRouter, createWebHistory } from 'vue-router';
import Home from '@/components/Home.vue'; 
import EmployeeDepartment from '@/components/EmployeeDepartment.vue'; 

const routes = [
  { path: '/', component: Home },
  { path: '/add-employee', component: EmployeeDepartment }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
