<?php
namespace App\Http\Controllers;

use App\Models\SmsNotification;
use App\Models\Employee;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Events\AuditTrailLogged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client;

class SmsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage_sms']);
    }

    public function index()
    {
        $smsNotifications = SmsNotification::with('user')->paginate(10);
        return view('sms.index', compact('smsNotifications'));
    }

    public function create()
    {
        $departments = Department::all();
        $gradeLevels = GradeLevel::all();
        return view('sms.create', compact('departments', 'gradeLevels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:All,Group,Department,GradeLevel',
            'recipient_id' => 'nullable|exists:departments,department_id',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'message' => 'required|string|max:160',
        ]);

        $recipientId = null;
        if ($validated['recipient_type'] === 'Department') {
            $recipientId = $validated['recipient_id'];
        } elseif ($validated['recipient_type'] === 'GradeLevel') {
            $recipientId = $validated['grade_level_id'];
        }

        $sms = SmsNotification::create([
            'user_id' => Auth::id(),
            'recipient_type' => $validated['recipient_type'],
            'recipient_id' => $recipientId,
            'message' => $validated['message'],
            'status' => 'Pending',
        ]);

        // Mock Twilio SMS integration
        $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
        
        $recipients = collect();
        if ($validated['recipient_type'] === 'Department') {
            $recipients = Employee::where('department_id', $validated['recipient_id'])->pluck('mobile_no');
        } elseif ($validated['recipient_type'] === 'GradeLevel') {
            // Assuming Employee model has a 'grade_level_id' relationship
            $recipients = Employee::where('grade_level_id', $validated['grade_level_id'])->pluck('mobile_no');
        } else { // 'All'
            $recipients = Employee::pluck('mobile_no');
        }

        foreach ($recipients as $phone) {
            try {
                $twilio->messages->create($phone, [
                    'from' => env('TWILIO_PHONE_NUMBER'),
                    'body' => $validated['message'],
                ]);
                $sms->update(['status' => 'Sent', 'sent_at' => now()]);
            } catch (\Exception $e) {
                $sms->update(['status' => 'Failed']);
            }
        }

        event(new AuditTrailLogged(
            Auth::id(),
            'Send SMS',
            "Sent SMS notification ID: {$sms->sms_id}",
            'SmsNotification',
            $sms->sms_id
        ));

        return redirect()->route('sms.index')->with('success', 'SMS notification sent.');
    }
}
