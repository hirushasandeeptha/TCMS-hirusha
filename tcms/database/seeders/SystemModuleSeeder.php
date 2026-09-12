<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name'        => 'Student',
                'slug'        => 'student',
                'description' => 'Manage your student roster — add, edit, track student details and QR codes.',
                'price'       => 0,
                'is_active'   => true,
            ],
            [
                'name'        => 'ClassSchedule',
                'slug'        => 'classschedule',
                'description' => 'Create and manage class timetables — schedule days, times, rooms, and subjects.',
                'price'       => 300,
                'is_active'   => true,
            ],
            [
                'name'        => 'Attendance',
                'slug'        => 'attendance',
                'description' => 'QR-code based attendance tracking — scan students in, log presence automatically.',
                'price'       => 500,
                'is_active'   => true,
            ],
            [
                'name'        => 'Payment',
                'slug'        => 'payment',
                'description' => 'Track monthly fees, discounts (full/half free), and payment receipts per student.',
                'price'       => 500,
                'is_active'   => true,
            ],
            [
                'name'        => 'Notification',
                'slug'        => 'notification',
                'description' => 'Send fee reminders, absence alerts, and messages to parents via SMS, WhatsApp, or email.',
                'price'       => 200,
                'is_active'   => true,
            ],
        ];

        foreach ($modules as $module) {
            DB::table('system_modules')->updateOrInsert(
                ['slug' => $module['slug']],
                array_merge($module, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
