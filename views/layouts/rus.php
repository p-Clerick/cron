<?php
	//bortscontroller
	Yii::app()->session['RemovingFailed'] = 'Удаление не удалось';
    Yii::app()->session['UpdateFailed'] = 'Оновлення не удалось';
    Yii::app()->session['AddFailed'] = 'Додавання не удалось';
	Yii::app()->session['BortText'] = 'Борт';
	Yii::app()->session['DoesNotExist'] = 'не существует';
	Yii::app()->session['AlreadyExistsPleaseSelectAnotherName'] ='уже существует. Выберите другое название.';
    Yii::app()->session['NoCorrectBortNumber']   ='Государственный номер борта некоректный!';

	//CalculateGrafikController
	Yii::app()->session['CalculationOfTheGraph'] ="Рассчет графика: ";
	Yii::app()->session['IntroducedLessThanPreviousTimeInSchedule'] ='Введенное время меньше за предыдущее в графике.';
	Yii::app()->session['ChangeSchedule'] ="Смена графика: ";
	Yii::app()->session['YouCanOnlyDeleteGraphicsCreatedToday'] ='Вы можете удалять только те графикт, которые созданы сегодня';

	//ControlpointController
	Yii::app()->session['RouteHasNoControlPoints'] ='Маршрут не имеет контрольных точук';

	//ExportController
	Yii::app()->session['PeriodOf'] =' за период с ';
	Yii::app()->session['ToText'] =' по ';
	Yii::app()->session['DataText'] ='Дата: ';
	Yii::app()->session['TimeText'] ='Время: ';
	Yii::app()->session['TypeOfTZ'] ='Тип ТЗ: ';
	Yii::app()->session['RouteText'] ='Маршрут: ';
	Yii::app()->session['GrafikText'] ='График: ';

	//GraphController
	Yii::app()->session['GrafikTextTwo'] ='График ';

	//OrdersController
	Yii::app()->session['NoData'] ="нет данных";
	Yii::app()->session['OrderTo'] ='Наряд на ';
	Yii::app()->session['ForThisRouteSuccessfullyDuplicated'] =' для данного маршрута продублировано успешно';

	//PointsControlController
	Yii::app()->session['RecordDeleted'] ='Запись удалена!';
	Yii::app()->session['RecordUpdated'] ='Запись обновлена!';
	Yii::app()->session['RecordAdded']   ='Запись добавлена!';
    Yii::app()->session['NoRights']   ='Отсутствуют права!';


	//ProblemsMoveOnLineCatalogController
	Yii::app()->session['OnboardDevice'] ='Бортовое устройство';
	Yii::app()->session['OnboardDeviceDescription'] ="Указывается процент заряда, если он достигает критического уровня";
	Yii::app()->session['OnboardDeviceExampleProblems'] ="Низкий уровень заряда: 3%";
	Yii::app()->session['Clock'] ="Время";
	Yii::app()->session['ClockDescription'] ="Синхронизация времени";
	Yii::app()->session['ClockExampleProblems'] ="Время наперед: 64 мин.";
	Yii::app()->session['ComplianceSchedule'] ="Соблюдение расписания";
	Yii::app()->session['ComplianceScheduleDescription'] ="Большое отклонение от расписания";
	Yii::app()->session['ComplianceScheduleExampleProblems'] ="Отклонение от расписания: -46 мин.";
	Yii::app()->session['Communication'] ="Связь";
	Yii::app()->session['CommunicationDescription'] ="Указывается продолжительность отсутствия данных от борта";
	Yii::app()->session['CommunicationExampleProblems'] ="Передача данных отсутствует: 125 мин.";
	Yii::app()->session['Map'] ="Карта";
	Yii::app()->session['MapDescription1'] ="Отсутсвует отображение при сформированим наряде";
	Yii::app()->session['MapExampleProblems1'] ="Отсутсвуют данные для отображения";
	Yii::app()->session['MapDescription2'] ="Дублирование отображения бортов на карте";
	Yii::app()->session['MapExampleProblems5'] ="Один борт на 2 графиках";
	Yii::app()->session['MapExampleProblems4']               ="На одном графике: 2 бортов";
	Yii::app()->session['MapDescription3'] ="На карте отображается, но не в графика";
	Yii::app()->session['MapExampleProblems3'] ="Карта фиолетовый цвет, не в графика";
	Yii::app()->session['Order'] ="Наряд";
	Yii::app()->session['OrderDescription1'] ="Не присвоено наряд";
	Yii::app()->session['OrderExampleProblems1'] ="На данный график отсутствует наряд";
	Yii::app()->session['OrderDescription2'] ="На один график одновременно поставлено несколько нарядов";
	Yii::app()->session['OrderExampleProblems2'] ="Нарядов на один график: 2";
	Yii::app()->session['OrderDescription3'] ="Борту присвоено несколько нарядов";
	Yii::app()->session['OrderExampleProblems3'] ="Нарядов на один борт: 2";
	Yii::app()->session['OrderDescription4'] ="Отображение на карті без присвоенного наряда";
	Yii::app()->session['OrderExampleProblems4'] ="На данный борт отсутствует наряд, на карте отображается";
	Yii::app()->session['OrderDescription5'] ="Календарный день не соответствует виду расписания за типом дня";
	Yii::app()->session['OrderExampleProblems5'] ="Двигается по расписанию дня: выходной";

	//ProblemsMoveOnLineController
	Yii::app()->session['DayTypeWork']                   ="рабочий";
	Yii::app()->session['DayTypeHollyday']               ="выходной";
	Yii::app()->session['OrderExampleProblems2Text']     ="нарядов на один график: ";
	Yii::app()->session['OrderExampleProblems3Text']     ='нарядов на один борт: ';
	Yii::app()->session['OrderExampleProblems4Text']     ="на данный борт отсутствует наряд, на карте отображается";
	Yii::app()->session['OrderExampleProblems5Text']     ="двигается по расписанию дня: ";
	Yii::app()->session['MapDescription4']               ="не в графика. Время начала движения согласно расписания ";
	Yii::app()->session['MapDescription5']               ="Карта фиолетовый цвет";
	Yii::app()->session['ComplianceScheduleDescription1']="отклонение от расписания: ";
	Yii::app()->session['MinutesText']                   ="мин.";
	Yii::app()->session['CommunicationExampleProblems1'] ="отсутствует передача данных: ";
	Yii::app()->session['MoveOnMapProblemsSpeedText']     ="По данным GPS";
	Yii::app()->session['SpeedLevel']                     ="Скорость";
	Yii::app()->session['ClockExampleProblems1']         ="время наперед: ";
	Yii::app()->session['OnboardDeviceExampleProblems1'] ="низкий уровень заряда: ";
	Yii::app()->session['MapExampleProblems2']           ="отсутствуют данные для отображения";
	Yii::app()->session['OneBortOn']                     ="oдин борт на ";
	Yii::app()->session['InGrafiks']                     =" графиках";
	Yii::app()->session['MapDubbing']                    ="Карта дубляж";
	Yii::app()->session['OnOneGrafik']                   ="на одном графике: ";
	Yii::app()->session['BortsIn']                       =" бортов";
	Yii::app()->session['PiecesText']                    ="шт.";
	Yii::app()->session['OrderExampleProblems6']         ="на данный график отсутствует наряд";

	//ReportAverageGraphsController
	Yii::app()->session['AllGrafiks']      = "все графики";
	Yii::app()->session['InGeneral']       ='Всего';
	Yii::app()->session['RouteTextFull']   ="Маршрут";
	Yii::app()->session['GrafikTextFull']  ="График";

	Yii::app()->session['YesTextSmall']  ='да';
	Yii::app()->session['NoTextSmall']  ='нет';
	Yii::app()->session['AllText']  ='все';
	Yii::app()->session['FlightText']  ='Рейс';
	Yii::app()->session['AllRoutes']      = "все маршруты";
	Yii::app()->session['FlightTextSmall']  ='рейс';

	Yii::app()->session['SuperAdmin']  ='Главный администратор';
	Yii::app()->session['Carrier']  ='Перевозчик';
	Yii::app()->session['Admin']  ='Администратор';
	Yii::app()->session['RoleCw']  ='Сценарист';
	Yii::app()->session['RoleDisp']  ='Диспетчер';
	Yii::app()->session['SuperUser']  ='Суперюзер';

	Yii::app()->session['ActivationDoing']='Активирован';
	Yii::app()->session['GrafikTextSmall']  ="график";

	Yii::app()->session['MoveTypeLine']  ='линейный';
	Yii::app()->session['MoveTypeRound']  ='круговой';
	Yii::app()->session['MoveTypeMixed']  ='смешаный';
	Yii::app()->session['CalculationText']  ='расчет';

	Yii::app()->session['OutingNumber']  =               'Выход №';                     
	Yii::app()->session['CountShiftChange']  =				'Количество смен';           
	Yii::app()->session['AttendanceText']  =				'Явка';                     
	Yii::app()->session['OutingFromDepot']  =				'Выход с депо/гаража';         
	Yii::app()->session['TimeStartOnRoute']  =				'Время начала движения на маршруте';
	Yii::app()->session['StopStartMoveOnRoute']  =				'Остановка начала движения на маршруте';
	Yii::app()->session['TimeFinishMoveOnRoute']  =				'Время окончания движения на маршруте';
	Yii::app()->session['StopFinishMoveOnRoute']  =				'Остановка окончания движения на маршруте';
	Yii::app()->session['DurationMoveAttendanceOutingFromDepot']  =				'Продолжительность движения "явка - выход из депо/гаража"';
	Yii::app()->session['DurationMoveOutingFromDepotStartMove']  =				'Продолжительность движения "выход из депо/гаража - начало движения на маршруте"';
	Yii::app()->session['DurationMoveStartFinishMove']  =				'Продолжительность движения "начало движения на маршруте - конец движения на маршруте"';
	Yii::app()->session['DurationMoveFinishOutingToDepot']  =				'Продолжительность движения "конец движения на маршруте - выход из депо/гаража"';
	Yii::app()->session['StopDinner']  =				'Остановка обеда';
	Yii::app()->session['TimeStartDinner']  =				'Время начала обеда';
	Yii::app()->session['TimeFinishDinner']  =				'Время окончания обеда';
	Yii::app()->session['DurationDinner']  =				'Продолжительность обеда';
	Yii::app()->session['DurationWorkWithDinner']  =				'Продолжительность работы с обедом';
	Yii::app()->session['WorkTimeWithoutDinner']  =				'Рабочее время водителя (без обеда)';
	Yii::app()->session['HoursText']                     ="ч.";
	Yii::app()->session['KmPerHoursText']                ="км/ч.";
	Yii::app()->session['SecondText']                    ="с.";

	Yii::app()->session['OnPointsOfEvents']                    ='по точкам контроля';
	Yii::app()->session['CalcDeleted']                    ="Данное расписание удалено";
	Yii::app()->session['CalcActivedDeletingNotPossible']                    ="Данное расписание активировано. Удалить не возможно.";
	Yii::app()->session['RouteHaveNotDayIntervals']                    ='Для маршрута не введено периодов суток';
	Yii::app()->session['CopyScheduleCreated']                    ='Копия расписания, созданного';

	Yii::app()->session['RouteHasNoStops']                  = 'Маршрут не имеет остановок';

	//SpeedDetailsController
	Yii::app()->session['canNotBeDetermined']                      = 'некорректные данные';
	Yii::app()->session['SpeedMoreThan120kmPerH']                  = "Скорость больше 120 км/ч. Некорректные данные.";
	Yii::app()->session['betweenDataMoreThan30s']                  = "Время между передачами более 30 с. Некорректные данные.";
	Yii::app()->session['SpeedOneOfSpeedGPSnull']                  = "Скорость по данным GPS ровна 0 км/ч. Некорректные данные.";
	Yii::app()->session['AccessDenied']                  = "Нет доступа!";
?>