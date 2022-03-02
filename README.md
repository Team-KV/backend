# Physio-therapeutic IS backend

This is backend part of Physio-therapeutic batchelor thesis.

## Installation

For running this project locally you need to have installed Docker.

1. composer install
2. cp .env.example .env
3. alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
4. sail up -d
5. sail artisan migrate

## Usage

After installation, you can use API on starting address http://localhost/api/.

## API description

Description of API you can find in Postman workspace: https://go.postman.co/workspace/BP~afc8454b-ad47-4756-8e9d-94d7e7321818/api/dd45e71c-9434-400c-8017-b2e58a8c4840

