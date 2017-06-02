<?php

	Router::connect('/', array('controller' => 'LoginPage', 'action' => 'login'));
	CakePlugin::routes();
	require CAKE . 'Config' . DS . 'routes.php';
