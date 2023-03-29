# Lumen base project

Lumen base code for goorita po=rojects

## Requirement
1. PHP 8.1
2. [Docker](http://docker.com/)
3. [Composer](https://getcomposer.org/)

## PHP Dependency
1. [Laravel](https://laravel.com/)
2. [Laravel Permission](https://spatie.be/docs/laravel-permission/v5/introduction)
3. [PHP-JWT](https://github.com/firebase/php-jwt)
4. [Swagger](https://zircote.github.io/)
## Run in development
```bash
docker compose up -d
```

## Build docker image for production
```bash
docker build -f docker/php-fpm/Dockerfile -t lumen:latest --target production .
```

## Generate swagger API docs
```bash
php artisan l5-swagger:generate
```

## Folder Structuree
    .
    ├── app
    │   ├── Console
    │   │   └── Commands        # Command files
    │   ├── Events
    │   ├── Exceptions
    │   ├── Http
    │   │   ├── Controllers     # Controllers files
    │   │   └── Middleware
    │   ├── Jobs
    │   ├── Listeners
    │   ├── Models              # Model files
    │   ├── Providers
    │   └── Traits              # Traits files
    ├── bootstrap
    ├── config                  # Configuration files
    ├── database
    │   ├── factories
    │   ├── migrations          # Migration database
    │   └── seeders
    ├── docker                  # Docker configuration
    │   ├── mysql
    │   │   └── conf
    │   ├── nginx
    │   │   └── conf.d
    │   └── php-fpm
    ├── public                  # Equal to public_html
    ├── routes                  # Routing files
        ├── api.php             # Write api code here
    ├── storage
    │   ├── app
    │   ├── framework
    │   └── logs
    ├── tests                   # Test unit files

## OAuth 2.0
    +--------+                                           +---------------+
    |        |--(A)------- Authorization Grant --------->|               |
    |        |                                           |               |
    |        |<-(B)----------- Access Token -------------|               |
    |        |               & Refresh Token             |               |
    |        |                                           |               |
    |        |                            +----------+   |               |
    |        |--(C)---- Access Token ---->|          |   |               |
    |        |                            |          |   |               |
    |        |<-(D)- Protected Resource --| Resource |   | Authorization |
    | Client |                            |  Server  |   |     Server    |
    |        |--(E)---- Access Token ---->|          |   |               |
    |        |                            |          |   |               |
    |        |<-(F)- Invalid Token Error -|          |   |               |
    |        |                            +----------+   |               |
    |        |                                           |               |
    |        |--(G)----------- Refresh Token ----------->|               |
    |        |                                           |               |
    |        |<-(H)----------- Access Token -------------|               |
    +--------+           & Optional Refresh Token        +---------------+
  
  
  (A)  The client requests an access token by authenticating with the
       authorization server and presenting an authorization grant.
  
  (B)  The authorization server authenticates the client and validates
       the authorization grant, and if valid, issues an access token
       and a refresh token.
  
  (C)  The client makes a protected resource request to the resource
       server by presenting the access token.
  
  (D)  The resource server validates the access token, and if valid,
       serves the request.
  
  (E)  Steps (C) and (D) repeat until the access token expires.  If the
       client knows the access token expired, it skips to step (G);
       otherwise, it makes another protected resource request.
  
  (F)  Since the access token is invalid, the resource server returns
       an invalid token error.
  
  (G)  The client requests a new access token by authenticating with
       the authorization server and presenting the refresh token.  The
       client authentication requirements are based on the client type
       and on the authorization server policies.
  
  (H)  The authorization server authenticates the client and validates
       the refresh token, and if valid, issues a new access token (and,
       optionally, a new refresh token).
