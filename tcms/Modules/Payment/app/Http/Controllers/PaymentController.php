<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Payment\Models\Payment;

final class PaymentController extends Controller
{
    private function resolvePayment($id): Payment
    {
        return Payment::findOrFail($id);
    }

    public function index(Request $request)
    {
        $payments = Payment::query()
            ->with('student')
            ->when($request->student_id, fn ($q, $id) =>
                $q->where('student_id', $id)
            )
            ->when($request->month, fn ($q, $month) =>
                $q->where('month', $month)
            )
            ->when($request->discount_type, fn ($q, $type) =>
                $q->where('discount_type', $type)
            )
            ->orderByDesc('month')
            ->orderBy('student_id')
            ->paginate($request->integer('per_page', 25));

        if ($request->expectsJson()) {
            return response()->json($payments);
        }

        return Inertia::render('Payments/Index', ['payments' => $payments]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'    => ['required', 'exists:students,id'],
            'month'         => ['required', 'date_format:Y-m-d'],
            'amount'        => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'in:full_free,half_free,none'],
            'receipt_meta'  => ['nullable', 'array'],
        ]);

        $payment = Payment::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment recorded successfully.',
                'payment' => $payment,
            ], 201);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(Payment $payment)
    {
        $payment = $this->resolvePayment($payment->id);
        $payment->load('student');

        if (request()->expectsJson()) {
            return response()->json(['payment' => $payment]);
        }

        return Inertia::render('Payments/Show', ['payment' => $payment]);
    }

    public function edit(Payment $payment)
    {
        $payment = $this->resolvePayment($payment->id);

        if (request()->expectsJson()) {
            return response()->json(['payment' => $payment]);
        }

        return Inertia::render('Payments/Edit', ['payment' => $payment]);
    }

    public function update(Request $request, Payment $payment)
    {
        $payment = $this->resolvePayment($payment->id);

        $validated = $request->validate([
            'amount'        => ['sometimes', 'numeric', 'min:0'],
            'discount_type' => ['sometimes', 'in:full_free,half_free,none'],
            'receipt_meta'  => ['nullable', 'array'],
        ]);

        $payment->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment updated successfully.',
                'payment' => $payment,
            ]);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment = $this->resolvePayment($payment->id);
        $payment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Payment record deleted successfully.',
            ]);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment record deleted successfully.');
    }
}
