<?php
namespace yii2keycloak\Keycloak;

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

    public static function find($id)
    {
        return self::admin()->getUserById($id);
    }

    public static function getUserSessions($userId)
    {
        return self::admin()->getUserSessions($userId);
    }

}
