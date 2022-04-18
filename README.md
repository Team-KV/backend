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

## Usage

After installation, you can use API on starting address http://localhost/api/.

## Mailhog

Mailhog for reading emails from system is available here: http://localhost:8025

## API description

Description of API you can find in Postman workspace: https://app.getpostman.com/join-team?invite_code=b572a18ed494f692a7008adf12d151dc&target_code=cad4dff91b9d9244ce6c59014b1b3011

