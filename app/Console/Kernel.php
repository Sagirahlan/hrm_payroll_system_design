<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ImportSampleDataCommand;
use App\Console\Commands\TestProbationFeature;
use App\Console\Commands\TestProbationSystem;
use App\Console\Commands\CheckProbationFields;
use App\Console\Commands\CreateProbationEmployee;
use App\Console\Commands\ApprovePendingChange;
use App\Console\Commands\CheckEmployee;
use App\Console\Commands\CheckAppointmentType;
use App\Console\Commands\DebugProbationLogic;
use App\Console\Commands\VerifyEmployeeProbation;
use App\Console\Commands\CheckRawDatabase;
use App\Console\Commands\SetEmployeeProbation;
use App\Console\Commands\UpdateProbationDates;
use App\Console\Commands\FinalProbationTest;
use App\Console\Commands\CheckAllProbationEmployees;
use App\Console\Commands\FixProbationConsistency;
use App\Console\Commands\CheckPromotionPermissions;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ImportSampleDataCommand::class,
        TestProbationFeature::class,
        TestProbationSystem::class,
        CheckProbationFields::class,
        CreateProbationEmployee::class,
        ApprovePendingChange::class,
        CheckEmployee::class,
        CheckAppointmentType::class,
        DebugProbationLogic::class,
        VerifyEmployeeProbation::class,
        CheckRawDatabase::class,
        SetEmployeeProbation::class,
        UpdateProbationDates::class,
        FinalProbationTest::class,
        CheckAllProbationEmployees::class,
        FixProbationConsistency::class,
        CheckPromotionPermissions::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('employees:retire-eligible')->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
