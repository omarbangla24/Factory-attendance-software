<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name'          => Setting::get('company_name', 'Hajira Payroll System'),
            'company_phone'         => Setting::get('company_phone', ''),
            'company_address'       => Setting::get('company_address', ''),
            'default_overtime_rate' => Setting::get('default_overtime_rate', '0'),
            'salary_auto_deduction' => Setting::get('salary_auto_deduction', '1'),
            'currency_symbol'       => Setting::get('currency_symbol', '৳'),
            'currency_code'         => Setting::get('currency_code', 'BDT'),
            'attendance_columns'    => json_decode(Setting::get('attendance_columns', '["absent","one","one_half","overtime","note"]'), true),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name'          => 'required|string|max:255',
            'company_phone'         => 'nullable|string|max:20',
            'company_address'       => 'nullable|string|max:500',
            'default_overtime_rate' => 'required|numeric|min:0',
            'salary_auto_deduction' => 'in:0,1',
            'currency_symbol'       => 'required|string|max:10',
            'currency_code'         => 'required|string|max:10',
            'attendance_columns'    => 'nullable|array',
            'attendance_columns.*'  => 'in:absent,one,one_half,overtime,note',
        ], [
            'company_name.required'         => 'Company name is required',
            'company_phone.max'             => 'Phone number cannot exceed 20 characters',
            'company_address.max'           => 'Address cannot exceed 500 characters',
            'default_overtime_rate.numeric' => 'Overtime rate must be a number',
            'default_overtime_rate.min'     => 'Overtime rate cannot be negative',
        ]);

        $columns = $request->input('attendance_columns', []);
        Setting::set('attendance_columns', json_encode($columns));
        unset($validated['attendance_columns']);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.index')
            ->with('success', 'Settings updated successfully');
    }

    public function clearData(Request $request, string $type)
    {
        $allowed = ['attendance', 'advances', 'salaries', 'all'];
        if (!in_array($type, $allowed)) {
            return back()->with('error', 'Invalid data type.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $label = ucfirst($type);
        switch ($type) {
            case 'attendance':
                DB::table('attendances')->truncate();
                $label = 'Attendance';
                break;
            case 'advances':
                DB::table('advance_deductions')->truncate();
                DB::table('advances')->truncate();
                $label = 'Advances & Deductions';
                break;
            case 'salaries':
                DB::table('advance_deductions')->truncate();
                DB::table('salary_payments')->truncate();
                DB::table('salary_sheets')->truncate();
                $label = 'Salary Sheets & Payments';
                break;
            case 'all':
                DB::table('attendances')->truncate();
                DB::table('advance_deductions')->truncate();
                DB::table('advances')->truncate();
                DB::table('salary_payments')->truncate();
                DB::table('salary_sheets')->truncate();
                DB::table('activity_log')->truncate();
                $label = 'All transactional data';
                break;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        return back()->with('success', "{$label} cleared successfully.");
    }
}
