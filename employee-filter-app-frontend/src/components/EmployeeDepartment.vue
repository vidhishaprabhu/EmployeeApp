<script setup>
import {
  ref,
  onMounted
} from "vue";
import axios from "axios";

const employees = ref([]);
const departments = ref([]);
const pagination = ref({});
const errors = ref({});

const employeeData = ref({
  emp_name: "",
  emp_email: "",
  salary: "",
  department_id: ""
});
const departmentData = ref({
  department_name: ""
});
const limit = ref(5);
const currentPage = ref(1);
const totalPages = ref(1);

const fetchEmployees = async () => {
  const response = await axios.get(`http://localhost:8000/api/employees?limit=${Number(limit.value)}&page=${currentPage.value}`);
    employees.value = response.data.data; 
    totalPages.value = response.data.last_page; 
}
const fetchDepartments = async () => {
  const response = await axios.get("http://localhost:8000/api/departments");
  departments.value = response.data;
}
const addEmployees = async () => {
  try {
    await axios.post("http://localhost:8000/api/employees", employeeData.value);
    
    employeeData.value = {
      emp_name: "",
      emp_email: "",
      salary: "",
      department_id: ""
    };
    errors.value = {};

    await fetchEmployees(); 
  }  catch (error) {
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error("Error adding employee:", error.response ? error.response.data : error.message);
    }
  }
};

const addDepartments = async () => {
  await axios.post("http://localhost:8000/api/departments", departmentData.value);
  departmentData.value = {
    department_name: ""
  };
  fetchDepartments();
}
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    fetchEmployees();
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    fetchEmployees();
  }
};
const getDepartmentName = (deptId) => {
  const dept = departments.value.find(d => d.id === deptId);
  return dept ? dept.department_name : "No department-name found";
};


onMounted(() => {
  fetchEmployees();
  fetchDepartments();
});
</script>
<template>
<div class="container mt-4">
  <h2 class="text-center mb-4">Employee Management System</h2>
  <div class="row">
    <div class="col-md-6">
      <div class="card-shadow">
        <div class="card-header bg-primary text-white">Add Employees</div>
        <form @submit.prevent="addEmployees">
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label">Employee Name</label>
              <input v-model="employeeData.emp_name" class="form-control" placeholder="Enter Employee Name">
              <div v-if="errors.emp_name" class="text-danger">{{ errors.emp_name[0] }}</div> 
            </div>
            <div class="mb-3">
              <label class="form-label">Employee Email</label>
              <input v-model="employeeData.emp_email" type="email" class="form-control" placeholder="Enter Employee Email">
              <div v-if="errors.emp_email" class="text-danger">{{ errors.emp_email[0] }}</div> 
            </div>
            <div class="mb-3">
              <label class="form-label">Employee Salary</label>
              <input v-model="employeeData.salary" class="form-control" placeholder="Enter Employee Salary">
              <div v-if="errors.salary" class="text-danger">{{ errors.salary[0] }}</div> 
            </div>
            <div class="mb-3">
              <label class="form-label">Employee Department Id</label>
              <input v-model="employeeData.department_id" class="form-control" placeholder="Enter Department Id">
              <div v-if="errors.department_id" class="text-danger">{{ errors.department_id[0] }}</div> 
            </div>
            <div class="mb-3">
              <label class="form-label">Department</label>
              <select v-model="employeeData.department_id" class="form-select">
                <option value="">Select department</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{dept.department_name}}</option>

              </select>
            </div>
            <button class="btn btn-primary w-100">Add Employee</button>
        
          </div>
        </form>
      </div>
    </div>
    <div class="mt-4">
      <div class="car-shadow">
        <div class="card-header bg-warning text-dark">Filter & Employees List</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Show Records</label>
            <select v-model="limit" @change="fetchEmployees">
              <option v-for="num in [5,10,20,50]" :key="num" :value="num">{{num}}</option>
            </select>
          </div>
          <table class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>Emp Name</th>
                <th>Emp Email</th>
                <th>Emp Salary</th>
                <th>Department Name</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in employees" :key="employee.id">
                <td>{{employee.emp_name}}</td>
                <td>{{employee.emp_email}}</td>
                <td>{{employee.salary}}</td>
                <td>{{ getDepartmentName(employee.department_id) }}</td>
              </tr>
            </tbody>
          </table>
          <div class="pagination">
              <button class="btn btn-secondary" @click="prevPage" :disabled="currentPage === 1">Previous</button>
              <span class="mx-3">Page {{ currentPage }} of {{ totalPages }}</span>
              <button class="btn btn-secondary" @click="nextPage" :disabled="currentPage === totalPages">Next</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<style scoped>
.container {
  max-width: 900px;
}
</style>
