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