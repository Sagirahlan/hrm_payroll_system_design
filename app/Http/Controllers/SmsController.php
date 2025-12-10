<?php
namespace App\Http\Controllers;

use App\Models\SmsNotification;
use App\Models\Employee;
use App\Models\Department;
use App\Models\GradeLevel;
use App\Models\Cadre;
use App\Models\AppointmentType;
use App\Models\State;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SmsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['permission:view_sms_notifications'], ['only' => ['index']]);
        $this->middleware(['permission:create_sms_notifications'], ['only' => ['create', 'store']]);
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
        $cadres = Cadre::all();
        $appointmentTypes = AppointmentType::all();
        $states = State::all();
        $steps = Step::all();

        // Get all possible employee statuses
        $statuses = Employee::select('status')->distinct()->pluck('status');

        // Get all possible genders
        $genders = Employee::select('gender')->distinct()->whereNotNull('gender')->pluck('gender');

        return view('sms.create', compact(
            'departments',
            'gradeLevels',
            'cadres',
            'appointmentTypes',
            'states',
            'steps',
            'statuses',
            'genders'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:All,Department,GradeLevel,Cadre,AppointmentType,Status,Gender,State',
            'recipient_id' => 'nullable|exists:departments,department_id',
            'grade_level_id' => 'nullable|exists:grade_levels,id',
            'cadre_id' => 'nullable|exists:cadres,cadre_id',
            'appointment_type_id' => 'nullable|exists:appointment_types,id',
            'status' => 'nullable|string',
            'gender' => 'nullable|string',
            'state_id' => 'nullable|exists:states,state_id',
            'message' => 'required|string|max:160',
        ]);

        $recipientId = null;
        if (in_array($validated['recipient_type'], ['Department', 'GradeLevel', 'Cadre', 'AppointmentType', 'State'])) {
            if ($validated['recipient_type'] === 'Department') {
                $recipientId = $validated['recipient_id'];
            } elseif ($validated['recipient_type'] === 'GradeLevel') {
                $recipientId = $validated['grade_level_id'];
            } elseif ($validated['recipient_type'] === 'Cadre') {
                $recipientId = $validated['cadre_id'];
            } elseif ($validated['recipient_type'] === 'AppointmentType') {
                $recipientId = $validated['appointment_type_id'];
            } elseif ($validated['recipient_type'] === 'State') {
                $recipientId = $validated['state_id'];
            }
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

        // Build query based on the selected filter
        $query = Employee::query();

        switch ($validated['recipient_type']) {
            case 'Department':
                $query->where('department_id', $validated['recipient_id']);
                break;
            case 'GradeLevel':
                $query->where('grade_level_id', $validated['grade_level_id']);
                break;
            case 'Cadre':
                $query->where('cadre_id', $validated['cadre_id']);
                break;
            case 'AppointmentType':
                $query->where('appointment_type_id', $validated['appointment_type_id']);
                break;
            case 'Status':
                $query->where('status', $validated['status']);
                break;
            case 'Gender':
                $query->where('gender', $validated['gender']);
                break;
            case 'State':
                $query->where('state_id', $validated['state_id']);
                break;
            case 'All':
            default:
                // No additional filtering needed, query already includes all employees
                break;
        }

        // Get the recipients
        $recipients = $query->pluck('mobile_no')->filter(function ($mobile_no) {
            return !empty($mobile_no); // Only include employees with valid mobile numbers
        });

        $successCount = 0;
        $failCount = 0;

        foreach ($recipients as $phone) {
            try {
                $twilio->messages->create($phone, [
                    'from' => env('TWILIO_PHONE_NUMBER'),
                    'body' => $validated['message'],
                ]);
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        // Update SMS status based on results
        if ($successCount > 0 && $failCount === 0) {
            $sms->update(['status' => 'Sent', 'sent_at' => now()]);
        } elseif ($successCount > 0 && $failCount > 0) {
            $sms->update(['status' => 'Partially Sent', 'sent_at' => now()]);
        } elseif ($failCount > 0) {
            $sms->update(['status' => 'Failed', 'sent_at' => now()]);
        } else {
            $sms->update(['status' => 'Sent', 'sent_at' => now()]); // If no recipients found, still mark as sent
        }

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action' => 'sent_sms',
            'description' => "Sent SMS notification ID: {$sms->sms_id} to {$recipients->count()} employees",
            'action_timestamp' => now(),
            'entity_type' => 'SmsNotification',
            'entity_id' => $sms->sms_id,
        ]);

        return redirect()->route('sms.index')->with('success', "SMS notification sent to {$recipients->count()} employees.");
    }
}
