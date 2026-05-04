<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $departments = Employee::where('status', 'active')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values();

        $employees = Employee::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'department']);

        $attendanceColumns = json_decode(Setting::get('attendance_columns', '["absent","one","one_half","overtime","note"]'), true);

        return view('attendance.index', compact('date', 'departments', 'employees', 'attendanceColumns'));
    }

    public function calendarData(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $month = $request->input('month', Carbon::now()->format('Y-m'));

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->keyBy(fn($a) => $a->date instanceof \Carbon\Carbon ? $a->date->format('Y-m-d') : $a->date);

        $summary = ['present' => 0, 'absent' => 0, 'hajira' => 0, 'overtime' => 0];
        foreach ($attendances as $a) {
            if ($a->hajira_type === 'absent') $summary['absent']++;
            else $summary['present']++;
            $summary['hajira']   = round($summary['hajira']   + (float)$a->hajira_value,   1);
            $summary['overtime'] = round($summary['overtime'] + (float)$a->overtime_hours, 1);
        }

        return response()->json([
            'employee'    => ['id' => $employee->id, 'name' => $employee->name, 'department' => $employee->department ?? ''],
            'month'       => $month,
            'attendances' => $attendances->map(fn($a) => [
                'hajira_type'    => $a->hajira_type,
                'hajira_value'   => (float)$a->hajira_value,
                'overtime_hours' => (float)$a->overtime_hours,
                'note'           => $a->note ?? '',
            ]),
            'summary' => $summary,
        ]);
    }

    public function data(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $searchQuery = $request->input('search', '');
        $department = $request->input('department', '');

        $employeeQuery = Employee::where('status', 'active');

        if ($searchQuery) {
            $employeeQuery->where('name', 'like', "%{$searchQuery}%");
        }

        if ($department) {
            $employeeQuery->where('department', $department);
        }

        $employees = $employeeQuery->orderBy('name')->get();

        $attendances = Attendance::where('date', $date)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        $departments = Employee::where('status', 'active')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values();

        $result = $employees->map(function ($emp) use ($attendances) {
            $att = $attendances->get($emp->id);
            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'department' => $emp->department ?? '',
                'hajira_type' => $att ? $att->hajira_type : 'absent',
                'hajira_value' => $att ? (float) $att->hajira_value : 0,
                'overtime_hours' => $att ? (float) $att->overtime_hours : 0,
                'note' => $att ? ($att->note ?? '') : '',
            ];
        });

        return response()->json([
            'employees' => $result,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['attendances' => 'required|string']);

        $attendances = json_decode($request->input('attendances'), true);

        if (!is_array($attendances) || empty($attendances)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'No attendance data provided.'], 422);
            }
            return back()->withErrors(['attendances' => 'No attendance data provided.']);
        }

        $successCount = 0;

        foreach ($attendances as $attendanceData) {
            $existing = Attendance::where('employee_id', $attendanceData['employee_id'])
                ->where('date', $attendanceData['date'])
                ->first();

            if ($existing) {
                $existing->update([
                    'hajira_type' => $attendanceData['hajira_type'],
                    'hajira_value' => $attendanceData['hajira_value'],
                    'overtime_hours' => $attendanceData['overtime_hours'] ?? 0,
                    'note' => $attendanceData['note'] ?? null,
                    'updated_by' => Auth::id(),
                ]);
            } else {
                Attendance::create([
                    'employee_id' => $attendanceData['employee_id'],
                    'date' => $attendanceData['date'],
                    'hajira_type' => $attendanceData['hajira_type'],
                    'hajira_value' => $attendanceData['hajira_value'],
                    'overtime_hours' => $attendanceData['overtime_hours'] ?? 0,
                    'note' => $attendanceData['note'] ?? null,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            $successCount++;
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => "{$successCount} record(s) saved"]);
        }

        $date = $attendances[0]['date'] ?? Carbon::today()->format('Y-m-d');
        return redirect()->route('attendance.index', ['date' => $date])
            ->with('success', "$successCount attendance record(s) saved successfully");
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'integer|exists:employees,id',
            'action' => 'required|in:set_absent,set_one,set_one_half,clear_overtime',
        ]);

        foreach ($validated['employee_ids'] as $employeeId) {
            $attendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $validated['date'])
                ->first();

            if (!$attendance) {
                $attendance = new Attendance([
                    'employee_id' => $employeeId,
                    'date' => $validated['date'],
                    'created_by' => Auth::id(),
                ]);
            }

            switch ($validated['action']) {
                case 'set_absent':
                    $attendance->hajira_type = 'absent';
                    $attendance->hajira_value = 0;
                    break;
                case 'set_one':
                    $attendance->hajira_type = 'one';
                    $attendance->hajira_value = 1;
                    break;
                case 'set_one_half':
                    $attendance->hajira_type = 'one_half';
                    $attendance->hajira_value = 1.5;
                    break;
                case 'clear_overtime':
                    $attendance->overtime_hours = 0;
                    break;
            }

            $attendance->updated_by = Auth::id();
            $attendance->save();
        }

        return redirect()->route('attendance.index', ['date' => $validated['date']])
            ->with('success', 'Bulk action completed successfully');
    }
}
