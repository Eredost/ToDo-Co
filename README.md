# ToDo-Co

[![Maintainability](https://api.codeclimate.com/v1/badges/3653f5751726218bcdcd/maintainability)](https://codeclimate.com/github/Eredost/ToDo-Co/maintainability)

Eighth project of the OpenClassrooms PHP application developer path.

This already existing project, developed using the Symfony framework, must be
improved from a performance point of view, fix the anomalies and add some
functionalities.

## Installation

Before you can download the project you must first have a PHP version
at least >=7.4, a recent version of Composer and the
[Symfony CLI](https://symfony.com/download).

To set up the project, follow the steps below:

1. Clone the repository
2. Move your current directory to the root of the project
3. Perform the command:

   ```shell
   composer install
   ```

4. Create a new file ``.env.local`` in order to configure the DSN for the database.

   ```
   DATABASE_URL="mysql://username:password@127.0.0.1:3306/database_name?serverVersion=8.0"
   ```

5. Then you have to set up the database, associated tables and fixtures
   with the following commands:

   ```shell
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   php bin/console doctrine:fixtures:load
   ```

6. Finally, you can launch the Symfony server with the following command:

   ```shell
   symfony serve
   ```

**And it's done !**

## Contributing

See the [Contributing.md](CONTRIBUTING.md) file for more information
on how to contribute to the project.

## Additional docs

-   [UML diagrams](docs/diagrams)
