<?php

namespace Yugo\SMSGateway\Vendors;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Unirest\Request;
use Unirest\Request\Body;
use Yugo\SMSGateway\Interfaces\SMS;

class Smsgatewayme implements SMS
{
    /**
     * API base URL.
     *
     * @var string
     */
    private $baseUrl = 'https://smsgateway.me/api/v4/';

    /**
     * Device ID from SMSgateway.me.
     *
     * @var string
     */
    private $device = null;

    /**
     * Generated token from SMSgateway.me.
     *
     * @link http://smsgateway.me/dashboard/settings
     *
     * @var string
     */
    private $token = null;

    public function __construct()
    {
        $this->device = config('message.smsgatewayme.device');
        $this->token = config('message.smsgatewayme.token');

        Request::defaultHeaders([
            'Accept'        => 'application/json',
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

        $response = Request::get($this->baseUrl.'device/'.$device);

        if ($response->code == 200) {
            Cache::forever('smsgatewayme.device.'.$device, $response->body);
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
     *
     * @return object|null
     */
    public function send(array $destinations, string $text): ?array
    {
        if (empty($destinations)) {
            return null;
        }

        $messages = [];
        foreach ($destinations as $destination) {
            $messages[] = [
                'phone_number' => $destination,
                'message'      => $text,
                'device_id'    => $this->device,
            ];
        }

        $body = Body::json($messages);
        $response = Request::post($this->baseUrl.'message/send', [], $body);

        if ($response->code != 200) {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return (array) $response->body ?? null;
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
        $response = Request::post($this->baseUrl.'message/cancel', [], $body);

        if ($response->code != 200) {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return (array) $response->body;
    }

    /**
     * Get detailed information about message.
     *
     * @param int $id
     *
     * @return array|null
     */
    public function info(int $id): ?array
    {
        $response = Request::get($this->baseUrl.'message/'.$id);

        return (array) $response->body;
    }
}
