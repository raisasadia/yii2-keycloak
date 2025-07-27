<?php

namespace yii2keycloak\Keycloak;

use GuzzleHttp\Client;
use Yii;

class KeycloakAdminService
{
    protected $realm;
    protected $baseUrl;
    protected $params;
    protected $cachedToken = null;

    public function __construct()
    {
        $this->params = Yii::$app->params['keycloak'];
        $this->realm = $this->params['realm'];
        $this->baseUrl = rtrim($this->params['base_url'], '/');
    }

    protected function getAdminToken()
    {
        if ($this->cachedToken) {
            return $this->cachedToken;
        }

        try {

            $client = new Client([
                'verify' => false,
            ]);

            $response = $client->post("{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token", [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->params['client_id'],
                    'client_secret' => $this->params['client_secret'],
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $this->cachedToken = $data['access_token'];

            return $this->cachedToken;
        } catch (\Exception $e) {
            Yii::error("Keycloak token error: " . $e->getMessage(), 'keycloak');
            return null;
        }
    }

    public function findUserByUsernameOrEmail($identifier)
    {
        $token = $this->getAdminToken();

        $client = new Client([
            'verify' => false
        ]);

        $response = $client->get($this->baseUrl . '/admin/realms/' . $this->realm . '/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'query' => ['username' => $identifier],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function createUser($userData)
    {
        $token = $this->getAdminToken();
        
        $client = new Client([
            'verify' => false,
        ]);

        $response = $client->post($this->baseUrl . '/admin/realms/' . $this->realm . '/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'json' => $userData
        ]);

        return [
            'status' => $response->getStatusCode(),
            'message' => $response->getBody()->getContents(),
        ];
    }

    public function updateUser($userId, $data)
    {
        $token = $this->getAdminToken();
        $url = "{$this->baseUrl}/admin/realms/{$this->realm}/users/{$userId}";

        $client = new Client([
            'verify' => false,
        ]);

        $response = $client->put($url, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);

        return $response->getStatusCode() === 204;
    }

    public function resetUserPassword($userId, $newPassword)
    {
        $token = $this->getAdminToken();
        $url = "{$this->baseUrl}/admin/realms/{$this->realm}/users/{$userId}/reset-password";

        $data = [
            'type' => 'password',
            'value' => $newPassword,
            'temporary' => false,
        ];

        $client = new Client([
            'verify' => false,
        ]);

        $response = $client->put($url, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);

        return $response->getStatusCode() === 204;
    }

    public function getAllUsers()
    {
        $token = $this->getAdminToken();
        if (!$token) return [];

        $client = new Client();
        $response = $client->get("{$this->baseUrl}/admin/realms/{$this->realm}/users", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getUserIdByEmail($email)
    {
        $token = $this->getAdminToken();
        $url = "{$this->baseUrl}/admin/realms/{$this->realm}/users?email=" . urlencode($email);

        $client = new \GuzzleHttp\Client();
        $response = $client->get($url, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ]
        ]);

        $users = json_decode($response->getBody(), true);

        return isset($users[0]['id']) ? $users[0]['id'] : null;
    }

    public function forceLogoutUserById($userId)
    {
        $token = $this->getAdminToken();
        if (!$token) return false;

        $client = new Client();
        $url = "{$this->baseUrl}/admin/realms/{$this->realm}/users/{$userId}/logout";

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]);

            return $response->getStatusCode() === 204;
        } catch (\Exception $e) {
            Yii::error("Force logout failed for user {$userId}: " . $e->getMessage(), 'keycloak');
            return false;
        }
    }
}