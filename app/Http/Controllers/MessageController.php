<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function NotifyApi($firebaseToken,$title,$message)
    {
        $SERVER_API_KEY = "AAAAmxGP9BQ:APA91bHLRV61ooU25yLgTM6xL3ndP5C0bXrNS-3hll-8EM5R5gpJ13yPQ3Uen4ezDZCfJWvHx4QGMm1nCVAaP8uoO_2XF4oCdEsa8JpVdGcqd1WFmcPwyIVm3EGD4UXAr2aUdv2M6rPh";

        $data = [
            "registration_ids" => [$firebaseToken],
            "notification" => [
                "title" => $title,
                "body" => $message,
                "content_available" => true,
                "priority" => "high",
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        return 1;
    }
}
