<?php

class BaseChartController extends CController
{
	const TYPE_GRAPH = 1;
	 
	const TYPE_ROUTE = 2; 

	const TYPE_VEHICLE = 3;

	protected $chartType;

	protected $height;
	
	protected $width;

	protected $title;

	protected $chartData;

	protected $result;

	protected function parseRequest($chartType){
		$this->chartType = $chartType;
		$this->height = $_POST['height'];
		$this->width = $_POST['width'];
		$this->title = $_POST['title'];
		$this->chartData = json_decode($_POST['data'], true);
	}

}
