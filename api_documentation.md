# HRM Payroll System - API Documentation

## Authentication API

### Login
- **POST** `/api/login`
- **Description**: Authenticate user and return API token
- **Request**:
  ```json
  {
    "username": "string",
    "password": "string"
  }
  ```
- **Response**:
  ```json
  {
    "user": {
      "user_id": "integer",
      "employee_id": "string",
      "username": "string",
      "email": "string",
      "roles": ["string"],
      "permissions": ["string"]
    },
    "token": "string",
    "token_type": "Bearer"
  }
  ```

### Logout
- **POST** `/api/logout`
- **Description**: Revoke current API token
- **Authentication**: Required (Bearer token)
- **Response**:
  ```json
  {
    "message": "Successfully logged out"
  }
  ```

### Refresh Token
- **POST** `/api/refresh`
- **Description**: Generate a new API token
- **Authentication**: Required (Bearer token)
- **Response**:
  ```json
  {
    "user": {
      "user_id": "integer",
      "employee_id": "string",
      "username": "string",
      "email": "string",
      "roles": ["string"],
      "permissions": ["string"]
    },
    "token": "string",
    "token_type": "Bearer"
  }
  ```

### Get User Details
- **POST** `/api/me`
- **Description**: Get authenticated user details
- **Authentication**: Required (Bearer token)
- **Response**:
  ```json
  {
    "user": {
      "user_id": "integer",
      "employee_id": "string",
      "username": "string",
      "email": "string",
      "roles": ["string"],
      "permissions": ["string"]
    }
  }
  ```

## Employee Management API

### List Employees
- **GET** `/api/employees`
- **Description**: Get paginated list of employees with filtering options
- **Authentication**: Required (Bearer token)
- **Permissions**: `view_employees`
- **Query Parameters**:
  - `search`: Search employees by name, ID, email, etc.
  - `department`: Filter by department ID
  - `cadre`: Filter by cadre ID
  - `status`: Filter by status (Active, Suspended, etc.)
  - `gender`: Filter by gender
  - `appointment_type_id`: Filter by appointment type ID
  - `state_of_origin`: Filter by state of origin
  - `grade_level_id`: Filter by grade level ID
  - `age_from`: Minimum age
  - `age_to`: Maximum age
  - `appointment_from`: Minimum date of appointment
  - `appointment_to`: Maximum date of appointment
  - `retirement_from`: Minimum retirement date
  - `retirement_to`: Maximum retirement date
  - `sort_by`: Field to sort by
  - `sort_order`: Sort order (asc/desc)
  - `per_page`: Number of items per page (default: 10)
- **Response**:
  ```json
  {
    "data": [
      {
        "employee_id": "string",
        "first_name": "string",
        "surname": "string",
        // ... other employee fields
        "department": { ... },
        "cadre": { ... },
        "gradeLevel": { ... }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 10,
      "total": 50,
      "from": 1,
      "to": 10
    },
    "filters": {
      "departments": [...],
      "cadres": [...],
      // ... other filter options
    }
  }
  ```

### Get Employee
- **GET** `/api/employees/{id}`
- **Description**: Get a specific employee
- **Authentication**: Required (Bearer token)
- **Permissions**: `view_employees`
- **Response**:
  ```json
  {
    "data": {
      "employee_id": "string",
      "first_name": "string",
      // ... full employee data with relations
      "department": { ... },
      "nextOfKin": { ... },
      "biometricData": { ... }
    }
  }
  ```

