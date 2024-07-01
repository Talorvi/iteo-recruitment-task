# Iteo Recruitment Task

This repository contains the implementation of tasks as part of the recruitment process for Iteo.

## Tasks overview

### Task 34: Client balance

Client balance information is managed with the following data contract:
```
{
  "clientId": "uuid",
  "name": "string",
  "balance": "float"
}
```

*Note: The balance field has been updated to a float to accommodate operations involving decimal numbers.*

### Task 35: Sending order to ERP

This task involves sending orders to an ERP system based on requests from the frontend.

#### Assumptions:

- The frontend uses products from our system which integrates with the ERP to fetch product data.
- Product availability checks are skipped
- Products can have different weights and prices than those recorded in the system. Validations are in place to ensure the integrity of weight, price, and quantity.
- Orders received from the frontend with null as order ID will have IDs generated during processing (or potentially by the ERP, depending on the strategy).

The order contract looks like that:
```
{
  "orderId": uuid,
  "clientId": uuid,
  "products": [
    {
      "productId": string,
      "quantity": int,
      "price": float,
      "weight": float
    }
  ]
}
```

```
{ADDRESS}/api/order [POST]
```

### Task 36: Additional order validation

Valid order must meet the following conditions:

- It must contain at least 5 products.
- Its weight must not exceed 24 tons.
- The client's balance must be positive after transaction

### Task 37: Adding new clients

The system provides an endpoint where the CRM will send information about new clients along with their initial balance.
The contract looks exactly the same as in Task 34.

```
{ADDRESS}/api/client [POST]
```

## Installation instructions:

#### Docker:

- `docker compose build --no-cache` to build fresh images
- `docker compose up --pull always -d --wait` to set up and start symfony
- `docker compose down --remove-orphans` to stop docker containers

#### Database installation

```
php bin/console doctrine:database:create
```

#### Environment configuration

```
DATABASE_URL=mysql://${MYSQL_USER:-app}:${MYSQL_PASSWORD:-!ChangeMe!}@database:3306/${MYSQL_DATABASE:-app}?serverVersion=${MYSQL_VERSION:-8}&charset=${MYSQL_CHARSET:-utf8mb4}
```

#### Test database connection

```
docker compose exec php bin/console dbal:run-sql -q "SELECT 1" && echo "OK" || echo "Connection is not working"
```

#### Migrations

```
docker compose exec php bin/console doctrine:migrations:migrate
```

#### Running tests

```
docker compose exec php ./vendor/bin/phpunit
```

#### ERP API configuration in env

```
ERP_URL=https://example-erp.com/api/
```

#### Helper commands to populate the database

```
docker compose exec php php bin/console app:create-clients 5
docker compose exec php php bin/console app:create-products 15
```
