Testing Login
=======

Required
--------

1) PHP >= 5.4.0 (better 5.5.*)

2) gettex

3) Turn on short tag

4) chmod 0777 ./logs


For use Twig
------------

So it looks something like this.

```php
<?php

$app['view'] = new \Twig_Environment($twig_loader, $twig_options);

```

And for 'layout'

```php
<?php

$twig = new \Twig_Environment($twig_loader, $twig_options);
$app['layout'] = function ($content) use ($twig) {
    return $twig->render('script.phtml', ['some_var' => $content);
};

```

For use with Nginx + PHP5-FPM
------------

Example [nginx_sample.conf](https://github.com/jenchik/slogin/blob/master/install/nginx_sample.conf)