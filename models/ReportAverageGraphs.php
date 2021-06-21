<?php
class ReportAverageGraphs extends CActiveRecord {

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public $advance_percentage;
    public $advance_average_for;
    public $lateness_percentage;
    public $lateness_average;
    public $ontime_percentage;
    public $ontime_average;
	public function relations()
    {
        return array(
            'route' => array(self::BELONGS_TO, 'Route', 'routes_id'),
            'graph' => array(self::BELONGS_TO, 'Graphs', 'graphs_id'),
        );
    }
	public function tableName()
	{
		return 'report_avarage_deviation_to_graphs';
	}
    /**
     *	Повертає згруповані по перевізнику дані про вчасність руху по графіку
     *	@param integer $fromDate
     *  @param integer $toDate
     *
     *	@return $complianceSchedule
     */
    public function getComplianceScheduleGroupByCarriers($fromDate,$toDate){
        $complianceSchedule = ReportAverageGraphs::model()->findAll(array(
            'select' => array('carriers_id',
                'avg(advance_percentage) as advance_percentage',
                'avg(advance_average) as advance_average',
                'avg(lateness_percentage) as lateness_percentage',
                'avg(lateness_average) as lateness_average',
                'avg(ontime_percentage) as ontime_percentage',
                'avg(ontime_average) as ontime_average'),
            'condition'=> 'date >= :f AND date <= :t',
            'params'   =>array(':f'=>$fromDate, ':t'=>$toDate),
            'order'    => 't.carriers_id',
            'group'    => 't.carriers_id'));

        if (!$complianceSchedule) {
            throw new CException("Error finding complianceSchedule");
        }
        else{
            return $complianceSchedule;
        }
    }

    /**
     *	Повертає згруповані по перевізнику дані про вчасність руху по графіку
     *	@param integer $fromDate
     *  @param integer $toDate
     *
     *	@return $complianceSchedule
     */
    public function getComplianceScheduleGroupByRoutesForCarrier($fromDate,$toDate,$carrierId){
        $complianceSchedule = ReportAverageGraphs::model()->findAll(array(
            'select' => array('carriers_id','routes_id',
                'avg(advance_percentage) as advance_percentage',
                'avg(advance_average) as advance_average',
                'avg(lateness_percentage) as lateness_percentage',
                'avg(lateness_average) as lateness_average',
                'avg(ontime_percentage) as ontime_percentage',
                'avg(ontime_average) as ontime_average'),
            'condition'=> 'date >= :f AND date <= :t AND t.carriers_id=:carid',
            'params'   =>array(':f'=>$fromDate, ':t'=>$toDate,':carid' => $carrierId),
            'order'    => 't.routes_id',
            'group'    => 't.carriers_id,t.routes_id'));

        if (!$complianceSchedule) {
            throw new CException("Error finding complianceSchedule");
        }
        else{
            return $complianceSchedule;
        }
    }


		
}