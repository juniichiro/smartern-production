<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core seeders required for the system to function
        $this->call([
            RoleSeeder::class,           // RBAC
            OfficeSeeder::class,         // Departments
            AssessmentItemSeeder::class, // The dynamic competency list (New)
            OfficeRuleSeeder::class,     // Default matching requirements (Updated)
            UserSeeder::class,           // Auth accounts
            SupervisorDetailSeeder::class, // Profile info for supervisors
            InternDetailSeeder::class,    // Profile info for interns
            SchoolCoordinatorDetailSeeder::class, // Profile info for school coordinators
            OnboardingApplicantSeeder::class, // 5 onboarding applications
            TaskSeeder::class, // Completed, submitted, pending tasks
            AttendanceSeeder::class, // Attendance records mapping to hours_rendered
            JournalSeeder::class, // Daily and weekly log journals
            ScheduleSeeder::class, // Default, holiday, suspension schedules per school
            AbsenceRequestSeeder::class, // Absence and early leave requests + attendance updates
            AnnouncementSeeder::class, // Office-scoped announcements
        ]);
    }
}
