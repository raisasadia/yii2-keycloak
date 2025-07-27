<?php
namespace yii2keycloak\Keycloak;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class UserService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Get Keycloak user info from access token
     */
    public function getUserInfo($accessToken)
    {
        try {
            $response = $this->client->get(\Yii::$app->params['keycloak']['userinfo_url'], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            \Yii::error('Failed to get user info from Keycloak: ' . $e->getMessage(), __METHOD__);

            if ($e->hasResponse()) {
                \Yii::error('Response body: ' . $e->getResponse()->getBody()->getContents(), __METHOD__);
            }

            return null;
        } catch (\Exception $e) {
            \Yii::error('Unexpected error in getUserInfo: ' . $e->getMessage(), __METHOD__);
            return null;
        }
    }
}
