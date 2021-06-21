<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="lib/ext/resources/css/ext-all.css"/>
    <link rel="stylesheet" type="text/css" href="images/icons/silk.css" />
    <link rel="stylesheet" type="text/css" href="lib/ux/coltree/ColumnNodeUI.css" />
    <link rel="stylesheet" type="text/css" href="lib/ux/examples.css" />
    <link rel="stylesheet" type="text/css" href="lib/ux/RowEditor/RowEditor.css" />
    <link rel="stylesheet" type="text/css" href="css/docs.css"></link>
    <link rel="stylesheet" type="text/css" href="css/marker_maps.css"></link>
    <link rel="stylesheet" type="text/css" href="lib/ux/Ext.ux.form.CheckboxCombo.css"></link>

    <title>М@К</title>
        <style type="text/css">
        /* style rows on mouseover */
        .x-grid3-row-over .x-grid3-cell-inner {
            font-weight: bold;
        }
    </style>
    <script  type="text/javascript" src="lib/ext/adapter/ext/ext-base.js"></script>
    <script  type="text/javascript" src="lib/ext/ext-all.js"></script>
    <script  type="text/javascript" src="lib/jQuery/jquery-1.4.2.min.js"></script>
    <script  type="text/javascript" src="lib/jQuery/jquery.json-1.3.js"></script>
    <?php
        if (!isset(Yii::app()->session['lang'])) {
            Yii::app()->session['lang']='ua';
            $lang = Yii::app()->session['lang'];
        }
        if (isset(Yii::app()->session['lang'])) {
            $lang = Yii::app()->session['lang'];
        }

         if($lang == "ua"){
            echo '<script type="text/javascript" src="lib/ext/srs/locale/ext-lang-ukr.js"></script>
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkB-dzd0veQ7fTM2W71kD2q38_Wj04Vfw&amp;libraries=drawing&amp;language=ua"></script>
            <script type="text/javascript" src="lib/lang/ua/ua.js"></script>';
            require_once(dirname(__FILE__).'/ua.php');

        }
        else if($lang == "ru") {
            echo '<script type="text/javascript" src="lib/ext/srs/locale/ext-lang-ru.js"></script>
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkB-dzd0veQ7fTM2W71kD2q38_Wj04Vfw&amp;libraries=drawing&amp;language=ru"></script>
            <script type="text/javascript" src="lib/lang/ru/ru.js"></script>';
            require_once(dirname(__FILE__).'/rus.php');
        }
        else{
            echo '<script type="text/javascript" src="lib/ext/srs/locale/ext-lang-ukr.js"></script>
            <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDkB-dzd0veQ7fTM2W71kD2q38_Wj04Vfw&amp;libraries=drawing&amp;language=ua"></script>
            <script type="text/javascript" src="lib/lang/ua/ua.js"></script>';
            require_once(dirname(__FILE__).'/ua.php');
        }
    ?>
    ?>



    <script  type="text/javascript" src="lib/ux/printer/Printer.js"></script>
    <script  type="text/javascript">
        Ext.ux.Printer.BaseRenderer.prototype.stylesheetPath ="lib/ux/printer/stylesheets/base1.css"
    </script>

    <!-- Include HighCharts -->
    <!--<script  type="text/javascript" src="../lib/highcharts/adapter-extjs.js"></script>-->
    <script  type="text/javascript" src="/lib/highcharts/highcharts.js"></script>
    <script  type="text/javascript" src="/lib/highcharts/modules/exporting.js"></script>
    <script  type="text/javascript">
          Highcharts.setOptions({
            lang:{
                downloadPNG: lang.hightcharts.export_in_png,
                downloadJPEG: lang.hightcharts.export_in_jpeg,
                downloadPDF: lang.hightcharts.export_in_pdf,
                downloadSVG: lang.hightcharts.export_in_svg,
                exportButtonTitle: lang.hightcharts.exportButtonTitle,
                printButtonTitle: lang.hightcharts.printButtonTitle,
                loading: lang.hightcharts.loading,
                resetZoom: lang.hightcharts.resetZoom,
                resetZoomTitle: lang.hightcharts.resetZoomTitle,
                months: [
                    lang.hightcharts.months_one,
                    lang.hightcharts.months_two,
                    lang.hightcharts.months_three,
                    lang.hightcharts.months_four,
                    lang.hightcharts.months_five,
                    lang.hightcharts.months_six,
                    lang.hightcharts.months_seven,
                    lang.hightcharts.months_eight,
                    lang.hightcharts.months_nine,
                    lang.hightcharts.months_ten,
                    lang.hightcharts.months_eleven,
                    lang.hightcharts.months_twelve],
                weekdays: [
                    lang.hightcharts.weekdays_one,
                    lang.hightcharts.weekdays_two,
                    lang.hightcharts.weekdays_three,
                    lang.hightcharts.weekdays_four,
                    lang.hightcharts.weekdays_five,
                    lang.hightcharts.weekdays_six,
                    lang.hightcharts.weekdays_seven]
            }
        });
    </script>



    <script  type="text/javascript">
         Ext.ns('App');
         var MAK = {};
         MAK.routes = {};
    </script>
    <script  type="text/javascript" src="lib/ux/ColumnHeaderGroup.js"></script>
    <script  type="text/javascript" src="lib/ux/App.js"></script>
    <script  type="text/javascript" src="lib/ux/Ext.ux.util.js"></script>
    <script  type="text/javascript" src="lib/ux/RowEditor/RowEditor.js"></script>
    <script  type="text/javascript" src="lib/ux/Ext.ux.grid.Search.js"></script>
    <script  type="text/javascript" src="lib/ux/Ext.ux.form.PopCombo.js"></script>
    <script  type="text/javascript" src="lib/ux/Ext.ux.form.CheckboxCombo.js"></script>
    <script  type="text/javascript" src="lib/ux/Ext.ux.grid.PageSizer.js"></script>
    <script  type="text/javascript" src="lib/ux/GMapPanel.js"></script>
    <script  type="text/javascript" src="lib/ux/Ext.ux.grid.AutoRefresher.js"></script>
    <script  type="text/javascript" src="lib/ux/n_tz_AR/n_tz_parent_AR.js"></script>
    <script  type="text/javascript" src="lib/ux/n_tz_AR/Status_n_tz_AR.js"></script>
    <script  type="text/javascript" src="lib/ux/coltree/ColumnNodeUI.js"></script>
    <script  type="text/javascript" src="lib/ux/TimeField.js"></script>
    <script  type="text/javascript" src="lib/ux/Ext.ux.Highcharts.js"></script>
    <script  type="text/javascript" src="lib/ux/Msg.js"></script>
    <script  type="text/javascript" src="lib/ux/CustomVTypes.js"></script>
    <script  type="text/javascript" src="lib/markerwithlabel.js"></script>
    <script  type="text/javascript" src="lib/markermanagerv3.js"></script>
    <script  type="text/javascript" src="lib/googlemapsfunctions.js"></script>
    <script  type="text/javascript" src="lib/maps_operations.js"></script>
    <script  type="text/javascript">
	    var Docs = function(){
	        return {
	            init : function(){
	                var loading = Ext.get('loading');
	                var mask = Ext.get('loading-mask');
	                //mask.setOpacity(.8);
	                mask.shift({
	                    xy:loading.getXY(),
	                    width:loading.getWidth(),
	                    height:loading.getHeight(),
	                    remove:true,
	                    duration:1,
	                    opacity:.3,
	                    easing:'bounceOut',
	                    callback : function(){
	                        loading.fadeOut({duration:.2,remove:true});
	                    }
	                });
	            }
	        };
	    }();
	    Ext.onReady(Docs.init, Docs, true);
	</script>
</head>

<body>
	<div id="loading-mask" style=""></div>
    <div id="loading">
        <div class="loading-indicator">
            <img src="images/extjs/extanim32.gif" width="32" height="32" style="margin-right:8px;" align="middle" alt="Short description of the image"/>Loading...
        </div>
    </div>




	<?php echo $content; ?>


<!-- Global Site Tag (gtag.js) - Google Analytics -->
<script async 
src="https://www.googletagmanager.com/gtag/js?id=UA-106891166-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments)};
  gtag('js', new Date());

  gtag('config', 'UA-106891166-1');
</script>


</body>
</html>
