<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Pensioner;
use App\Models\Retirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;

class PensionerReportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $employee;
    protected $pensioner;

    protected function setUp(): void
    {
        parent::setUp();

        // Run seeders to set up permissions
        $this->seed(PermissionSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create a user with necessary permissions
        $this->user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        // Assign the manage_employees permission to the user
        $this->user->givePermissionTo('manage_employees');

        // Create a test employee
        $this->employee = Employee::create([
            'employee_id' => 'EMP001',
            'first_name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'date_of_birth' => '1970-01-01',
            'date_of_first_appointment' => '2000-01-01',
            'status' => 'Retired',
            'basic_salary' => 150000.00
        ]);

        // Create a retirement record
        $retirement = Retirement::create([
            'employee_id' => $this->employee->employee_id,
            'retirement_date' => now(),
            'status' => 'approved',
            'gratuity_amount' => 5000000.00
        ]);

        // Create a pensioner record
        $this->pensioner = Pensioner::create([
            'employee_id' => $this->employee->employee_id,
            'pension_start_date' => now(),
            'pension_amount' => 250000.00,
            'rsa_balance_at_retirement' => 10000000.00,
            'lump_sum_amount' => 2500000.00,
            'pension_type' => 'PW',
            'expected_lifespan_months' => 240,
            'status' => 'Active'
        ]);
    }

    /** @test */
    public function it_can_generate_pensioners_report()
    {
        $response = $this->actingAs($this->user)
            ->post(route('reports.generate_pensioners'), [
                'export_format' => 'PDF'
            ]);

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHas('success');

        // Check that a report was created
        $this->assertDatabaseHas('reports', [
            'report_type' => 'pensioners'
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_generate_pensioners_report()
    {
        $response = $this->post(route('reports.generate_pensioners'), [
            'export_format' => 'PDF'
        ]);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_requires_proper_permissions_to_generate_pensioners_report()
    {
        // Create a user without proper permissions
        $unauthorizedUser = User::create([
            'name' => 'Unauthorized User',
            'username' => 'unauthorized',
            'email' => 'unauthorized@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($unauthorizedUser)
            ->post(route('reports.generate_pensioners'), [
                'export_format' => 'PDF'
            ]);

        $response->assertStatus(403); // Forbidden
    }
}