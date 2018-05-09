<?php

namespace Yugo\SMSGateway\Vendors;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yugo\SMSGateway\Interfaces\SMS;
use Yugo\SMSGateway\Models\Contact;
use Yugo\SMSGateway\Models\Message;
use Unirest\Request;
use Unirest\Request\Body;

class Smsgatewayme implements SMS
{

    /**
     * API base URL.
     *
     * @var string
     */
    private $baseUrl = 'https://smsgateway.me/api/v4/';

    /**
     * Device ID from SMSgateway.me
     *
     * @var string
     */
    private $device = null;

    /**
     * Generated token from SMSgateway.me.
     *
     * @link http://smsgateway.me/dashboard/settings
     * @var string
     */
    private $token = null;

    public function __construct()
    {
        $this->device = config('services.smsgatewayme.device');
        $this->token = config('services.smsgatewayme.token');

        Request::defaultHeaders([
            'Accept' => 'application/json',
            'Authorization' => $this->token,
        ]);
    }

    /**
     * Get device information.
     *
     * @return object|null
     */
    public function device(?string $device = null): ?array
    {
        if (is_null($device)) {
            $device = $this->device;
        }

        $response = Request::get($this->baseUrl . 'device/' . $device);

        if ($response->code == 200) {
            Cache::forever('smsgatewayme.device.' . $device, $response->body);
        } else {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return (array) $response->body ?? null;
    }

    /**
     * Send message to single number.
     *
     * @param string $destination
     * @param string $text
     * @return object|null
     */
    public function send(array $destinations = [], string $text, ?int $userId = null): ?array
    {
        if (empty($destinations)) {
            return null;
        }

        $messages = [];
        foreach ($destinations as $destination) {
            $messages[] = [
                'phone_number' => $destination,
                'message' => $text,
                'device_id' => $this->device,
            ];
        }

        $body = Body::json($messages);
        $response = Request::post($this->baseUrl . 'message/send', [], $body);

        $messages = [];
        if ($response->code == 200) {
            foreach ($response->body as $body) {
                DB::transaction(function () use (&$messages, $body, $userId) {
                    $contact = Contact::firstOrCreate([
                        'number' => $body->phone_number,
                    ]);

                    $messages[] = Message::create([
                        'message_id' => $body->id,
                        'contact_id' => $contact->id ?? null,
                        'user_id' => $userId,
                        'source' => '',
                        'destination' => $body->phone_number,
                        'text' => $body->message,
                        'status' => $body->status,
                        'metadata' => [
                            'provider' => env('SMS_VENDOR'),
                            'device_id' => $body->device_id,
                        ],
                    ]);
                });
            }
        } else {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return $messages;
    }

    /**
     * Cancel queued message.
     *
     * @return array|null
     */
    public function cancel(array $identifiers = []): ?array
    {
        if (empty($identifiers)) {
            return null;
        }

        $messages = [];
        foreach ($identifiers as $id) {
            $messages[] = ['id' => (int) $id];
        }

        $body = Body::json($messages);
        $response = Request::post($this->baseUrl . 'message/cancel', [], $body);

        if ($response->code == 200) {
            foreach ($response->body as $body) {
                $message = Message::whereMessageId($body->id)->first();
                if (!empty($message)) {
                    $message->fill(['status' => 'canceled']);
                    $message->save();
                }
            }
        } else {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return (array) $response->body;
    }

    /**
     * Get detailed information about message.
     *
     * @param integer $id
     * @return array|null
     */
    public function info(int $id): ?array
    {
        $response = Request::get($this->baseUrl . 'message/' . $id);

        return (array) $response->body;
    }
}
