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

- The frontend uses products from our system which will integrate with the ERP to fetch product data.
- Product availability checks are skipped.
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

### Task 38: Subtract order total from client balance

The system automatically subtracts the amount from the client's balance after processing the order.
To ensure accurate processing, transactions are used.

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

#### Helper commands to populate the database for testing purposes

```
docker compose exec php php bin/console app:create-clients 5
docker compose exec php php bin/console app:create-products 15
```

#### Postman collection

In the repository there is `iteo_recruitment_task.postman_collection.json` file which can be imported to postman for testing purposes.

## Project structure

The project is organized into several directories, each with a specific responsibility in the application. Below is an overview of the structure and the purpose of each directory and its files.

### Command
Contains commands for populating the database with initial data.

- `AppCreateClientsCommand.php`: Command to create and populate clients in the database.
- `AppCreateProductsCommand.php`: Command to create and populate products in the database.

### Controller
Holds the controllers responsible for handling HTTP requests.

- `ClientController.php`: Manages client-related requests, such as creating a new client.
- `OrderController.php`: Manages order-related requests, including creating new orders and processing them.

### DTO (Data Transfer Objects)
Defines the data structures for transferring data between different layers of the application.

- `ClientDTO.php`: Defines the structure for client data transfer objects.
- `OrderDTO.php`: Defines the structure for order data transfer objects.
- `ProductDTO.php`: Defines the structure for product data transfer objects.

### Entity
Contains the entity classes that map to the database tables.

- `Client.php`: Entity representing a client.
- `Order.php`: Entity representing an order.
- `OrderProduct.php`: Entity representing the relationship between orders and products.
- `Product.php`: Entity representing a product.

### Repository
Holds the repository classes for accessing and managing the data in the database.

- `ClientRepository.php`: Repository for client entities.
- `OrderRepository.php`: Repository for order entities.
- `OrderProductRepository.php`: Repository for order-product relationship entities.
- `ProductRepository.php`: Repository for product entities.

### Service
Contains the business logic and services used by the controllers.

- Client
  - `ClientService.php`: Provides methods to manage client-related operations, such as fetching and creating clients.
- ERP
  - `ErpIntegrationService.php`: Handles the integration with the ERP system.
- Order
    - `OrderDtoConversionService.php`: Converts entities to DTOs and vice versa.
    - `OrderService.php`: Provides methods to manage order-related operations, such as creating orders and processing order products.
    - `OrderValidationService.php`: Validates the orders against defined business rules.
- Product
    - `ProductService.php`: Provides methods to manage product-related operations.
