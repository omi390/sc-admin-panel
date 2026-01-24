<?php

namespace Modules\SMSModule\Lib;

use Illuminate\Support\Facades\Config;
use Nexmo\Laravel\Facade\Nexmo;
use Twilio\Rest\Client;

class SMS_gateway
{
    public static function send($receiver, $otp): string
    {
        $config = self::get_settings('twilio');
        if (isset($config) && $config['status'] == 1) {
            return self::twilio($receiver, $otp);
        }

        $config = self::get_settings('nexmo');
        if (isset($config) && $config['status'] == 1) {
            return self::nexmo($receiver, $otp);
        }

        $config = self::get_settings('2factor');
        if (isset($config) && $config['status'] == 1) {
            return self::two_factor($receiver, $otp);
        }

        $config = self::get_settings('msg91');
        if (isset($config) && $config['status'] == 1) {
            return self::msg_91($receiver, $otp);
        }

        $config = self::get_settings('releans');
        if (isset($config) && $config['status'] == 1) {
            return self::releans($receiver, $otp);
        }

        $config = self::get_settings('alphanet_sms');
        if (isset($config) && $config['status'] == 1) {
            return self::alphanet_sms($receiver, $otp);
        }


        return 'not_found';
    }

    public static function twilio($receiver, $otp): string
    {
        $config = self::get_settings('twilio');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $sid = $config['sid'];
            $token = $config['token'];
            try {
                $twilio = new Client($sid, $token);
                $twilio->messages
                    ->create($receiver,
                        array(
                            "messagingServiceSid" => $config['messaging_service_sid'],
                            "body" => $message
                        )
                    );
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function nexmo($receiver, $otp): string
    {
        $config = self::get_settings('nexmo');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            try {
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, 'https://rest.nexmo.com/sms/json');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, "from=" . $config['from'] . "&text=" . $message . "&to=" . $receiver . "&api_key=" . $config['api_key'] . "&api_secret=" . $config['api_secret']);

                $headers = array();
                $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                }
                curl_close($ch);
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function two_factor($receiver, $otp): string
    {
        $config = self::get_settings('2factor');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $api_key = $config['api_key'];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://2factor.in/API/V1/" . $api_key . "/SMS/" . $receiver . "/" . $otp . "",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if (!$err) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function msg_91($receiver, $otp): string
    {
        
        
        
        
 
  

        
        $config = self::get_settings('msg91');
        $response = 'error';
        
          $templateId = '65cb03aad6fc057f4f174c52'; // Replace with your MSG91 template ID
         $authKey = '265261AmRIjXcx565cb5a87P1'; // Replace with your MSG91 auth key
   


   

    $data = [
        "template_id" => $templateId,
        "short_url" => "1", // Example value, adjust as needed
        "short_url_expiry" => "3600", // Example value, adjust as needed
        "realTimeResponse" => "1", // Example value, adjust as needed
        "recipients" => [
            [
                "mobiles" => $receiver, // Replace with the recipient's mobile number
                "var1" => $otp,             // Pass the OTP dynamically
                "VAR2" => "VALUE 2"         // Replace with actual value as needed
            ]
        ]
    ];
    

    
    // Initialize cURL
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://control.msg91.com/api/v5/flow",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data), // Pass the JSON-encoded payload
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "authkey: 265261AmRIjXcx565cb5a87P1", // Replace with your actual auth key
            "content-type: application/json"
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        $response = 'error';
    } else {
      $response = 'success';
    }
    
    return $response;

        
        // if (isset($config) && $config['status'] == 1) {
            
        //     \Log::info("template id");
        //     \Log::info($config['template_id']);
                     
        //     \Log::info(" id");
        //     \Log::info($config['auth_key']);
        //     $receiver = str_replace("+", "", $receiver);
        //     $curl = curl_init();
        //     curl_setopt_array($curl, array(
        //         CURLOPT_URL => "https://api.msg91.com/api/v5/otp?template_id=".$config['template_id']."&mobile=".$receiver."&authkey=".$config['auth_key']."",
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => "",
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => "GET",
        //         CURLOPT_POSTFIELDS => "{\"VAR1\":\"$otp\"}",
        //         CURLOPT_HTTPHEADER => array(
        //             "content-type: application/json"
        //         ),
        //     ));
        //     $response = curl_exec($curl);
        //     $err = curl_error($curl);
            
        //     \Log::info($response);
        //     curl_close($curl);
        //     if (!$err) {
        //         $response = 'success';
        //     } else {
        //         $response = 'error';
        //     }
        // }
        return $response;
    }

    public static function releans($receiver, $otp): string
    {
        $config = self::get_settings('releans');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $curl = curl_init();
            $from = $config['from'];
            $to = $receiver;
            $message = str_replace("#OTP#", $otp, $config['otp_template']);

            try {
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.releans.com/v2/message",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "sender=$from&mobile=$to&content=$message",
                    CURLOPT_HTTPHEADER => array(
                        "Authorization: Bearer " . $config['api_key']
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $response = 'success';
            } catch (\Exception $exception) {
                $response = 'error';
            }

        }
        return $response;
    }

    public static function alphanet_sms($receiver, $otp): string
    {
        $config = self::get_settings('alphanet_sms');
        $response = 'error';
        if (isset($config) && $config['status'] == 1) {
            $receiver = str_replace("+", "", $receiver);
            $message = str_replace("#OTP#", $otp, $config['otp_template']);
            $api_key = $config['api_key'];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.sms.net.bd/sendsms',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('api_key' => $api_key, 'msg' => $message, 'to' => $receiver),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ((int) data_get(json_decode($response,true),'error') === 0) {
                $response = 'success';
            } else {
                $response = 'error';
            }
        }
        return $response;
    }

    public static function get_settings($name)
    {
        $data = config_settingss($name, 'sms_config');
        if (isset($data) && !is_null($data->live_values)) {
            return json_decode($data->live_values, true);
        }
        return null;
    }
}
