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
            $client = new Client();

            $response = $client->post("{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token", [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->params['admin_client_id'],
                    'client_secret' => $this->params['admin_client_secret'],
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

    public function getUserById($userId)
    {
        $token = $this->getAdminToken();
        if (!$token) return null;

        $client = new Client();
        $response = $client->get("{$this->baseUrl}/admin/realms/{$this->realm}/users/{$userId}", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function getUserSessions($userId)
    {
        $token = $this->getAdminToken();
        if (!$token) return [];

        $client = new Client();
        $response = $client->get("{$this->baseUrl}/admin/realms/{$this->realm}/users/{$userId}/sessions", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function deleteSession($sessionId)
    {
        $token = $this->getAdminToken();
        if (!$token) return false;

        $client = new Client();
        $url = "{$this->baseUrl}/admin/realms/{$this->realm}/sessions/{$sessionId}";

        try {
            $response = $client->delete($url, [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]);

            return $response->getStatusCode() === 204;
        } catch (\Exception $e) {
            Yii::error("Failed to delete session {$sessionId}: " . $e->getMessage(), 'keycloak');
            return false;
        }
    }

    public function getUserIdFromSessionId($sessionId)
    {
        $token = $this->getAdminToken();
        if (!$token) return null;

        $client = new Client();
        $response = $client->get("{$this->baseUrl}/admin/realms/{$this->realm}/users", [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        $users = json_decode($response->getBody(), true);

        foreach ($users as $user) {
            $sessions = $this->getUserSessions($user['id']);
            foreach ($sessions as $session) {
                if ($session['id'] === $sessionId) {
                    return $user['id'];
                }
            }
        }

        return null;
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