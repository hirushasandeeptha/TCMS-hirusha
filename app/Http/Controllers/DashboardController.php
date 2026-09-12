<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Student\Models\Student;
use Modules\ClassSchedule\Models\ClassSchedule;
use Modules\Attendance\Models\Attendance;
use Modules\Payment\Models\Payment;
use Modules\Notification\Models\Notification;
use Modules\Core\Models\SystemModule;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Subscribed module slugs
        $subscribedSlugs = $user->subscribedModules()
            ->where('user_modules.status', 'active')
            ->pluck('slug')
            ->toArray();

        // All system modules (for marketplace toggle)
        $allModules = SystemModule::active()->get()->map(fn ($m) => [
            'id'            => $m->id,
            'name'          => $m->name,
            'slug'          => $m->slug,
            'description'   => $m->description,
            'price'         => (float) $m->price,
            'is_subscribed' => in_array($m->slug, $subscribedSlugs),
        ]);

        // Live stats (only count what the user is subscribed to)
        $stats = [];

        if (in_array('student', $subscribedSlugs)) {
            $stats['students_total'] = Student::count();
            $stats['students_this_week'] = Student::where('created_at', '>=', now()->subWeek())->count();
        }

        if (in_array('classschedule', $subscribedSlugs)) {
            $stats['schedules_total'] = ClassSchedule::where('is_active', true)->count();
            $stats['schedules_today'] = ClassSchedule::where('is_active', true)
                ->where('day_of_week', strtolower(now()->format('l')))
                ->count();
        }

        if (in_array('attendance', $subscribedSlugs)) {
            $stats['attendance_today'] = Attendance::whereDate('scanned_at', now()->toDateString())->count();
            $stats['attendance_present'] = Attendance::whereDate('scanned_at', now()->toDateString())
                ->where('status', 'present')->count();
            $stats['attendance_late'] = Attendance::whereDate('scanned_at', now()->toDateString())
                ->where('status', 'late')->count();
            $stats['attendance_absent'] = Attendance::whereDate('scanned_at', now()->toDateString())
                ->where('status', 'absent')->count();
        }

        if (in_array('payment', $subscribedSlugs)) {
            $stats['payments_this_month'] = Payment::whereMonth('month', now()->month)
                ->whereYear('month', now()->year)
                ->count();
            $stats['revenue_this_month'] = (float) Payment::whereMonth('month', now()->month)
                ->whereYear('month', now()->year)
                ->sum('amount');
        }

        if (in_array('notification', $subscribedSlugs)) {
            $stats['notifications_total'] = Notification::count();
            $stats['notifications_unsent'] = Notification::where('status', 'draft')->count();
        }

        return Inertia::render('Dashboard', [
            'subscribedModules' => $subscribedSlugs,
            'allModules'        => $allModules,
            'stats'             => $stats,
        ]);
    }
}
