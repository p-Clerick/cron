<?php
class ReportEndStops extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public $count_is_not_marked_endpoints;

	public function relations()
    {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
            'station'=>array(self::BELONGS_TO, 'Stations', 'stations_id'),
            'bort'=>array(self::BELONGS_TO, 'Borts', 'borts_id'),
            'park' => array(self::BELONGS_TO, 'Parks', 'parks_id'),
            'carrier' => array(self::BELONGS_TO, 'Carriers', 'carriers_id'),
        );
    }
	public function tableName()
	{
		return 'report_end_stops';
	}
    /**
     *	Повертає згруповані по перевізнику дані про не відмічені кінцеві
     *	@param integer $fromDate
     *  @param integer $toDate
     *
     *	@return $endpointsPerformance
     */
    public function getEndPointsPerformanceGroupByCarriers($fromDate,$toDate){
        $endpointsPerformance = ReportEndStops::model()->findAll(array(
            'select' => array('carriers_id','count(stations_id) as count_is_not_marked_endpoints'),
            'condition'=> 'date >= :f AND date <= :t',
            'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
            'order'    => 't.carriers_id',
            'group'    => 't.carriers_id'));

        if (!$endpointsPerformance) {
            return array();
            //throw new CException("Error finding endpointsPerformance");
        }
        else{
            return $endpointsPerformance;
        }
    }
    /**
     *	Повертає згруповані по перевізнику дані про не відмічені кінцеві
     *	@param integer $fromDate
     *  @param integer $toDate
     *
     *	@return $endpointsPerformance
     */
    public function getEndPointsPerformanceGroupByRoutesForCarrier($fromDate,$toDate,$carrierId){
        $endpointsPerformance = ReportEndStops::model()->findAll(array(
            'select' => array('carriers_id','routes_id','count(stations_id) as count_is_not_marked_endpoints'),
            'condition'=> 'date >= :f AND date <= :t AND t.carriers_id=:carid',
            'params'   =>array(':f'=>$fromDate, ':t'=>$toDate,':carid' => $carrierId),
            'order'    => 't.routes_id',
            'group'    => 't.carriers_id,t.routes_id'));

        if (!$endpointsPerformance) {
            return array();
            //throw new CException("Error finding endpointsPerformance");
        }
        else{
            return $endpointsPerformance;
        }
    }
}