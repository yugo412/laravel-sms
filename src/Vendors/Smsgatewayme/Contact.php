<?php

namespace Yugo\SMSGateway\Vendors\Smsgatewayme;

use Illuminate\Support\Facades\Log;
use Unirest\Request;
use Unirest\Request\Body;

class Contact
{
    /**
     * API URL.
     *
     * @var string
     */
    private $baseUrl = 'https://smsgateway.me/api/v4/';

    /**
     * Default device.
     *
     * @var int
     */
    private $device;

    /**
     * Authorization.
     *
     * @var string
     */
    private $token;

    public function __construct(int $device, string $token)
    {
        $this->device = $device;
        $this->token = $token;

        Request::defaultHeaders([
            'Accept'        => 'application/json',
            'Authorization' => $this->token,
        ]);
    }

    /**
     * Store new contact to SMSGateway.me.
     *
     * @param string $name
     * @param array  $numbers
     *
     * @return array|null
     */
    public function create(string $name, array $numbers): ?array
    {
        $body = Body::json([
            [
                'name'          => $name,
                'phone_numbers' => $numbers,
            ],
        ]);
        $response = Request::post($this->baseUrl.'contact', [], $body);

        if ($response->code != 200) {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return [
            'code'    => $response->code,
            'message' => ($response->code == 200) ? 'OK' : $response->body->message ?? '',
            'data'    => $response->body,
        ];
    }

    /**
     * Get detailed stored contact.
     *
     * @param int $id
     *
     * @return array|null
     */
    public function info(int $id): ?array
    {
        $response = Request::get($this->baseUrl.'contact/'.$id);

        if ($response->code != 200) {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return [
            'code'    => $response->code,
            'message' => ($response->code == 200) ? 'OK' : $response->body->message ?? '',
            'data'    => $response->body,
        ];
    }

    /**
     * Add number to existing contact.
     *
     * @param int    $id
     * @param string $number
     *
     * @return array|null
     */
    public function addNumber(int $id, string $number): ?array
    {
        $response = Request::put($this->baseUrl.sprintf('contact/%d/phone-number/%s', $id, $number));

        if ($response->code != 200) {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return [
            'code'    => $response->code,
            'message' => ($response->code == 200) ? 'OK' : $response->body->message ?? '',
            'data'    => $response->body,
        ];
    }

    /**
     * Remove number from existing contact.
     *
     * @param int    $id
     * @param string $number
     *
     * @return array|null
     */
    public function removeNumber(int $id, string $number): ?array
    {
        $response = Request::delete($this->baseUrl.sprintf('contact/%d/phone-number/%s', $id, $number));

        if ($response->code != 200) {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return [
            'code'    => $response->code,
            'message' => ($response->code == 200) ? 'OK' : $response->body->message ?? '',
            'data'    => $response->body,
        ];
    }

    public function update(int $id, string $name, array $numbers = []): ?array
    {
        $body = Body::json([
            'name'          => $name,
            'phone_numbers' => $numbers,
        ]);
        $response = Request::put($this->baseUrl.'contact/'.$id, [], $body);

        if ($response->code != 200) {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }
        }

        return [
            'code'    => $response->code,
            'message' => ($response->code == 200) ? 'OK' : $response->body->message ?? '',
            'data'    => $response->body,
        ];
    }
}
