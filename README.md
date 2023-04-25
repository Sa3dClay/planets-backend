# Planets
## backend server with API endpoints for the planets web app
    - Pusher for chat events
    - Laravel-JWT for authentication
    - API endpoints for client communication
## Server Installation
    First, configure your Database and Pusher in the .env file and change broadcast_driver to pusher
    Second, run the following commands
```
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan serve
```
