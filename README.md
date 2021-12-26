# ReverseGeocoder

### Local install

```bash
cd qlico
docker-compose build --pull --no-cache
cd ..
make up
make shell
composer install
```

### Installation in project

```bash
composer require jesperveldhuizen/reversegeocode
```

### Usage

See test.php for example.

```bash
php test.php :key :lat :lng
```
