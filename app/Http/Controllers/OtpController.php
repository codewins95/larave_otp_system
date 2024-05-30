<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class OtpController extends Controller
{


    public function sendMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $otp = rand(100000, 999999);
        $email = $request->email;
        $otpCreatedAt = Carbon::now();

        try {
            Mail::to($email)->send(new SendMail($otp));

            Cache::put('OTP_Token', ['otp' => $otp, 'otp_created_at' => Carbon::now()], now()->addMinutes(2));

            $data = [
                "message" => "OTP sent via email!",
                "response" => "success"
            ];

            return response()->json($data);
        } catch (\Exception $th) {
            $error = [
                "message" => "Failed to send OTP via email.",
                "error" => $th->getMessage()
            ];
            return response()->json($error, 500);
        }
    }


    public function validateOTP(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "otp" => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }


        $cachedData = Cache::get('OTP_Token');

        if (!$cachedData || empty($cachedData['otp'])) {
            return response()->json(['error' => 'OTP data not found.'], 422);
        }

        $otp = $request->otp;
        $otpCreatedAt = Carbon::parse($cachedData['otp_created_at']);


        $expirationTime = $otpCreatedAt->addMinutes(2);
        if (Carbon::now()->gt($expirationTime)) {
            Cache::forget('OTP_Token');
            return response()->json(['error' => 'OTP has expired.'], 422);
        }


        if ($otp != $cachedData['otp']) {
            return response()->json(['error' => 'Invalid OTP.'], 422);
        }

        Cache::forget('OTP_Token');
        return response()->json(['message' => 'OTP validated successfully.', 'response' => 'success']);
    }


    public function formSubmit(Request $request)
    {
        dd($request->all());
    }
}
