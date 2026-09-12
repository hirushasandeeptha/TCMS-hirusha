<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\SystemModule;

/**
 * Seeds the system_modules table with all available modules.
 * Run: php artisan module:seed Core
 */
final class CoreDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Student Management',
                'slug' => 'student',
                'description' => 'Manage student profiles, enrollment, guardians, and academic records.',
                'price' => 29.99,
                'is_active' => true,
            ],
            [
                'name' => 'Fee & Payments',
                'slug' => 'payment',
                'description' => 'Handle fee structures, invoicing, payments, and financial reports.',
                'price' => 39.99,
                'is_active' => true,
            ],
            [
                'name' => 'Class Scheduling',
                'slug' => 'schedule',
                'description' => 'Create and manage class schedules, batches, and timetables.',
                'price' => 19.99,
                'is_active' => true,
            ],
            [
                'name' => 'Attendance',
                'slug' => 'attendance',
                'description' => 'Track student and teacher attendance with reports.',
                'price' => 14.99,
                'is_active' => true,
            ],
            [
                'name' => 'Examinations',
                'slug' => 'exam',
                'description' => 'Manage exams, grades, report cards, and academic analytics.',
                'price' => 24.99,
                'is_active' => true,
            ],
        ];

        foreach ($modules as $module) {
            SystemModule::updateOrCreate(
                ['slug' => $module['slug']],
                $module
            );
        }
    }
}
