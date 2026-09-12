<?php

namespace Modules\Attendance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Attendance\Models\Attendance;
use Modules\Student\Models\Student;

final class AttendanceController extends Controller
{
    private function resolveAttendance($id): Attendance
    {
        return Attendance::findOrFail($id);
    }

    public function index(Request $request)
    {
        $attendances = Attendance::query()
            ->with(['student', 'classSchedule'])
            ->when($request->student_id, fn ($q, $id) =>
                $q->where('student_id', $id)
            )
            ->when($request->status, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->when($request->date, fn ($q, $date) =>
                $q->whereDate('scanned_at', $date)
            )
            ->orderByDesc('scanned_at')
            ->paginate($request->integer('per_page', 25));

        if ($request->expectsJson()) {
            return response()->json($attendances);
        }

        return Inertia::render('Attendances/Index', ['attendances' => $attendances]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'         => ['required', 'exists:students,id'],
            'class_schedule_id'  => ['nullable', 'exists:class_schedules,id'],
            'status'             => ['required', 'in:present,absent,late'],
            'scanned_at'         => ['nullable', 'date'],
        ]);

        $attendance = Attendance::create([
            ...$validated,
            'scanned_at' => $validated['scanned_at'] ?? now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'Attendance recorded successfully.',
                'attendance' => $attendance,
            ], 201);
        }

        return redirect()->route('attendances.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    /**
     * Fast-scan attendance via QR code token.
     * Resolves student within tenant scope and auto-logs attendance.
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'qr_code_token'     => ['required', 'string', 'max:50'],
            'class_schedule_id' => ['nullable', 'exists:class_schedules,id'],
        ]);

        // Find student by QR token — Tenantable ensures teacher isolation
        $student = Student::where('qr_code_token', $validated['qr_code_token'])->first();

        if (!$student) {
            return response()->json([
                'message' => 'No student found for this QR code.',
            ], 404);
        }

        // Check duplicate scan today
        $today = now()->toDateString();
        $alreadyScanned = Attendance::where('student_id', $student->id)
            ->whereDate('scanned_at', $today)
            ->when($validated['class_schedule_id'] ?? null, fn ($q, $id) =>
                $q->where('class_schedule_id', $id)
            )
            ->exists();

        if ($alreadyScanned) {
            return response()->json([
                'message' => 'Attendance already recorded for this student today.',
                'student' => $student,
            ], 409);
        }

        $attendance = Attendance::create([
            'student_id'         => $student->id,
            'class_schedule_id'  => $validated['class_schedule_id'] ?? null,
            'status'             => 'present',
            'scanned_at'         => now(),
        ]);

        return response()->json([
            'message'    => 'Attendance recorded successfully.',
            'student'    => $student,
            'attendance' => $attendance,
        ], 201);
    }

    public function show(Attendance $attendance)
    {
        $attendance = $this->resolveAttendance($attendance->id);
        $attendance->load(['student', 'classSchedule']);

        if (request()->expectsJson()) {
            return response()->json(['attendance' => $attendance]);
        }

        return Inertia::render('Attendances/Show', ['attendance' => $attendance]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $attendance = $this->resolveAttendance($attendance->id);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:present,absent,late'],
        ]);

        $attendance->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message'    => 'Attendance updated successfully.',
                'attendance' => $attendance,
            ]);
        }

        return redirect()->route('attendances.index')
            ->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance = $this->resolveAttendance($attendance->id);
        $attendance->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Attendance record deleted successfully.',
            ]);
        }

        return redirect()->route('attendances.index')
            ->with('success', 'Attendance record deleted successfully.');
    }
}
