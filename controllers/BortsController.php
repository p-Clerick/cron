<?php
class BortsController extends Controller
{
    public function actionBorts(){
        if (isset($_POST)){
            if($_POST['level'] === '1'){
                $sql = Borts::model()->findAll(array('order' => 'id'));
                $res =  CJSON::encode(array(
                'success'=>'true',
                'data'=>$sql
                ));
                echo $res;
            }
        }
    }
    public function actionDelete($id){
        if( Yii::app()->user->checkAccess('deleteBorts')){
            $result = array();
            $bort = Borts::model()->findByPk($id);
            if($bort->delete()){
                $result = array(
                    'success' => true,
                    'message' => Yii::app()->session['RecordDeleted']);
            } else {
                $result = array(
                    'success' => false,
                    'message' => Yii::app()->session['RemovingFailed']
                );
            }
        }else {
            $result = array(
                'success' => false,
                'message' => Yii::app()->session['NoRights']
            );
        }
        echo CJSON::encode($result);
    }
    public function actionRead(){
        $carrier = Yii::app()->user->checkUser(Yii::app()->user);
        if($carrier){
            $borts = Borts::model()->with('model','park')->findAll(array(
                'condition'=>'model.transport_types_id=:trtypeid and park.carriers_id=:carrid',
                'params'=>array(':trtypeid'=>$_GET['nodeid'],':carrid'=>$carrier['carrier_id']),
                'order' => 't.id'));
        }
        else{
            $borts = Borts::model()->with('model','park')->findAll(array(
                'condition'=>'model.transport_types_id=:trtypeid',
                'params'=>array(':trtypeid'=>$_GET['nodeid']),
                'order' => 't.id'));
        }
        $result = array(
            'success'=>true,
            'data'=>array(),
        );
        foreach($borts as $bort){
            $result['data'][] = array(
                'id'			=> $bort->id,
                'number'		=> $bort->number,
                'state_number' 	=> $bort->state_number,
                'models' 		=> $bort->model->mark->name.' - '.$bort->model->name,
                'parks'			=> $bort->park->name,
                'status' 		=> $bort->status,
                'connection' 	=> $bort->connection,
                'special_needs' => $bort->special_needs
            );
        }
        echo json_encode($result);
    }
    public function actionUpdate(){
        if( Yii::app()->user->checkAccess('updateBorts')){
            $result = array();
            $data = json_decode(Yii::app()->request->getPut('data'),true);
            if(isset($data['state_number']) and !preg_match('|^[A-Z0-9]+$|i', $data['state_number']))
            {
                $result = array(
                    'success' => false,
                    'message' =>  Yii::app()->session['NoCorrectBortNumber']
                );
            }
            else {
                if ($borts = Borts::model()->findByPk($data['id'])) {
                    if (isset($data['number'])) {
                        $borts->number = $data['number'];
                    }
                    if (isset($data['state_number'])) {
                        $borts->state_number = $data['state_number'];
                    }
                    if (isset($data['status'])) {
                        $borts->status = $data['status'];
                    }
                    if (isset($data['special_needs'])) {
                        $borts->special_needs = $data['special_needs'];
                    }
                    if (isset($data['connection'])) {
                        $borts->connection = $data['connection'];
                    }
                    if (isset($data['models'])) {
                        list($markname, $modelname) = explode(" - ", $data['models']);
                        $models = Models::model()->find('name=:name', array(':name' => $modelname));
                        $borts->models_id = $models->id;
                    }
                    if (isset($data['parks'])) {
                        $parks = Parks::model()->find('name=:name', array(':name' => $data['parks']));
                        $borts->parks_id = $parks->id;
                    }
                    if ($borts->save()) {
                        $result = array('success' => true, 'message' => Yii::app()->session['RecordUpdated']);
                    } else {
                        $result = array('success' => false, 'message' =>  Yii::app()->session['UpdateFailed'] );
                    }
                } else {
                    $result = array(
                        'success' => false,
                        'message' => Yii::app()->session['BortText'] . ' ' . $data['number'] . ' ' . Yii::app()->session['DoesNotExist'] . '.',
                    );
                }
            }
        }else {
            $result = array(
                'success' => false,
                'message' => Yii::app()->session['NoRights']
            );
        }
        echo CJSON::encode($result);
    }
    public function actionCreate(){
        $carrier = Yii::app()->user->checkUser(Yii::app()->user);
        if( Yii::app()->user->checkAccess('createBorts')){
            $result = array();
            $data = CJSON::decode(Yii::app()->request->getPost('data'),true);
            if(!preg_match('|^[A-Z0-9]+$|i', $data['state_number']))
            {
                $data['id'] = '';
                $result = array(
                    'success' => false,
                    'message' =>  Yii::app()->session['NoCorrectBortNumber'],
                    'data' => $data
                );
            }
            else {
                if (!Borts::model()->exists(array(
                    'condition' => 't.number = :number and t.state_number=:stnum',
                    'params' => array(':number' => $data['number'], ':stnum' => $data['state_number']),
                    'order' => 't.id'))
                ) {
                    list($markname, $modelname) = explode(" - ", $data['models']);
                    $models = Models::model()->find('name=:name', array(':name' => $modelname));
                    $parks = Parks::model()->find('name=:name', array(':name' => $data['parks']));
                    $borts = new Borts;
                    $borts->number = $data['number'];
                    $borts->state_number = $data['state_number'];
                    $borts->parks_id = $parks->id;
                    $borts->models_id = $models->id;
                    $borts->status = $data['status'];
                    $borts->connection = $data['connection'];
                    $borts->special_needs = $data['special_needs'];

                    if ($borts->save()) {
                        $data['id'] = $borts->id;
                        $result = array('success' => true, 'message' => Yii::app()->session['RecordAdded'], 'data' => $data);
                    } else {
                        $data['id'] = '';
                        $result = array('success' => false, 'message' => Yii::app()->session['AddFailed'], 'data' => $data);
                    }
                } else {
                    $result = array(
                        'success' => false,
                        'message' => Yii::app()->session['BortText'] . ' ' . $data['number'] . ' ' . Yii::app()->session['AlreadyExistsPleaseSelectAnotherName'],
                    );
                }
            }
        }else {
            $result = array(
                'success' => false,
                'message' => Yii::app()->session['NoRights']
            );
        }
        echo CJSON::encode($result);
    }
    public function actionParksName(){
        $carrier = Yii::app()->user->checkUser(Yii::app()->user);
        if($carrier){
            $parks = Parks::model()->findAll(array(
            'select'=>'id,name',
            'condition'=>'t.carriers_id=:carrid',
            'params'=>array(':carrid'=>$carrier['carrier_id']),
            'order' => 'id'));
        }
        else{
            $parks = Parks::model()->findAll(array(
                'select'=>'id,name',
                'order' => 'id'));
        }
        $result = array(
            'success'=>true,
            'data'=>array(),
        );
        foreach($parks as $park){
            $result['data'][] = array(
                'id'	=> $park->id,
                'parks'	=> $park->name
            );
        }
        echo json_encode($result);
    }
    public function actionModelsName(){
        $models = Models::model()->with('mark')->findAll(array(
        'select'=>'id,name',
        'condition'=>'transport_types_id=:trtypeid',
        'params'=>array(':trtypeid'=>$_POST['nodeid']),
        'order' => 't.id'));
        $result = array(
        'success'=>true,
        'data'=>array(),
        );
        foreach($models as $model){
            $result['data'][] = array(
                'id'		=> $model->id,
                'models'	=> $model->mark->name.' - '.$model->name
            );
        }
        echo json_encode($result);
    }
}