<?php

namespace ADS\TakeHome;

include __DIR__ . "/../vendor/autoload.php";

use ADS\TakeHome\Cities\MySqlCityRepository;
use ADS\TakeHome\Controllers\CityController;
use ADS\TakeHome\Controllers\ExampleController;
use ADS\TakeHome\Controllers\WeatherController;
use ADS\TakeHome\Framework\MySQL;
use ADS\TakeHome\Framework\Router;
use ADS\TakeHome\Weather\MySqlWeatherRepository;

$router = new Router();

$mysql = new MySQL("db", "weather", "root", "root");
$helloController = new ExampleController(greeting: "Hello", db: $mysql);
$cityRepository = new MySqlCityRepository($mysql);
$weatherController = new WeatherController($cityRepository, new MySqlWeatherRepository($mysql));
$cityController = new CityController($cityRepository);

$router->addRoute("GET", "/hello", $helloController->sayHello(...));
$router->addRoute("GET", "/budapest", $helloController->getBudapestMaxTempCount(...));
$router->addRoute("GET", "/weather/city", $weatherController->getCityWeather(...));
$router->addRoute("GET", "/weather/region", $weatherController->getRegionWeather(...));
$router->addRoute("GET", "/cities", $cityController->listCities(...));

$router->handleRequest();
