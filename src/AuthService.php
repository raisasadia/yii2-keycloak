<?php
namespace yii2keycloak;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class AuthService
{

    public function getToken($code, $redirectUri)
    {
        $client = new Client();
        try {
            $response = $client->post(\Yii::$app->params['keycloak']['token_url'], [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => \Yii::$app->params['keycloak']['client_id'],
                    'client_secret' => \Yii::$app->params['keycloak']['client_secret'],
                    'code' => $code,
                    'redirect_uri' => $redirectUri
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            // Optional: log or display error details
            \Yii::error("Keycloak token request failed: " . $e->getMessage(), __METHOD__);

            // You can also get the response body if available
            $errorBody = $e->getResponse()?->getBody()?->getContents();

            return [
                'error' => 'token_request_failed',
                'message' => $e->getMessage(),
                'response' => $errorBody
            ];
        }
    }
}