### Create Employee
- **POST** `/api/employees`
- **Description**: Create a new employee (creates a pending change request)
- **Authentication**: Required (Bearer token)
- **Permissions**: `create_employees`
- **Request**:
  ```json
  {
    "first_name": "string",
    "surname": "string",
    "middle_name": "string",
    "gender": "string",
    "date_of_birth": "date",
    "state_id": "integer",
    "lga_id": "integer",
    "ward_id": "integer",
    "nationality": "string",
    "nin": "string",
    "staff_no": "string",
    "mobile_no": "string",
    "email": "string",
    "pay_point": "string",
    "address": "string",
    "date_of_first_appointment": "date",
    "appointment_type_id": "integer",
    "status": "string",
    "highest_certificate": "string",
    "photo": "file",
    "kin_name": "string",
    "kin_relationship": "string",
    "kin_mobile_no": "string",
    "kin_address": "string",
    "kin_occupation": "string",
    "kin_place_of_work": "string",
    "bank_name": "string",
    "bank_code": "string",
    "account_name": "string",
    "account_no": "string",
    "department_id": "integer",
    // Additional fields based on appointment type
  }
  ```
- **Response**:
  ```json
  {
    "message": "Employee creation request submitted for approval.",
    "pending_change": { ... }
  }
  ```

### Update Employee
- **PUT/PATCH** `/api/employees/{id}`
- **Description**: Update an employee (creates a pending change request)
- **Authentication**: Required (Bearer token)
- **Permissions**: `edit_employees`
- **Request**: Similar fields as create
- **Response**:
  ```json
  {
    "message": "Employee update request submitted for approval.",
    "pending_change": { ... }
  }
  ```

### Delete Employee
- **DELETE** `/api/employees/{id}`
- **Description**: Delete an employee (creates a pending change request)
- **Authentication**: Required (Bearer token)
- **Permissions**: `delete_employees`
- **Response**:
  ```json
  {
    "message": "Employee deletion requested. Changes are pending approval.",
    "pending_change": { ... }
  }
  ```

### Export Employee Data
- **GET** `/api/employees/export/pdf`
- **GET** `/api/employees/export/excel`
- **GET** `/api/employees/export/filtered`
- **GET** `/api/employees/{employeeId}/export`
- **Description**: Export employee data in various formats
- **Authentication**: Required (Bearer token)
- **Permissions**: `view_employees`
- **Response**: JSON response indicating export (file generation in full system)

### Import Employees
- **POST** `/api/employees/import`
- **Description**: Import employees from Excel file
- **Authentication**: Required (Bearer token)
- **Permissions**: `create_employees`
- **Request**:
  ```json
  {
    "import_file": "file (xlsx, xls, csv)"
  }
  ```
- **Response**:
  ```json
  {
    "message": "Employees imported successfully."
  }
  ```

### Get Location Data
- **GET** `/api/employees/lgas-by-state`
- **GET** `/api/employees/wards-by-lga`
- **GET** `/api/employees/ranks-by-grade-level`
- **Description**: Get location-related dropdown data
- **Authentication**: Required (Bearer token)
- **Permissions**: `view_employees`

### Get Salary Data
- **GET** `/api/api/salary-scales`
- **GET** `/api/api/salary-scales/{id}`
- **GET** `/api/api/salary-scales/{id}/grade-levels`
- **GET** `/api/salary-scales/{id}/retirement-info`
- **GET** `/api/api/grade-levels/with-steps`
- **Description**: Get salary-related information
- **Authentication**: Required (Bearer token)
- **Permissions**: `view_employees`

## Error Handling

All API endpoints return appropriate HTTP status codes:

- `200 OK`: Successful request
- `201 Created`: Resource successfully created
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `500 Internal Server Error`: Server error

When errors occur, the API returns:

```json
{
  "message": "Error message",
  "error": "Detailed error message" // optional
}
```

For validation errors:
```json
{
  "message": "Validation errors occurred.",
  "errors": {
    "field_name": ["validation message"]
  }
}
```

## Usage Example

```javascript
// Login
fetch('/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    username: 'your_username',
    password: 'your_password'
  })
})
.then(response => response.json())
.then(data => {
  // Store token for subsequent requests
  const token = data.token;
  
  // Make authenticated request
  fetch('/api/employees', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => console.log(data));
});
```