<?php

echo '<h1>Template</h1>';

$items = new StdClass();
$items->menu1 = new StdClass();
	$items->menu1->url = '#link1';
	$items->menu1->caption = 'Menu1';

$items->menu2 = new StdClass();
	$items->menu2->url = '#link2';
	$items->menu2->caption = 'Menu2';

$items->menu3 = new StdClass();
	$items->menu3->url = '#link3';
	$items->menu3->caption = 'Menu3';

include('Class.php');
include('template.tpl');