<?php
	//bortscontroller
	Yii::app()->session['RemovingFailed']                           = 'Видалення не вдалось';
    Yii::app()->session['UpdateFailed']                             = 'Оновлення не вдалось';
    Yii::app()->session['AddFailed']                                = 'Додавання не вдалось';
	Yii::app()->session['BortText']                                 = 'Борт';
	Yii::app()->session['DoesNotExist']                             = 'не існує';
	Yii::app()->session['AlreadyExistsPleaseSelectAnotherName']     = 'вже існує. Виберіть, будь-ласка, іншу назву.';
    Yii::app()->session['NoCorrectBortNumber']   ='Державний номер борту некоректний!';

	//CalculateGrafikController
	Yii::app()->session['CalculationOfTheGraph']                    = "Розрахунок графіка: ";
	Yii::app()->session['IntroducedLessThanPreviousTimeInSchedule'] = 'Введений час менший за попередній час в графіку.';
	Yii::app()->session['ChangeSchedule']                           = "Зміна графіка: ";
	Yii::app()->session['YouCanOnlyDeleteGraphicsCreatedToday']     = 'Ви можете видаляти лише графіки, створені сьогодні';

	//ControlpointController
	Yii::app()->session['RouteHasNoControlPoints']                  = 'Маршрут не має контрольних точок';

	//ExportController
	Yii::app()->session['PeriodOf']   =' за період з ';
	Yii::app()->session['ToText']     =' по ';
	Yii::app()->session['DataText']   ='Дата: ';
	Yii::app()->session['TimeText']   ='Час: ';
	Yii::app()->session['TypeOfTZ']   ='Тип ТЗ: ';
	Yii::app()->session['RouteText']  ='Маршрут: ';
	Yii::app()->session['GrafikText'] ='Графік: ';

	//GraphController
	Yii::app()->session['GrafikTextTwo'] ='Графік ';

	//OrdersController
	Yii::app()->session['NoData'] ="немає даних";
	Yii::app()->session['OrderTo'] ='Наряд на ';
	Yii::app()->session['ForThisRouteSuccessfullyDuplicated'] =' для даного маршруту продубльовано успішно';

	//PointsControlController
	Yii::app()->session['RecordDeleted'] ='Запис видалено!';
	Yii::app()->session['RecordUpdated'] ='Запис оновлено!';
	Yii::app()->session['RecordAdded']   ='Запис додано!';
    Yii::app()->session['NoRights']   ='Відсутні права!';


	//ProblemsMoveOnLineCatalogController
	Yii::app()->session['OnboardDevice']                     ='Бортовий пристрій';
	Yii::app()->session['OnboardDeviceDescription']          ="Вказується процент заряду, якщо він досягає критичного рівня";
	Yii::app()->session['OnboardDeviceExampleProblems']      ="Низький рівень заряду: 3%";
	Yii::app()->session['Clock']                             ="Годинник";
	Yii::app()->session['ClockDescription']                  ="Синхронізація часу";
	Yii::app()->session['ClockExampleProblems']              ="Годинник наперед: 64 хв.";
	Yii::app()->session['ComplianceSchedule']                ="Дотримання розкладу";
	Yii::app()->session['ComplianceScheduleDescription']     ="Велике відхилення від розкладу";
	Yii::app()->session['ComplianceScheduleExampleProblems'] ="Різниця з розкладом: -46 хв.";
	Yii::app()->session['Communication']                     ="Зв'язок";
	Yii::app()->session['CommunicationDescription']          ="Вказується тривалість відсутності даних від борта";
	Yii::app()->session['CommunicationExampleProblems']      ="Відсутня передача даних: 125 хв.";
	Yii::app()->session['Map']                               ="Карта";
	Yii::app()->session['MapDescription1']                   ="Відсутнє відображення при сформованому наряді";
	Yii::app()->session['MapExampleProblems1']               ="Відсутні дані для відображення";
	Yii::app()->session['MapDescription2']                   ="Дублювання відображення бортів на карті";
	Yii::app()->session['MapExampleProblems5']               ="Один борт на 2 графіках";
	Yii::app()->session['MapExampleProblems4']               ="На одному графіку: 2 бортів";
	Yii::app()->session['MapDescription3']                   ="На карті відображається, але не в графіку";
	Yii::app()->session['MapExampleProblems3']               ="Карта фіолетовий колір. Поза межами графіку";
	Yii::app()->session['Order']                             ="Наряд";
	Yii::app()->session['OrderDescription1']                 ="Не присвоєно наряд";
	Yii::app()->session['OrderExampleProblems1']             ="На даний графік відсутній наряд";
	Yii::app()->session['OrderDescription2']                 ="На один графік одночасно поставлено декілька нарядів";
	Yii::app()->session['OrderExampleProblems2']             ="Нарядів на один графік: 2";
	Yii::app()->session['OrderDescription3']                 ="Борту присвоєно декілька нарядів";
	Yii::app()->session['OrderExampleProblems3']             ="Нарядів на один борт: 2";
	Yii::app()->session['OrderDescription4']                 ="Відображення на карті без присвоєнного наряду";
	Yii::app()->session['OrderExampleProblems4']             ="На даний борт відсутній наряд, на карті відображається";
	Yii::app()->session['OrderDescription5']                 ="Календарний день не відповідає виду розкладу за типом дня";
	Yii::app()->session['OrderExampleProblems5']             ="Їздить за розкладом дня: вихідний";

	//ProblemsMoveOnLineController
	Yii::app()->session['DayTypeWork']                   ="робочий";
	Yii::app()->session['DayTypeHollyday']               ="вихідний";
	Yii::app()->session['OrderExampleProblems2Text']     ="нарядів на один графік: ";
	Yii::app()->session['OrderExampleProblems3Text']     ='нарядів на один борт: ';
	Yii::app()->session['OrderExampleProblems4Text']     ="на даний борт відсутній наряд, на карті відображається";
	Yii::app()->session['OrderExampleProblems5Text']     ="їздить за розкладом дня: ";
	Yii::app()->session['MapDescription4']               ="не має в графіку. Час початку руху за розкладом ";
	Yii::app()->session['MapDescription5']               ="Карта фіолетовий колір";
	Yii::app()->session['ComplianceScheduleDescription1']="різниця з розкладом: ";
	Yii::app()->session['MinutesText']                   ="хв.";
	Yii::app()->session['MoveOnMapProblemsSpeedText']     ="По GPS даним";
	Yii::app()->session['SpeedLevel']                     ="Швидкість";

	Yii::app()->session['CommunicationExampleProblems1'] ="відсутня передача даних: ";
	Yii::app()->session['ClockExampleProblems1']         ="годинник наперед: ";
	Yii::app()->session['OnboardDeviceExampleProblems1'] ="низький рівень заряду: ";
	Yii::app()->session['MapExampleProblems2']           ="відсутні дані для відображення";
	Yii::app()->session['OneBortOn']                     ="oдин борт на ";
	Yii::app()->session['InGrafiks']                     =" графіках";
	Yii::app()->session['MapDubbing']                    ="Карта дубляж";
	Yii::app()->session['OnOneGrafik']                   ="на одному графіку: ";
	Yii::app()->session['BortsIn']                       =" бортів";
	Yii::app()->session['PiecesText']                    ="шт.";
	Yii::app()->session['OrderExampleProblems6']         ="на даний графік відсутній наряд";

	//ReportAverageGraphsController
	Yii::app()->session['AllGrafiks']      = "усі графіки";
	Yii::app()->session['InGeneral']       ='Загалом';
	Yii::app()->session['RouteTextFull']   ="Маршрут";
	Yii::app()->session['GrafikTextFull']  ="Графік";

	Yii::app()->session['YesTextSmall']  ='так';
	Yii::app()->session['NoTextSmall']  ='ні';
	Yii::app()->session['AllText']  ='усі';
	Yii::app()->session['FlightText']  ='Рейс';

	Yii::app()->session['AllRoutes']      = "усі маршрути";
	Yii::app()->session['FlightTextSmall']  ='рейс';

	Yii::app()->session['SuperAdmin']  ='Головний адміністратор';
	Yii::app()->session['Carrier']  ='Перевізник';
	Yii::app()->session['Admin']  ='Адміністратор';
	Yii::app()->session['RoleCw']  ='Сценарист';
	Yii::app()->session['RoleDisp']  ='Диспетчер';
	Yii::app()->session['SuperUser']  ='Суперюзер';

	Yii::app()->session['ActivationDoing']  ='Активовано';
	Yii::app()->session['GrafikTextSmall']  ="графік";

	Yii::app()->session['MoveTypeLine']     ='лінійний';
	Yii::app()->session['MoveTypeRound']    ='кільцевий';
	Yii::app()->session['MoveTypeMixed']    ='змішаний';
	Yii::app()->session['CalculationText']  ='розрахунок';

	Yii::app()->session['OutingNumber']  =               'Вихід №';
	Yii::app()->session['CountShiftChange']  =				'Кількість змін';
	Yii::app()->session['AttendanceText']  =				'Явка';
	Yii::app()->session['OutingFromDepot']  =				'Вихід з депо/гаража';
	Yii::app()->session['TimeStartOnRoute']  =				'Час початку руху на маршруті';
	Yii::app()->session['StopStartMoveOnRoute']  =				'Зупинка початку руху на маршруті';
	Yii::app()->session['TimeFinishMoveOnRoute']  =				'Час закінчення руху на маршруті';
	Yii::app()->session['StopFinishMoveOnRoute']  =				'Зупинка закінчення руху на маршруті';
	Yii::app()->session['DurationMoveAttendanceOutingFromDepot']  =				'Тривалість руху "явка - вихід з депо/гаража"';
	Yii::app()->session['DurationMoveOutingFromDepotStartMove']  =				'Тривалість руху "вихід з депо/гаража - початок руху на маршруті"';
	Yii::app()->session['DurationMoveStartFinishMove']  =				'Тривалість руху "початок руху на маршруті - кінець руху на маршруті"';
	Yii::app()->session['DurationMoveFinishOutingToDepot']  =				'Тривалість руху "кінець руху на маршруті - вихід з депо/гаража"';
	Yii::app()->session['StopDinner']  =				'Зупинка обіду';
	Yii::app()->session['TimeStartDinner']  =				'Час початку обіду';
	Yii::app()->session['TimeFinishDinner']  =				'Час закінчення обіду';
	Yii::app()->session['DurationDinner']  =				'Тривалість обіду';
	Yii::app()->session['DurationWorkWithDinner']  =				'Тривалість роботи з обідом';
	Yii::app()->session['WorkTimeWithoutDinner']  =				'Робочий час водія (без обіду)';
	Yii::app()->session['HoursText']                     ="год.";
	Yii::app()->session['KmPerHoursText']                ="км/год.";
	Yii::app()->session['SecondText']                    ="с.";

	Yii::app()->session['OnPointsOfEvents']                    ='по точках контролю';
	Yii::app()->session['CalcDeleted']                    ="Даний розклад видалено";
	Yii::app()->session['CalcActivedDeletingNotPossible']                    ="Даний розклад активовано. Видалити не можливо.";
	Yii::app()->session['RouteHaveNotDayIntervals']                    ='Для маршруту не введено періодів доби';
	Yii::app()->session['CopyScheduleCreated']                    ='Копія з розкладу, створеного';

	Yii::app()->session['RouteHasNoStops']                  = 'Маршрут не має зупинок';

	//SpeedDetailsController
	Yii::app()->session['canNotBeDetermined']                      = 'некоректні дані';
	Yii::app()->session['SpeedMoreThan120kmPerH']                  = "Швидкість більша за 120 км/год. Некоректні дані.";
	Yii::app()->session['betweenDataMoreThan30s']                  = "Час між передачами більше 30 с. Некоректні дані.";
	Yii::app()->session['SpeedOneOfSpeedGPSnull']                  = "Одна зі швидкістей по даним GPS рівна 0 км/год. Некоректні дані.";
	Yii::app()->session['AccessDenied']                  = "Немає доступу!";
?> 