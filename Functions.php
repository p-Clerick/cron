<?php
function getRewiew($dateToRecalc)
{
    $rewiew = $dateToRecalc;
    $countDate = count($rewiew);

    if ($countDate == 0) {
        $rewiew = [];
        $day = date('Y-m-d');
        $find = DaysToReport::model()->findByAttributes([
            'date' => $day,
        ]);
        $dy = $find->found_days;
        if ($dy) {
            $dyy = explode(",", $dy);
            foreach ($dyy as $key => $value) {
                $rewiew[$key] = $value;
            }
        }
        $countDate = count($rewiew);
    }
    return [$rewiew, $countDate];

}

function logger($text, $data, $logname = 'custom'){
    $fileName = getcwd() . '/runtime/LOG/' . $logname . '.log';

    // echo "\n" . $fileName . "\n";

    $file = fopen($fileName, 'a');
    fwrite($file, "\n\n" . date('H:i:s d-m-Y: ' . $text . ': ' . var_export($data, true) ) );
    fclose($file);
}