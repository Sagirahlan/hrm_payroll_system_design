<?php
namespace App\Http\Controllers;

use App\Models\BiometricData;
use App\Models\Employee;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BiometricController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_biometric_data'], ['only' => ['index']]);
        $this->middleware(['permission:create_biometric_data'], ['only' => ['create', 'store']]);
    }

    public function index(Request $request)
    {
        $query = Employee::with('biometricData');
        
        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('surname', 'like', "%{$searchTerm}%")
                  ->orWhere('middle_name', 'like', "%{$searchTerm}%")
                  ->orWhere('employee_id', 'like', "%{$searchTerm}%")
                  ->orWhere('staff_no', 'like', "%{$searchTerm}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', surname) LIKE ?", ["%{$searchTerm}%"])
                  ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', surname) LIKE ?", ["%{$searchTerm}%"]);
            });
        }
        
        // Filter by biometric status
        if ($request->filled('status')) {
            if ($request->status === 'registered') {
                $query->whereHas('biometricData');
            } elseif ($request->status === 'unregistered') {
                $query->whereDoesntHave('biometricData');
            }
        }
        
        $employees = $query->paginate(10)->appends($request->except('page'));
        return view('biometrics.index', compact('employees'));
    }

    public function create(Request $request)
    {
        $employees = Employee::whereDoesntHave('biometricData');
        
        // If an employee is pre-selected, filter to just that employee
        if ($request->filled('employee_id')) {
            $employees->where('employee_id', $request->employee_id);
        }
        
        $employees = $employees->get();
        return view('biometrics.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'nin' => 'required|string|max:20|unique:biometric_data,nin',
            'fingerprint_data' => 'required|string', // Mock for biometric data
        ]);

        // Mock biometric SDK integration (e.g., DigitalPersona)
        $fingerprintData = base64_encode($validated['fingerprint_data']); // Simulate encoding

        // Mock NIMC API call
        $response = Http::post('https://mock-nimc-api.test/verify', [
            'nin' => $validated['nin'],
        ]);

        $verificationStatus = $response->successful() ? 'Verified' : 'Failed';

        $biometric = BiometricData::create([
            'employee_id' => $validated['employee_id'],
            'nin' => $validated['nin'],
            'fingerprint_data' => $fingerprintData,
            'verification_status' => $verificationStatus,
            'verification_date' => now(),
        ]);

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'created',
            'description' => "Added biometric data for employee ID: {$validated['employee_id']}",
            'action_timestamp' => now(),
            'log_data' => json_encode(['entity_type' => 'BiometricData', 'entity_id' => $biometric->biometric_id]),
        ]);

        return redirect()->route('biometrics.index')->with('success', 'Biometric data added successfully.');
    }
}
