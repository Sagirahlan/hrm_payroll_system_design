# HRM Payroll System Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [System Architecture](#system-architecture)
3. [Database Schema](#database-schema)
4. [Core Modules](#core-modules)
5. [Payroll Calculation Process](#payroll-calculation-process)
6. [User Interface](#user-interface)
7. [Security and Permissions](#security-and-permissions)
8. [API Endpoints](#api-endpoints)
9. [Reports and Exports](#reports-and-exports)
10. [Audit Trail](#audit-trail)
11. [Configuration and Setup](#configuration-and-setup)
12. [Business Logic Details](#business-logic-details)
13. [Performance Considerations](#performance-considerations)
14. [Part 2: User Manual](#part-2-user-manual)

## Introduction

The HRM Payroll System is a comprehensive solution built with Laravel that manages employee data, payroll processing, deductions, additions, and generates payslips. The system is designed to handle complex payroll calculations, including statutory and non-statutory deductions and additions, with support for suspended employees and bulk operations.

### Key Features
- Employee management with detailed personal information
- Payroll generation with complex calculation logic
- Deduction and addition management (statutory and non-statutory)
- Bulk operations for employee adjustments
- Payroll workflow with review and approval process
- Comprehensive reporting and export functionality
- Audit trail for all system activities
- Responsive UI with dark mode support

### System Overview
The HRM Payroll System is a web-based application designed to automate and streamline human resource and payroll management processes in organizations. The system is built using the Laravel framework, which provides robust features for authentication, authorization, database management, and more.

#### Business Objectives
1. **Automation of Payroll Processing**: Reduce manual work and minimize errors in payroll calculations
2. **Compliance Management**: Ensure adherence to statutory requirements for deductions and additions
3. **Employee Self-Service**: Provide employees with access to their payroll information
4. **Administrative Efficiency**: Streamline HR processes through centralized management
5. **Transparency**: Maintain clear audit trails for all operations
6. **Flexible Configuration**: Accommodate different organizational structures and pay scales

#### Technical Goals
- Scalable architecture to handle growing organizations
- Secure data management with role-based access control
- Responsive design for cross-device compatibility
- Performance optimization for large datasets
- Integration support with external systems

## System Architecture

### Technology Stack
- **Framework**: Laravel 12.x (latest version)
- **Database**: MySQL (presumably)
- **Frontend**: Bootstrap 5, Font Awesome, JavaScript, Alpine.js
- **PDF Generation**: barryvdh/laravel-dompdf
- **Excel Export**: maatwebsite/excel
- **Permissions**: spatie/laravel-permission
- **Authentication**: Laravel built-in auth
- **Background Jobs**: Laravel Queues
- **File Storage**: Laravel File Storage System

### System Structure
```
hrm_payroll_system_design/
├── app/                       # Application source code
│   ├── Console/              # Artisan commands
│   │   └── Commands/         # Custom Artisan commands
│   ├── Events/               # Event classes
│   ├── Exports/              # Excel export classes
│   ├── Files/                # File handling utilities
│   ├── Helpers/              # Helper functions
│   │   └── AuditLogger.php   # Custom audit logging
│   ├── Http/                 # Controllers, Middleware, Requests
│   │   ├── Controllers/      # Action controllers
│   │   ├── Middleware/       # Request processing middleware
│   │   ├── Requests/         # Form request validation
│   │   └── Kernel.php        # HTTP kernel configuration
│   ├── Imports/              # Excel import classes
│   ├── Listeners/            # Event listeners
│   ├── Models/               # Eloquent models
│   ├── Providers/            # Service providers
│   │   └── AppServiceProvider.php # Main service provider
│   ├── Rules/                # Custom validation rules
│   ├── Services/             # Business logic services
│   │   └── PayrollCalculationService.php # Core calculation logic
│   └── View/                 # View components
├── bootstrap/                # Framework bootstrap files
├── config/                   # Configuration files
│   ├── app.php               # Main application configuration
│   ├── auth.php              # Authentication settings
│   ├── database.php          # Database connection settings
│   ├── filesystems.php       # File storage configuration
│   ├── mail.php              # Email configuration
│   ├── permission.php        # Laravel-permission settings
│   └── ...                   # Other configuration files
├── database/                 # Database migrations and seeds
│   ├── factories/            # Model factories for testing
│   ├── migrations/           # Database schema changes
│   │   ├── 2025_08_23_174127_create_deductions_table.php
│   │   ├── 2025_08_23_162401_create_additions_table.php
│   │   ├── 2025_08_23_181147_create_pay_slips_table.php
│   │   └── ...               # Other migration files
│   └── seeders/              # Database seeding files
├── public/                   # Public web assets
│   ├── css/                  # CSS files
│   ├── images/               # Image assets
│   │   └── logo-white.png    # System logo
│   ├── js/                   # JavaScript files
│   └── index.php             # Main entry point
├── resources/                # Application resources
│   ├── css/                  # CSS source files
│   ├── js/                   # JavaScript source files
│   ├── views/                # Blade templates
│   │   ├── auth/             # Authentication views
│   │   ├── dashboard/        # Dashboard views
│   │   ├── employees/        # Employee management views
│   │   ├── layouts/          # Master layouts
│   │   ├── payroll/          # Payroll management views
│   │   │   ├── index.blade.php  # Main payroll list
│   │   │   ├── payslip.blade.php # Payslip template
│   │   │   └── ...           # Other payroll views
│   │   └── ...               # Other view directories
│   └── ...                   # Other resource files
├── routes/                   # Application routes
│   ├── web.php               # Web routes
│   ├── api.php               # API routes
│   └── console.php           # Console routes
├── storage/                  # Storage directory
├── tests/                    # Test files
├── vendor/                   # Composer dependencies
├── artisan                   # Artisan command line tool
├── composer.json             # PHP dependencies
├── package.json              # Node.js dependencies
├── README.md                 # Project documentation
└── .env.example             # Environment configuration example
```

### Architecture Patterns
1. **Model-View-Controller (MVC)**: Separation of business logic, presentation, and data
2. **Repository Pattern**: Abstract data access layer
3. **Service Layer**: Encapsulate complex business logic
4. **Event-Driven Architecture**: Handle system events and notifications
5. **Dependency Injection**: Manage class dependencies and reduce coupling

### Development Environment
- PHP 8.2+ (minimum requirement from composer.json)
- Composer for dependency management
- Node.js and NPM for frontend assets
- MySQL or compatible database system
- Web server (Apache, Nginx, or built-in PHP server)

## Database Schema

### Core Models and Relationships

#### Employee Model
**Primary Key**: `employee_id`
**Table**: `employees`

**Attributes**:
- `employee_id` (string): Unique employee identifier (primary key)
- `first_name`, `surname`, `middle_name`: Employee names
- `gender`: Employee gender
- `date_of_birth`: Birth date (automatically formatted to Y-m-d)
- `state_id`, `lga_id`, `ward_id`: Geographic location references
- `nationality`, `nin`: Identity information
- `mobile_no`, `email`: Contact information
- `address`: Physical address
- `date_of_first_appointment`: Employment start date (Y-m-d format)
- `cadre_id`: Employee cadre classification
- `staff_no`: Registration number
- `grade_level_id`, `step_id`, `rank_id`: Position classification
- `department_id`: Department assignment
- `expected_next_promotion`: Expected promotion date
- `expected_retirement_date`: Expected retirement date
- `status`: Employment status (Active, Suspended, etc.)
- `highest_certificate`: Educational qualification
- `grade_level_limit`: Grade level constraints
- `appointment_type_id`: Type of appointment
- `photo_path`: Path to employee photo
- `years_of_service`: Automatically calculated years of service
- `contract_start_date`, `contract_end_date`: Contract period
- `amount`: Salary amount (possibly for contract employees)

**Relationships**:
- Belongs to State (`state_id`)
- Belongs to LGA (`lga_id`)
- Belongs to Ward (`ward_id`)
- Belongs to Department (`department_id`)
- Belongs to Cadre (`cadre_id`)
- Belongs to GradeLevel (`grade_level_id`) with steps
- Belongs to Step (`step_id`)
- Belongs to Rank (`rank_id`)
- Belongs to AppointmentType (`appointment_type_id`)
- Has one BiometricData
- Has many PayrollRecords
- Has one NextOfKin
- Has one Bank
- Has many Reports
- Has many DisciplinaryActions
- Has many Deductions
- Has many Additions
- Has one Retirement

**Key Features**:
- Mutators for date fields to ensure proper formatting
- Calculated attribute for years of service
- Computed retirement date based on grade level salary scale

#### PayrollRecord Model
**Primary Key**: `payroll_id`
**Table**: `payroll_records`

**Attributes**:
- `payroll_id` (string): Unique payroll record identifier
- `employee_id`: Reference to Employee
- `grade_level_id`: Reference to GradeLevel
- `basic_salary`: Calculated basic salary for the period
- `status`: Payroll status (Pending, Processed, Under Review, Reviewed, Pending Final Approval, Approved, Paid, Rejected)
- `total_additions`: Sum of all additions for the period
- `total_deductions`: Sum of all deductions for the period
- `net_salary`: Final calculated salary (Basic + Additions - Deductions)
- `payment_date`: Date when payment was made
- `payroll_month`: Month for which payroll is generated
- `remarks`: Additional notes or comments

**Relationships**:
- Belongs to Employee (`employee_id`)
- Belongs to GradeLevel (`grade_level_id`)
- Has many Deductions (via payroll_id, though this might be incorrect in the model)
- Has one PaymentTransaction (`payroll_id`)

**Key Features**:
- Date casting for payroll_month and payment_date
- Status-driven workflow system

#### GradeLevel Model
**Primary Key**: `id`
**Table**: `grade_levels`

**Attributes**:
- `id` (integer): Grade level identifier
- `name`: Name of the grade level
- `grade_level`: Grade level code/number
- `description`: Description of the grade level
- `salary_scale_id`: Reference to associated salary scale

**Relationships**:
- Belongs to SalaryScale (`salary_scale_id`)
- Has many Employees (`grade_level_id`)
- Many-to-many with DeductionType (through grade_level_adjustments with percentage pivot)
- Many-to-many with AdditionType (through grade_level_adjustments with percentage pivot)
- Has many Steps

**Key Features**:
- Computed basic salary attribute (first step's basic salary)
- Polymorphic many-to-many relationship with deduction and addition types
- Percentage-based calculations for statutory adjustments

#### Deduction Model
**Primary Key**: `deduction_id`
**Table**: `deductions`

**Attributes**:
- `deduction_id` (integer): Unique deduction identifier
- `deduction_type`: Name of the deduction type
- `amount`: Financial amount of the deduction
- `amount_type`: Type of calculation (percentage or fixed)
- `deduction_period`: Period type (OneTime, Monthly, Perpetual)
- `start_date`, `end_date`: Validity period
- `employee_id`: Reference to the employee
- `deduction_type_id`: Reference to DeductionType

**Relationships**:
- Belongs to Employee (`employee_id`)
- Belongs to DeductionType (`deduction_type_id`)

**Key Features**:
- Formatted amount attribute for display
- Calculation type description attribute
- Status tracking for validity period

#### Addition Model
**Primary Key**: `addition_id`
**Table**: `additions`

**Attributes**:
- `addition_id` (integer): Unique addition identifier
- `addition_type`: Name of the addition type
- `amount`: Financial amount of the addition
- `amount_type`: Type of calculation (percentage or fixed)
- `addition_period`: Period type (OneTime, Monthly, Perpetual)
- `start_date`, `end_date`: Validity period
- `employee_id`: Reference to the employee
- `addition_type_id`: Reference to AdditionType

**Relationships**:
- Belongs to Employee (`employee_id`)
- Belongs to AdditionType (`addition_type_id`)

**Key Features**:
- Formatted amount attribute for display
- Calculation type description attribute
- Status tracking for validity period

#### DeductionType Model
**Primary Key**: `id`
**Table**: `deduction_types`

**Attributes**:
- `id` (integer): Unique deduction type identifier
- `name`: Name of the deduction type
- `description`: Description of the deduction
- `is_statutory`: Boolean indicating if it's a statutory deduction
- `calculation_type`: How the amount is calculated (percentage or fixed)
- `rate_or_amount`: Rate (for percentage) or fixed amount

**Relationships**:
- Many-to-many with GradeLevel (through grade_level_adjustments)
- Has many Deductions

**Key Features**:
- Classification as statutory vs non-statutory
- Flexible calculation method support

#### AdditionType Model
**Primary Key**: `id`
**Table**: `addition_types`

**Attributes**:
- `id` (integer): Unique addition type identifier
- `name`: Name of the addition type
- `description`: Description of the addition
- `is_statutory`: Boolean indicating if it's a statutory addition
- `calculation_type`: How the amount is calculated (percentage or fixed)
- `rate_or_amount`: Rate (for percentage) or fixed amount

**Relationships**:
- Many-to-many with GradeLevel (through grade_level_adjustments)
- Has many Additions

**Key Features**:
- Classification as statutory vs non-statutory
- Flexible calculation method support

#### GradeLevelAdjustment Pivot Model
**Table**: `grade_level_adjustments`

**Attributes**:
- `adjustable_id`: Foreign key to either AdditionType or DeductionType
- `adjustable_type`: Polymorphic type (AdditionType or DeductionType)
- `grade_level_id`: Foreign key to GradeLevel
- `percentage`: Percentage rate for this grade level (for statutory adjustments)

**Key Features**:
- Polymorphic relationship supporting both deduction and addition types
- Grade-level specific percentage rates for statutory adjustments

#### Related Models
Several other important models exist:

**Bank Model**: Employee banking information
- `employee_id`, `bank_name`, `account_no`, `account_name`, `bank_code`

**NextOfKin Model**: Emergency contact information
- `employee_id`, `full_name`, `relationship`, `phone`, `address`

**Department Model**: Organizational units
- `department_id`, `department_name`, `description`

**User Model**: System users with authentication
- Standard Laravel User with permissions support

**AuditTrail Model**: System activity logs
- `user_id`, `action`, `description`, `action_timestamp`, `log_data`

## Core Modules

### 1. Employee Management Module

#### Overview
The Employee Management module serves as the foundation of the entire system, containing all employee-related information. It handles onboarding, personal information management, employment details, and maintains the central employee database.

#### Main Components

**Employee Registration**
- Comprehensive profile creation with personal, educational, and employment details
- Support for multiple document uploads (photos, certificates)
- Validation for critical fields like dates and IDs
- Automatic calculation of years of service and retirement eligibility
- Geographic information linking (State, LGA, Ward)

**Employee Information Fields**
- **Personal Information**: Names, gender, date of birth, nationality, NIN
- **Contact Information**: Mobile number, email, physical address
- **Employment Details**: Date of first appointment, expected next promotion, expected retirement date
- **Position Classification**: Cadre, grade level, step, rank, department
- **Employment Status**: Active, Suspended, Retired (with reason tracking)

**Employee Relationships**
- Next of kin information management
- Banking information maintenance
- Biometric data tracking (if applicable)
- Disciplinary action history
- Payroll history and records

#### Key Features
- Bulk employee import functionality via Excel
- Export capabilities for various formats (PDF, Excel)
- Advanced search and filtering options
- Employee status tracking and management
- Photo management with proper storage handling
- Retirement eligibility calculation based on salary scale rules

#### Validation and Business Rules
- Date validation for date of birth, appointment date, etc.
- Unique constraints on employee_id and staff_no
- Mandatory fields validation for critical information
- Automatic years of service calculation
- Retirement date calculation based on grade level parameters

### 2. Payroll Management Module

#### Overview
The Payroll Management module is the core of the system, handling all aspects of payroll processing from generation to final payment. It manages complex calculations, workflow approvals, and payslip generation.

#### Main Components

**Payroll Generation Process**
- Monthly payroll generation for all active employees
- Special handling for suspended employees (salary halving)
- Automated calculation of basic salary, additions, and deductions
- Payment transaction creation for each payroll record
- Status tracking through the approval workflow

**Payroll Calculation Algorithm**
1. Retrieve employee's grade level and current step
2. Determine basic salary (halved for suspended employees)
3. Calculate statutory deductions based on grade level percentages
4. Calculate statutory additions based on grade level percentages
5. Process employee-specific non-statutory adjustments
6. Calculate net salary using the formula: Net Salary = Basic Salary - Total Deductions + Total Additions

**Payroll Workflow**
- **Pending Review**: Initial generated state
- **Under Review**: Sent for initial review
- **Reviewed**: Initial review completed
- **Pending Final Approval**: Awaiting final approval
- **Approved**: Final approval given
- **Paid**: Payment processed
- **Rejected**: With rejection reason

**Payslip Generation**
- PDF generation with comprehensive employee and salary information
- Detailed breakdown of basic salary, additions, deductions
- Historical payslip access
- Download and print capabilities

#### Key Features
- Automated monthly payroll generation
- Complex calculation logic for suspended employees
- Multi-level approval workflow
- Bulk operations for status updates
- Detailed payslip generation with all components
- Recalculation functionality for corrections
- Payment transaction tracking

### 3. Deductions and Additions Management Module

#### Overview
This module handles all financial adjustments to employee salaries, including statutory requirements and company-specific adjustments. It supports both percentage-based and fixed-amount calculations with time-based validity periods.

#### Main Components

**Statutory Adjustments**
- Automatically applied based on grade level settings
- Percentage-based calculations linked to basic salary
- Required by law or organizational policy
- Applied to all employees in the specified grade level

**Non-Statutory Adjustments**
- Employee-specific additions/deductions
- Can be percentage-based or fixed amounts
- Configurable validity periods (OneTime, Monthly, Perpetual)
- Flexible application based on business needs

**Adjustment Types Configuration**
- Addition/Deduction type creation and management
- Classification as statutory or non-statutory
- Setting calculation methods (percentage or fixed)
- Defining default rates or amounts

**Employee-Specific Adjustments**
- Individual assignment of non-statutory adjustments
- Start and end date configuration
- Amount determination with validation
- Period-based application rules

#### Key Features
- Bulk assignment of adjustments to multiple employees
- Individual employee adjustment management
- Time-based validity period support
- Percentage and fixed amount calculation methods
- Statutory vs non-statutory distinction
- Historical tracking of adjustments
- Validation to prevent invalid configurations

### 4. Salary Scales and Grade Levels Module

#### Overview
This configuration module manages the organizational salary structure, linking grade levels to salary scales and defining statutory adjustments. It ensures compliance with organizational pay policies and standardizes salary calculations.

#### Main Components

**Salary Scale Management**
- Creation and modification of salary scales
- Setting maximum retirement age and years of service
- Defining the scope and applicability of salary scales
- Organizational level salary structure configuration

**Grade Level Configuration**
- Linking grade levels to specific salary scales
- Defining grade level names, codes, and descriptions
- Step-level salary definitions within each grade level
- Statutory adjustment percentages specific to grade levels

**Step Management**
- Multiple salary steps within each grade level
- Basic salary amounts for each step
- Promotion criteria and pathways between steps
- Salary progression tracking

**Statutory Adjustment Configuration**
- Setting deduction percentages for each grade level
- Setting addition percentages for each grade level
- Polymorphic relationships supporting both deduction and addition types

#### Key Features
- Hierarchical salary structure management
- Grade-level specific statutory percentages
- Step-based salary progression
- Retirement age and service calculation
- Flexible organizational structure support

### 5. User Management and Permissions Module

#### Overview
This module handles system access control, user accounts, and permission management. It ensures that users can only access and perform actions appropriate to their roles and responsibilities.

#### Main Components

**User Account Management**
- User creation, modification, and deletion
- Role assignment and management
- Authentication and session management
- Password policies and security

**Role-Based Access Control (RBAC)**
- Predefined roles with specific permissions
- Custom role creation and modification
- Permission assignment to roles
- Granular access control

**Permissions System**
- Resource-level permissions (manage_employees, view_employees, etc.)
- Action-level permissions (create, read, update, delete)
- Contextual permissions based on business rules
- Audit trail for permission changes

#### Key Features
- Comprehensive permission system
- Role-based access control
- User session management
- Password security policies
- Account activation/deactivation
- Audit trail for user actions

### 6. Reporting and Analytics Module

#### Overview
This module provides comprehensive reporting capabilities for all system data, enabling management to make informed decisions based on accurate and timely information.

#### Main Components

**Standard Reports**
- Employee reports with detailed information
- Payroll summary and detailed reports
- Deduction and addition reports
- Department-wise reports
- Status-based reports

**Export Capabilities**
- Excel export with multiple formatting options
- PDF export for formal documentation
- Filtered exports based on criteria
- Scheduled report generation

**Dashboard Analytics**
- Real-time payroll statistics
- Employee status breakdowns
- Salary distribution analysis
- Deduction and addition summaries

#### Key Features
- Advanced filtering and search capabilities
- Multiple export formats
- Customizable report parameters
- Scheduled report generation
- Real-time analytics dashboard
- Historical report access

### 7. Audit Trail Module

#### Overview
This module maintains comprehensive logs of all actions performed in the system, providing accountability, security, and compliance capabilities.

#### Main Components

**Action Logging**
- User identification for all actions
- Action type classification
- Entity type and ID tracking
- Timestamp recording
- Detailed action descriptions
- JSON data logging for context

**Audit Trail Interface**
- Comprehensive audit log viewing
- Filtered audit trail searches
- Export capabilities for audit reports
- User activity tracking
- System event monitoring

#### Key Features
- Comprehensive action logging
- Advanced search and filtering
- Export capabilities for compliance
- User activity monitoring
- System integrity verification

## Payroll Calculation Process

### The Calculation Algorithm

The payroll calculation is managed by the `PayrollCalculationService` class, which implements a comprehensive algorithm to handle various scenarios including suspended employees, statutory requirements, and employee-specific adjustments.

#### Detailed Calculation Process

1. **Employee and Grade Level Retrieval**
   - Load the employee record with associated grade level information
   - Validate that the employee has a valid grade level assigned
   - Retrieve the current step information for salary calculation
   - Handle cases where grade level or steps are not defined

2. **Basic Salary Determination**
   - Get the basic salary from the employee's current step within their grade level
   - For active employees: basic salary = step basic salary
   - For suspended employees: basic salary = (step basic salary) / 2
   - Apply this halving logic consistently throughout calculations

3. **Statutory Deduction Calculations**
   - Retrieve all statutory deduction types associated with the employee's grade level
   - Calculate amounts as percentages of the adjusted basic salary (halved for suspended employees)
   - Apply the percentage from the grade level adjustment pivot table
   - Add calculated amounts to total deductions accumulator
   - Track these deductions for detailed reporting

4. **Statutory Addition Calculations**
   - Retrieve all statutory addition types associated with the employee's grade level
   - Calculate amounts as percentages of the adjusted basic salary (halved for suspended employees)
   - Apply the percentage from the grade level adjustment pivot table
   - Add calculated amounts to total additions accumulator
   - Track these additions for detailed reporting

5. **Employee-Specific Adjustment Processing**
   - Query all non-statutory deductions for the employee that are valid for the payroll month
   - Validity check: start_date <= payroll_month <= end_date (or end_date is null for ongoing)
   - Calculate deduction amounts based on their configuration (percentage or fixed)
   - Apply percentage-based deductions to the basic salary if linked
   - For suspended employees, apply halving logic to percentage-based deductions if appropriate
   - Add calculated amounts to total deductions accumulator

   - Query all non-statutory additions for the employee that are valid for the payroll month
   - Apply same validity checks as deductions
   - Calculate addition amounts based on their configuration
   - Add calculated amounts to total additions accumulator

6. **Net Salary Calculation**
   - Apply the formula: Net Salary = Basic Salary - Total Deductions + Total Additions
   - Ensure the calculation accounts for any special handling (like suspended employee adjustments)
   - Return comprehensive results including breakdown of all components

### Handling Suspended Employees

#### Special Processing Logic
When an employee is in "Suspended" status, the system applies special calculation rules:

1. **Basic Salary Adjustment**
   - The basic salary used in calculations is halved
   - This affects all percentage-based calculations that depend on basic salary

2. **Statutory Deduction Impact**
   - Percentage-based statutory deductions are calculated on the halved basic salary
   - Fixed statutory deductions are also halved to maintain proportionality
   - This ensures suspended employees pay reduced statutory obligations

3. **Statutory Addition Impact**
   - Percentage-based statutory additions are calculated on the halved basic salary
   - Fixed statutory additions may also be reduced proportionally

4. **Non-Statutory Adjustment Handling**
   - Percentage-based non-statutory deductions linked to basic salary are halved
   - Fixed non-statutory deductions remain unchanged (unless specifically configured otherwise)
   - Non-statutory additions follow similar logic based on their configuration

#### Implementation Details
The suspension logic is implemented in multiple places:
- Payroll generation process
- Payroll calculation service
- Payslip generation
- Bulk operations
- Individual adjustment calculations

### Workflow Process

The system implements a comprehensive workflow to ensure proper payroll approval:

1. **Generation Phase**: Payroll records are created with "Pending Review" status
2. **Initial Review**: Records are sent for initial review, changing status to "Under Review"
3. **Review Completion**: Records are marked as "Reviewed" after initial review
4. **Final Approval**: Records are sent for final approval, changing status to "Pending Final Approval"
5. **Final Approval**: Records are approved with "Approved" status
6. **Payment Phase**: Records can be marked as "Paid" after payment processing
7. **Rejection Path**: Records can be rejected at various stages with reason documentation

### Calculation Validation and Error Handling

1. **Data Validation**
   - Verify employee has valid grade level assignment
   - Ensure grade level has associated steps
   - Confirm step has valid basic salary amount

2. **Error Scenarios**
   - Handle missing grade level (set all values to 0)
   - Handle missing steps (set all values to 0)
   - Handle invalid basic salary (set to 0)

3. **Return Values**
   - Complete breakdown of all calculated components
   - Individual deduction and addition records
   - Total amounts for each category
   - Final net salary calculation

## User Interface

### Main Dashboard

#### Overview
The main dashboard serves as the central hub for system navigation, providing an overview of key metrics and quick access to essential functions.

#### Layout Structure
- **Top Navigation Bar**: Contains user profile, dark/light mode toggle, and notifications
- **Sidebar Navigation**: Collapsible menu with all system modules
- **Main Content Area**: Dashboard statistics and quick access panels
- **Footer**: System information and copyright notice

#### Dashboard Components
1. **Statistics Cards**: Show real-time counts of employees, payroll records, etc.
2. **Quick Actions**: Shortcuts to frequently used functions
3. **Recent Activity**: Recent payroll processing or employee changes
4. **System Status**: Overall system health and performance indicators

#### Responsive Design Features
- Mobile-first approach ensuring compatibility across devices
- Collapsible sidebar for mobile devices using Bootstrap's off-canvas component
- Adaptive layout that adjusts to different screen sizes
- Touch-friendly interface elements optimized for mobile interaction

### Navigation Structure

The navigation follows a logical hierarchy based on permission levels and functional groupings:

```
Dashboard
├── Employees
│   ├── Employee List (view_employees permission)
│   ├── Add Employee (manage_employees permission)
│   └── Pending Changes (manage_employees permission)
├── User Management (manage_users permission)
│   ├── Users
│   └── Roles
├── Departments (manage_departments permission)
│   ├── Department List
│   └── Add Department
├── Biometrics (manage_biometrics permission)
├── Audit Trail (view_audit_logs permission)
├── Disciplinary Actions (manage_disciplinary permission)
│   ├── Disciplinary List
│   └── Log Action
├── Retirements
│   ├── Retirement List (view_retirement permission)
│   └── Add Retirement (manage_retirement permission)
├── SMS Notifications (manage_sms permission)
├── Payroll (manage_payroll permission)
│   ├── Process Payroll
│   ├── Bulk Additions
│   ├── Bulk Deductions
│   ├── Employee Adjustments
│   ├── Addition Types
│   ├── Deduction Types
│   └── Salary Scales
└── Reports (manage_reports permission)
```

### Payroll Interface

#### Payroll Generation Section
- **Month Selection**: Calendar widget for selecting payroll month
- **Generate Button**: Triggers the payroll calculation process
- **Status Indicators**: Shows current processing status
- **Progress Tracking**: For large employee datasets

#### Advanced Search & Filter System
- **Quick Search Bar**: Text-based search across multiple fields
- **Filter Collapsible Panel**: Extensive filtering options
  - Status filtering (Pending, Processed, Approved, Paid, etc.)
  - Month filtering with calendar widget
  - Salary range filtering with predefined brackets
  - Sort options (by date, employee name, salary, etc.)
  - Date range filtering
  - Department filtering
  - Employee status filtering (Active, Suspended)

#### Results Display Area
- **Summary Information**: Shows record counts and total salary
- **Pagination Controls**: Navigation for large datasets
- **Bulk Action Controls**: For processing multiple records simultaneously

#### Main Payroll Table
- **Multi-select Functionality**: Checkbox column for bulk operations
- **Employee Identification**: Staff number, full name, ID, grade level
- **Salary Components**: Basic salary, additions, deductions, net salary
- **Status Indicators**: Visual badges for different workflow statuses
- **Date Information**: Payment date, payroll month
- **Action Dropdowns**: Context-sensitive operations per record

#### Action Dropdown Options
- **View Details**: Detailed payroll information
- **Download Payslip**: PDF generation and download
- **Manage Deductions/Adoptions**: Individual employee adjustments
- **Workflow Actions**: Send for review, approve, reject, etc.
- **Recalculate**: Re-run payroll calculation for this record

### Employee Management Interface

#### Employee Registration Form
- **Personal Information Section**: Names, gender, date of birth, contact details
- **Address and Location**: Geographic information with cascading dropdowns
- **Employment Information**: Appointment details, grade level, department
- **Photo Upload**: Image upload with preview functionality
- **Banking Information**: Account details for salary payment
- **Next of Kin**: Emergency contact information
- **Document Upload**: Support for multiple document types

#### Employee List Interface
- **Comprehensive Search**: Multi-field search capabilities
- **Advanced Filtering**: Department, status, grade level, date ranges
- **Bulk Operations**: Import, export, status updates
- **Quick View Cards**: Compact information display
- **Detailed Modal Views**: For complex employee information

### Deductions and Additions Interface

#### Bulk Operations Interface
- **Employee Selection**: Multi-select with filtering capabilities
- **Adjustment Type Selection**: Dropdown with statutory and non-statutory options
- **Amount Configuration**: Percentage or fixed amount with validation
- **Period Settings**: OneTime, Monthly, Perpetual with date ranges
- **Preview and Confirmation**: Review before processing

#### Individual Adjustments Interface
- **Employee Search**: Quick search for specific employees
- **Current Adjustments List**: View active adjustments with validity periods
- **New Adjustment Form**: Additions and deductions with full configuration
- **Validation and Error Handling**: Comprehensive validation with user feedback

### Responsive Design Implementation

#### Mobile Considerations
- **Off-canvas Sidebar**: Sidebar transforms to off-canvas on smaller screens
- **Touch-optimized Controls**: Larger touch targets for mobile interaction
- **Adaptive Tables**: Horizontal scrolling or stacked view for tables on small screens
- **Streamlined Workflows**: Simplified interfaces for mobile use

#### Tablet and Desktop Optimizations
- **Multi-column Layouts**: Efficient use of wider screens
- **Hover Effects**: Subtle UI enhancements for desktop users
- **Keyboard Shortcuts**: Efficiency improvements for power users
- **Advanced Filtering**: More filtering options on larger screens

#### Cross-browser Compatibility
- **Progressive Enhancement**: Core functionality works on all browsers
- **Modern CSS Features**: Flexbox and Grid for layout
- **JavaScript Fallbacks**: Graceful degradation for older browsers
- **Bootstrap Framework**: Consistent interface across browsers

## Security and Permissions

### Permission-based Access Control

The system implements a comprehensive role-based access control (RBAC) system using Laravel's built-in authentication and the spatie/laravel-permission package.

#### Main Permission Categories

**Employee Management Permissions**
- `manage_employees`: Full employee lifecycle management including creation, modification, deletion
- `view_employees`: Read-only access to employee information
- `approve_employee_changes`: Approve pending employee changes

**System Management Permissions**
- `manage_users`: User account creation, role assignment, password management
- `manage_departments`: Department structure management
- `manage_roles`: Role definition and permission assignment

**Payroll Management Permissions**
- `manage_payroll`: Full payroll processing including generation, approval, and payment
- `view_payroll`: Read-only access to payroll information

**Specialized Permissions**
- `manage_biometrics`: Access to biometric data (if implemented)
- `view_audit_logs`: Access to system audit trails
- `manage_disciplinary`: Disciplinary action management
- `manage_retirement`: Retirement processing
- `manage_sms`: SMS notification system
- `approve_employee_changes`: Approval of pending employee changes

#### Permission Implementation

**Route Protection**
- Middleware-based protection for sensitive routes
- Controller-level permission checks using Laravel's built-in can() method
- Automatic redirection and error messages for unauthorized access

**View-Level Protection**
- Conditional display of interface elements based on permissions
- Blade directives (@can, @cannot) for interface customization
- Dynamic navigation based on user permissions

**Model-Level Protection**
- Policies for complex model operations
- Automatic model ownership checks
- Soft deletes and restore capabilities

#### Role Management

**Predefined Roles**
The system supports various predefined roles with appropriate permissions:

1. **Super Admin**: Full system access to all features
2. **HR Manager**: Employee, payroll, and disciplinary management
3. **Payroll Manager**: Payroll processing and related functions
4. **System Admin**: User and role management
5. **Auditor**: Read-only access to reports and audit trails
6. **Employee**: Self-service capabilities (if implemented)

**Custom Role Creation**
- Dynamic role creation through the admin interface
- Permission assignment through intuitive checkboxes
- Role inheritance and groupings
- Role-based access templates

#### Authentication System

**Standard Laravel Authentication**
- Username/email and password authentication
- Remember me functionality
- Password reset via email
- Session management with configurable timeouts

**Security Features**
- CSRF protection across all forms
- Session encryption and security
- Secure password hashing (bcrypt)
- Rate limiting for authentication attempts
- IP-based access restrictions (configurable)

**Password Policies**
- Complexity requirements (minimum length, character types)
- Password history tracking to prevent reuse
- Expiration policies
- Multi-factor authentication support (if configured)

### Data Security

**Input Validation and Sanitization**
- Comprehensive validation for all user inputs
- XSS prevention through automatic escaping
- SQL injection prevention through query parameterization
- File upload validation and sanitization

**Data Encryption**
- Sensitive data encryption at rest
- SSL/TLS encryption for data in transit
- Encrypted session storage
- Database connection encryption

**Audit Trail Security**
- Immutable logs of all system actions
- Tamper-evident logging mechanisms
- Secure log storage with access controls
- Regular log review and maintenance

### Session Management

**Session Configuration**
- Configurable session timeouts
- Database-based session storage
- Concurrent session limits
- Session regeneration after login

**Security Measures**
- Session identifier randomness
- Session fixation protection
- Cross-site request forgery tokens
- Secure cookie policies

## API Endpoints

### RESTful API Structure

The system provides a comprehensive API for various operations, built using Laravel's powerful routing system. APIs follow RESTful conventions where appropriate and custom endpoints where special functionality is required.

### Payroll-related Endpoints

```
POST /payroll/generate          # Generate payroll for a specific month
GET  /payroll                  # List payroll records with search/filters
GET  /payroll/{id}             # View specific payroll record  
GET  /payroll/{id}/edit        # Edit payroll record form
PUT  /payroll/{id}             # Update payroll record
DELETE /payroll/{id}           # Delete payroll record
GET  /payroll/{id}/payslip     # Download payslip PDF
POST /payroll/{id}/recalculate # Recalculate payroll for specific record
POST /payroll/{id}/approve     # Approve payroll record
POST /payroll/{id}/reject      # Reject payroll record with reason
GET  /payroll/export           # Export payroll records to Excel
GET  /payroll/additions        # Bulk additions management interface
POST /payroll/additions/bulk   # Process bulk addition assignments
GET  /payroll/deductions       # Bulk deductions management interface
POST /payroll/deductions/bulk  # Process bulk deduction assignments
GET  /payroll/adjustments      # Employee adjustments management
GET  /payroll/{id}/detailed    # Get detailed payroll information
```

### Employee-related Endpoints

```
GET    /employees                    # List employees with filters
GET    /employees/{id}              # View specific employee
POST   /employees                    # Create new employee
PUT    /employees/{id}              # Update employee information
DELETE /employees/{id}              # Delete employee
GET    /employees/create            # Create employee form
GET    /employees/{id}/edit         # Edit employee form
POST   /employees/import            # Import employees from Excel
GET    /employees/export/pdf        # Export employees to PDF
GET    /employees/export/excel      # Export employees to Excel
GET    /employees/{id}/export       # Export single employee
GET    /employees/export/filtered   # Export filtered employee list
GET    /employees/lgas-by-state     # AJAX: Get LGAs for a state
GET    /employees/wards-by-lga      # AJAX: Get wards for an LGA
GET    /employees/ranks-by-grade-level # AJAX: Get ranks for grade level
```

### Bulk Operations Endpoints

```
POST /payroll/bulk/update-status        # Update status for multiple records
POST /payroll/bulk/send-for-review      # Send multiple records for review
POST /payroll/bulk/mark-as-reviewed     # Mark multiple records as reviewed
POST /payroll/bulk/send-for-approval    # Send multiple records for approval
POST /payroll/bulk/final-approve        # Final approve multiple records
POST /users/bulk-create                 # Create multiple users
GET  /users/employees-without-users     # Get employees without user accounts
```

### Employee Adjustment Endpoints

```
GET  /payroll/employee/{employeeId}/deductions  # View employee deductions
POST /payroll/employee/{employeeId}/deductions  # Add employee deduction
GET  /payroll/employee/{employeeId}/additions  # View employee additions
POST /payroll/employee/{employeeId}/additions  # Add employee addition
```

### AJAX and Search Endpoints

```
GET  /payroll/api/search           # Search payroll records
GET  /payroll/api/statistics       # Get payroll statistics
POST /session/store-selected-employee # Store selected employee in session
GET  /api/salary-scales/{scaleId}/grade-levels # Get grade levels for salary scale
GET  /api/salary-scales/{scaleId}/grade-levels/{levelName}/steps # Get steps for grade level
GET  /salary-scales/{scaleId}/retirement-info  # Get retirement info for salary scale
```

### User and Administration Endpoints

```
GET    /users                      # List users
GET    /users/create              # Create user form
POST   /users                     # Create new user
GET    /users/{id}                # View specific user
PUT    /users/{id}                # Update user
DELETE /users/{id}                # Delete user
PATCH  /users/{id}/role           # Update user role
PATCH  /users/{id}/reset-password # Reset user password
GET    /roles                     # List roles
GET    /roles/create              # Create role form
POST   /roles                     # Create new role
GET    /roles/{id}                # View specific role
PUT    /roles/{id}                # Update role
DELETE /roles/{id}                # Delete role
```

### Report and Audit Endpoints

```
GET    /reports                           # List reports
POST   /reports/generate                 # Generate specific report
POST   /reports/bulk-generate            # Generate multiple reports
GET    /reports/{id}/download            # Download specific report
GET    /reports/export                   # Export reports
GET    /audit-trails                    # View audit trails
GET    /audit-logs                      # Alternative audit view
```

### API Security Implementation

**Middleware Protection**
- Authentication middleware for all API routes
- Permission checks for each endpoint
- Rate limiting to prevent abuse
- Request validation for all inputs

**Response Formats**
- Consistent JSON responses for API calls
- Proper HTTP status codes
- Error messages with context
- Data validation feedback

**Request Validation**
- Form request classes for complex validation
- Automatic error response formatting
- Validation rule customization
- Sanitization of input data

## Reports and Exports

### Export Capabilities

#### Comprehensive Export System
The system provides extensive export capabilities designed to support various operational needs, from detailed payroll analysis to compliance reporting.

**Excel Exports**
- Standard payroll records export
- Detailed payroll reports with all calculation components
- Employee information export with all fields
- Filtered exports based on search criteria
- Customizable column selection
- Multiple worksheet support for complex reports
- Formatted currency and date values

**PDF Exports**
- Professional PDF reports for formal documentation
- Employee information packets
- Payslip PDF generation
- Organizational charts and reports
- Audit trail reports
- Compliant document formatting

#### Export Configuration Options

**Data Filtering**
- Date range filtering for export data
- Status-based filtering (active employees, specific payroll statuses)
- Department-based filtering
- Grade level and salary range filtering
- Employee status filtering (active, suspended, retired)

**Format Options**
- Multiple Excel format support (XLS, XLSX)
- Customizable PDF layouts
- Portrait/landscape orientation options
- Page size customization
- Header and footer configuration
- Watermark and branding options

**Performance Optimization**
- Batch processing for large datasets
- Memory-efficient export for large files
- Progress tracking for long-running exports
- Background processing for very large exports

### Reporting Features

#### Standard Reports

**Employee Reports**
- Complete employee directory
- Employee information by department
- Employee status reports (active, suspended, retired)
- Grade level distribution reports
- Salary grade analysis
- Years of service reports
- Retirement eligibility reports

**Payroll Reports**
- Monthly payroll summaries
- Detailed payroll breakdowns
- Salary distribution analysis
- Deduction and addition summaries
- Payment processing reports
- Payroll trend analysis
- Department-wise payroll reports

**Deduction and Addition Reports**
- Statutory deduction compliance reports
- Individual employee adjustment summaries
- Department-specific adjustment reports
- Time-based adjustment reports
- Percentage vs fixed amount breakdowns

#### Advanced Reporting

**Dashboard Analytics**
- Real-time payroll statistics
- Employee distribution by grade level
- Salary range analysis
- Monthly payroll trends
- Department-wise cost analysis
- Deduction and addition totals

**Custom Report Builder**
- Configurable report parameters
- Flexible field selection
- Custom filtering options
- Scheduling for regular reports
- Automated delivery options

#### Bulk Generation and Distribution

**Mass Report Generation**
- Generate multiple reports simultaneously
- Batch processing for large organizations
- Automated scheduling for regular reports
- Email delivery for generated reports
- Secure file sharing options

**Compliance Reports**
- Statutory compliance reporting
- Payroll tax preparation documents
- Regulatory submission formats
- Audit preparation documents
- Year-end reporting packages

### Report Customization

#### User Interface for Report Configuration

**Filter Interface**
- Intuitive date range pickers
- Multi-select dropdowns for categories
- Advanced search within reports
- Real-time preview of report data
- Save frequently used report configurations

**Format Customization**
- Choose which columns to include
- Customize column order and width
- Format number and currency displays
- Choose date and time formats
- Add custom headers and footers

**Scheduling System**
- Define report generation schedules
- Set up automatic email delivery
- Configure delivery recipients
- Schedule reports for specific dates/times
- Manage multiple scheduled reports

## Audit Trail

### Comprehensive Logging System

#### Overview
The system maintains a complete audit trail of all significant actions performed within the application, ensuring accountability, security, and compliance with organizational policies and regulatory requirements.

#### Audit Trail Model Structure

**AuditTrail Model Attributes**
- `user_id`: Foreign key linking to the user who performed the action
- `action`: String describing the type of action performed (e.g., "created_employee", "generated_payroll")
- `description`: Human-readable description of what happened
- `action_timestamp`: When the action occurred (with timezone information)
- `log_data`: JSON object containing additional context about the action
- `entity_type`: The type of entity affected by the action (e.g., "Employee", "PayrollRecord")
- `entity_id`: The identifier of the specific entity affected

#### Types of Actions Tracked

**Employee Management Actions**
- Employee record creation and modification
- Employee status changes (active, suspended, retired)
- Personal information updates
- Position changes (grade level, department, etc.)
- Document uploads and updates
- Employee photo updates

**Payroll Actions**
- Payroll generation for specific months
- Payroll record creation and updates
- Payslip generation and downloads
- Payroll workflow actions (review, approval, rejection)
- Payroll recalculations
- Payment processing and status updates

**Deduction and Addition Actions**
- Creation of new deductions and additions
- Updates to existing adjustments
- Bulk assignment operations
- Period changes for adjustments
- Amount modifications

**System Administration Actions**
- User account creation, modification, deletion
- Role assignments and changes
- Permission modifications
- System configuration changes
- Import and export operations
- Data backup and maintenance operations

**Security-related Actions**
- Login and logout events
- Failed authentication attempts
- Password changes
- Account lockouts and unlocks
- Permission changes
- Administrative access to sensitive areas

#### Log Data Structure

The `log_data` field contains comprehensive context information in JSON format:

```json
{
  "entity_type": "Employee",
  "entity_id": "EMP001234",
  "changes": {
    "field_name": {
      "old": "old_value",
      "new": "new_value"
    }
  },
  "metadata": {
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "session_id": "session_hash"
  }
}
```

#### Audit Trail Interface

**Comprehensive View**
- Detailed audit log with all recorded actions
- Advanced filtering capabilities (by user, action type, date range, entity type)
- Search functionality across all audit data
- Export capabilities for compliance reporting
- Real-time updates for new audit entries

**Filtering Options**
- By specific user or user role
- By action type or category
- By date range with custom periods
- By entity type (Employee, PayrollRecord, etc.)
- By IP address or location
- By specific entity IDs

**Compliance Features**
- Immutable audit records that cannot be deleted or modified
- Export to standard compliance formats
- Automatic backup of audit data
- Integration with external compliance systems
- Alert systems for suspicious activities

#### Security and Compliance

**Data Integrity**
- Immutable audit records protected from modification
- Cryptographic hashing for tamper detection
- Regular integrity checks and validation
- Secure storage with access controls
- Backup and disaster recovery procedures

**Access Controls**
- Role-based access to audit logs
- Administrative access required for audit trail viewing
- Log access tracking and monitoring
- Regular access reviews and updates
- Separation of duties for audit trail management

**Compliance Standards**
- Support for various regulatory requirements
- Regular audit trail maintenance
- Compliance reporting capabilities
- Integration with external audit systems
- Historical data retention policies

#### Performance Optimization

**Efficient Storage**
- Optimized database schema for audit data
- Automatic archiving of old audit records
- Indexing for fast query performance
- Storage optimization for large volumes
- Data compression where appropriate

**Query Performance**
- Optimized indexes on key audit fields
- Efficient filtering and search capabilities
- Paginated results for large datasets
- Caching for frequently accessed audit data
- Asynchronous processing for bulk operations

## Configuration and Setup

### Initial Installation

#### Prerequisites
- PHP 8.2 or higher
- Composer dependency manager
- MySQL 8.0 or compatible database
- Web server (Apache, Nginx, or built-in PHP server)
- Node.js and NPM for frontend assets

#### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone [repository-url]
   cd hrm_payroll_system_design
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend Dependencies**
   ```bash
   npm install
   npm run build  # or npm run dev for development
   ```

4. **Configure Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Configuration**
   - Update `.env` with database credentials
   - Ensure database exists and is accessible

6. **Run Migrations**
   ```bash
   php artisan migrate --seed
   ```

7. **Storage Setup**
   ```bash
   php artisan storage:link
   ```

### Environment Configuration

#### Key Environment Variables

**Database Configuration**
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrm_payroll
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**Application Configuration**
```
APP_NAME="HRM Payroll System"
APP_ENV=local
APP_KEY=base64:generated_key
APP_DEBUG=true
APP_URL=http://localhost:8000
```

**Mail Configuration** (for password resets, notifications)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**Queue Configuration** (for background processing)
```
QUEUE_CONNECTION=database
```

### Database Migration and Seeding

#### Migration Structure
The system includes comprehensive migrations for all database tables:

- **User and Authentication Tables**: Laravel's built-in auth tables
- **Employee Tables**: All employee-related information
- **Payroll Tables**: Payroll records, transactions, etc.
- **Adjustment Tables**: Deductions and additions
- **Configuration Tables**: Grade levels, salary scales, etc.
- **Audit and System Tables**: Logging and system information

#### Seeding Strategy
- **Role and Permission Seeding**: Set up initial RBAC structure
- **Sample Data Seeding**: Demo data for development
- **System Configuration**: Default settings and parameters
- **Reference Data**: States, LGAs, Departments, etc.

### User Setup and Onboarding

#### Initial User Creation
1. **Super Admin Creation**
   ```bash
   php artisan tinker
   >>> $user = new App\Models\User();
   >>> $user->username = 'admin';
   >>> $user->email = 'admin@example.com';
   >>> $user->password = bcrypt('password');
   >>> $user->save();
   >>> $user->assignRole('Super Admin');
   ```

2. **Default Roles Setup**
   - Super Admin: Full system access
   - HR Manager: Employee and payroll management
   - Payroll Manager: Payroll processing only
   - Auditor: Read-only access

### File Storage Configuration

#### Storage Settings
The system uses Laravel's storage system for:

- **Employee Photos**: Profile pictures and documents
- **System Backups**: Database and configuration backups
- **Generated Files**: Exported reports and payslips

Configuration in `config/filesystems.php`:
```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'visibility' => 'private',
    ],
],
```

### Performance Configuration

#### Caching Configuration
- **Route Caching**: `php artisan route:cache`
- **Configuration Caching**: `php artisan config:cache`
- **View Caching**: Blade template optimization
- **Redis Configuration**: For advanced caching (if available)

#### Queue Configuration
For background processing:
- Database queue driver configured by default
- Mail notifications sent via queues
- Large exports processed in background
- Audit log writing optimized

## Business Logic Details

### Complex Business Rules

#### Payroll Processing Rules

**Suspended Employee Processing**
- Basic salary calculation = (grade level step basic salary) / 2
- All percentage-based statutory deductions calculated on halved amount
- Fixed statutory deductions also halved for proportional fairness
- Non-statutory percentage-based deductions linked to basic salary halved
- Net salary calculated with all adjustments applied to halved base

**Statutory Compliance Rules**
- Statutory deductions automatically applied based on grade level
- Percentage rates determined by salary scale configurations
- Legal compliance for all statutory requirements
- Time-based application of statutory adjustments

**Payment Processing**
- Payment transactions created for each payroll record
- Bank information validation before payment
- Payment status tracking and reconciliation
- Automatic payment file generation for bank processing

#### Employee Status Management

**Active Status Rules**
- Full payroll processing
- All statutory and non-statutory adjustments applied
- Full access to benefits and allowances
- Standard deduction and addition processing

**Suspended Status Rules**
- Salary halved for calculation purposes
- Proportional adjustment of percentage-based items
- Continued employment relationship
- Maintained benefit eligibility (as per policy)

**Retired Status Rules**
- No ongoing payroll processing
- Final settlement calculations
- Access restriction to active employee features

#### Workflow Business Logic

**Multi-stage Approval Process**
- Initial generation creates records in "Pending Review" status
- Departmental review moves to "Under Review"
- Review completion moves to "Reviewed"
- Final approval moves to "Pending Final Approval"
- Administrative approval moves to "Approved"
- Payment processing moves to "Paid"

**Permission-based Workflow Access**
- Each stage requires appropriate permissions
- Overlapping permissions for different review levels
- Administrative override capabilities
- Audit trail at each workflow stage

### Calculation Algorithms

#### Complex Salary Calculations

**Net Salary Formula**
```
Net Salary = Basic Salary - Total Statutory Deductions - Total Non-Statutory Deductions + Total Statutory Additions + Total Non-Statutory Additions
```

**Step Progression Logic**
- Automatic step progression based on years of service
- Grade level promotion criteria
- Merit-based advancement tracking
- Automatic salary scale progression

**Retirement Eligibility**
- Based on maximum retirement age from salary scale
- Based on maximum years of service from salary scale
- Whichever comes first determines retirement date
- Automatic retirement processing at eligibility date

#### Tax and Compliance Calculations

**Statutory Requirement Processing**
- Tax calculations based on current tax brackets
- National Health Insurance contributions
- Social security contributions

**Regional Variations**
- State-based tax variations
- Local government allocation considerations
- Regional allowance variations
- Cost of living adjustments

### Data Validation and Integrity

#### Input Validation Rules

**Employee Data Validation**
- Unique employee ID enforcement
- Date validation with reasonable ranges
- Required field enforcement
- Format validation for contact information
- Image size and type validation for photos

**Payroll Data Validation**
- Salary range reasonableness checks
- Deduction and addition percentage limits
- Date range validation for adjustments
- Amount validation to prevent errors

#### Business Rule Enforcement

**Cross-Field Validation**
- Date of birth vs date of appointment consistency
- Grade level vs department compatibility
- Salary vs grade level validation
- Duplicate employee detection

**System Integrity Checks**
- Referential integrity through foreign keys
- Data type consistency validation
- Range validation for numeric fields
- Format validation for structured data

## Performance Considerations

### Database Optimization

#### Indexing Strategy
- Primary keys for all tables with efficient clustering
- Foreign key indexes for all relationship fields
- Composite indexes for common query patterns
- Full-text indexes for search operations
- Partial indexes for frequently filtered columns

#### Query Optimization
- Eager loading to prevent N+1 queries
- Select specific columns instead of full models
- Database transactions for complex operations
- Connection pooling for high-load scenarios
- Read/write separation for scalability

### Application Performance

#### Caching Strategy
- Route caching for faster route resolution
- Configuration caching for improved boot time
- Blade template compilation and caching
- Database query result caching
- Session data caching for active users

#### Memory Management
- Batch processing for large datasets
- Efficient pagination for large result sets
- Lazy loading for related model data
- Memory-efficient file processing
- Garbage collection optimization

### Scalability Considerations

#### Load Management
- Queue-based processing for intensive operations
- Background job processing for exports
- Rate limiting for API endpoints
- Database connection pooling
- Cache layer implementation

#### Horizontal Scaling
- Stateless application design
- Shared session storage configuration
- Load balancer readiness
- Database read replica support
- CDN configuration for static assets

### Monitoring and Optimization

#### Performance Monitoring
- Database query time tracking
- API response time monitoring
- Memory usage tracking
- Slow query identification
- Performance profiling tools integration

#### Optimization Tools
- Laravel Telescope for detailed monitoring
- Database query analysis tools
- Application profiling utilities
- Code quality and performance checks
- Automated performance testing

### Maintenance Considerations

#### Database Maintenance
- Regular backup procedures
- Index optimization and maintenance
- Data archiving for old records
- Database cleanup routines
- Performance monitoring and alerts

#### Application Maintenance
- Automated testing procedures
- Code quality checks
- Security vulnerability scanning
- Dependency update management
- Documentation maintenance

---

## Conclusion

The HRM Payroll System is a robust, scalable solution designed for comprehensive payroll management. It handles complex payroll calculations, supports various employee statuses, and includes extensive administrative controls with proper security measures. The system's modular architecture allows for easy maintenance and future enhancements.

Key strengths include:
- Comprehensive payroll calculation logic with special handling for suspended employees
- Flexible deduction and addition management with statutory and non-statutory options
- Strong security and audit trail with role-based access control
- Responsive user interface with dark mode support
- Bulk operation capabilities for efficient management
- Extensive export and reporting features
- Scalable architecture supporting organizational growth
- Comprehensive data validation and business rule enforcement
- Performance-optimized design for handling large datasets
- Compliant with regulatory requirements and organizational policies

# PART 2: USER MANUAL
====================


# Kundi HR Management System - User Manual

## Introduction
Welcome to the comprehensive user manual for the Kundi Human Resources Management and Payroll System. This guide covers every feature, button, and field within the application, providing a step-by-step walkthrough of the entire system.

## 1. Login Page
Access the application through your web browser. You will be greeted by the Login screen.

### Top Navigation Bar
- **Brand Name**: "Kundi Human Resources Management and Payroll" is displayed on the top left.
- **Login / Register**: Links on the top right to switch between Login and Register pages.

### Main Login Form
The central card contains the login credentials form:
1.  **Email Address**: Enter your registered email address here.
2.  **Password**: Enter your secure password.
3.  **Remember Me**: A checkbox to keep you logged in on this device.
4.  **Login Button**: Click this blue button to authenticate and access the dashboard.
5.  **Forgot Password**: (If enabled) A link to reset your password.

---

## 2. Dashboard
Upon successful login, you are directed to the main Dashboard. The view differs depending on your role (Employee vs Admin/Manager).

### A. Employee Dashboard
Designed for personal overview and self-service.
- **Header**: Displays a welcome message.
- **Employment Information**: A card showing your Department, Employment Status, Position/Cadre, and Date of Appointment.
- **Leave Management**:
    - **Request Leave Button**: Quick access to the leave request form.
    - **My Recent Leave Requests**: A list of your last 5 leave applications with status (Pending, Approved, Rejected).
    - **View All Button**: Link to your full leave history.
- **Recent Activities**: A log of your recent actions within the system (e.g., login, profile update).

### B. Admin/Manager Dashboard
Designed for workforce overview and analytics.
- **Statistics Summary**: A grid of boxes showing key metrics:
    - Total Employees, Active Employees, Suspended, Hold Status.
    - Permanent Staff, Contract Staff, Retired, Deceased.
    - Male/Female breakdown.
    - Department count.
    - Open/Resolved Disciplinary Actions.
    - Current Month Payrolls.
    - Retiring in 6 Months / Pending Retirement Confirmations.
    - **Pending Tasks**: Counts for Pending Approvals, Leave Requests, Payroll Approvals, Probation Reviews, Promotions, Employee Changes, Disciplinary Actions.
- **Employees Retiring Within 6 Months**: A detailed table listing employees nearing retirement, showing Name, Department, Grade Level, Estimated Date, and Reason (Old Age or Service Years).
- **Pending Retirement Confirmations**: A List of employees awaiting retirement confirmation with eligibility checks.
- **Department Distribution**: A list showing the number of employees in each department.
- **Recent Audit Trail**: A log of recent system activities by users.

---

## 3. Employees Management
Accessed via the "Employees" menu in the sidebar.

### Employee List
This is the main directory of all staff members.
- **Top Buttons**:
    - **Add Employee**: Navigate to the form to register a new employee.
    - **Probation Employees**: Quick filter for staff currently on probation.
- **Import Section**: Tool to bulk upload employees via Excel (.xlsx, .xls).
- **Search & Filter Options**: Click to expand advanced filtering criteria:
    - **Fields**: Department, Cadre, Status, Gender, Appointment Type, State of Origin, Grade Level.
    - **Ranges**: Age Range (From/To), Appointment Date Range (From/To).
    - **Probation Status**: Filter by Pending, Approved, or Rejected.
    - **Buttons**: "Search", "Clear", "Apply Filters", "Reset All".
- **Employee Table**: Displays a list of employees with columns for:
    - **Identity**: Staff No, Photo, Name.
    - **Position**: Department, Cadre, Pay Point, Appointment Type.
    - **Status**: Current status (Active, Suspended, etc.) and Probation status.
    - **Contact**: Phone and Email.
    - **Actions**: A dropdown menu for each employee:
        - **View**: See full employee profile.
        - **Probation Details**: (If applicable) Manage probation.
        - **Edit**: Modify employee details.
        - **Delete**: Remove employee record (requires reason).

### Add Employee (Registration)
Accessed via the "Add Employee" button on the Employee List page. This is a multi-step form.
- **Navigation**: Use "Next" and "Previous" buttons or click the tabs at the top (Personal, Contact, Work, Other, Next of Kin, Bank) to jump between sections.
- **Sections**:
    1.  **Personal**: First Name, Surname, Gender, Date of Birth, Nationality, State of Origin, LGA, Ward, Staff ID, NIN, Mobile No.
    2.  **Contact**: Email, Pay Point, Residential Address.
    3.  **Work**:
        - **Appointment Type**: Select Regular or Contract.
        - **Fields**: Date of First Appointment, Department, Salary Scale, Grade Level, Step, Rank, Expected Next Promotion.
        - **Contract Specifics**: Start/End Date, Contract Amount.
    4.  **Other**: Status (Active/Suspended/etc), Qualification (Highest Certificate), Photo Upload (Upload file or Use Camera).
    5.  **Next of Kin**: Full Name, Relationship, Contact Info, Address.
    6.  **Bank**: Bank Name, Account Name, Account Number (Verified against Bank Code).
- **Save**: Click "Save Employee" on the final step to submit.

### Pending Changes
Tracks requests for employee data updates that require approval.
- **Search & Filter**:
    - **Search**: By name or ID.
    - **Status**: Pending, Approved, Rejected.
    - **Change Type**: Create, Update, Delete.
- **Data Changes Table**: Lists modifications to employee records (e.g., name change, address update).
    - **Columns**: Employee, Change Type, Description, Requested By, Request Date, Status.
    - **Action**: "View" to see details.
- **Promotions/Demotions Table**: Lists pending rank changes.
    - **Columns**: Employee, Change Type (Promotion/Demotion), Grade Details (Old -> New), Reason, Status.
    - **Actions**: Approve or Reject buttons (for authorized users).

### Promotions & Demotions
A history and management view of all employee grade changes.
- **Top Button**: "New Promotion/Demotion" (to initiate a change).
- **Filters**: Search, Type, Status, Employee, Promotion Date.
- **Table**:
    - **Employee**: Name and ID.
    - **Type**: Badge indicating Promotion (Green) or Demotion (Orange).
    - **Details**: Previous Grade, New Grade, Promotion Date, Effective Date.
    - **Status**: Approved, Rejected, or Warning.
    - **Status**: Approved, Rejected, or Warning.
    - **Actions**: "View" button for details.

### Leave Management
Manage employee leave requests.
- **Top Button**: "Request Leave" (to submit a new application).
- **Table**:
    - **Employee**: Requester's name and department.
    - **Details**: Type of leave, Start/End Dates, Total Days, Reason.
    - **Status**: Pending (Orange), Approved (Green), Rejected (Red).
    - **Actions**:
        - **View**: See full request.
        - **Approve/Reject**: (For Approvers) Buttons to decide on the request.
        - **Delete**: Remove the request.

### Probation Management
Track and manage employees currently on probation.
- **Filters**: Search by Employee Name, Department, or Status (Pending/Approved/Rejected).
- **Table**:
    - **Staff Info**: Name and Staff Number.
    - **Timeline**: Start/End Dates and Days Remaining.
    - **Status**: Current probation status.
    - **Actions**:
        - **Approve**: Confirm employee after probation period (Active only if period ended).
        - **Reject**: Terminate probation (opens a modal to provide a reason).

### Bank Details Management
Focused interface for managing employee banking information.
- **Search**: Find employees by Name, ID, or Staff Number.
- **Table**: Lists current bank details (Bank Name, Code, Account Number/Name).
- **Actions**: "Update" button to modify banking information.

---

## 4. User Management
Accessed via the "User Management" menu. This section controls system access.

### Users
Manage system accounts.
- **Top Actions**:
    - **Create User**: Manually add a user.
    - **Auto Create Users**: (If employees exist without users) Bulk generate accounts based on email.
    - **View Employees Without Users**: List staff who can't login yet.
- **Stats**: Cards showing Total Users, Missing Accounts, and Role Counts.
- **Filters**: Search by username/email/employee or Filter by Role.
- **Table**:
    - **User Info**: Linked Employee, Username, Email.
    - **Role**: Current assigned role (Badge).
    - **Actions**:
        - **Update Role**: Change permissions.
        - **Reset Password**: Reset to default (`12345678`).
        - **Delete**: Remove user account (maintains employee record).

### Roles
Manage permission sets.
- **Table**: Lists Role Name, Assigned Permissions, and Actions (Edit/Delete).
- **Add Role**: Define a new role with specific system capabilities.

---

## 5. Departments
Manage organizational structure.
- **Add Department**: Create a new department unit.
- **Table**:
    - **Info**: Name and Description.
    - **Employees**: Click the number to see a modal list of all staff in that department.
    - **Actions**: Edit or Delete department.

---

## 6. Biometrics
Manage fingerprint/biometric registration status.
- **Filters**: Search by ID/Name, Status (Registered/Not Registered).
- **Table**:
    - **Staff Info**: ID, Name Department.
    - **Status**: Registered (Green) / Not Registered (Red).
    - **Verification**: Status and Date of last check.
    - **Status**: Registered (Green) / Not Registered (Red).
    - **Verification**: Status and Date of last check.
    - **Action**: "Register" button (if not yet registered) to capture data.

---

## 7. Audit Trails
Logs of all system activities for security and accountability.
- **Filters**:
    - **Search**: Text search logs.
    - **Action**: Filter by specific activity (Login, Employee Creation, Payroll, etc.).
    - **Date**: Start and End Date range.
    - **User**: Filter by specific administrator/user.
- **Table**:
    - **User**: Who performed the action.
    - **Action**: Type of action (Create/Update/Delete).
    - **Description**: Details of what changed.
    - **Timestamp**: Exact date and time.

---

## 8. Disciplinary Actions
Manage staff disciplinary records and sanctions.
- **Add Action**: Record a new disciplinary case.
- **Filters**: Search, Employee, Action Type (Query, Suspension, etc.), Department, Status.
- **Table**:
    - **Case Info**: Employee, Action Type, Description.
    - **Timing**: Action Date and Resolution Date.
    - **Status**: Current status of the case.
    - **Actions**: View, Edit, or Delete records.

---

## 9. Retirements
Manage the retirement lifecycle.
- **Views**: Toggle between "Approaching Retirement" (Active staff) and "Retired Employees".
- **Approaching Retirement**:
    - Lists staff near retirement age or max service years.
    - Shows Calculated vs Expected Date, Age, Service Years, and Reason (Old Age / Service Years).
    - **Confirm Retirement**: Button to process a retirement.
- **Retired Employees**:
    - Historical list of retired staff.
    - Includes Gratuity Amount.

---

## 10. Pensioners
Manage post-retirement benefits.
- **Table**:
    - **Retiree Info**: Name, Rank, Department.
    - **Financials**: Gratuity Status (Paid/Unpaid) and Pension Amount.
    - **Status**: Active (Winning Pension), Deceased, etc.
    - **Actions**:
        - **Pay Gratuity**: Mark the one-off gratuity payment as complete.
        - **Mark Deceased**: Stop future payments.

---

## 11. Reports
Generate system-wide documentation.
- **Generate Report**: Create a new report for a specific employee. (Select Type: Comprehensive, Payroll, Disciplinary, etc. and Format: PDF/Excel).
- **Comprehensive Reports**: Link to full system-wide analytics.
- **Report History**:
    - List of all previously generated reports.
    - **Download**: Retrieve past reports in their original format.

---

## 12. SMS Notifications
System communication log.
- **Send SMS**: Manually trigger a message to staff.
- **Table**:
    - **Sender/Recipient**: Who sent it and to whom.
    - **Message**: Content of the text.
    - **Status**: Delivery confirmation.

---

## 13. Payroll Management
Comprehensive tools for salary processing.

### Generate Payroll
Process payments for a specific period.
- **Top Panel**: Select "Month", "Category" (Active Staff, Pensioners, or Gratuity), and "Appointment Type".
- **Action**: Click "Generate" to calculate salaries based on current scales and data.

### Manage Records
- **Filters**: Extensive options to filter by Status (Pending, Paid, etc.), Month, Salary Range, and Department.
- **Table**:
    - **Employee Info**: Staff details including Grade Level and Step.
    - **Financials**: Basic Salary, Total Additions, Total Deductions, and Net Salary.
        - *Note*: Click/Hover on Additions or Deductions to see the breakdown of individual items.
    - **Status**: Visual badge indicating workflow stage (e.g., Under Review, Approved).
    - **Actions**:
        - **View Details**: Full breakdown.
        - **Download Payslip**: Generate individual PDF payslip.
        - **Manage Additions/Deductions**: Add specific allowances or fines for this employee.

### Bulk Operations
Perform actions on multiple records at once.
- **Selection**: Use checkboxes to select rows (or apply to all filtered results).
- **Actions**: Bulk Send for Review, Final Approve, Update Status, or Delete.

### Salary Scales
Define the pay structure.
- **Management**: Create and edit salary scales (e.g., CONPSS, CONMESS).
- **Structures**: Define Grade Levels and Steps within each scale.
- **Rules**: Set Max Retirement Age and Years of Service per scale.

---

## 14. Conclusion
This system centralizes your HR and Payroll operations. For technical support or system configuration changes (such as defining new roles or core settings), please contact the system administrator.
