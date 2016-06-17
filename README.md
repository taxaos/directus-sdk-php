<p align="center">
<img src="https://s3.amazonaws.com/f.cl.ly/items/3Q2830043H1Y1c1F1K2D/directus-logo-stacked.png" alt="Directus Logo"/>
</p>

# Directus SDK for PHP
For PHP driven applications, use this SDK to more easily communicate with your Directus managed database.

[![Build Status](https://travis-ci.org/directus/directus-sdk-php.svg?branch=master)](https://travis-ci.org/directus/directus-sdk-php)

## Work In Process.

## Install

Via Composer

``` bash
$ composer require directus/directus-sdk
```

## Usage

``` php
require 'vendor/autoload.php';

$config = [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'root',
    'name' => 'directus'
];
$database = new \Directus\Database($config);

$articles = $database->fetchEntries('articles');

foreach($articles as $article) {
    echo '<h2>'.$article->title.'</h2>';
}
```
