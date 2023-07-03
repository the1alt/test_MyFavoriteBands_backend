# MyFavoriteBands Backend

This project was generated with [Symfony CLI](https://github.com/symfony-cli/symfony-cli) version 5.5.6.

## Database

This project is made to work with a PostgreSql server.

Copy the .env.example file has .env.

Replace USERNAME, PASSWORD and DATABASENAME from the DATABASE_URL with your own configuration.

Run `php bin/console doctrine:migrations:migrate` to generate the tables on your database.

## User Interface

This project is a CRUD API. 

You can download and run the [MyFavoriteBands Frontend](https://github.com/the1alt/test_MyFavoriteBands_frontend.git) to interact with the api.

## Development server

Run `symfony serve:start` for a dev server. Navigate to `http://localhost:8000/`.
