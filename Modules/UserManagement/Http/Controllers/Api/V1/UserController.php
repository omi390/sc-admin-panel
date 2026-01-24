<?php

namespace Modules\UserManagement\Http\Controllers\Api\V1;

use Carbon\CarbonInterval;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\SMSModule\Lib\SMS_gateway;
use Modules\UserManagement\Emails\OTPMail;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserVerification;
use Modules\PaymentModule\Traits\SmsGateway;

class UserController extends Controller
{
    public function __construct(
        private User $user,
        private UserVerification $userVerification
    )
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
       public function banners(){
        
        $banners = [
            'https://mediamodifier.com/blog/wp-content/uploads/2020/03/free-banner-maker-online-design-templates.jpg'
            ];
          return response()->json(response_formatter(DEFAULT_200, $banners), 200);
    }
    
     public function tendors(){
     
                    $tendors = DB::table('tendors')->orderBy('id','desc')->get();

          return response()->json(response_formatter(DEFAULT_200, $tendors), 200);
    }
    
    public function createPaymentSession(Request $request)
    {
        $customerId = $request->user_id; // or anything unique
        $amount = $request->amount;
        $currency = 'INR';

        $response = Http::withHeaders([
            'x-client-id' =>'84419368351191ee749267ce45391448',
            'x-client-secret' => 'cfsk_ma_prod_9eb05bcadfe0b3d8707ad0d04202af4c_99fffcfe',
            'x-api-version' => '2022-09-01',
            'Content-Type' => 'application/json'
        ])->post('https://api.cashfree.com/pg/orders', [
            "customer_details" => [
                "customer_id" => $customerId,
                "customer_email" => $request->email ?? "test@example.com",
                "customer_phone" => $request->phone ?? "9999999999"
            ],
            "order_amount" => $amount,
            "order_currency" => $currency,
            "order_note" => "Test Order from Laravel API"
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json([
                'payment_session_id' => $data['payment_session_id'],
                'order_id' => $data['order_id'],
                'payment_link' => $data['payment_link'] ?? null,
            ]);
        }

        return response()->json([
            'error' => true,
            'message' => $response->json()
        ], 400);
    }
    public function checkCashfreeStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);
    
        $orderId = $request->order_id;
    
        $response = Http::withHeaders([
            'x-api-version' => '2022-09-01',
            'x-client-id' =>'84419368351191ee749267ce45391448',
            'x-client-secret' => 'cfsk_ma_prod_9eb05bcadfe0b3d8707ad0d04202af4c_99fffcfe',
            'Content-Type' => 'application/json',
        ])->get("https://api.cashfree.com/pg/orders/$orderId");
    
        if ($response->successful()) {
            $data = $response->json();
            return response()->json(
                [
                    'status' => $data['order_status']
                    ]
               
                );
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch transaction status',
                'details' => $response->body(),
            ], $response->status());
        }
    }
    
}
