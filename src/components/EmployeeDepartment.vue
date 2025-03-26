<template>
  <div class="container mt-4">
    <h2 class="text-center mb-4">Employee Management System</h2>

    <div class="text-center mb-3">
      <button class="btn btn-success mx-2" @click="handleCreateClick">Create</button>
      <button class="btn btn-info mx-2" @click="showForm = false">List</button>
    </div>
    <div v-if="showForm">
      <div class="col-md-6 mx-auto">
        <div class="card shadow">
          <div class="card-header bg-primary text-white">
            {{ editingEmployeeId ? "Edit Employee" : "Add Employee" }}
          </div>
          <form @submit.prevent="editingEmployeeId ? updateEmployee() : addEmployee()">
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
                <label class="form-label">Department</label>
                <select v-model="employeeData.department_id" class="form-select">
                  <option value="">Select department</option>
                  <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.department_name }}</option>
                </select>
                <div v-if="errors.department_id" class="text-danger">{{ errors.department_id[0] }}</div>
              </div>

              <button class="btn btn-primary w-100">
                {{ editingEmployeeId ? "Update Employee" : "Add Employee" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div v-else>
      <div class="card shadow">
        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
          <span>Filter & Employees List</span>
          <input v-model="searchQuery" type="text" class="form-control w-25" placeholder="Search by Name, Email, or Salary">

        </div>

        <div class="card-body">
          <div class="mb-3 d-flex align-items-center">
            <label class="me-2">Show Records:</label>
            <select v-model="recordsPerPage" class="form-select w-auto" @change="fetchEmployees">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="15">15</option>
              <option value="100">100</option>
            </select>
          </div>

          <div v-if="loading" class="text-center my-3">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>

          <table v-else class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>Emp Name</th>
                <th>Emp Email</th>
                <th>Emp Salary</th>
                <th>Department</th>
                <th>Edit</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="employee in filteredEmployees" :key="employee.id">
                <td>{{ employee.emp_name }}</td>
                <td>{{ employee.emp_email }}</td>
                <td>{{ employee.salary }}</td>
                <td>{{ getDepartmentName(employee.department_id) }}</td>
                <td>
                  <button class="btn btn-sm btn-warning" @click="editEmployee(employee)">Edit</button>
                </td>
                <td>
                  <button class="btn btn-sm btn-danger" @click="deleteEmployee(employee.id)">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="card-footer text-center">
          <button class="btn btn-secondary" @click="prevPage" :disabled="currentPage === 1">Prev</button>
          <span class="mx-3">Page {{ currentPage }} of {{ totalPages }}</span>
          <button class="btn btn-secondary" @click="nextPage" :disabled="currentPage >= totalPages">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import { debounce } from "lodash";

const employees = ref([]);
const departments = ref([]);
const errors = ref({});
const searchQuery = ref("");
const editingEmployeeId = ref(null);
const recordsPerPage = ref(5);
const loading = ref(false);

const employeeData = ref({
  emp_name: "",
  emp_email: "",
  salary: "",
  department_id: ""
});

const showForm = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

const fetchEmployees = debounce(async () => {
  loading.value = true;
  try {
    const response = await axios.get("http://localhost:8000/api/employees", {
      params: {
        page: currentPage.value,
        limit: recordsPerPage.value,
        search: searchQuery.value, 
      },
    });
    employees.value = response.data.data;
    totalPages.value = response.data.last_page;
  } catch (error) {
    console.error("Error fetching employees:", error);
  } finally {
    loading.value = false;
  }
}, 300);
watch(searchQuery, debounce(() => {
  fetchEmployees();
}, 300)); 

const fetchDepartments = async () => {
  const response = await axios.get("http://localhost:8000/api/departments");
  departments.value = response.data;
};

const filteredEmployees = computed(() => {
  return employees.value.filter(employee =>
    employee.emp_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    employee.salary.toString().includes(searchQuery.value)
  );
});

const addEmployee = async () => {
  try {
    await axios.post("http://localhost:8000/api/employees", employeeData.value);
    resetForm();
    fetchEmployees();
  } catch (error) {
    handleValidationError(error);
  }
};

const editEmployee = (employee) => {
  editingEmployeeId.value = employee.id;
  employeeData.value = { ...employee };
  showForm.value = true;
};

const updateEmployee = async () => {
  if (!editingEmployeeId.value) return;

  loading.value = true;

  try {
    await axios.put(`http://localhost:8000/api/employees/${editingEmployeeId.value}`, employeeData.value);
    resetForm();
    await fetchEmployees();
  } catch (error) {
    handleValidationError(error);
  } finally {
    loading.value = false;
  }
};

const deleteEmployee = async (id) => {
  if (!confirm("Are you sure you want to delete this employee?")) return;

  loading.value = true;
  try {
    await axios.delete(`http://localhost:8000/api/employees/${id}`);
    fetchEmployees();
  } catch (error) {
    console.error("Error deleting employee:", error);
  } finally {
    loading.value = false;
  }
};

const resetForm = () => {
  employeeData.value = { emp_name: "", emp_email: "", salary: "", department_id: "" };
  editingEmployeeId.value = null;
  errors.value = {};
  showForm.value = false;
};

const getDepartmentName = (deptId) => {
  const dept = departments.value.find(d => d.id === deptId);
  return dept ? dept.department_name : "No department found";
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    fetchEmployees();
  }
};
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    fetchEmployees();
  }
};

watch(searchQuery, debounce(() => {
  fetchEmployees();
}, 300)); 

onMounted(() => {
  fetchEmployees();
  fetchDepartments();
});
</script>
