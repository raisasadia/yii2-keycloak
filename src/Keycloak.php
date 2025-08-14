<?php
namespace yii2keycloak;

class Keycloak
{
    public static function auth()
    {
        return new AuthService();
    }

    public static function user()
    {
        return new UserService();
    }
    
    public static function admin()
    {
        return new KeycloakAdminService();
    }

}
