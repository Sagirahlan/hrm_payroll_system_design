<?php
namespace App\Http\Controllers;

use App\Models\BiometricData;
use App\Models\Employee;
use App\Events\AuditTrailLogged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BiometricController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_biometrics']);
    }

    public function index()
    {
        $biometrics = BiometricData::with('employee')->paginate(10);
        return view('biometrics.index', compact('biometrics'));
    }

    public function create()
    {
        $employees = Employee::whereDoesntHave('biometricData')->get();
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

        event(new AuditTrailLogged(
            Auth::id(),
            'Create Biometric',
            "Added biometric data for employee ID: {$validated['employee_id']}",
            'BiometricData',
            $biometric->biometric_id
        ));

        return redirect()->route('biometrics.index')->with('success', 'Biometric data added successfully.');
    }
}
