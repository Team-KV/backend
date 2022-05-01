# Physio-therapeutic IS backend

This is backend part of Physio-therapeutic batchelor thesis.

## Installation

For running this project locally you need to have installed Docker.

1. composer install
2. cp .env.example .env
3. alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
4. sail up -d
5. sail artisan migrate

## Database seeding

sail artisan db:seed

## Login credentials

Admin login: admin@test.com, admin  
Client login: client@test.com, client

## Testing

This command run all tests: ./vendor/bin/phpunit --testdox tests

## Usage

After installation, you can use API on starting address http://localhost/api/.

## Mailhog

Mailhog for reading emails from system is available here: http://localhost:8025

## API response codes

- 200: JSON response with data
- 204: No content (usually delete action)
- 400: Bad request (missing required parameter)
- 401: Unauthorized (request without token or expired token)
- 404: Not found (usually sent id of object which is not in DB)
- 409: Conflict (some type of validation error)
- 500: Internal server error (create or update query exception)

## API description

Description of API you can find in Postman workspace: https://app.getpostman.com/join-team?invite_code=b572a18ed494f692a7008adf12d151dc&target_code=cad4dff91b9d9244ce6c59014b1b3011

