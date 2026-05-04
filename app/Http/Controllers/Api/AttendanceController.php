<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $date = $request->input('date');

        if (!$date) {
            return response()->json([
                'message' => 'Date parameter required',
            ], 400);
        }

        $attendances = Attendance::where('date', $date)
            ->with('employee')
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'employee_id' => $attendance->employee_id,
                    'employee_name' => $attendance->employee->name,
                    'date' => $attendance->date,
                    'hajira_type' => $attendance->hajira_type,
                    'hajira_value' => $attendance->hajira_value,
                    'overtime_hours' => $attendance->overtime_hours,
                    'note' => $attendance->note,
                ];
            });

        return response()->json([
            'date' => $date,
            'count' => $attendances->count(),
            'data' => $attendances,
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $date = $request->input('date');

        if (!$date) {
            return response()->json([
                'message' => 'Date parameter required',
            ], 400);
        }

        $attendances = Attendance::where('date', $date)->get();

        $summary = [
            'date' => $date,
            'total_employees' => Employee::where('status', 'active')->count(),
            'total_present' => $attendances->where('hajira_type', '!=', 'absent')->count(),
            'total_absent' => $attendances->where('hajira_type', 'absent')->count(),
            'total_hajira' => $attendances->sum('hajira_value'),
            'total_overtime_hours' => $attendances->sum('overtime_hours'),
            'recorded_count' => $attendances->count(),
        ];

        return response()->json($summary);
    }

    public function bulkSave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attendances' => 'required|array',
            'attendances.*.employee_id' => 'required|integer|exists:employees,id',
            'attendances.*.date' => 'required|date',
            'attendances.*.hajira_type' => 'required|in:absent,one,one_half',
            'attendances.*.hajira_value' => 'required|numeric|in:0,1,1.5',
            'attendances.*.overtime_hours' => 'nullable|numeric|min:0',
            'attendances.*.note' => 'nullable|string',
        ]);

        $successCount = 0;
        $updateCount = 0;

        foreach ($validated['attendances'] as $attendanceData) {
            $existing = Attendance::where('employee_id', $attendanceData['employee_id'])
                ->where('date', $attendanceData['date'])
                ->first();

            if ($existing) {
                $existing->update([
                    'hajira_type' => $attendanceData['hajira_type'],
                    'hajira_value' => $attendanceData['hajira_value'],
                    'overtime_hours' => $attendanceData['overtime_hours'] ?? 0,
                    'note' => $attendanceData['note'] ?? null,
                    'updated_by' => auth()->id(),
                ]);
                $updateCount++;
            } else {
                Attendance::create([
                    'employee_id' => $attendanceData['employee_id'],
                    'date' => $attendanceData['date'],
                    'hajira_type' => $attendanceData['hajira_type'],
                    'hajira_value' => $attendanceData['hajira_value'],
                    'overtime_hours' => $attendanceData['overtime_hours'] ?? 0,
                    'note' => $attendanceData['note'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
                $successCount++;
            }
        }

        return response()->json([
            'message' => 'Attendance saved successfully',
            'created' => $successCount,
            'updated' => $updateCount,
            'total' => $successCount + $updateCount,
        ], 201);
    }
}
