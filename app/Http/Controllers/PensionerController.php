<?php
namespace App\Http\Controllers;

use App\Models\{Retirement, Pensioner, Employee, PayrollRecord, AuditTrail};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PensionerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_pensioners']);
    }

    public function index(Request $request)
    {
        Log::info('Pensioner Index Search', [
            'search' => $request->input('search'),
            'filter' => $request->input('filter'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);

        $query = Pensioner::query()->with('employee', 'retirement');

        if ($search = trim($request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'LIKE', "%{$search}%")
                  ->orWhere('pension_amount', 'LIKE', "%{$search}%")
                  ->orWhere('status', 'LIKE', "%{$search}%")
                  ->orWhereHas('employee', function ($q) use ($search) {
                      $q->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('surname', 'LIKE', "%{$search}%")
                        ->orWhere('employee_id', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($filter = $request->input('filter')) {
            $query->where('status', $filter);
        }

        if ($startDate = $request->input('start_date')) {
            $query->where('pension_start_date', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->where('pension_start_date', '<=', $endDate);
        }

        $pensioners = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        Log::info('Pensioner Index Results', ['count' => $pensioners->count()]);

        $filterOptions = [
            ['value' => 'Active', 'label' => 'Active'],
            ['value' => 'Deceased', 'label' => 'Deceased'],
        ];

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'searched_pensioners',
            'description' => "Searched pensioners with query: search='{$request->input('search')}', filter='{$request->input('filter')}'",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Pensioner', 'entity_id' => null, 'search_query' => $request->input('search'), 'filter' => $request->input('filter')]),
        ]);

        return view('pensioners.index', compact('pensioners', 'filterOptions'));
    }

    public function create()
    {
        $employees = Employee::select('employee_id', 'first_name', 'surname')
            ->whereIn('employee_id', Retirement::where('status', 'approved')->pluck('employee_id'))
            ->whereNotIn('employee_id', Pensioner::pluck('employee_id'))
            ->get();
        return view('pensioners.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id|unique:pensioners,employee_id',
            'pension_start_date' => 'required|date|after_or_equal:today',
            'pension_amount' => 'required|numeric|min:0',
            'status' => 'required|in:Active,Deceased',
            'rsa_balance_at_retirement' => 'nullable|numeric|min:0',
            'lump_sum_amount' => 'nullable|numeric|min:0',
            'pension_type' => 'nullable|in:PW,Annuity',
            'expected_lifespan_months' => 'nullable|integer|min:1',
        ]);

        try {
            $retirement = Retirement::where('employee_id', $validated['employee_id'])
                ->where('status', 'approved')
                ->firstOrFail();

            // Get employee to use their RSA balance if not provided
            $employee = Employee::find($validated['employee_id']);
            if (!$validated['rsa_balance_at_retirement'] && $employee) {
                $validated['rsa_balance_at_retirement'] = $employee->rsa_balance;
            }
            
            // Calculate default lump sum (25% of RSA balance) if not provided
            if (!$validated['lump_sum_amount'] && $validated['rsa_balance_at_retirement']) {
                $validated['lump_sum_amount'] = $validated['rsa_balance_at_retirement'] * 0.25;
            }
            
            // Set default pension type if not provided
            if (empty($validated['pension_type'])) {
                $validated['pension_type'] = 'PW'; // Default to Programmed Withdrawal
            }
            
            // Set default expected lifespan if not provided (240 months = 20 years)
            if (empty($validated['expected_lifespan_months'])) {
                $validated['expected_lifespan_months'] = 240;
            }

            $pensioner = Pensioner::create($validated);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'created_pensioner',
                'description' => "Created pensioner for employee ID: {$validated['employee_id']}, amount: {$validated['pension_amount']}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Pensioner', 'entity_id' => $pensioner->pensioner_id, 'employee_id' => $validated['employee_id'], 'amount' => $validated['pension_amount']]),
            ]);

            return redirect()->route('pensioners.index')->with('success', 'Pensioner record created successfully.');
        } catch (\Exception $e) {
            Log::error('Pensioner creation failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to create pensioner: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $pensioner_id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Active,Deceased',
        ]);

        try {
            $pensioner = Pensioner::findOrFail($pensioner_id);

            $pensioner->update(['status' => $validated['status']]);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'updated_pensioner_status',
                'description' => "Updated pensioner status to {$validated['status']} for employee ID: {$pensioner->employee_id}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Pensioner', 'entity_id' => $pensioner->pensioner_id, 'employee_id' => $pensioner->employee_id, 'new_status' => $validated['status']]),
            ]);

            return redirect()->route('pensioners.index')->with('success', 'Pensioner status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Pensioner status update failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update pensioner status: ' . $e->getMessage());
        }
    }
    
    // Method to track pension payments
    public function trackPayment(Request $request, $pensioner_id)
    {
        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'reference' => 'nullable|string|max:100',
            'status' => 'required|in:Pending,Paid,Failed,Cancelled',
        ]);

        try {
            $pensioner = Pensioner::findOrFail($pensioner_id);

            // Create a payroll record for this pension payment to track it
            $payroll = PayrollRecord::create([
                'employee_id' => $pensioner->employee_id,
                'grade_level_id' => $pensioner->employee->gradeLevel->id ?? null,
                'payroll_month' => Carbon::parse($validated['payment_date'])->format('Y-m-01'),
                'basic_salary' => 0,
                'total_additions' => 0,
                'total_deductions' => 0,
                'net_salary' => $validated['amount'],
                'status' => $validated['status'],
                'payment_date' => $validated['payment_date'],
                'remarks' => "Pension payment - " . ($validated['payment_method'] ?? 'Direct Bank Transfer') . " - Ref: {$validated['reference']}",
            ]);

            // Create a payment transaction for this pension payment
            \App\Models\PaymentTransaction::create([
                'payroll_id' => $payroll->payroll_id,
                'employee_id' => $pensioner->employee_id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'bank_code' => null,
                'account_name' => $pensioner->employee->first_name . ' ' . $pensioner->employee->surname,
                'account_number' => $validated['account_number'] ?? ($pensioner->employee->bank->account_no ?? 'N/A'),
                'status' => $validated['status'],
                'reference' => $validated['reference'],
                'method' => $validated['payment_method'] ?? 'Bank Transfer',
            ]);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'pension_payment_tracked',
                'description' => "Tracked pension payment for pensioner ID: {$pensioner_id}, amount: {$validated['amount']}",
                'action_timestamp' => now(),
                'log_data' => json_encode([
                    'entity_type' => 'PensionPayment',
                    'entity_id' => $payroll->payroll_id,
                    'pensioner_id' => $pensioner_id,
                    'amount' => $validated['amount'],
                    'status' => $validated['status']
                ]),
            ]);

            return redirect()->back()->with('success', 'Pension payment tracked successfully.');
        } catch (\Exception $e) {
            Log::error('Pension payment tracking failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to track pension payment: ' . $e->getMessage());
        }
    }
    
    public function show($pensioner_id)
    {
        $pensioner = Pensioner::with(['employee', 'retirement'])->findOrFail($pensioner_id);
        
        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'viewed_pensioner',
            'description' => "Viewed pensioner details: {$pensioner->employee->first_name} {$pensioner->employee->surname}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'Pensioner', 'entity_id' => $pensioner->pensioner_id]),
        ]);

        return view('pensioners.show', compact('pensioner'));
    }
    
    public function edit($pensioner_id)
    {
        $pensioner = Pensioner::findOrFail($pensioner_id);
        $employees = Employee::select('employee_id', 'first_name', 'surname')
            ->whereIn('employee_id', Retirement::where('status', 'approved')->pluck('employee_id'))
            ->get();
        
        return view('pensioners.edit', compact('pensioner', 'employees'));
    }

    public function update(Request $request, $pensioner_id)
    {
        $validated = $request->validate([
            'pension_start_date' => 'required|date|after_or_equal:today',
            'pension_amount' => 'required|numeric|min:0',
            'status' => 'required|in:Active,Deceased',
            'rsa_balance_at_retirement' => 'nullable|numeric|min:0',
            'lump_sum_amount' => 'nullable|numeric|min:0',
            'pension_type' => 'nullable|in:PW,Annuity',
            'expected_lifespan_months' => 'nullable|integer|min:1',
        ]);

        try {
            $pensioner = Pensioner::findOrFail($pensioner_id);
            
            $pensioner->update($validated);

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'updated_pensioner',
                'description' => "Updated pensioner details for {$pensioner->employee->first_name} {$pensioner->employee->surname}",
                'action_timestamp' => now(),
                'log_data' => json_encode(['entity_type' => 'Pensioner', 'entity_id' => $pensioner->pensioner_id]),
            ]);

            return redirect()->route('pensioners.show', $pensioner->pensioner_id)->with('success', 'Pensioner record updated successfully.');
        } catch (\Exception $e) {
            Log::error('Pensioner update failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update pensioner: ' . $e->getMessage());
        }
    }
    
    // Method to view pension payment history
    public function paymentHistory($pensioner_id)
    {
        $pensioner = Pensioner::with('employee')->findOrFail($pensioner_id);
        
        // Get all payroll records for this pensioner (monthly pension payments)
        $paymentHistory = PayrollRecord::where('employee_id', $pensioner->employee_id)
            ->with('transaction') // Load the payment transaction relationship
            ->orderBy('payroll_month', 'desc')
            ->paginate(20);
        
        return view('pensioners.payment_history', compact('pensioner', 'paymentHistory'));
    }
}