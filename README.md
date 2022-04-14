Nam
=====

Nam is a open source PHP router. super small, fast, and powerful. micro-framework

### Install

Just Copy clone or copy nam.php in directory

> wget https://raw.githubusercontent.com/sigareng/nam/master/nam.php

If you have Composer, just include Nam as a project dependency in your `composer.json`.
with 

```
require: {
    "sigareng/nam": "dev-master"
}
```

or with command line
```
composer require sigareng/nam:dev-master
```

### Examples Used


First, `use` the `sigareng\Nam` namespace:

```PHP
use sigareng\Nam\Nam;
```


if clone child folder dont forget to call
```
Nam::setbase('<your path>');
```

Nam is not an object, so you can just make direct operations to the class. Here's the Hello World:

```PHP
Nam::get('/', function() {
  echo 'Hello world!';
});

Nam::dispatch();
```

> like this

index.php

```PHP
require __DIR__.'/nam.php';
use sigareng\Nam\Nam;

Nam::get('/', function() {
  echo 'Hello world!';
});

Nam::dispatch();
```

with composer
```PHP
require './vendor/autoload.php';

use sigareng\Nam\Nam;

Nam::get('/', function() {
  echo 'Hello world!';
});

Nam::dispatch();
```

`Nam` also supports lambda URIs, such as:

```
(:any) -> [^/]+
(:num) -> [0-9]+
(:all) -> .*
```

```PHP
Nam::get('/(:any)', function($slug) {
  echo 'I get : ' . $slug;
});

Nam::dispatch();
```

You can also make requests for HTTP methods in Nam, so you could also do:

```PHP
Nam::get('/', function() {
  echo 'Im a GET request!';
});

Nam::post('/', function() {
  echo 'Im a POST request!';
});

Nam::any('/', function() {
  echo 'I can be both a GET and a POST request!';
});

Nam::dispatch();
```

## View Renderer

> assumed directory layout

    .
    ├── nam.php
    ├── view
        ├── head.php
        ├── body.php
        ├── footer.php
    └── index.php

```PHP
require __DIR__.'/nam.php';
use Nam\Nam;

Nam::get('/', function() {
  Nam::render('./view/head.php');
  Nam::render('./view/body.php');
  Nam::render('./view/footer.php');
});

Nam::dispatch();
```

```PHP
Nam::get('/(:num)', function($val) {
  $age['alice']=$val;
  Nam::render('./hi.php',$age);
//   echo '<pre>' . print_r(get_defined_vars(), true) . '</pre>';
});
```
and inside hi.php
```PHP
echo 'hola, age alice is :'.$data['alice'];
```
> to test after clone this repository, run `php -S localhost:8080` and goto `http://localhost:8080/Example/` on browser

## Error Handling

You can pass a message into the exception that will be displayed in place of the default message on the 404 page.

```PHP
Nam::error(function() {
  echo '404 :: Not Found';
});
```

If you don't specify an error callback, Nam will just echo `404`.

<hr>

to direct properly without `.php` extension
example [configuration files](https://github.com/sigareng/Nam/blob/master/config).


## Example passing to a controller instead of a closure
<hr>

index.php:

```php
require __DIR__.'/nam.php';

use Nam;

Nam::get('/', 'Controllers\see@index');
Nam::get('page', 'Controllers\see@page');
Nam::get('view/(:num)', 'Controllers\see@view');

Nam::dispatch();
```

see.php:

```php
<?php
namespace Controllers;

class Demo {

    public function index()
    {
        echo 'home';
    }

    public function page()
    {
        echo 'page';
    }

    public function view($id)
    {
        echo $id;
    }

}
```

### see so simple to use


.htaccess(Apache):

```
RewriteEngine On
RewriteBase /

# Allow any files or directories that exist to be displayed directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*)$ index.php?$1 [QSA,L]
```

.htaccess(Nginx):

```
rewrite ^/(.*)/$ /$1 redirect;

if (!-e $request_filename){
	rewrite ^(.*)$ /index.php break;
}

```