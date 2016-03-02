<?php
require __DIR__ . '/../vendor/autoload.php';

$loader = new Twig_Loader_Array(array(
    'index' => '<b>Hello {% for name in names %} {{name}} {%endfor%}!</b>',
));
$twig = new Twig_Environment($loader);

echo $twig->render('index', array('names' => ['Ryan', 'Michael']));