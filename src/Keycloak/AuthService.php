<?php
namespace yii2keycloak\Keycloak;
use GuzzleHttp\Client;

class AuthService
{
    public function getToken($code, $redirectUri)
    {
        $client = new Client();
        $response = $client->post(\Yii::$app->params['keycloak']['token_url'], [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => \Yii::$app->params['keycloak']['client_id'],
                'code' => $code,
                'redirect_uri' => $redirectUri
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
