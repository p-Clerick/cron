<?php
class ReportPercentageRoutesGraphs extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public $percent_performance_graph_by_all;
    public $percent_performance_graph_by_points;
    public $count_points_fact;
    public $count_points_plan;
    public $percent_performance_graph_by_flights;
    public $count_flights_fact;
    public $count_flights_plan;
    public $carrier_name;
    public $routes_names;

	public function relations()
    {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
            'carrier' => array(self::BELONGS_TO, 'Carriers', 'carriers_id'),
        );
    }
	public function tableName()
	{
		return 'report_percentage_routes_to_graphs';
	}

    /**
     *	Повертає згруповані по перевізнику дані про виконання графіка руху для періоду
     *	@param integer $fromDate
     *  @param integer $toDate
     *
     *	@return $graphPerformance
     */
    public function getGraphPerformanceGroupByCarriers($fromDate,$toDate){
        $graphPerformance = ReportPercentageRoutesGraphs::model()->with('carrier','route')->findAll(array(
            'select' => array('carriers_id', 'carrier.name as carrier_name',
                'GROUP_CONCAT(DISTINCT route.name ORDER BY cast(route.name as unsigned)) AS routes_names',
                'avg(percentage_realization) as percent_performance_graph_by_all',
                'avg(percentage_stations) as percent_performance_graph_by_points',
                'sum(count_stations_fakt) as count_points_fact',
                'sum(count_stations_plan) as count_points_plan',
                'avg(percentage_flight) as percent_performance_graph_by_flights',
                'sum(count_flight_fakt) as count_flights_fact',
                'sum(count_flight_plan) as count_flights_plan'),
            'condition'=> 'date >= :f AND date <= :t',
            'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
            'order'    => 't.carriers_id',
            'group'    => 't.carriers_id'));

        if (!$graphPerformance) {
            throw new CException("Error finding graphPerformance");
        }
        else{
            return $graphPerformance;
        }
    }

    /**
     *	Повертає згруповані по маршрутах для перевізника дані про виконання графіка руху для періоду
     *	@param integer $fromDate
     *  @param integer $toDate
     *  @param integer $carrierId
     *
     *	@return $graphPerformance
     */
    public function getGraphPerformanceGroupByRoutesForCarrier($fromDate,$toDate,$carrierId){
        $graphPerformance = ReportPercentageRoutesGraphs::model()->with('carrier','route')->findAll(array(
            'select' => array('carriers_id', 'routes_id', 'carrier.name as carrier_name',
                'GROUP_CONCAT(DISTINCT route.name ORDER BY cast(route.name as unsigned)) AS routes_names',
                'avg(percentage_realization) as percent_performance_graph_by_all',
                'avg(percentage_stations) as percent_performance_graph_by_points',
                'sum(count_stations_fakt) as count_points_fact',
                'sum(count_stations_plan) as count_points_plan',
                'avg(percentage_flight) as percent_performance_graph_by_flights',
                'sum(count_flight_fakt) as count_flights_fact',
                'sum(count_flight_plan) as count_flights_plan'),
            'condition'=> 'date >= :f AND date <= :t AND t.carriers_id=:carid',
            'params'   =>array(':f'=>$fromDate, ':t'=>$toDate,':carid' => $carrierId),
            'order'    => 't.routes_id',
            'group'    => 't.routes_id'));

        if (!$graphPerformance) {
            throw new CException("Error finding graphPerformance");
        }
        else{
            return $graphPerformance;
        }
    }


		
}