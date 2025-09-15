<?php

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
	$r->addRoute('GET', '/', function () {
		include('pages/home.php');
	});

	// Admin Routes
	$r->addGroup("/admin", function (FastRoute\RouteCollector $r) {
		$r->addRoute('GET', '', function () {
			header("Location: /admin/");
			die();
		});

		$r->addRoute('GET', '/', function () {
			include('pages/admin/home.php');
		});

		$r->addRoute('GET', '/settings', function () {
			include('pages/admin/settings.php');
		});

		$r->addRoute('GET', '/logout', function () {
			// TODO: Implement logout logic
			header("Location: /");
		});
	});

	// Development Routes
	$r->addRoute('GET', '/react', function () {
		include('pages/react.php');
	});

	$r->addGroup('/migrate', function (FastRoute\RouteCollector $r) {
		$r->addRoute('GET', '/initial', function () {
		include('system/migrations/initial_migration.php');
		});

		// Add more...
	});
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
	$uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
	case FastRoute\Dispatcher::NOT_FOUND:
		http_response_code(404);
		include('pages/errors/404.php');
		break;
	// case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
	// 	$allowedMethods = $routeInfo[1];
	// 	// ... 405 Method Not Allowed
	// 	break;
	case FastRoute\Dispatcher::FOUND:
		$routeInfo[1]($routeInfo[2]);
		break;
	default:
		die();
		break;
}
