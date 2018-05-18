<?php

namespace Yugo\SMSGateway\Vendors;

use Illuminate\Support\Facades\Log;
use Unirest\Request;
use Yugo\SMSGateway\Interfaces\SMS;

class Zenziva implements SMS
{
    /**
     * API base URL.
     *
     * @var string
     */
    private $baseUrl = 'https://reguler.zenziva.net/apps';

    /**
     * Default userkey from Zenziva.
     *
     * @var string
     */
    private $userkey = null;

    /**
     * Default passkey from Zenziva.
     *
     * @var string
     */
    private $passkey = null;

    public function __construct()
    {
        $this->userkey = config('message.zenziva.userkey');
        $this->passkey = config('message.zenziva.passkey');
    }

    /**
     * Set userkey manually via app.
     *
     * @param string $user
     *
     * @return self
     */
    public function setUser(string $user): self
    {
        $this->userkey = $user;

        return $this;
    }

    /**
     * Set password manually via app.
     *
     * @param string $password
     *
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->passkey = $password;

        return $this;
    }

    /**
     * Send message using Zenziva API.
     *
     * @param array  $destinations
     * @param string $message
     *
     * @return array|null
     */
    public function send(array $destinations, string $message): ?array
    {
        $this->checkConfig();

        if (!empty($destinations)) {
            $destination = $destinations[0];
        }

        $query = http_build_query([
            'userkey' => $this->userkey,
            'passkey' => $this->passkey,
            'nohp'    => $destination,
            'pesan'   => $message,
        ]);

        $response = Request::get($this->baseUrl.'/smsapi.php?'.$query);

        $xml = simplexml_load_string($response->body);
        $body = json_decode(json_encode($xml), true);

        if (!empty($body['message']) and $body['message']['status'] != 0) {
            Log::error(sprintf('Zenziva: %s.', $body['message']['text']));
        }

        return [
            'code'    => $response->code,
            'message' => ($response->code == 200) ? 'OK' : $body['message']['text'] ?? '',
            'data'    => $body,
        ];
    }

    /**
     * Check credit balance.
     *
     * @return array|null
     */
    public function credit(): ?array
    {
        $this->checkConfig();

        $query = http_build_query([
            'userkey' => $this->userkey,
            'passkey' => $this->passkey,
        ]);

        $response = Request::get($this->baseUrl.'/smsapibalance.php?'.$query);

        $xml = simplexml_load_string($response->body);
        $body = json_decode(json_encode($xml), true);

        return [
            'code'    => $response->code,
            'message' => ($response->code == 200) ? 'OK' : $body['message']['text'] ?? '',
            'data'    => $body,
        ];
    }

    /**
     * Check config and add to log for easy debugging.
     *
     * @return void
     */
    private function checkConfig(): void
    {
        if (empty($this->userkey)) {
            Log::warning('Config "message.zenziva.userkey" is not defined.');
        }

        if (empty($this->passkey)) {
            Log::warning('Config "message.zenziva.passkey" is not defined.');
        }
    }
}
