<?php namespace AbstractInterface\TransmissionRemote\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Transmission\Transmission;

class TransmissionServiceProvider implements ServiceProviderInterface {

	public function register(Application $app)
	{
		$config = array(
			'host' => null,
			'port' => null,
			'path' => null,
		);

		$app['transmission'] = $app->share(function ($app) use ($config) {
			if (isset($app['transmission.options']['host'])) {
				$config['host'] = $app['transmission.options']['host'];
			}

			if (isset($app['transmission.options']['port'])) {
				$config['port'] = $app['transmission.options']['port'];
			}

			if (isset($app['transmission.options']['path'])) {
				$config['path'] = $app['transmission.options']['path'];
			}

			return new Transmission($config['host'], $config['port'], $config['path']);
		});

	}

	public function boot(Application $app) 
	{

	}

}