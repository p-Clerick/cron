<?php

Yii::import('application.vendor.*');
require_once('autoload.php');
use Httpful\Request as Request;
use Httpful\Http as Http;

Yii::import('application.vendors.underscore.*');
require_once('underscore.php');

class ViewUpdateCommand extends CConsoleCommand
{
	public function run($args)
	{
		$connectionConfig = Yii::app()->CouchConnection->options;
		$template = Request::init()
			->method(Http::GET)
			->authenticateWith(
				$connectionConfig['user'],
				$connectionConfig['password']
			);

		Request::ini($template);

		$paths = __(Yii::app()->params['dbs'])
			->reduce(function ($acc, $next) {
				$db_name = $next['db_name'];
				$path = __($next['views'])
					->map(function ($view) use ($db_name) {
						return "$db_name/_design/$view[0]/_view/$view[1]";
					});

				return array_merge($acc, $path);
			}, array());

		$urls = __($paths)->map(function ($path) use ($connectionConfig) {
			$host = $connectionConfig['host'];
			$port = $connectionConfig['port'];
			return "http://$host:$port/$path";
		});

		__($urls)->each(function ($url) {
			$timeStart = new DateTime();
			echo '[' . $timeStart->format('Y-m-d H:i:s') . "] start indexing $url";
			echo "\n";
			$response = Request::get($url)->send();

			$timeEnd = new DateTime();
			$interval = $timeEnd->diff($timeStart, true);
			echo '[' . $timeEnd->format('Y-m-d h:m:i') . "] finish indexing $url";
			echo "\n";
			echo 'status: ' . $response->code;
			echo "\n";
			echo 'time: ' . $interval->format('%h:%I:%S');
			echo "\n";
			echo "-----------------------------------------------------------";
			echo "\n";
		});
	}
}