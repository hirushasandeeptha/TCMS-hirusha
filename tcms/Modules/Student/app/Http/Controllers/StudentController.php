<?php

namespace Modules\Student\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Student\Models\Student;

/**
 * StudentController — handles both Inertia (web) and JSON (API) responses.
 * All queries auto-scoped by Tenantable — no manual user_id filtering.
 *
 * IMPORTANT: Route model binding bypasses Tenantable global scope.
 * Every {student} parameter is resolved via explicit query to enforce ownership.
 */
final class StudentController extends Controller
{
    /** Resolve student through tenant scope (prevents IDOR). */
    private function resolveStudent($id): Student
    {
        return Student::findOrFail($id);
    }

    public function index(Request $request)
    {
        $students = Student::query()
            ->when($request->search, fn ($q, $search) =>
                $q->where(function ($query) use ($search) {
                    $query->where('full_name', 'ilike', "%{$search}%")
                          ->orWhere('student_code', 'ilike', "%{$search}%")
                          ->orWhere('phone', 'ilike', "%{$search}%");
                })
            )
            ->orderBy('full_name')
            ->paginate($request->integer('per_page', 25));

        if ($request->expectsJson()) {
            return response()->json($students);
        }

        return Inertia::render('Students/Index', ['students' => $students]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'       => ['required', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'qr_code_token'   => ['nullable', 'string', 'max:50', 'unique:students,qr_code_token'],
            'custom_fields'   => ['nullable', 'array'],
        ]);

        $student = Student::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Student created successfully.',
                'student' => $student,
            ], 201);
        }

        return redirect()->route('students.index')
            ->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        // Enforce tenant scope — student must belong to authenticated teacher
        $student = $this->resolveStudent($student->id);

        if (request()->expectsJson()) {
            return response()->json(['student' => $student]);
        }

        return Inertia::render('Students/Show', ['student' => $student]);
    }

    public function edit(Student $student)
    {
        $student = $this->resolveStudent($student->id);

        if (request()->expectsJson()) {
            return response()->json(['student' => $student]);
        }

        return Inertia::render('Students/Edit', ['student' => $student]);
    }

    public function update(Request $request, Student $student)
    {
        $student = $this->resolveStudent($student->id);

        $validated = $request->validate([
            'full_name'       => ['sometimes', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'qr_code_token'   => ['nullable', 'string', 'max:50', 'unique:students,qr_code_token,' . $student->id],
            'custom_fields'   => ['nullable', 'array'],
        ]);

        $student->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Student updated successfully.',
                'student' => $student,
            ]);
        }

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $student = $this->resolveStudent($student->id);
        $student->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Student deleted successfully.',
            ]);
        }

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
