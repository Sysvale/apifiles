# ApiFiles

A Laravel library to abstract communication with api-files


## Installation

```bash
composer require sysvale/api-files
```

## Configuration

- Publish the config file and it will be created as `apifiles.php`

```bash
php artisan vendor:publish --tag apifiles-config
```

- Set URL and the access token


## Development
- Set up environment
```bash
docker-composer up -d
```

- Install dependencies
```bash
./docker-exec.sh composer update
```

- Run tests
```bash
./docker-exec.sh vendor/bin/phpunit
```
