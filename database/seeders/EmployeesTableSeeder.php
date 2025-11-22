<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Disable foreign key constraints
        Schema::disableForeignKeyConstraints();
        
        // Get existing data
        $departments = DB::table('departments')->pluck('department_id');
        $cadres = DB::table('cadres')->pluck('cadre_id');
        $gradeLevels = DB::table('grade_levels')->pluck('id');
        $steps = DB::table('steps')->pluck('id');
        $ranks = DB::table('ranks')->pluck('id');
        $states = DB::table('states')->pluck('state_id');
        $lgas = DB::table('lgas')->pluck('id');
        $wards = DB::table('wards')->pluck('ward_id');
        $appointmentTypes = DB::table('appointment_types')->pluck('id');
        
        // If no data, create some sample ones
        if ($departments->isEmpty()) {
            $departments = collect([1, 2, 3, 4, 5]);
        }
        if ($cadres->isEmpty()) {
            $cadres = collect([1, 2, 3, 4, 5]);
        }
        if ($gradeLevels->isEmpty()) {
            $gradeLevels = collect([1, 2, 3, 4, 5]);
        }
        if ($steps->isEmpty()) {
            $steps = collect([1, 2, 3, 4, 5]);
        }
        if ($ranks->isEmpty()) {
            $ranks = collect([1, 2, 3, 4, 5]);
        }
        if ($appointmentTypes->isEmpty()) {
            $appointmentTypes = collect([1, 2, 3]);
        }
        
        // Get the next available employee ID
        $nextId = DB::table('employees')->max('employee_id') + 1;
        if ($nextId === null) {
            $nextId = 1;
        }
        
        // Generate 200 employees
        $employees = [];
        $nextOfKin = [];
        $banks = [];
        
        for ($i = $nextId; $i < $nextId + 200; $i++) {
            $firstName = $this->getRandomFirstName();
            $surname = $this->getRandomSurname();
            $middleName = rand(0, 3) > 1 ? $this->getRandomFirstName() : null;
            $bankName = $this->getRandomBank();

            $employee = [
                'employee_id' => $i,
                'reg_no' => 'REG' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'surname' => $surname,
                'middle_name' => $middleName,
                'gender' => rand(0, 1) ? 'Male' : 'Female',
                'date_of_birth' => date('Y-m-d', strtotime('-' . rand(22, 65) . ' years')),
                'state_id' => $states->random(),
                'lga_id' => $lgas->random(),
                'ward_id' => $wards->random(),
                'nationality' => $this->getRandomNationality(),
                'nin' => 'NIN' . rand(100000000, 999999999),
                'mobile_no' => '080' . rand(10000000, 99999999),
                'email' => strtolower($firstName . '.' . $surname) . $i . '@company.com',
                'address' => rand(1, 1000) . ' ' . $this->getRandomStreet() . ', ' . $this->getRandomCity(),
                'date_of_first_appointment' => date('Y-m-d', strtotime('-' . rand(1, 20) . ' years')),
                'cadre_id' => $cadres->random(),
                'grade_level_id' => $gradeLevels->random(),
                'step_id' => $steps->random(),
                'rank_id' => $ranks->random(),
                'department_id' => $departments->random(),
                'expected_next_promotion' => date('Y-m-d', strtotime('+' . rand(1, 3) . ' years')),
                'expected_retirement_date' => date('Y-m-d', strtotime('+' . rand(5, 35) . ' years')),
                'status' => 'Active', // Set all employees to active
                'highest_certificate' => $this->getRandomCertificate(),
                'grade_level_limit' => rand(1, 17),
                'appointment_type_id' => $appointmentTypes->random(),
                'photo_path' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $employees[] = $employee;
            
            // Add next of kin
            $nextOfKin[] = [
                'employee_id' => $i,
                'name' => $this->getRandomFirstName() . ' ' . $this->getRandomSurname(),
                'relationship' => collect(['Spouse', 'Father', 'Mother', 'Brother', 'Sister', 'Uncle', 'Aunt'])->random(),
                'mobile_no' => '080' . rand(10000000, 99999999),
                'address' => rand(1, 1000) . ' ' . $this->getRandomStreet() . ', ' . $this->getRandomCity(),
                'occupation' => $this->getRandomOccupation(),
                'place_of_work' => $this->getRandomCompany(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Add bank details
            $banks[] = [
                'employee_id' => $i,
                'bank_name' => $bankName,
                'bank_code' => $this->getBankCode($bankName),
                'account_name' => $firstName . ' ' . $surname,
                'account_no' => rand(1000000000, 9999999999),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert data
        DB::table('employees')->insert($employees);
        DB::table('next_of_kin')->insert($nextOfKin);
        DB::table('banks')->insert($banks);
        
        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
    
    private function getRandomFirstName()
    {
        $firstNames = ['John', 'Mary', 'Michael', 'Sarah', 'David', 'Lisa', 'James', 'Jennifer', 'Robert', 'Patricia', 'William', 'Linda', 'Thomas', 'Elizabeth', 'Christopher', 'Barbara', 'Daniel', 'Susan', 'Matthew', 'Jessica', 'Anthony', 'Karen', 'Mark', 'Nancy', 'Donald', 'Margaret', 'Steven', 'Betty', 'Paul', 'Sandra', 'Andrew', 'Ashley', 'Joshua', 'Kimberly', 'Kenneth', 'Kevin', 'Brian', 'Michelle'];
        return $firstNames[array_rand($firstNames)];
    }
    
    private function getRandomSurname()
    {
        $surnames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores'];
        return $surnames[array_rand($surnames)];
    }
    
    private function getRandomStreet()
    {
        $streets = ['Main Street', 'Park Avenue', 'Oak Street', 'Pine Street', 'Maple Avenue', 'Cedar Road', 'Elm Street', 'Washington Boulevard', 'Lake Street', 'Hillside Drive'];
        return $streets[array_rand($streets)];
    }
    
    private function getRandomCity()
    {
        $cities = ['Lagos', 'Abuja', 'Kano', 'Port Harcourt', 'Ibadan', 'Benin City', 'Kaduna', 'Onitsha', 'Ilorin', 'Enugu'];
        return $cities[array_rand($cities)];
    }
    
    private function getRandomOccupation()
    {
        $occupations = ['Teacher', 'Engineer', 'Doctor', 'Nurse', 'Lawyer', 'Accountant', 'Businessman', 'Farmer', 'Trader', 'Civil Servant', 'Student', 'Artist', 'Musician', 'Writer', 'Journalist', 'Chef', 'Driver', 'Security Guard', 'Tailor', 'Hairdresser'];
        return $occupations[array_rand($occupations)];
    }
    
    private function getRandomCompany()
    {
        $companies = ['Tech Solutions Ltd', 'Global Enterprises', 'Prime Services', 'National Bank', 'State Insurance', 'City Hospital', 'Metro University', 'Capital Group', 'United Industries', 'Continental Corporation'];
        return $companies[array_rand($companies)];
    }
    
    private function getRandomBank()
    {
        $banks = ['Access Bank', 'Citibank Nigeria', 'Ecobank Nigeria', 'Fidelity Bank', 'First Bank of Nigeria', 'First City Monument Bank', 'Globus Bank', 'Guaranty Trust Bank', 'Heritage Bank', 'Keystone Bank', 'Polaris Bank', 'Providus Bank', 'Stanbic IBTC Bank', 'Standard Chartered Bank', 'Sterling Bank', 'Titan Trust Bank', 'Union Bank of Nigeria', 'United Bank for Africa', 'Unity Bank', 'Wema Bank', 'Zenith Bank'];
        return $banks[array_rand($banks)];
    }

    private function getBankCode($bankName)
    {
        $bankCodes = [
            'Access Bank' => '044',
            'Citibank Nigeria' => '023',
            'Ecobank Nigeria' => '050',
            'Fidelity Bank' => '070',
            'First Bank of Nigeria' => '011',
            'First City Monument Bank' => '214',
            'Globus Bank' => '103',
            'Guaranty Trust Bank' => '058',
            'Heritage Bank' => '030',
            'Keystone Bank' => '082',
            'Polaris Bank' => '076',
            'Providus Bank' => '101',
            'Stanbic IBTC Bank' => '221',
            'Standard Chartered Bank' => '068',
            'Sterling Bank' => '232',
            'Titan Trust Bank' => '102',
            'Union Bank of Nigeria' => '032',
            'United Bank for Africa' => '033',
            'Unity Bank' => '215',
            'Wema Bank' => '035',
            'Zenith Bank' => '057',
        ];
        return $bankCodes[$bankName] ?? rand(100, 999);
    }

    private function getRandomNationality()
    {
        $nationalities = ['Nigeria', 'Benin', 'Cameroon', 'Chad', 'Ghana', 'Niger', 'Togo', 'Burkina Faso', 'Equatorial Guinea', 'Central African Republic'];
        return $nationalities[array_rand($nationalities)];
    }

    private function getRandomCertificate()
    {
        $certificates = ['No formal education', 'Primary education', 'Secondary education / High school or equivalent', 'Vocational qualification', 'Associate degree / NCE / ND', 'Bachelor’s degree', 'Professional degree/license', 'Master’s degree', 'Doctorate / Ph.D. or higher'];
        return $certificates[array_rand($certificates)];
    }
}