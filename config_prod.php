<?php

/* @var $container \Dice\Dice */

$container->addRule( '\JoltCache', [
	'shared' => true,
] );
$container->addRule( '\ComposePress\Settings\Managers\Page', [
	'instanceOf' => '\JoltCache\UI\Managers\Page',
] );