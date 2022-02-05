<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use App\Models\PushNotificationTokens;
use App\Models\FailedPushNotifications;

use DateTime;

class CloudMessagingController extends Controller
{

    //using
    //CloudMessagingController::send_messages_to_device(1, 'app en pinches segundo plano', 'test final vaso.xml poppers');

    public static function send_messages_to_device($user_id, $title, $body)
    {

        $messaging = app('firebase.messaging');
        $tokens = PushNotificationTokens::where('user_id', $user_id)->get();
        $deviceTokens = array();

        foreach ($tokens as $token) {
            array_push($deviceTokens, $token->token,);
        }

        $message = CloudMessage::fromArray([
            'data' => array(
                'first_key' => 'First Value',
                'second_key' => 'Second Value',
            ), // optional
            'android' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#androidconfig
                'ttl' => '3600s',
                'priority' => 'HIGH',
                'notification' => [
                    'notification_priority' => 'PRIORITY_HIGH',
                    'title' => $title,
                    'body' => $body,
                    //'icon' => 'stock_ticker_update',
                    //'color' => '#f45342',
                ],
            ],
            'apns' => [
                // https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages#apnsconfig
                'headers' => [
                    'apns-priority' => '10',
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'badge' => 42,
                    ],
                ],
            ],
        ]);

        $sendReport = $messaging->sendMulticast($message, $deviceTokens);

        if ($sendReport->hasFailures()) {
            foreach ($sendReport->failures()->getItems() as $failure) {
                FailedPushNotifications::create([
                    'user_id' => $user_id,
                    'title' => $title,
                    'body' => $body,
                    'token' => $failure->target()->value(),
                    'error' => $failure->error()->getMessage()
                ]);
            }
        }
    }

    public function clean_db_tokens(){

        $date = new DateTime();
        $date->modify("-30 day");
        PushNotificationTokens::where('updated_at', '<', $date)->delete();
        
    }
}
