<?php
namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;

class FcmService
{
protected $messaging;

protected $userRepository;

public function __construct()
{
$firebase = (new Factory) ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
$this->messaging = $firebase->createMessaging();   }

public function sendNotification($deviceToken, $title, $body, array $data = [])
{
$notification = Notification::create($title, $body);

$message = CloudMessage::withTarget('token', $deviceToken)
->withNotification($notification)
->withData($data);

return $this->messaging->send($message); }
}
