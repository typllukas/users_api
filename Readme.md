
## Built docker containers

```bash
docker-compose up --build
```

## Run migrations to create db tables

```bash
docker-compose exec app php bin/console doctrine:migrations:migrate
```

## Run fixtures to create testing users

```bash
docker-compose exec app php bin/console doctrine:fixtures:load
```

## Test via localhost

```http
http://localhost:8000
```

**Test User**
  - **Email:**`testuser1@example.com`
  - **Password:** `testUser1*`

**Test Admin**
  - **Email:** `testadmin1@example.com`
  - **Password:** `testAdmin1*`