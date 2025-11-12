<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmployeesSeeder extends Seeder
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
        $salaryScales = DB::table('salary_scales')->pluck('id');
        $gradeLevels = DB::table('grade_levels')->pluck('id');
        $steps = DB::table('steps')->pluck('id');
        $ranks = DB::table('ranks')->pluck('id');
        $appointmentTypes = DB::table('appointment_types')->pluck('id');

        // Get Katsina state ID
        $katsinaState = DB::table('states')->where('name', 'katsina')->first();
        $katsinaStateId = $katsinaState ? $katsinaState->state_id : null;
        
        // Get all LGAs for Katsina state only
        $katsinaLgas = $katsinaStateId ? DB::table('lgas')->where('state_id', $katsinaStateId)->pluck('id') : collect([]);
        
        // Get all wards (this will be filtered per LGA when creating each employee)
        $wards = DB::table('wards')->pluck('ward_id');

        // If no data, create some sample ones
        if ($departments->isEmpty()) {
            $departments = collect([1, 2, 3, 4, 5]);
        }
        if ($cadres->isEmpty()) {
            $cadres = collect([1, 2, 3, 4, 5]);
        }
        if ($salaryScales->isEmpty()) {
            $salaryScales = collect([1, 2, 3, 4, 5, 15]);
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
            // Only use appointment types with IDs 1 and 2 as requested
            $appointmentTypes = collect([1, 2]);
        }

        // If no data, create some sample ones
        if ($departments->isEmpty()) {
            $departments = collect([1, 2, 3, 4, 5]);
        }
        if ($cadres->isEmpty()) {
            $cadres = collect([1, 2, 3, 4, 5]);
        }
        if ($salaryScales->isEmpty()) {
            $salaryScales = collect([1, 2, 3, 4, 5, 15]);
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
            // Only use appointment types with IDs 1 and 2 as requested
            $appointmentTypes = collect([1, 2]);
        }
        
        // Get the next available employee ID
        $nextId = DB::table('employees')->max('employee_id') + 1;
        if ($nextId === null) {
            $nextId = 1;
        }
        
        // Generate 200 employees
        $employees = [];
        $nextOfKin = [];
        $bankDetails = [];
        
        for ($i = $nextId; $i < $nextId + 200; $i++) {
            $firstName = $this->getRandomNigerianFirstName();
            $surname = $this->getRandomNigerianSurname();
            $middleName = rand(0, 3) > 1 ? $this->getRandomNigerianFirstName() : null;
            $bankName = $this->getRandomNigerianBankName();
            
            // Generate age between 18 and 65 years (age should not be under 18)
            $age = rand(18, 60); // Using 60 to ensure retirement happens before 65
            $dateOfBirth = date('Y-m-d', strtotime('-' . $age . ' years'));
            
            // Generate years of service between 0 and 35 years
            $yearsOfService = min(rand(0, 35), $age - 18); // Ensure years of service doesn't exceed working years
            $dateOfFirstAppointment = date('Y-m-d', strtotime('-' . $yearsOfService . ' years'));
            
            // Select appointment type (1 or 2)
            $appointmentTypeId = rand(1, 2); // Only 1 or 2 as requested
            $appointmentType = DB::table('appointment_types')->where('id', $appointmentTypeId)->first();
            
            // Calculate retirement date based on max age 65 or max years of service 35
            $retirementDate = $this->calculateRetirementDate($dateOfBirth, $dateOfFirstAppointment, $age, $yearsOfService);
            
            // Generate Nigerian phone number
            $mobileNo = $this->getRandomNigerianPhoneNumber();
            
            // Generate pay point
            $payPoint = $this->getRandomPayPoint();

            // Select random LGA from Katsina state
            $lgaId = $katsinaLgas->isNotEmpty() ? $katsinaLgas->random() : null;
            
            // Get wards for the selected LGA
            $lgaWards = $lgaId ? DB::table('wards')->where('lga_id', $lgaId)->pluck('ward_id') : $wards;
            $wardId = $lgaWards->isNotEmpty() ? $lgaWards->random() : $wards->random();

            // Initialize the employee array with all possible fields for consistency
            $employee = [
                'employee_id' => $i,
                'staff_no' => 'REG' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'surname' => $surname,
                'middle_name' => $middleName,
                'gender' => rand(0, 1) ? 'Male' : 'Female',
                'date_of_birth' => $dateOfBirth,
                'state_id' => $katsinaStateId,
                'lga_id' => $lgaId,
                'ward_id' => $wardId,
                'nationality' => 'Nigeria',
                'nin' => $this->generateNIN(),
                'mobile_no' => $mobileNo,
                'email' => strtolower($firstName . '.' . $surname . $i . '@company.com'),
                'pay_point' => $payPoint, // Add pay point as requested
                'address' => rand(1, 1000) . ' ' . $this->getRandomStreet() . ', ' . $this->getRandomCity(),
                'date_of_first_appointment' => $dateOfFirstAppointment,
                'appointment_type_id' => $appointmentTypeId,
                'status' => collect(['Active', 'Suspended'])->random(), // Avoid Retired/Deceased for new employees
                'highest_certificate' => $this->getRandomCertificate(),
                'years_of_service' => $yearsOfService,
                'created_at' => now(),
                'updated_at' => now(),
                
                // Contract fields - initially null
                'contract_start_date' => null,
                'contract_end_date' => null,
                'amount' => null,
                
                // Permanent employee fields - initially null
                'cadre_id' => null,
                'grade_level_id' => null,
                'step_id' => null,
                'department_id' => null,
                'expected_next_promotion' => null,
                'expected_retirement_date' => null,
                'rank_id' => null,
            ];
            
            // Apply validation rules based on appointment type (Permanent vs Contract)
            if ($appointmentTypeId == 2) { // Contract employees have appointment_type_id = 2
                // Add contract-specific fields
                $contractStartDate = $dateOfFirstAppointment;
                $contractEndDate = date('Y-m-d', strtotime($contractStartDate . ' + 2 years')); // 2-year contract
                $amount = rand(100000, 500000); // Random contract amount
                
                $employee['contract_start_date'] = $contractStartDate;
                $employee['contract_end_date'] = $contractEndDate;
                $employee['amount'] = $amount;
                // Contract employees should also have a department
                $employee['department_id'] = $departments->random();
            } else {
                // Add permanent employee fields
                $employee['cadre_id'] = $cadres->random();
                $employee['grade_level_id'] = $gradeLevels->random(); // Use random grade level instead of hardcoded 15
                $employee['step_id'] = $steps->random();
                $employee['department_id'] = $departments->random();
                $employee['expected_next_promotion'] = date('Y-m-d', strtotime('+' . rand(1, 3) . ' years'));
                $employee['expected_retirement_date'] = $retirementDate;
                $employee['rank_id'] = $ranks->random();
                
                // Basic salary is now calculated dynamically based on step, so we don't need to set it here
                // The system will calculate it when needed based on the employee's step_id
            }
            
            $employees[] = $employee;
            
            // Add next of kin
            $nextOfKin[] = [
                'employee_id' => $i,
                'name' => $this->getRandomNigerianFirstName() . ' ' . $this->getRandomNigerianSurname(),
                'relationship' => collect(['Spouse', 'Father', 'Mother', 'Brother', 'Sister', 'Uncle', 'Aunt'])->random(),
                'mobile_no' => $this->getRandomNigerianPhoneNumber(),
                'address' => rand(1, 1000) . ' ' . $this->getRandomStreet() . ', ' . $this->getRandomCity(),
                'occupation' => $this->getRandomOccupation(),
                'place_of_work' => $this->getRandomCompany(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Add bank details
            $bankDetails[] = [
                'employee_id' => $i,
                'bank_name' => $bankName,
                'bank_code' => $this->getBankCode($bankName),
                'account_name' => $firstName . ' ' . $surname,
                'account_no' => $this->generateNigerianAccountNumber(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert data
        DB::table('employees')->insert($employees);
        DB::table('next_of_kin')->insert($nextOfKin);
        DB::table('banks')->insert($bankDetails);
        
        // Re-enable foreign key constraints
        Schema::enableForeignKeyConstraints();
    }
    
    /**
     * Calculate retirement date based on max age 65 or max years of service 35
     */
    private function calculateRetirementDate($dateOfBirth, $dateOfFirstAppointment, $currentAge, $currentYearsOfService)
    {
        $birthDate = new \DateTime($dateOfBirth);
        $appointmentDate = new \DateTime($dateOfFirstAppointment);
        
        // Retirement based on age: person born in year X, retire at age 65 by year X+65
        $retirementByAge = (clone $birthDate)->add(new \DateInterval('P65Y'));
        
        // Retirement based on years of service: appointed in year Y, retire after 35 years in year Y+35
        $retirementByService = (clone $appointmentDate)->add(new \DateInterval('P35Y'));
        
        // Take the earlier retirement date
        if ($retirementByAge < $retirementByService) {
            return $retirementByAge->format('Y-m-d');
        } else {
            return $retirementByService->format('Y-m-d');
        }
    }
    
    private function getRandomNigerianFirstName()
    {
        $firstNames = [
            'Adunni', 'Adebayo', 'Adedayo', 'Adeola', 'Adepeju', 'Aderonke', 'Adunni', 'Aishat', 'Aisha', 'Akin', 
            'Bukola', 'Busola', 'Bimbo', 'Bisi', 'Chinedu', 'Chinwe', 'Chioma', 'Emeka', 'Ezinne', 'Ebere',
            'Funmi', 'Folake', 'Gbemisola', 'Grace', 'Hassana', 'Halima', 'Ifeoma', 'Ibrahim', 'Jumoke', 'Kemi',
            'Kikelomo', 'Lanre', 'Lekan', 'Mubarak', 'Mojisola', 'Mojisola', 'Ngozi', 'Nkem', 'Nkiru', 'Nnamdi',
            'Obiageli', 'Olamide', 'Omowumi', 'Omotola', 'Omobola', 'Oyin', 'Paul', 'Rasheedah', 'Sikiru', 'Titi',
            'Tunde', 'Uche', 'Uchechukwu', 'Wasiu', 'Yusuf', 'Zainab', 'Zara', 'Aminat', 'Afolabi', 'Ahmad',
            'Taiwo', 'Kehinde', 'Babatunde', 'Ifeanyi', 'Ngozi', 'Chidi', 'Chukwu', 'Chinaza', 'Chidimma', 'Chidiebere',
            'Ugochi', 'Ujunwa', 'Uzochi', 'Uchenna', 'Uchechi', 'Onyinye', 'Nwamaka', 'Nkechi', 'Ngozika', 'Nwankwo',
            'John', 'Mary', 'Michael', 'Sarah', 'David', 'Lisa', 'James', 'Jennifer', 'Robert', 'Patricia',
            'William', 'Linda', 'Thomas', 'Elizabeth', 'Christopher', 'Barbara', 'Daniel', 'Susan', 'Matthew', 'Jessica'
        ];
        return $firstNames[array_rand($firstNames)];
    }
    
    private function getRandomNigerianSurname()
    {
        $surnames = [
            'Abiodun', 'Adebayo', 'Adeduntan', 'Adegoke', 'Adejumo', 'Adeleke', 'Adeniyi', 'Adesina', 'Adeyemi', 'Ajiboye',
            'Akintola', 'Aliyu', 'Amodu', 'Anifowoshe', 'Asaaju', 'Azeez', 'Balogun', 'Bamidele', 'Bankole', 'Bello',
            'Busari', 'Chukwu', 'Dauda', 'David', 'Ebiti', 'Eze', 'Ezeala', 'Falana', 'Fashina', 'Gbajumo',
            'Hassan', 'Igbinedion', 'Iwu', 'Igbo', 'Isiaka', 'Jaja', 'Kalu', 'Ladele', 'Mbanefo', 'Mohammed',
            'Mustapha', 'Nwankwo', 'Nwosu', 'Odetola', 'Odeyemi', 'Okafor', 'Okonkwo', 'Olawale', 'Olufunmi', 'Omololu',
            'Omorede', 'Omosule', 'Onyima', 'Orji', 'Osagie', 'Osunde', 'Oyesiku', 'Oyinloye', 'Oyinlola', 'Ozoigbo',
            'Patel', 'Popoola', 'Salami', 'Sanni', 'Shittu', 'Sobowale', 'Soneye', 'Sule', 'Taiwo', 'Udom',
            'Uwakwe', 'Wahab', 'Yusuf', 'Zaynab', 'Adebisi', 'Adebowale', 'Adegboye', 'Adenuga', 'Adepoju', 'Aderibigbe',
            'Adesanya', 'Adeyemo', 'Afolabi', 'Agboola', 'Agoi', 'Ahmad', 'Ajumobi', 'Akande', 'Akinyemi', 'Akpan',
            'Ali', 'Aliyu', 'Aluko', 'Amarachi', 'Aminu', 'Amusa', 'Ani', 'Aniefiok', 'Aniok', 'Anozie',
            'Awosika', 'Ayeni', 'Ayodele', 'Azikiwe', 'Bakare', 'Balogun', 'Bamisebi', 'Bashir', 'Bassey', 'Bature',
            'Emmanuel', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez',
            'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
            'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
            'Walker', 'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores'
        ];
        return $surnames[array_rand($surnames)];
    }
    
    private function getRandomStreet()
    {
        $streets = [
            'Ahmadu Bello Way', 'Tafawa Balewa Way', 'Herbert Macaulay Way', 'Broad Street', 'Marina Road',
            'Allen Avenue', 'Ikorodu Road', 'Lekki-Epe Expressway', 'Obafemi Awolowo Way', 'University Road',
            'Opebi Road', 'Ogba Road', 'Lagos-Badagry Expressway', 'Congo Road', 'Kaduna Road',
            'Ojuelegba Road', 'Oshodi-APC Junction', 'Isheri Road', 'Ikorodu Road', 'Lekki Phase 1',
            'Main Street', 'Park Avenue', 'Oak Street', 'Pine Street', 'Maple Avenue', 'Cedar Road', 
            'Elm Street', 'Washington Boulevard', 'Lake Street', 'Hillside Drive'
        ];
        return $streets[array_rand($streets)];
    }
    
    private function getRandomCity()
    {
        $cities = [
            'Lagos', 'Abuja', 'Kano', 'Port Harcourt', 'Ibadan', 'Benin City', 'Kaduna', 'Onitsha', 
            'Ilorin', 'Enugu', 'Katsina', 'Jos', 'Sokoto', 'Calabar', 'Warri', 'Minna', 'Oshogbo', 
            'Akure', 'Ogbomosho', 'Ife', 'Ila', 'Oyo', 'Offa', 'Ikorodu', 'Aba', 'Umuahia', 'Asaba',
            'Owerri', 'Awka', 'Nsukka', 'Makurdi', 'Gombe', 'Yola', 'Jalingo', 'Bauchi', 'Sokoto',
            'Zaria', 'Bida', 'Lafia', 'Ikom', 'Ondo', 'Biu', 'Dutse', 'Gashua', 'Keffi'
        ];
        return $cities[array_rand($cities)];
    }
    
    private function getRandomOccupation()
    {
        $occupations = [
            'Teacher', 'Engineer', 'Doctor', 'Nurse', 'Lawyer', 'Accountant', 'Businessman', 'Farmer',
            'Trader', 'Civil Servant', 'Student', 'Artist', 'Musician', 'Writer', 'Journalist', 'Chef',
            'Driver', 'Security Guard', 'Tailor', 'Hairdresser', 'Mechanic', 'Plumber', 'Carpenter',
            'Electrician', 'Banker', 'Pilot', 'Diplomat', 'Dentist', 'Pharmacist', 'Veterinarian',
            'Architect', 'Surveyor', 'Geologist', 'IT Specialist', 'Web Developer', 'Software Engineer',
            'Data Analyst', 'Social Worker', 'Psychologist', 'Dermatologist', 'Pediatrician', 'Surgeon',
            'Veterinary Doctor', 'Optometrist', 'Laboratory Scientist', 'Radiographer', 'Pharmacist',
            'Human Resource Manager', 'Marketing Manager', 'Sales Executive', 'Accountant', 'Auditor'
        ];
        return $occupations[array_rand($occupations)];
    }
    
    private function getRandomCompany()
    {
        $companies = [
            'Nigeria National Petroleum Corporation', 'Nigerian Communications Satellite Limited', 'Arik Air', 
            'Air Peace', 'Emirates Nigerial Airlines', 'Access Bank', 'UBA', 'First Bank', 'GTBank',
            'Zenith Bank', 'Fidelity Bank', 'Stanbic IBTC', 'Jaiz Bank', 'FCMB', 'Heritage Bank',
            'Nigeria Export Import Bank', 'Nigeria Industrial Development Bank', 'Nigerian Army', 
            'Nigeria Police Force', 'Nigerian Air Force', 'Nigerian Navy', 'Nigerian Prisons Service',
            'Nigerian Security and Civil Defence Corps', 'Nigerian Immigration Service', 'Nigerian Customs Service',
            'Nigerian Ports Authority', 'Nigeria Airports Authority', 'Federal Airports Authority of Nigeria',
            'Nigerian Television Authority', 'Federal Road Safety Corps', 'Central Bank of Nigeria',
            'Nigerian Stock Exchange', 'National Insurance Commission', 'Federal Ministry of Education',
            'Federal Ministry of Health', 'Federal Ministry of Agriculture', 'Nigerian Television Authority',
            'Channels Television', 'Nollywood', 'MNet', 'DStv', 'Nigerian Breweries', 'Nestle Nigeria',
            'PZ Cussons Nigeria', 'Unilever Nigeria', 'Coca-Cola Bottling Company', 'Nigerian Bottling Company',
            'MTN Nigeria', 'Airtel Nigeria', 'Glo Mobile', '9mobile', 'Nigeria Liquefied Natural Gas',
            'Dangote Group', 'Bua Cement', 'Cement Company of Northern Nigeria', 'United Bank for Africa',
            'First Bank of Nigeria', 'Guaranty Trust Bank', 'Fidelity Bank', 'Access Bank', 'Stanbic IBTC'
        ];
        return $companies[array_rand($companies)];
    }

    private function getRandomNigerianBankName()
    {
        $banks = [
            'Access Bank', 'Citibank Nigeria', 'Ecobank Nigeria', 'Fidelity Bank', 'First Bank of Nigeria', 
            'First City Monument Bank', 'Globus Bank', 'Guaranty Trust Bank', 'Heritage Bank', 'Keystone Bank', 
            'Polaris Bank', 'Providus Bank', 'Stanbic IBTC Bank', 'Standard Chartered Bank', 'Sterling Bank', 
            'Titan Trust Bank', 'Union Bank of Nigeria', 'United Bank for Africa', 'Unity Bank', 'Wema Bank', 
            'Zenith Bank', 'Jaiz Bank', 'FCMB', 'SunTrust Bank', 'Parallex Bank'
        ];
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
            'Jaiz Bank' => '301',
            'FCMB' => '280',
            'SunTrust Bank' => '100',
            'Parallex Bank' => '104'
        ];
        return $bankCodes[$bankName] ?? rand(100, 999);
    }

    private function getRandomCertificate()
    {
        $certificates = [
            'No formal education', 
            'Primary education', 
            'Secondary education / High school or equivalent', 
            'Vocational qualification', 
            'Associate degree / NCE / ND', 
            'Bachelor’s degree', 
            'Professional degree/license', 
            'Master’s degree', 
            'Doctorate / Ph.D. or higher'
        ];
        return $certificates[array_rand($certificates)];
    }
    
    /**
     * Generate Nigerian phone number in various valid formats
     */
    private function getRandomNigerianPhoneNumber()
    {
        $prefixes = [
            '0701', '0702', '0703', '0704', '0801', '0802', '0803', '0804', '0805', '0806',
            '0807', '0808', '0809', '0810', '0811', '0812', '0813', '0814', '0815', '0816',
            '0817', '0818', '0819', '0901', '0902', '0903', '0904', '0905', '0906', '0907',
            '0908', '0909'
        ];
        
        $prefix = $prefixes[array_rand($prefixes)];
        $number = $prefix . rand(1000000, 9999999); // 4 more digits
        
        return $number;
    }
    
    /**
     * Generate a valid Nigerian Account Number
     */
    private function generateNigerianAccountNumber()
    {
        return rand(1000000000, 9999999999);
    }
    
    /**
     * Generate Nigerian NIN
     */
    private function generateNIN()
    {
        return 'NIN' . rand(10000000000, 99999999999);
    }
    
    /**
     * Generate a random pay point
     */
    private function getRandomPayPoint()
    {
        $payPoints = [
            'Abuja Main',
            'Lagos Central',
            'Kano State',
            'Port Harcourt',
            'Ibadan Municipal',
            'Enugu Central',
            'Kaduna South',
            'Benin City',
            'Jos Plateau',
            'Owerri Imo',
            'Onitsha Anambra',
            'Bauchi State',
            'Maiduguri',
            'Sokoto State',
            'Calabar Cross River',
            'Gombe State'
        ];
        
        return $payPoints[array_rand($payPoints)];
    }
}