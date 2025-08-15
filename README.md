# yii2-keycloak-auth

A simple Keycloak integration package for the Yii2 framework.  
Provides authentication, user information retrieval, and admin API operations through Keycloak's OpenID Connect and Admin REST API.

---

## Features
- Keycloak Authentication — Exchange authorization code for tokens
- User Service — Fetch user info from Keycloak using an access token
- Admin Service — Create, update, search, and manage Keycloak users
- Force Logout — Logout users remotely from Keycloak sessions
- Easy Yii2 Integration — Works with Yii2 `components` configuration
- PSR-4 Autoloading — Compatible with Composer

---

## Requirements
- PHP >= 7.4
- [yiisoft/yii2](https://github.com/yiisoft/yii2) >= 2.0
- [guzzlehttp/guzzle](https://github.com/guzzle/guzzle) >= 7.0
- Keycloak server (tested with Keycloak v21+)

---

## Installation
Install via Composer:

```bash
composer require raisa/yii2-keycloak-auth
```

## Configuration

Add the following to your Yii2 config file (config/params.php):

```php

<?php

return [
  'keycloak' => [
    'realm' => 'realm-name',
    'client_id' => 'client-id',
    'client_secret' => 'your-client-secret',
    'base_url' => 'https://keycloak.example.com',
    'token_url' => 'https://keycloak.example.com/realms/my-realm/protocol/openid-connect/token',
    'auth_url' => 'https://keycloak.example.com/realms/my-realm/protocol/openid-connect/auth',
    'userinfo_url' => 'https://keycloak.example.com/realms/my-realm/protocol/openid-connect/userinfo',
    'logout_url' => 'https://keycloak.example.com/realms/my-realm/protocol/openid-connect/logout',
    'redirect_uri' => 'https://yourapp.com/callback',
    'redirect_uri_after_logout' => 'http://yourapp.com',
  ],
];

```


## File Structure

yii2-keycloak-auth/
├── src/
│   ├── AuthService.php            # Handles token exchange
│   ├── UserService.php            # Retrieves Keycloak user info
│   ├── KeycloakAdminService.php   # Admin API operations
│   └── Keycloak.php               # Facade for quick access to services
├── composer.json
├── README.md
└── LICENSE


## Usage
1. Authentication

```php

  use yii2keycloak\Keycloak;

  $code = $_GET['code']; // From Keycloak redirect
  $redirectUri = 'https://yourapp.com/callback';

  $tokenData = Keycloak::auth()->getToken($code, $redirectUri);

  if (isset($tokenData['access_token'])) {
      // Store tokens in session or database
  }

```

2. Get User Info

```php

  $accessToken = $tokenData['access_token'];
  $userInfo = Keycloak::user()->getUserInfo($accessToken);
  print_r($userInfo);

```

3. Admin Operations

```php

  // Get all users
  $users = Keycloak::admin()->getAllUsers();

  // Create a new user
  $newUser = [
      'username' => 'john.doe',
      'email' => 'john@example.com',
      'enabled' => true,
  ];
  Keycloak::admin()->createUser($newUser);

  // Force logout a user
  $userId = 'keycloak-user-id';
  Keycloak::admin()->forceLogoutUserById($userId);
```

> **Security Note:**  
> Some parts of this package (e.g., `KeycloakAdminService`) initialize the Guzzle HTTP client with `'verify' => false` to bypass SSL certificate verification.  
> This is intended for local development or self-signed certificates only.  
> **Do not use `'verify' => false` in production**, as it makes HTTPS connections insecure.  
> For production environments, remove this option or set it to `true` and ensure you have a valid SSL >certificate.

## License

This package is open-sourced software licensed under the MIT license.

