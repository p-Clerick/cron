<?php
	
	/**
	 * Клас для роботи з часом.
	 * Дозволяє порівнювати та додавати час
	 * 
	 */
	class Time {

		public static $separator = ":";
		
		/**
		 * Час в секундах
		 *
		 *
		 */
		private $timeInSeconds;

		/**
		 * Кількість годин
		 * 
		 * @var integer 
		 */
		private $hours;

		/**
		 * Кількість хвилин
		 *
		 * @var integer 
		 */
		private $minutes;


		/**
		 * Кількість секунд
		 *
		 * @var integer
		 */
		private $seconds;

		/**
		 * Кількість днів
		 * @var integer
		 */
		private $days = 0;

		/**
		 *
		 * @param integer $t Час в секундах
		 */
		public function __construct($t = 0){
			$this->setTimeInSeconds($t);
			$this->setHMS($t);
		}

		public function getTimeInSeconds(){
			return $this->timeInSeconds;
		}

		public function getHours(){
			return $this->hours;
		}

		public function getMinutes(){
			return $this->minutes;
		}

		public function getSeconds(){
			return $this->seconds;
		}

		public function getDays(){
			return $this->days;
		}

		/**
		 * Повертає новий об'кт Time із заданим у $timeString часом
		 * @param string $timeString Час у форматі HH:MM:SS
		 * @param string $separator
		 * @return Time Новий час
		 */
		public static function factory($timeString, $separator = false){
			$time = new Time();
			$time->setFormattedTime($timeString, $separator);
			return $time;
		}

		/**
		 * Встановлює час $time
		 * @param string $time Час у форматі HH:MM:SS
		 * @return Time Поточний об'єкт часу для використання в ланцюжку викликів
		 */
		public function setFormattedTime($time, $separator = false){
			if(!$separator){
				$separator = self::$separator;
			}
			$time = explode($separator, $time);
			//print_r($time);
			$this	->setHours($time[0])
		    		->setMinutes($time[1])
		    		->setSeconds($time[2]);

		    $this->calculateSec();
		    return $this;
		}

		/**
		 * Повертає відформатований у стрічку час
		 * @param string $separator Роздільник між годинами, хвилинами та секундами
		 * @return string Відформатований час
		 */
		public function getFormattedTime($separator = false){
			if(!$separator){
				$separator = self::$separator;
			}
			return  str_pad($this->getHours(),2,"0",STR_PAD_LEFT).$separator
					.str_pad($this->getMinutes(),2,"0",STR_PAD_LEFT).$separator
					.str_pad($this->getSeconds(),2,"0",STR_PAD_LEFT);
		}

		public function setTimeInSeconds($time){
			$this->timeInSeconds = $time;
			$this->setHMS($time);
			return $this;
		}

		public function calculateSec(){
			$this->timeInSeconds = $this->getHours()*60*60 + $this->getMinutes()*60
					+ $this->getSeconds() + $this->getDays()*60*60*24;
			return $this;
		}

		public function setHMS($sec){
			if($sec >= 60*60*24){
				$sec -= 60*60*24;
				$this->setDays(1);
			}
			$hh = floor($sec/3600);
		    $temp = $sec - $hh*3600;
		    $mm = floor($temp/60);
		    $ss = $temp - $mm*60;
		    $this	->setHours($hh)
		    		->setMinutes($mm)
		    		->setSeconds($ss);
		    $this->calculateSec();
		    return $this;			
		}

		public function setHours($h){
			$this->hours = intval($h);
			//print_r($h, "\n");
			$this->calculateSec();
			return $this;
		}

		public function setMinutes($m){
			$this->minutes = intval($m);
			$this->calculateSec();
			return $this;
		}

		public function setSeconds($s){
			$this->seconds = intval($s);
			$this->calculateSec();
			return $this;
		}

		public function setDays($d){
			$this->days = $d;
			$this->calculateSec();
			return $this;
		}

		public static function setSeparator($s){
			self::$separator = $s;
		}


		/**
		 * Порівняння двох часів $time1 та $time2
		 *
		 * @param Time $time1
		 * @param Time $time1
		 * @return integer 
		 * 		1 - якщо $time1 більший за $time2
		 * 		0 - якщо $time1 менший за $time2
		 * 		-1 - якщо вони рівні
 		 */
		public static function compare($time1, $time2){
			if($time1->getTimeInSeconds() > $time2->getTimeInSeconds()){
				return 1;
			} else if($time1->getTimeInSeconds() < $time2->getTimeInSeconds()){
				return 0;
			} else {
				return -1;
			}
		}

		/**
		 * Додавання двох часів
		 * @param Time $time1
		 * @param Time $time1
		 * @return Time Час, що дорівнює сумі $time1 і $time2
		 */
		public static function add($time1, $time2){
			$s = $time1->getTimeInSeconds() + $time2->getTimeInSeconds();
			$time = new Time($s);
			return $time;
		}

		/**
		 * Різниця двох часів, по модулю!
		 * @param Time $time1
		 * @param Time $time1
		 * @return Time Різниця $time1 - $time2
		 */
		public static function sub($time1, $time2){
			$s = $time1->getTimeInSeconds() - $time2->getTimeInSeconds();
			$time = new Time(abs($s));
			return $time;
		}

		/**
		 * Аліас для getFormattedTime()
		 */
		public function format () {
			return $this->getFormattedTime();
		}
	}
?>