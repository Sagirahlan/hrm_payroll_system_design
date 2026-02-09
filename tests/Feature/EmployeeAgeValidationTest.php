<?php

use App\Models\User;
use App\Models\AppointmentType;
use App\Models\State;
use App\Models\Lga;
use App\Models\Department;
use App\Models\Rank;
use App\Models\GradeLevel;
use App\Models\Cadre;
use App\Models\SalaryScale;
use App\Models\Step;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->givePermissionTo('create_employees');

    // Seed necessary data
    $this->permanentType = AppointmentType::create(['name' => 'Permanent']);
    $this->casualType = AppointmentType::create(['name' => 'Casual']);
    
    $this->state = State::create(['name' => 'Test State', 'state_id' => 1]);
    $this->lga = Lga::create(['name' => 'Test LGA', 'state_id' => 1]);
    $this->department = Department::create(['name' => 'Test Dept', 'code' => 'TD']);
    $this->rank = Rank::create(['name' => 'Test Rank']);
    $this->gradeLevel = GradeLevel::create(['name' => 'GL01']);
    $this->cadre = Cadre::create(['name' => 'Test Cadre']);
    $this->salaryScale = SalaryScale::create(['name' => 'Test Scale', 'slug' => 'test-scale']);
    $this->step = Step::create(['name' => 'Step 1', 'salary_scale_id' => $this->salaryScale->id, 'grade_level_id' => $this->gradeLevel->id]);
});

test('permanent staff older than 45 cannot be created', function () {
    $data = [
        'first_name' => 'John',
        'surname' => 'Doe',
        'gender' => 'Male',
        'date_of_birth' => now()->subYears(46)->format('Y-m-d'), // 46 years old
        'date_of_first_appointment' => now()->format('Y-m-d'),
        'appointment_type_id' => $this->permanentType->id,
        'state_id' => $this->state->state_id,
        'lga_id' => $this->lga->id,
        'nationality' => 'Nigerian',
        'nin' => '12345678901',
        'staff_no' => 'STAFF001',
        'mobile_no' => '08012345678',
        'pay_point' => 'Headquarters',
        'address' => 'Test Address',
        'status' => 'Active',
        'kin_name' => 'Jane Doe',
        'kin_relationship' => 'Sister',
        'kin_mobile_no' => '08087654321',
        'kin_address' => 'Kin Address',
        'bank_name' => 'Test Bank',
        'bank_code' => '001',
        'account_name' => 'John Doe',
        'account_no' => '1234567890',
        'department_id' => $this->department->department_id,
        // Permanent required fields
        'cadre_id' => $this->cadre->cadre_id,
        'salary_scale_id' => $this->salaryScale->id,
        'grade_level_id' => $this->gradeLevel->id,
        'step_id' => $this->step->id,
        'rank_id' => $this->rank->id,
        'expected_retirement_date' => now()->addYears(10)->format('Y-m-d'),
    ];

    $response = $this->actingAs($this->user)
        ->post(route('employees.store'), $data);

    $response->assertSessionHasErrors(['date_of_birth']);
});

test('permanent staff under 45 CAN be created', function () {
    $data = [
        'first_name' => 'John',
        'surname' => 'Doe',
        'gender' => 'Male',
        'date_of_birth' => now()->subYears(30)->format('Y-m-d'), // 30 years old
        'date_of_first_appointment' => now()->format('Y-m-d'),
        'appointment_type_id' => $this->permanentType->id,
        'state_id' => $this->state->state_id,
        'lga_id' => $this->lga->id,
        'nationality' => 'Nigerian',
        'nin' => '12345678901',
        'staff_no' => 'STAFF002',
        'mobile_no' => '08012345678',
        'pay_point' => 'Headquarters',
        'address' => 'Test Address',
        'status' => 'Active',
        'kin_name' => 'Jane Doe',
        'kin_relationship' => 'Sister',
        'kin_mobile_no' => '08087654321',
        'kin_address' => 'Kin Address',
        'bank_name' => 'Test Bank',
        'bank_code' => '001',
        'account_name' => 'John Doe',
        'account_no' => '1234567890',
        'department_id' => $this->department->department_id,
        // Permanent required fields
        'cadre_id' => $this->cadre->cadre_id,
        'salary_scale_id' => $this->salaryScale->id,
        'grade_level_id' => $this->gradeLevel->id,
        'step_id' => $this->step->id,
        'rank_id' => $this->rank->id,
        'expected_retirement_date' => now()->addYears(20)->format('Y-m-d'),
    ];

    $response = $this->actingAs($this->user)
        ->post(route('employees.store'), $data);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employees.index'));
});

test('casual staff older than 45 CAN be created', function () {
    $data = [
        'first_name' => 'Jane',
        'surname' => 'Casual',
        'gender' => 'Female',
        'date_of_birth' => now()->subYears(50)->format('Y-m-d'), // 50 years old
        'date_of_first_appointment' => now()->format('Y-m-d'),
        'appointment_type_id' => $this->casualType->id,
        'state_id' => $this->state->state_id,
        'lga_id' => $this->lga->id,
        'nationality' => 'Nigerian',
        'nin' => '09876543210',
        'staff_no' => 'CAS001',
        'mobile_no' => '07012345678',
        'pay_point' => 'Site A',
        'address' => 'Casual Address',
        'status' => 'Active',
        'kin_name' => 'John Casual',
        'kin_relationship' => 'Brother',
        'kin_mobile_no' => '07087654321',
        'kin_address' => 'Kin Address',
        'bank_name' => 'Casual Bank',
        'bank_code' => '002',
        'account_name' => 'Jane Casual',
        'account_no' => '0987654321',
        'department_id' => $this->department->department_id,
        // Casual required fields
        'contract_start_date' => now()->format('Y-m-d'),
        'contract_end_date' => now()->addYear()->format('Y-m-d'),
        'amount' => 50000,
    ];

    $response = $this->actingAs($this->user)
        ->post(route('employees.store'), $data);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('employees.index'));
});
