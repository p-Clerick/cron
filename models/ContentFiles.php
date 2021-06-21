<?php
class ContentFiles extends CActiveRecord
{
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function relations()
    {
        return array(
            
        );
    }

    public function tableName()
    {
        return 'content_files';
    }
}
?>