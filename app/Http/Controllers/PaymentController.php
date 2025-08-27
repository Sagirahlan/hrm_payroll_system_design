<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function initiateBatchNabroll(Request $request)
    {
        $publicKey = 'Pk_TeStHV9FnLZE1vSidgkH36b4s473lpKYkI58gYgc6M';
        $secretKey = 'Sk_teSTN-HY[n1]wIO32A-AU0XP5kRZ[tzHpOxQ6bf9]]';
        $responseUrl = route('nabroll.response');
        $apiEndpoint = 'https://demo.nabroll.com.ng/api/v1/transactions/initiate';

        $transactions = PaymentTransaction::where('status', 'pending')->get();

        foreach ($transactions as $tx) {
            $payerRefNo = 'EMP' . str_pad($tx->employee_id, 5, '0', STR_PAD_LEFT);
            $amount = number_format($tx->amount, 2, '.', '');

            $hashString = $payerRefNo . $amount . $publicKey;
            $hash = hash_hmac('sha256', $hashString, $secretKey);

            $payload = [
                "ApiKey" => $publicKey,
                "Hash" => $hash,
                "Amount" => $amount,
                "PayerRefNo" => $payerRefNo,
                "PayerName" => $tx->account_name,
                "Email" => "employee{$tx->employee_id}@company.gov",
                "Mobile" => "08000000000",
                "Description" => "Salary Payment - " . now()->format('F Y'),
                "ResponseUrl" => $responseUrl,
                "MetaData" => "Payroll ID: {$tx->payroll_id}",
                "FeeBearer" => "Customer"
            ];

            // ✅ Log the payload being sent
            Log::info("Sending NABRoll payload for Employee ID {$tx->employee_id}:", $payload);

            $response = Http::post($apiEndpoint, $payload);

            // ✅ Log raw HTTP response
            Log::info("NABRoll response for Employee ID {$tx->employee_id}:", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // ✅ Try to decode and log JSON result (if applicable)
            $decoded = $response->json();
            Log::info("NABRoll decoded response for Employee ID {$tx->employee_id}:", $decoded ?? ['decode' => 'Failed']);

            if ($response->successful() && $decoded['code'] === '00') {
                $tx->update([
                    'status' => 'processing',
                    'transaction_ref' => $decoded['TransactionRef'] ?? null,
                    'payment_code' => $decoded['PaymentCode'] ?? null,
                    'payment_url' => $decoded['PaymentUrl'] ?? null,
                    'payment_date' => now()
                ]);
            } else {
                Log::error("NABRoll batch failed for Employee ID {$tx->employee_id}", [
                    'payload' => $payload,
                    'response' => $response->body(),
                ]);
            }
        }

        return back()->with('success', 'Payroll sent to NABRoll successfully.');
    }

    public function initiateSingleNabroll(Request $request, $transactionId)
    {
        $publicKey = 'Pk_TeStHV9FnLZE1vSidgkH36b4s473lpKYkI58gYgc6M';
        $secretKey = 'Sk_teSTN-HY[n1]wIO32A-AU0XP5kRZ[tzHpOxQ6bf9]]';
        $responseUrl = route('nabroll.response');
        $apiEndpoint = 'https://demo.nabroll.com.ng/api/v1/transactions/initiate';

        $tx = PaymentTransaction::findOrFail($transactionId);

        $payerRefNo = 'EMP' . str_pad($tx->employee_id, 5, '0', STR_PAD_LEFT);
        $amount = number_format($tx->amount, 2, '.', '');

        $hashString = $payerRefNo . $amount . $publicKey;
        $hash = hash_hmac('sha256', $hashString, $secretKey);

        $payload = [
            "ApiKey" => $publicKey,
            "Hash" => $hash,
            "Amount" => $amount,
            "PayerRefNo" => $payerRefNo,
            "PayerName" => $tx->account_name,
            "Email" => "employee{$tx->employee_id}@company.gov",
            "Mobile" => "08000000000",
            "Description" => "Salary Payment - " . now()->format('F Y'),
            "ResponseUrl" => $responseUrl,
            "MetaData" => "Payroll ID: {$tx->payroll_id}",
            "FeeBearer" => "Customer"
        ];

        Log::info("Sending NABRoll single payment payload for Transaction ID {$tx->transaction_id}:", $payload);

        $response = Http::post($apiEndpoint, $payload);

        Log::info("NABRoll single payment response for Transaction ID {$tx->transaction_id}:", [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $decoded = $response->json();
        Log::info("NABRoll single payment decoded response for Transaction ID {$tx->transaction_id}:", $decoded ?? ['decode' => 'Failed']);

        if ($response->successful() && $decoded['code'] === '00') {
            $tx->update([
                'status' => 'processing',
                'transaction_ref' => $decoded['TransactionRef'] ?? null,
                'payment_code' => $decoded['PaymentCode'] ?? null,
                'payment_url' => $decoded['PaymentUrl'] ?? null,
                'payment_date' => now()
            ]);
            return back()->with('success', 'Payment initiated successfully.');
        } else {
            Log::error("NABRoll single payment failed for Transaction ID {$tx->transaction_id}", [
                'payload' => $payload,
                'response' => $response->body(),
            ]);
            return back()->with('error', 'Failed to initiate payment. Please check logs for details.');
        }
    }
