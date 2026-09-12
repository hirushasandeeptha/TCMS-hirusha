<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\SystemModule;
use Modules\Student\Models\Student;
use Modules\ClassSchedule\Models\ClassSchedule;
use Modules\Attendance\Models\Attendance;
use Modules\Payment\Models\Payment;
use Modules\Notification\Models\Notification;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            SystemModuleSeeder::class,
        ]);

        // 1) Create the production teacher account
        $teacher = User::create([
            'name'              => 'Hirusha Ranasinghe',
            'email'             => 'hirusharanasighe171@gmail.com',
            'email_verified_at' => now(),
            'password'          => bcrypt('password'),   // change after first login
            'role'              => 'teacher',
            'phone'             => '+94771234567',
            'remember_token'    => Str::random(10),
        ]);

        // 2) Subscribe the teacher to ALL system modules
        $allModules = SystemModule::all();
        foreach ($allModules as $module) {
            DB::table('user_modules')->updateOrInsert(
                [
                    'user_id'          => $teacher->id,
                    'system_module_id' => $module->id,
                ],
                [
                    'subscribed_at' => now(),
                    'expires_at'    => now()->addYear(),
                    'status'        => 'active',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]
            );
        }

        // 3) Seed 20 sample students
        $studentNames = [
            ['Kamal Perera',    '0771112223'],
            ['Nimal Fernando',  '0772223334'],
            ['Sunil de Silva',  '0773334445'],
            ['Amara Jayawardena','0774445556'],
            ['Ruwan Bandara',   '0775556667'],
            ['Dilshan Weerasinghe','0776667778'],
            ['Chathurika Herath','0777778889'],
            ['Lakshitha Kodithuwakku','0778889990'],
            ['Madushi Senanayake','0779990001'],
            ['Tharaka Wickramasinghe','0770001112'],
            ['Dinesh Liyanage', '0771122334'],
            ['Hashini Kumari',  '0772233445'],
            ['Kasun Gunasekara','0773344556'],
            ['Madhawa Bandara', '0774455667'],
            ['Nipuni Fernando', '0775566778'],
            ['Pasindu Herath',  '0776677889'],
            ['Sachithra Rajapaksa','0777788990'],
            ['Thilina Mendis',  '0778899001'],
            ['Udaya Samaraweera','0779900112'],
            ['Wathsala Bandaranayake','0770011223'],
        ];

        $students = [];
        foreach ($studentNames as $i => [$name, $phone]) {
            $students[] = Student::create([
                'user_id'        => $teacher->id,
                'student_code'   => 'STU' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'full_name'      => $name,
                'phone'          => $phone,
                'qr_code_token'  => Str::uuid()->toString(),
                'custom_fields'  => [
                    'school'   => ['Royal College', 'St. Thomas', 'Ananda College', 'Nalanda College'][array_rand([0,1,2,3])],
                    'grade'    => ['Grade 10', 'Grade 11', 'Grade 12'][array_rand([0,1,2])],
                    'guardian' => $name,
                ],
            ]);
        }

        // 4) Class schedules for the teacher
        $scheduleData = [
            ['Grade 10 Maths',   'Mathematics', 'monday',    '08:00', '10:00', 'Room A'],
            ['Grade 11 Science', 'Science',     'tuesday',   '08:00', '10:00', 'Room B'],
            ['Grade 12 Physics', 'Physics',     'wednesday', '10:00', '12:00', 'Room A'],
            ['Grade 10 English', 'English',     'thursday',  '14:00', '16:00', 'Room C'],
            ['Grade 11 Maths',   'Mathematics', 'friday',    '08:00', '10:00', 'Room B'],
            ['Grade 12 Chemistry','Chemistry',  'saturday',  '09:00', '11:00', 'Room A'],
        ];

        $schedules = [];
        foreach ($scheduleData as [$name, $subject, $day, $start, $end, $room]) {
            $schedules[] = ClassSchedule::create([
                'user_id'     => $teacher->id,
                'name'        => $name,
                'subject'     => $subject,
                'day_of_week' => $day,
                'start_time'  => $start,
                'end_time'    => $end,
                'room'        => $room,
                'is_active'   => true,
                'meta'        => ['capacity' => 30, 'recurring' => true],
            ]);
        }

        // 5) Attendance records — past 5 days for 10 students
        $statuses = ['present', 'present', 'present', 'present', 'late', 'absent'];
        for ($dayOffset = 1; $dayOffset <= 5; $dayOffset++) {
            $date = now()->subDays($dayOffset);
            foreach (array_slice($students, 0, 10) as $student) {
                Attendance::create([
                    'user_id'            => $teacher->id,
                    'student_id'         => $student->id,
                    'class_schedule_id'  => $schedules[array_rand($schedules)]->id,
                    'status'             => $statuses[array_rand($statuses)],
                    'scanned_at'         => $date->copy()->addHours(rand(8, 16))->addMinutes(rand(0, 59)),
                ]);
            }
        }

        // 6) Payment records — last 3 months for 15 students
        $discountTypes = ['none', 'none', 'none', 'half_free', 'full_free'];
        for ($monthOffset = 0; $monthOffset < 3; $monthOffset++) {
            $monthDate = now()->subMonths($monthOffset)->startOfMonth()->toDateString();
            foreach (array_slice($students, 0, 15) as $student) {
                Payment::create([
                    'user_id'        => $teacher->id,
                    'student_id'     => $student->id,
                    'month'          => $monthDate,
                    'amount'         => [2500, 3000, 3500, 4000][array_rand([0,1,2,3])],
                    'discount_type'  => $discountTypes[array_rand($discountTypes)],
                    'receipt_meta'   => [
                        'payment_method' => ['cash', 'bank_transfer', 'online'][array_rand([0,1,2])],
                        'receipt_no'     => 'RCP-' . strtoupper(Str::random(6)),
                    ],
                ]);
            }
        }

        // 7) Notifications — 10 sample messages
        $notifData = [
            ['fee_reminder',   'Monthly Fee Reminder',        'sms',      'sent',     'Dear parent, please pay the monthly fee.'],
            ['absence_alert',  'Absence Notification',        'whatsapp', 'sent',     'Your child was absent today.'],
            ['general',        'Holiday Announcement',        'email',    'sent',     'School will be closed next Monday.'],
            ['fee_reminder',   'Fee Overdue Notice',          'sms',      'sent',     'Your fee payment is overdue. Please pay ASAP.'],
            ['exam_notice',    'Upcoming Exam Schedule',      'in_app',   'draft',    'Exams start next week.'],
            ['absence_alert',  'Repeated Absence Warning',    'sms',      'sent',     'Your child has been absent 3 times this month.'],
            ['general',        'PTA Meeting Notice',          'whatsapp', 'draft',    'PTA meeting on Friday at 4 PM.'],
            ['fee_reminder',   'Scholarship Opportunity',     'email',    'draft',    'Merit-based scholarships available.'],
            ['exam_notice',    'Exam Results Published',      'in_app',   'read',     'Exam results are now available.'],
            ['general',        'Welcome to TCMS',             'in_app',   'read',     'Welcome to Tuition Centre Management System!'],
        ];

        foreach ($notifData as [$type, $title, $channel, $status, $body]) {
            Notification::create([
                'user_id'    => $teacher->id,
                'student_id' => $students[array_rand($students)]->id,
                'type'       => $type,
                'title'      => $title,
                'body'       => $body,
                'channel'    => $channel,
                'status'     => $status,
                'sent_at'    => in_array($status, ['sent', 'read']) ? now()->subDays(rand(1, 10)) : null,
                'read_at'    => $status === 'read' ? now()->subDays(rand(1, 5)) : null,
                'meta'       => ['delivery_id' => Str::random(10)],
            ]);
        }
    }
}
