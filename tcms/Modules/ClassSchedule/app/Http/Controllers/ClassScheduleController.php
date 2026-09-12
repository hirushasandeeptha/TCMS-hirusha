<?php

namespace Modules\ClassSchedule\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\ClassSchedule\Models\ClassSchedule;

final class ClassScheduleController extends Controller
{
    private function resolveSchedule($id): ClassSchedule
    {
        return ClassSchedule::findOrFail($id);
    }

    public function index(Request $request)
    {
        $schedules = ClassSchedule::query()
            ->when($request->day_of_week, fn ($q, $day) =>
                $q->where('day_of_week', $day)
            )
            ->when($request->boolean('active_only'), fn ($q) =>
                $q->where('is_active', true)
            )
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate($request->integer('per_page', 25));

        if ($request->expectsJson()) {
            return response()->json($schedules);
        }

        return Inertia::render('ClassSchedules/Index', ['schedules' => $schedules]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'subject'     => ['nullable', 'string', 'max:255'],
            'day_of_week' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time'  => ['required', 'date_format:H:i'],
            'end_time'    => ['required', 'date_format:H:i', 'after:start_time'],
            'room'        => ['nullable', 'string', 'max:100'],
            'is_active'   => ['sometimes', 'boolean'],
            'meta'        => ['nullable', 'array'],
        ]);

        $schedule = ClassSchedule::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Class schedule created successfully.',
                'schedule' => $schedule,
            ], 201);
        }

        return redirect()->route('class-schedules.index')
            ->with('success', 'Class schedule created successfully.');
    }

    public function show(ClassSchedule $classSchedule)
    {
        $classSchedule = $this->resolveSchedule($classSchedule->id);

        if (request()->expectsJson()) {
            return response()->json(['schedule' => $classSchedule]);
        }

        return Inertia::render('ClassSchedules/Show', ['schedule' => $classSchedule]);
    }

    public function edit(ClassSchedule $classSchedule)
    {
        $classSchedule = $this->resolveSchedule($classSchedule->id);

        if (request()->expectsJson()) {
            return response()->json(['schedule' => $classSchedule]);
        }

        return Inertia::render('ClassSchedules/Edit', ['schedule' => $classSchedule]);
    }

    public function update(Request $request, ClassSchedule $classSchedule)
    {
        $classSchedule = $this->resolveSchedule($classSchedule->id);

        $validated = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'subject'     => ['nullable', 'string', 'max:255'],
            'day_of_week' => ['sometimes', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_time'  => ['sometimes', 'date_format:H:i'],
            'end_time'    => ['sometimes', 'date_format:H:i'],
            'room'        => ['nullable', 'string', 'max:100'],
            'is_active'   => ['sometimes', 'boolean'],
            'meta'        => ['nullable', 'array'],
        ]);

        $classSchedule->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Class schedule updated successfully.',
                'schedule' => $classSchedule,
            ]);
        }

        return redirect()->route('class-schedules.index')
            ->with('success', 'Class schedule updated successfully.');
    }

    public function destroy(ClassSchedule $classSchedule)
    {
        $classSchedule = $this->resolveSchedule($classSchedule->id);
        $classSchedule->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Class schedule deleted successfully.',
            ]);
        }

        return redirect()->route('class-schedules.index')
            ->with('success', 'Class schedule deleted successfully.');
    }
}
