<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function initiateBatchNabroll(Request $request)
    {
        // NABROLL functionality has been removed
        return back()->with('error', 'NABROLL payment system is no longer available.');
    }

    public function initiateSingleNabroll(Request $request, $transactionId)
    {
        // NABROLL functionality has been removed
        return back()->with('error', 'NABROLL payment system is no longer available.');
    }
    
    public function handleNabrollResponse(Request $request)
    {
        // NABROLL functionality has been removed
        return response()->json(['status' => 'NABROLL has been removed from the system']);
    }
}
