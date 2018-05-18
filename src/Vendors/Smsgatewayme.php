<?php

namespace Yugo\SMSGateway\Vendors;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Unirest\Request;
use Unirest\Request\Body;
use Yugo\SMSGateway\Interfaces\SMS;
use Yugo\SMSGateway\Vendors\Smsgatewayme\Callback;
use Yugo\SMSGateway\Vendors\Smsgatewayme\Contact;

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

    /**
     * Store requested data into cache.
     *
     * @var bool
     */
    private $cache = false;

    public function __construct()
    {
        $this->device = (int) config('message.smsgatewayme.device');
        $this->token = config('message.smsgatewayme.token');

        Request::defaultHeaders([
            'Accept'        => 'application/json',
            'Authorization' => $this->token,
        ]);
    }

    /**
     * Set device ID manually.
     *
     * @param int $id
     *
     * @return self
     */
    public function setDevice(int $id): self
    {
        $this->device = $id;

        return $this;
    }

    /**
     * Set token manually.
     *
     * @param string $token
     *
     * @return self
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set cache as true and trigger cache data for every request.
     *
     * @param bool $cache
     *
     * @return self
     */
    public function setCache(bool $cache = true): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get device information.
     *
     * @return object|null
     */
    public function device(?int $id = null): ?array
    {
        if (is_null($id)) {
            $id = $this->device;
        }

        $key = sprintf('smsgatewayme.device.%s', $id);
        $device = Cache::remember($key, 3600 * 24 * 7, function () use (&$response, $id) {
            $response = Request::get($this->baseUrl.'device/'.$id);

            if ($response->code != 200) {
                if (!empty($response->body->message)) {
                    Log::error($response->body->message);
                }
            }

            return $response->body;
        });

        return [
            'code'    => $response->code ?? 200,
            'message' => 'OK',
            'data'    => $device,
        ];
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
        $this->checkConfig();

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

        if ($response->code == 200) {
            return [
                'code'    => $response->code,
                'message' => 'OK',
                'data'    => $response->body,
            ];
        } else {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }

            return [
                'code'    => $response->code,
                'message' => $response->body->message ?? '',
                'data'    => $response->body,
            ];
        }
    }

    /**
     * Cancel queued message.
     *
     * @return array|null
     */
    public function cancel(array $identifiers = []): ?array
    {
        $this->checkConfig();

        if (empty($identifiers)) {
            return null;
        }

        $messages = [];
        foreach ($identifiers as $id) {
            $messages[] = ['id' => (int) $id];
        }

        $body = Body::json($messages);
        $response = Request::post($this->baseUrl.'message/cancel', [], $body);

        if ($response->code == 200) {
            return [
                'code'    => $response->code,
                'message' => 'OK',
                'data'    => $response->body,
            ];
        } else {
            if (!empty($response->body->message)) {
                Log::error($response->body->message);
            }

            return [
                'code'    => $response->code,
                'message' => $response->body->message ?? '',
                'data'    => $response->body,
            ];
        }
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
        $this->checkConfig();

        $key = sprintf('smsgatewayme.info.%s', $id);

        if ($this->cache === true and Cache::has($key)) {
            $message = [
                'code'    => 200,
                'message' => 'OK',
                'data'    => Cache::get($key),
            ];
        } else {
            $response = Request::get($this->baseUrl.'message/'.$id);

            if ($response->code == 200) {
                Cache::put($key, $response->body, 3600 * 24);
            } else {
                if (!empty($response->body->message)) {
                    Log::error($response->body->message);
                }
            }

            $message = [
                'code'    => $response->code,
                'message' => ($response->code == 200) ? 'OK' : $response->body->message ?? '',
                'data'    => $response->body,
            ];
        }

        return $message;
    }

    /**
     * Callback/hook operations for SMSgateway.me.
     *
     * @return callable
     */
    public function callback(): Callback
    {
        return new Callback($this->device, $this->token);
    }

    /**
     * Contact utilities.
     *
     * @return Contact
     */
    public function contact(): Contact
    {
        return new Contact($this->device, $this->token);
    }

    /**
     * Check default config for easy debuggin.
     *
     * @return void
     */
    private function checkConfig(): void
    {
        if (empty($this->device)) {
            Log::warning('Config "message.smsgatewayme.device" is not defined.');
        }

        if (empty($this->token)) {
            Log::warning('Config "message.smsgatewayme.token" is not defined.');
        }
    }
}
