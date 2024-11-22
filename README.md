# Laravel Recipe API

## Introduction
This is a demo of creating a modern RESTful API using Laravel. The purpose is to excercise and showcase my skills and
to serve as a backend for future frontend demo's utilizing my API.
The topic is that of managing recipes and some related entities, it contains endpoints functionality enable:
- managing recipes by users and admins
- managing recipe instructions as aggregate of recipes
- managing recipe ingredients as aggregate of recipes
- browsing these data as anonymous users and being able to filter by e.g. categories
- managing users as admins

Some criteria that have driven the development are as follows:
- differentiated access restrictions for different kinds of users (anonymous, users, admins)
- it should follow a specification for building [json api's](https://jsonapi.org/)
- it should contain tests of its endpoints

The demo is inspired by the excellent [laravel API master class tutorial](https://github.com/laracasts/laravel-api-master-class/) 
by Jeremy McPeak on [laracasts](https://laracasts.com/series/laravel-api-master-class). 

Thanks for that :-)

## Requirements
For running this demo you should be able to run docker, docker-compose. My version as of this writing is docker version 
20.10.20.

## Installation & running the demo
You should start by cloning the repo and switch to the folder it was cloned into.
```
git clone git@github.com:johi/laravel-recipe-api.git
cd laravel-recipe-api
```
Now you need to create an environment file.
```
cp .env.example .env
```
Next you should make sure that you install dependencies via composer install. The PHP version used is 8.3, if you have 
that locally you could probably just run `composer install` but I would always recommend using docker for that, so you
install dependencies via docker in this manner:
```
docker run --rm \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install
```
When that has completed you can start your containers like so:
```
./vendor/bin/sail up -d
```

### Documentation

I have pregenerated some documentation using the excellent library 
[scribe](https://packagist.org/packages/knuckleswtf/scribe). You can generate documentation with the following command: 
```
./vendor/bin/sail artisan scribe:generate    
```
You should now be able to see the docs here: [http://localhost:3001/docs](http://localhost:3001/docs)

### Testing
I have done some end-to-end test for the endpoints in this API demo, you can run the tests as so:
```
./vendor/bin/sail artisan test
```
*...missing: general notes on what tests have been and have not been done...*
### Postman
*...missing: this section is yet to be written...*

