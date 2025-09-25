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
        $this->middleware(['auth', 'permission:manage_employees']);
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
        ]);

        try {
            $retirement = Retirement::where('employee_id', $validated['employee_id'])
                ->where('status', 'approved')
                ->firstOrFail();

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
}