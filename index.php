<?php

require 'vendor/autoload.php';

use AbstractInterface\TransmissionRemote\Provider\TransmissionServiceProvider;
use Symfony\Component\DomCrawler\Crawler;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use MarcW\Silex\Provider\BuzzServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JDesrosiers\Silex\Provider\CorsServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application;

$app['debug'] = true;

$app->register(new UrlGeneratorServiceProvider());

$app->register(new TransmissionServiceProvider(), array(
	'transmission.options' => array(
		'host' => 'localhost',
		'port' => 11111,
	),
));

$app->register(new BuzzServiceProvider(), array(
	'buzz.options' => array(
		'client' => function() {
				return new Buzz\Client\Curl();
			},
		),
		'buzz.client.timeout' => 10000,
	)
);

$app->register(new TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/views',
));

$app->register(new CorsServiceProvider());

$app->post('/add', function(Application $app, Request $request) {

	$url = $request->get('url');
	
	$html = $app['buzz']->get($url);
	$crawler = new Crawler();
	$crawler->addContent($html);

	$link = $crawler->filter('a[title="Get this torrent"]')->first();
	$magnet = $link->attr("href");

	$torrent = $app['transmission']->add($magnet);

	return $app->json(array(
		'url' => $url,
		'name' => $torrent->getName(),
	));

})->bind('add-torrent');

$app->get('/bookmarklet', function(Application $app) {
	$js = 'javascript: ' . JShrink\Minifier::minify(
		$app['twig']->render('bookmarklet-js.html')
	);
	
	return $js;
});

$app->after($app["cors"]);
$app->run();
