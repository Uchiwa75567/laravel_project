# TODO: Add New Endpoint /api/welcome to Laravel API Service

## Steps to Complete
- [x] Create `app/Http/Controllers/WelcomeController.php` with the `welcome` method that logs request metadata and returns JSON welcome message.
- [x] Edit `routes/api.php` to add GET route for `/welcome` pointing to `WelcomeController@welcome`.
- [x] Edit `app/Http/Controllers/SwaggerController.php` to add `@OA\PathItem` for `/api/welcome` with Swagger annotations.
- [x] Run `php artisan l5-swagger:generate` to regenerate API documentation.
- [x] Test the endpoint locally to verify logging and response.
