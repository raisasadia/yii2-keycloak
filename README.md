yii2-keycloak-auth
-------------------
A lightweight Yii2 package to integrate Keycloak authentication and administration. Supports login, session management, user info, and force logout functionality using Keycloak's OpenID Connect and Admin REST APIs.

### Features
** Keycloak OIDC login with authorization code grant

** Retrieve authenticated user info

** View all users from Keycloak

** Get user by ID

** Admin-only session management (view, delete, force logout)

** Frontchannel logout support

### Installation

git clone https://github.com/raisasadia/yii2-keycloak.git

Then in your main project’s composer.json, add:
"repositories": [
  {
    "type": "path",
    "url": "relative/path/to/yii2-keycloak-auth"
  }
],

Then run:

composer require raisa/yii2-keycloak-auth:dev-main


Then test in a Yii2 controller:

use yii2keycloak\Keycloak\Keycloak;

$userInfo = Keycloak::user()->getUserInfo($accessToken);

### Configuration
In params.php:

return [
    'keycloak' => [
        'base_url' => 'http://localhost:8081',
        'realm' => 'myrealm',

        'client_id' => 'yii-client',
        'redirect_uri' => 'http://localhost:8080/site/callback',
        'logout_url' => 'http://localhost:8081/realms/myrealm/protocol/openid-connect/logout',
        'userinfo_url' => 'http://localhost:8081/realms/myrealm/protocol/openid-connect/userinfo',
        'token_url' => 'http://localhost:8081/realms/myrealm/protocol/openid-connect/token',

        'admin_client_id' => 'yii-admin',
        'admin_client_secret' => 'YOUR_ADMIN_CLIENT_SECRET',
        'redirect_uri_after_logout' => 'http://localhost:8080',
    ]
];


### Folder Structure
yii2-keycloak-auth/
├── Keycloak/
│   ├── Keycloak.php              # Facade class for unified access
│   ├── AuthService.php           # Handles token exchange
│   ├── UserService.php           # Fetch user info via /userinfo
│   └── KeycloakAdminService.php  # Full Keycloak Admin API support



