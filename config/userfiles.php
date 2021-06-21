<?php

return array(

	'components'=>array(
		'clientScript'=>array(
			'coreScriptPosition'=>CClientScript::POS_END,
			'packages'=>array(
				'admin'=>array(
					'basePath'=>'webroot.js',
					'baseUrl'=>'/js',
					'css'=>array(

					),
					'js'=>array(
						/*-- Бібліотека загальних функцій --*/
						"lib/lib.js",

						"lib/date/date.js",
						"lib/date/hms_to_seconds.js",
						"lib/date/seconds_to_hms.js",
						
						"lib/msg/msg.js",
						"lib/msg/show_error.js",
						"lib/msg/show_warning.js",


						/*-- Інтерфейс для роботи з умовами ігнорування зупинок --*/
						"ignore_stations/ignore_stations.js",

						"ignore_stations/current_record.js",

						"ignore_stations/is_record_active.js",
						"ignore_stations/set_delete_button_enabled.js",

						"ignore_stations/data/data.js",

						"ignore_stations/data/proxy/proxy.js",
						"ignore_stations/data/proxy/ignore_stations_http_proxy.js",

						"ignore_stations/data/reader/reader.js",
						"ignore_stations/data/reader/ignore_stations_json_reader.js",

						"ignore_stations/data/writer/writer.js",
						"ignore_stations/data/writer/ignore_stations_json_writer.js",

						"ignore_stations/data/store/store.js",
						"ignore_stations/data/store/ignore_stations_grouping_store.js",
						"ignore_stations/data/store/stations_json_store.js",
						"ignore_stations/data/store/routes_json_store.js",
						"ignore_stations/data/store/weekdays_array_store.js",

						"ignore_stations/renderer/renderer.js",
						"ignore_stations/renderer/station_renderer.js",
						"ignore_stations/renderer/routes_list_renderer.js",
						"ignore_stations/renderer/weekdays_list_renderer.js",

						"ignore_stations/row_editor.js",
						"ignore_stations/tbar.js",
						"ignore_stations/column_model.js",
						"ignore_stations/grid_panel.js",


						"RestTreeLoader.js",
						"tree/BaseTree.js",
						"tree/TmgTree.js",
						"tree/ReportTree.js",
						"tree/RoutesTree.js",
						"tree/SettingsTree.js",						
						"tree/StationsTree.js",
						"tree/NoticeTree.js",
												
						"report/DateFieldset.js",
						"report/DatesFieldset.js",

						"report/panel/SidebarPanel.js",

						"report/store/BaseProxy.js",

						"reklama/rek_data_panel.js",
						"reklama/map_rek.js",
						"reklama/tree_events_rek.js",
						"reklama/rek_copy_panel.js",
						"reklama/col_tree_grid.js",

						"reklama/rek_data_scenario_panel.js",

						"table_ruh/table_ruh_on_data.js",
						"table_ruh/tree_events_ruh.js",

						"data_history_map/history_map.js",
						"data_history_map/tree_his.js",

						"karta/maps.js",
						"karta/base_map.js",
						"karta/tree_events_on_line_map.js",

						"tochki_zupynki/zup_data_panel.js",
						"tochki_zupynki/zup_data_scenario_panel.js",
						"tochki_zupynki/map_zup.js",
						"tochki_zupynki/tree_events_zup.js",

						"stations/station_data_panel.js",
						"stations/station_data_scenario_panel.js",
						"stations/map_station.js",
						"stations/map_station_scenario.js",

						"tochki_control/toch_control_data_panel.js",
						"tochki_control/toch_control_scenario_data_panel.js",
						"tochki_control/map_toch_control.js",
						"tochki_control/tree_events_toch_control.js",

                        "n_tz/order_components.js",
						"n_tz/n_tz_data_panel.js",
						"n_tz/tree_events_n_tz.js",
						"n_tz/status_panel.js",

                        "routes/routes_map_panel/routes_map_function.js",
						"routes/routes_map_panel/routes_map_draw.js",
						"routes/routes_map_panel/render_routes_map.js",
						"routes/routes_map_panel/all_routes_map.js",
						"routes/routes_map_panel/routes_map_panel.js",

						"routes/routes_grid_panel/routes_directions_panel.js",
						"routes/routes_grid_panel/routes_config_panel.js",
						"routes/routes_grid_panel/routes_grid_panel.js",
						
						"routes/routes_tab_panel.js",

						/*-- Модуль "Пошук маршруту" --*/
						"route_search/main_variables.js",

						"route_search/buttons/get_directions_button.js",
						"route_search/buttons/help_button.js",
						"route_search/buttons/map_button.js",

						"route_search/functions/calc_route.js",
						"route_search/functions/create_end_marker.js",
						"route_search/functions/create_start_marker.js",
						"route_search/functions/delete_route.js",
						"route_search/functions/delete_stations.js",
						"route_search/functions/display_results_panel.js",
						"route_search/functions/display_stations.js",
						"route_search/functions/draw_route.js",
						"route_search/functions/namespace.js",
						"route_search/functions/scalling_map.js",
						"route_search/functions/show_msg.js",

						"route_search/map/route_search_map.js",	

						"route_search/functions.js",
						"route_search/help_window.js",
						"route_search/context_menu.js",		
						"route_search/sidebar_panel.js",
						"route_search/route_toolbar_container.js",
						"route_search/results_route_search_panel.js",

						
						"tz_info/tz_info_data_panel.js",
						"tz_info/tree_events_tz_info.js",

						"settings/settings_tab_panel.js",

						"notice/notice_map.js",	
						"notice/notification/notification_store.js",
						"notice/notification/notification_panel.js",
						"notice/notification_response/notification_response_store.js",
						"notice/notification_response/notification_response_panel.js",
						"notice/notification_history/notification_history_store.js",
						"notice/notification_history/notification_history_panel.js",						
						"notice/notice_tab_panel.js",

						"report/store/ParkingStore.js",
						"report/store/NoconnectionStore.js",
						"report/store/RaceStore.js",
						"report/store/SpeedStore.js",
						"report/store/SpeedmodeStore.js",
						"report/store/WorktimeStore.js",
						"report/store/VidhylStore.js",
						"report/store/DeviationByMonthStore.js",
						"report/store/DeviationStore.js",
						"report/store/WorktimeByRouteStore.js",
						"report/store/FlightCompletionStore.js",

						"report/grid/ParkingGrid.js",
						"report/grid/NoconnectionGrid.js",
						"report/grid/RaceGrid.js",
						"report/grid/SpeedGrid.js",
						"report/grid/SpeedmodeGrid.js",
						"report/grid/WorktimeGrid.js",
						"report/grid/VidhylGrid.js",
						"report/grid/DeviationByMonthGrid.js",
						"report/grid/DeviationGrid.js",
						"report/grid/WorktimeByRouteGrid.js",
						"report/grid/FlightCompletionGrid.js",

						"report/panel/NoconnectionPanel.js",
						"report/panel/ParkingPanel.js",
						"report/panel/RacePanel.js",
						"report/panel/SpeedPanel.js",
						"report/panel/SpeedmodePanel.js",
						"report/panel/VidhylPanel.js",
						"report/panel/WorktimePanel.js",
						"report/panel/DeviationByMonthPanel.js",
						"report/panel/DeviationPanel.js",
						"report/panel/WorktimeByRoutePanel.js",
						"report/panel/FlightCompletionPanel.js",

						"accounts/grid_accounts.js",

						"tmg/RouteStore.js",
						"tmg/route_grid.js",
						"tmg/grafik_grid.js",
						"tmg/RouteHistoryReviewId.js",
						"tmg/RouteReviewTimeCarrier.js",
						"tmg/RouteGraphOrder.js",
						"tmg/ReysOrder.js",
						"tmg/EngineViewTableCarrier.js",
						"tmg/ExtractEngine.js",
						"tmg/Singboard.js",
						"tmg/Comparizon.js",
						"tmg/DrowingStopsChart.js",
						"tmg/ListStops.js",
						"tmg/DistanceBoard.js",
						
						"main/main_menu.js",
						"main/main_panel.js",

						"move_report/MoveOnLine.js",
						"move_report/ChartMoveOnLine.js",

						"move_report/StopsMoveOnLine.js",
						"move_report/ChartStopsMoveOnLine.js",
						"move_report/ChartStopsInDateMoveOnLine.js",

						"move_report/ReportPercentageGraphs.js",
						"move_report/ChartReportPercentageGraphs.js",

						"move_report/ReportAverageGraphs.js",
						"move_report/ChartReportAverageGraphs.js",

						"move_report/ReportPercentageFlightsGraphs.js",
						"move_report/ChartReportPercentageFlightsGraphs.js",

						"move_report/ReportOutingGraphs.js",
						"move_report/ReportEndStopsGraphs.js",
						"move_report/OutingOnDoingTime.js",

						"move_report/ReportEndStopsCarriers.js",
						"move_report/ChartReportEndStopsCarriers.js",

						"move_report/DoNotCachStops.js",
						"move_report/ScheduleCachStops.js",

						"move_report/ProblemsMoveOnLineCatalog.js",
						"move_report/ProblemsMoveOnLine.js",
						"move_report/Speed.js",
						"move_report/SpeedDetailsPanel.js",
						"move_report/IntervalsReport.js",
						"move_report/ReportCongressRoute.js",
						"move_report/ReportTransferData.js"
					),
				),


				'gov'=>array(
					'basePath'=>'webroot.js',
					'baseUrl'=>'/js',
					'css'=>array(

					),
					'js'=>array(
						/*-- Бібліотека загальних функцій --*/
						"lib/lib.js",

						"lib/date/date.js",
						"lib/date/hms_to_seconds.js",
						"lib/date/seconds_to_hms.js",
						
						"lib/msg/msg.js",
						"lib/msg/show_error.js",
						"lib/msg/show_warning.js",


						/*-- Інтерфейс для роботи з умовами ігнорування зупинок --*/
						"ignore_stations/ignore_stations.js",

						"ignore_stations/current_record.js",

						"ignore_stations/is_record_active.js",
						"ignore_stations/set_delete_button_enabled.js",

						"ignore_stations/data/data.js",

						"ignore_stations/data/proxy/proxy.js",
						"ignore_stations/data/proxy/ignore_stations_http_proxy.js",

						"ignore_stations/data/reader/reader.js",
						"ignore_stations/data/reader/ignore_stations_json_reader.js",

						"ignore_stations/data/writer/writer.js",
						"ignore_stations/data/writer/ignore_stations_json_writer.js",

						"ignore_stations/data/store/store.js",
						"ignore_stations/data/store/ignore_stations_grouping_store.js",
						"ignore_stations/data/store/stations_json_store.js",
						"ignore_stations/data/store/routes_json_store.js",
						"ignore_stations/data/store/weekdays_array_store.js",

						"ignore_stations/renderer/renderer.js",
						"ignore_stations/renderer/station_renderer.js",
						"ignore_stations/renderer/routes_list_renderer.js",
						"ignore_stations/renderer/weekdays_list_renderer.js",

						"ignore_stations/row_editor.js",
						"ignore_stations/tbar.js",
						"ignore_stations/column_model.js",
						"ignore_stations/grid_panel.js",


						"RestTreeLoader.js",
						"tree/BaseTree.js",
						"tree/TmgTree.js",
						"tree/ReportTree.js",
						"tree/RoutesTree.js",						
						"tree/StationsTree.js",
												
						"report/DateFieldset.js",
						"report/DatesFieldset.js",

						"report/panel/SidebarPanel.js",

						"report/store/BaseProxy.js",

						"table_ruh/table_ruh_on_data.js",
						"table_ruh/tree_events_ruh.js",

						"data_history_map/history_map.js",
						"data_history_map/tree_his.js",

						"karta/maps.js",
						"karta/base_map.js",
						"karta/tree_events_on_line_map.js",

						"tochki_zupynki/zup_data_panel.js",
						"tochki_zupynki/zup_data_scenario_panel.js",
						"tochki_zupynki/map_zup.js",
						"tochki_zupynki/tree_events_zup.js",

						"stations/station_data_panel.js",
						"stations/station_data_scenario_panel.js",
						"stations/map_station.js",
						"stations/map_station_scenario.js",

						"tochki_control/toch_control_data_panel.js",
						"tochki_control/toch_control_scenario_data_panel.js",
						"tochki_control/map_toch_control.js",
						"tochki_control/tree_events_toch_control.js",

                        "n_tz/order_components.js",
                        "n_tz/n_tz_data_panel.js",
						"n_tz/tree_events_n_tz.js",
						"n_tz/status_panel.js",

                        "routes/routes_map_panel/routes_map_function.js",
                        "routes/routes_map_panel/routes_map_draw.js",
						"routes/routes_map_panel/render_routes_map.js",
						"routes/routes_map_panel/all_routes_map.js",
						"routes/routes_map_panel/routes_map_panel.js",

						"routes/routes_grid_panel/routes_directions_panel.js",
						"routes/routes_grid_panel/routes_config_panel.js",
						"routes/routes_grid_panel/routes_grid_panel.js",
						
						"routes/routes_tab_panel.js",

						/*-- Модуль "Пошук маршруту" --*/
						"route_search/main_variables.js",

						"route_search/buttons/get_directions_button.js",
						"route_search/buttons/help_button.js",
						"route_search/buttons/map_button.js",

						"route_search/functions/calc_route.js",
						"route_search/functions/create_end_marker.js",
						"route_search/functions/create_start_marker.js",
						"route_search/functions/delete_route.js",
						"route_search/functions/delete_stations.js",
						"route_search/functions/display_results_panel.js",
						"route_search/functions/display_stations.js",
						"route_search/functions/draw_route.js",
						"route_search/functions/namespace.js",
						"route_search/functions/scalling_map.js",
						"route_search/functions/show_msg.js",

						"route_search/map/route_search_map.js",	

						"route_search/functions.js",
						"route_search/help_window.js",
						"route_search/context_menu.js",		
						"route_search/sidebar_panel.js",
						"route_search/route_toolbar_container.js",
						"route_search/results_route_search_panel.js",

						
						"tz_info/tz_info_data_panel.js",
						"tz_info/tree_events_tz_info.js",

						"report/store/ParkingStore.js",
						"report/store/NoconnectionStore.js",
						"report/store/RaceStore.js",
						"report/store/SpeedStore.js",
						"report/store/SpeedmodeStore.js",
						"report/store/WorktimeStore.js",
						"report/store/VidhylStore.js",
						"report/store/DeviationByMonthStore.js",
						"report/store/DeviationStore.js",
						"report/store/WorktimeByRouteStore.js",
						"report/store/FlightCompletionStore.js",

						"report/grid/ParkingGrid.js",
						"report/grid/NoconnectionGrid.js",
						"report/grid/RaceGrid.js",
						"report/grid/SpeedGrid.js",
						"report/grid/SpeedmodeGrid.js",
						"report/grid/WorktimeGrid.js",
						"report/grid/VidhylGrid.js",
						"report/grid/DeviationByMonthGrid.js",
						"report/grid/DeviationGrid.js",
						"report/grid/WorktimeByRouteGrid.js",
						"report/grid/FlightCompletionGrid.js",

						"report/panel/NoconnectionPanel.js",
						"report/panel/ParkingPanel.js",
						"report/panel/RacePanel.js",
						"report/panel/SpeedPanel.js",
						"report/panel/SpeedmodePanel.js",
						"report/panel/VidhylPanel.js",
						"report/panel/WorktimePanel.js",
						"report/panel/DeviationByMonthPanel.js",
						"report/panel/DeviationPanel.js",
						"report/panel/WorktimeByRoutePanel.js",
						"report/panel/FlightCompletionPanel.js",

						"tmg/RouteStore.js",
						"tmg/route_grid.js",
						"tmg/grafik_grid.js",
						"tmg/RouteHistoryReviewId.js",
						"tmg/RouteReviewTimeCarrier.js",
						"tmg/RouteGraphOrder.js",
						"tmg/ReysOrder.js",
						"tmg/EngineViewTableCarrier.js",
						"tmg/ExtractEngine.js",
						"tmg/Singboard.js",
						"tmg/Comparizon.js",
						"tmg/DrowingStopsChart.js",
						"tmg/ListStops.js",
						"tmg/DistanceBoard.js",
						
						"main/MakGovermentMainMenu.js",
						"main/MakGovermentMainPanel.js",

						"move_report/MoveOnLine.js",
						"move_report/ChartMoveOnLine.js",

						"move_report/StopsMoveOnLine.js",
						"move_report/ChartStopsMoveOnLine.js",
						"move_report/ChartStopsInDateMoveOnLine.js",

						"move_report/ReportPercentageGraphs.js",
						"move_report/ChartReportPercentageGraphs.js",

						"move_report/ReportAverageGraphs.js",
						"move_report/ChartReportAverageGraphs.js",

						"move_report/ReportPercentageFlightsGraphs.js",
						"move_report/ChartReportPercentageFlightsGraphs.js",

						"move_report/ReportOutingGraphs.js",
						"move_report/ReportEndStopsGraphs.js",
						"move_report/OutingOnDoingTime.js",

						"move_report/ReportEndStopsCarriers.js",
						"move_report/ChartReportEndStopsCarriers.js",

						"move_report/DoNotCachStops.js",
						"move_report/ScheduleCachStops.js",

						"move_report/ProblemsMoveOnLineCatalog.js",
						"move_report/ProblemsMoveOnLine.js",
						"move_report/Speed.js",
						"move_report/SpeedDetailsPanel.js",
						"move_report/IntervalsReport.js",
						"move_report/ReportCongressRoute.js",
						"move_report/ReportTransferData.js"
					),
				),




				'state_agencies'=>array(
					'basePath'=>'webroot.js',
					'baseUrl'=>'/js',
					'css'=>array(

					),
					'js'=>array(
						/*-- Бібліотека загальних функцій --*/
						"lib/lib.js",

						"lib/date/date.js",
						"lib/date/hms_to_seconds.js",
						"lib/date/seconds_to_hms.js",
						
						"lib/msg/msg.js",
						"lib/msg/show_error.js",
						"lib/msg/show_warning.js",


						"RestTreeLoader.js",
						"tree/BaseTree.js",

						"table_ruh/table_ruh_on_data.js",
						"table_ruh/tree_events_ruh.js",

						"data_history_map/history_map.js",
						"data_history_map/tree_his.js",

						"karta/maps.js",
						"karta/base_map.js",
						"karta/tree_events_on_line_map.js",

						/*-- Модуль "Пошук маршруту" --*/
						"route_search/main_variables.js",

						"route_search/buttons/get_directions_button.js",
						"route_search/buttons/help_button.js",
						"route_search/buttons/map_button.js",

						"route_search/functions/calc_route.js",
						"route_search/functions/create_end_marker.js",
						"route_search/functions/create_start_marker.js",
						"route_search/functions/delete_route.js",
						"route_search/functions/delete_stations.js",
						"route_search/functions/display_results_panel.js",
						"route_search/functions/display_stations.js",
						"route_search/functions/draw_route.js",
						"route_search/functions/namespace.js",
						"route_search/functions/scalling_map.js",
						"route_search/functions/show_msg.js",

						"route_search/map/route_search_map.js",	

						"route_search/functions.js",
						"route_search/help_window.js",
						"route_search/context_menu.js",		
						"route_search/sidebar_panel.js",
						"route_search/route_toolbar_container.js",
						"route_search/results_route_search_panel.js",
						
						"main/MakStateAgenciesMainMenu.js",
						"main/MakStateAgenciesMainPanel.js"
					),
				),


				'superadmin'=>array(
					'basePath'=>'webroot.js',
					'baseUrl'=>'/js',
					'css'=>array(

					),
					'js'=>array(
						/*-- Бібліотека загальних функцій --*/
						"lib/lib.js",

						"lib/date/date.js",
						"lib/date/hms_to_seconds.js",
						"lib/date/seconds_to_hms.js",
						
						"lib/msg/msg.js",
						"lib/msg/show_error.js",
						"lib/msg/show_warning.js",


						/*-- Інтерфейс для роботи з умовами ігнорування зупинок --*/
						"ignore_stations/ignore_stations.js",

						"ignore_stations/current_record.js",

						"ignore_stations/is_record_active.js",
						"ignore_stations/set_delete_button_enabled.js",

						"ignore_stations/data/data.js",

						"ignore_stations/data/proxy/proxy.js",
						"ignore_stations/data/proxy/ignore_stations_http_proxy.js",

						"ignore_stations/data/reader/reader.js",
						"ignore_stations/data/reader/ignore_stations_json_reader.js",

						"ignore_stations/data/writer/writer.js",
						"ignore_stations/data/writer/ignore_stations_json_writer.js",

						"ignore_stations/data/store/store.js",
						"ignore_stations/data/store/ignore_stations_grouping_store.js",
						"ignore_stations/data/store/stations_json_store.js",
						"ignore_stations/data/store/routes_json_store.js",
						"ignore_stations/data/store/weekdays_array_store.js",

						"ignore_stations/renderer/renderer.js",
						"ignore_stations/renderer/station_renderer.js",
						"ignore_stations/renderer/routes_list_renderer.js",
						"ignore_stations/renderer/weekdays_list_renderer.js",

						"ignore_stations/row_editor.js",
						"ignore_stations/tbar.js",
						"ignore_stations/column_model.js",
						"ignore_stations/grid_panel.js",


						"RestTreeLoader.js",
						"tree/BaseTree.js",
						"tree/TmgTree.js",
						"tree/ReportTree.js",
						"tree/RoutesTree.js",
						"tree/SettingsTree.js",
						"tree/StationsTree.js",
						"tree/NoticeTree.js",
												
						"report/DateFieldset.js",
						"report/DatesFieldset.js",

						"report/panel/SidebarPanel.js",

						"report/store/BaseProxy.js",

						"reklama/rek_data_panel.js",
						"reklama/map_rek.js",
						"reklama/tree_events_rek.js",
						"reklama/rek_copy_panel.js",
						"reklama/col_tree_grid.js",

						"reklama/rek_data_scenario_panel.js",

						"table_ruh/table_ruh_on_data.js",
						"table_ruh/tree_events_ruh.js",

						"data_history_map/history_map.js",
						"data_history_map/tree_his.js",

						"karta/maps.js",
						"karta/base_map.js",
						"karta/tree_events_on_line_map.js",

						"tochki_zupynki/zup_data_panel.js",
						"tochki_zupynki/zup_data_scenario_panel.js",
						"tochki_zupynki/map_zup.js",
						"tochki_zupynki/tree_events_zup.js",

						"stations/station_data_panel.js",
						"stations/station_data_scenario_panel.js",
						"stations/map_station.js",
						"stations/map_station_scenario.js",											

						"tochki_control/toch_control_data_panel.js",
						"tochki_control/toch_control_scenario_data_panel.js",
						"tochki_control/map_toch_control.js",
						"tochki_control/tree_events_toch_control.js",

                        "n_tz/order_components.js",
						"n_tz/n_tz_data_panel.js",
						"n_tz/tree_events_n_tz.js",
						"n_tz/status_panel.js",

                        "routes/routes_map_panel/routes_map_function.js",
                        "routes/routes_map_panel/routes_map_draw.js",
						"routes/routes_map_panel/render_routes_map.js",
						"routes/routes_map_panel/all_routes_map.js",
						"routes/routes_map_panel/routes_map_panel.js",

						"routes/routes_grid_panel/routes_directions_panel.js",
						"routes/routes_grid_panel/routes_config_panel.js",
						"routes/routes_grid_panel/routes_grid_panel.js",

						"routes/routes_tab_panel.js",

						/*-- Модуль "Пошук маршруту" --*/
						"route_search/main_variables.js",

						"route_search/buttons/get_directions_button.js",
						"route_search/buttons/help_button.js",
						"route_search/buttons/map_button.js",

						"route_search/functions/calc_route.js",
						"route_search/functions/create_end_marker.js",
						"route_search/functions/create_start_marker.js",
						"route_search/functions/delete_route.js",
						"route_search/functions/delete_stations.js",
						"route_search/functions/display_results_panel.js",
						"route_search/functions/display_stations.js",
						"route_search/functions/draw_route.js",
						"route_search/functions/namespace.js",
						"route_search/functions/scalling_map.js",
						"route_search/functions/show_msg.js",

						"route_search/map/route_search_map.js",	

						"route_search/functions.js",
						"route_search/help_window.js",
						"route_search/context_menu.js",		
						"route_search/sidebar_panel.js",
						"route_search/route_toolbar_container.js",
						"route_search/results_route_search_panel.js",


						"tz_info/tz_info_data_panel.js",
						"tz_info/tree_events_tz_info.js",

						"settings/settings_tab_panel.js",

						"notice/notice_map.js",	
						"notice/notification/notification_store.js",
						"notice/notification/notification_panel.js",
						"notice/notification_response/notification_response_store.js",
						"notice/notification_response/notification_response_panel.js",
						"notice/notification_history/notification_history_store.js",
						"notice/notification_history/notification_history_panel.js",						
						"notice/notice_tab_panel.js",


						"report/store/ParkingStore.js",
						"report/store/NoconnectionStore.js",
						"report/store/RaceStore.js",
						"report/store/SpeedStore.js",
						"report/store/SpeedmodeStore.js",
						"report/store/WorktimeStore.js",
						"report/store/VidhylStore.js",
						"report/store/DeviationByMonthStore.js",
						"report/store/DeviationStore.js",
						"report/store/WorktimeByRouteStore.js",
						"report/store/FlightCompletionStore.js",

						"report/grid/ParkingGrid.js",
						"report/grid/NoconnectionGrid.js",
						"report/grid/RaceGrid.js",
						"report/grid/SpeedGrid.js",
						"report/grid/SpeedmodeGrid.js",
						"report/grid/WorktimeGrid.js",
						"report/grid/VidhylGrid.js",
						"report/grid/DeviationByMonthGrid.js",
						"report/grid/DeviationGrid.js",
						"report/grid/WorktimeByRouteGrid.js",
						"report/grid/FlightCompletionGrid.js",

						"report/panel/NoconnectionPanel.js",
						"report/panel/ParkingPanel.js",
						"report/panel/RacePanel.js",
						"report/panel/SpeedPanel.js",
						"report/panel/SpeedmodePanel.js",
						"report/panel/VidhylPanel.js",
						"report/panel/WorktimePanel.js",
						"report/panel/DeviationByMonthPanel.js",
						"report/panel/DeviationPanel.js",
						"report/panel/WorktimeByRoutePanel.js",
						"report/panel/FlightCompletionPanel.js",

						"accounts/grid_accounts.js",

						"tmg/RouteStore.js",
						"tmg/route_grid_admin.js",
						"tmg/grafik_grid_admin.js",
						"tmg/RouteHistoryReviewId.js",
						"tmg/RouteReviewTime.js",
						"tmg/RouteGraphOrder.js",
						"tmg/ReysOrder.js",
						"tmg/EngineViewTable.js",
						"tmg/ExtractEngine.js",
						"tmg/Singboard.js",
						"tmg/Comparizon.js",
						"tmg/DrowingStopsChart.js",
						"tmg/ListStops.js",
						"tmg/DistanceBoard.js",
						
						"main/main_menu.js",
						"main/main_panel.js",

						"move_report/MoveOnLine.js",
						"move_report/ChartMoveOnLine.js",
						
						"move_report/StopsMoveOnLine.js",
						"move_report/ChartStopsMoveOnLine.js",
						"move_report/ChartStopsInDateMoveOnLine.js",

						"move_report/ReportPercentageGraphs.js",
						"move_report/ChartReportPercentageGraphs.js",

						"move_report/ReportAverageGraphs.js",
						"move_report/ChartReportAverageGraphs.js",
						
						"move_report/ReportPercentageFlightsGraphs.js",
						"move_report/ChartReportPercentageFlightsGraphs.js",

						"move_report/ReportOutingGraphs.js",
						"move_report/ReportEndStopsGraphs.js",
						"move_report/OutingOnDoingTime.js",

						"move_report/ReportEndStopsCarriers.js",
						"move_report/ChartReportEndStopsCarriers.js",

						"move_report/DoNotCachStops.js",
						"move_report/ScheduleCachStops.js",

						"move_report/ProblemsMoveOnLineCatalog.js",
						"move_report/ProblemsMoveOnLine.js",
						"move_report/Speed.js",
						"move_report/SpeedDetailsPanel.js",
						"move_report/IntervalsReport.js",
						"move_report/ReportCongressRoute.js",
						"move_report/ReportTransferData.js"
					),
				),
				'guest' => array(
					'basePath'=>'webroot.js',
					'baseUrl'=>'/js',
					'css'=>array(

					),
					'js'=>array(
						/*-- Бібліотека загальних функцій --*/
						"lib/lib.js",

						"lib/date/date.js",
						"lib/date/hms_to_seconds.js",
						"lib/date/seconds_to_hms.js",
						
						"lib/msg/msg.js",
						"lib/msg/show_error.js",
						"lib/msg/show_warning.js",


						"RestTreeLoader.js",
						"tree/BaseTree.js",
						"tree/TmgTree.js",
						"tree/FreeScheduleTree.js",						
						"table_ruh/table_ruh_on_data.js",
						"table_ruh/tree_events_ruh.js",

						"karta/maps.js",
						"karta/base_map.js",
						"karta/tree_events_on_line_map.js",

						"main/free_menu.js",
						"main/free_panel.js",

						/*-- Модуль "Пошук маршруту" --*/
						"route_search/main_variables.js",

						"route_search/buttons/get_directions_button.js",
						"route_search/buttons/help_button.js",
						"route_search/buttons/map_button.js",

						"route_search/functions/calc_route.js",
						"route_search/functions/create_end_marker.js",
						"route_search/functions/create_start_marker.js",
						"route_search/functions/delete_route.js",
						"route_search/functions/delete_stations.js",
						"route_search/functions/display_results_panel.js",
						"route_search/functions/display_stations.js",
						"route_search/functions/draw_route.js",
						"route_search/functions/namespace.js",
						"route_search/functions/scalling_map.js",
						"route_search/functions/show_msg.js",

						"route_search/map/route_search_map.js",	

						"route_search/functions.js",
						"route_search/help_window.js",
						"route_search/context_menu.js",		
						"route_search/sidebar_panel.js",
						"route_search/route_toolbar_container.js",
						"route_search/results_route_search_panel.js",


						"tmg/free_grafik_grid.js"				
					)
				),
			),
		),
	),
);