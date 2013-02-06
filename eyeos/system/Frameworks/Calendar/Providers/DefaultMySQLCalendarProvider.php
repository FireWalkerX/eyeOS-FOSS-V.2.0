<?php
class DefaultMySQLCalendarProvider implements ICalendarProvider {
	const CALENDAR_TABLE_NAME = 'calendar';
	const CALENDARPREFS_TABLE_NAME = 'calendarprefs';
	const EVENT_TABLE_NAME = 'calendarevent';
	
	/**
	 * @var PDO
	 */
	private static $Connection = null;
	
	private static $Instance = null;
	
	protected function __construct() {}
	
	protected static function convertResultsToCalendarObjects(array $results) {
		$return = array();
		foreach($results as $result) {
			$obj = new Calendar();
			$obj->setId($result[CalendarManager::CALENDAR_KEY_ID]);
			$obj->setName($result[CalendarManager::CALENDAR_KEY_NAME]);
			$obj->setDescription($result[CalendarManager::CALENDAR_KEY_DESCRIPTION]);
			$obj->setTimezone($result[CalendarManager::CALENDAR_KEY_TIMEZONE]);
			$obj->setOwnerId($result[CalendarManager::CALENDAR_KEY_OWNERID]);
			$return[] = $obj;
		}
		return $return;
	}
	
	protected static function convertResultsToCalendarPrefsObjects(array $results) {
		$return = array();
		foreach($results as $result) {
			$obj = new CalendarPrefs();
			$obj->setId($result[CalendarManager::CALENDARPREFS_KEY_ID]);
			$obj->setUserId($result[CalendarManager::CALENDARPREFS_KEY_USERID]);
			$obj->setCalendarId($result[CalendarManager::CALENDARPREFS_KEY_CALENDARID]);
			$obj->setColor($result[CalendarManager::CALENDARPREFS_KEY_COLOR]);
			$obj->setNotifications($result[CalendarManager::CALENDARPREFS_KEY_NOTIFICATIONS]);
			$obj->setVisible($result[CalendarManager::CALENDARPREFS_KEY_VISIBLE]);
			$return[] = $obj;
		}
		return $return;
	}
	
	protected static function convertResultsToEventObjects(array $results) {
		$return = array();
		foreach($results as $result) {
			$obj = new CalendarEvent();
			$obj->setId($result[CalendarManager::EVENT_KEY_ID]);
			$obj->setSubject($result[CalendarManager::EVENT_KEY_SUBJECT]);
			$obj->setLocation($result[CalendarManager::EVENT_KEY_LOCATION]);
			$obj->setDescription($result[CalendarManager::EVENT_KEY_DESCRIPTION]);
			$obj->setIsAllDay($result[CalendarManager::EVENT_KEY_ISALLDAY]? true : false);
			$obj->setTimeStart($result[CalendarManager::EVENT_KEY_TIMESTART]);
			$obj->setTimeEnd($result[CalendarManager::EVENT_KEY_TIMEEND]);
			$obj->setType($result[CalendarManager::EVENT_KEY_TYPE]);
			$obj->setPrivacy($result[CalendarManager::EVENT_KEY_PRIVACY]);
			$obj->setRepetition($result[CalendarManager::EVENT_KEY_REPETITION]);
			$obj->setCreatorId($result[CalendarManager::EVENT_KEY_CREATORID]);
			$obj->setCalendarId($result[CalendarManager::EVENT_KEY_CALENDARID]);
			$return[] = $obj;
		}
		return $return;
	}
	
	public function createCalendar(ICalendar $calendar) {
		try {
			$dbHandler = $this->getConnection();
			
			$data = array(
				CalendarManager::CALENDAR_KEY_ID => $calendar->getId(),
				CalendarManager::CALENDAR_KEY_NAME => $calendar->getName(),
				CalendarManager::CALENDAR_KEY_DESCRIPTION => $calendar->getDescription(),
				CalendarManager::CALENDAR_KEY_TIMEZONE => $calendar->getTimezone(),
				CalendarManager::CALENDAR_KEY_OWNERID => $calendar->getOwnerId()
			);
			
			$sqlQuery = 'INSERT INTO ' . self::CALENDAR_TABLE_NAME . ' VALUES ('
				. ' :' . CalendarManager::CALENDAR_KEY_ID . ','
				. ' :' . CalendarManager::CALENDAR_KEY_NAME . ','
				. ' :' . CalendarManager::CALENDAR_KEY_DESCRIPTION . ','
				. ' :' . CalendarManager::CALENDAR_KEY_TIMEZONE . ','
				. ' :' . CalendarManager::CALENDAR_KEY_OWNERID . ')';
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($data);
		} catch (PDOException $e) {
			$this->destroyConnection();
			throw new EyeDBException('An error occured while attempting to store calendar data.', 0, $e);
		}
	}
	
	public function createCalendarPreferences(ICalendarPrefs $calendarPrefs) {
		$dao = StorageManager::getInstance()->getHandler('SQL/EyeosDAO');
		$dao->create($calendarPrefs);
		unset($dao);
	}
	
	public function createEvent(ICalendarEvent $event) {
		try {
			$dbHandler = $this->getConnection();
			
			$data = array(
				CalendarManager::EVENT_KEY_ID => $event->getId(),
				CalendarManager::EVENT_KEY_SUBJECT => $event->getSubject(),
				CalendarManager::EVENT_KEY_LOCATION => $event->getLocation(),
				CalendarManager::EVENT_KEY_DESCRIPTION => $event->getDescription(),
				CalendarManager::EVENT_KEY_ISALLDAY => $event->getIsAllDay(),
				CalendarManager::EVENT_KEY_TIMESTART => $event->getTimeStart(),
				CalendarManager::EVENT_KEY_TIMEEND => $event->getTimeEnd(),
				CalendarManager::EVENT_KEY_TYPE => $event->getType(),
				CalendarManager::EVENT_KEY_PRIVACY => $event->getPrivacy(),
				CalendarManager::EVENT_KEY_REPETITION => $event->getRepetition(),
				CalendarManager::EVENT_KEY_CREATORID => $event->getCreatorId(),
				CalendarManager::EVENT_KEY_CALENDARID => $event->getCalendarId()
			);
			
			$sqlQuery = 'INSERT INTO ' . self::EVENT_TABLE_NAME . ' VALUES ('
				. ' :' . CalendarManager::EVENT_KEY_ID . ','
				. ' :' . CalendarManager::EVENT_KEY_SUBJECT . ','
				. ' :' . CalendarManager::EVENT_KEY_LOCATION . ','
				. ' :' . CalendarManager::EVENT_KEY_DESCRIPTION . ','
				. ' :' . CalendarManager::EVENT_KEY_ISALLDAY . ','
				. ' :' . CalendarManager::EVENT_KEY_TIMESTART . ','
				. ' :' . CalendarManager::EVENT_KEY_TIMEEND . ','
				. ' :' . CalendarManager::EVENT_KEY_TYPE . ','
				. ' :' . CalendarManager::EVENT_KEY_PRIVACY . ','
				. ' :' . CalendarManager::EVENT_KEY_REPETITION . ','
				. ' :' . CalendarManager::EVENT_KEY_CREATORID . ','
				. ' :' . CalendarManager::EVENT_KEY_CALENDARID . ')';
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($data);
		} catch (PDOException $e) {
			$this->destroyConnection();
			throw new EyeDBException('An error occured while attempting to store event data.', 0, $e);
		}
	}
	
	public function deleteCalendar(ICalendar $calendar) {
		try {
			$dbHandler = $this->getConnection();
			
			// delete all events from given calendar
			$data = array(
				CalendarManager::EVENT_KEY_CALENDARID => $calendar->getId()
			);
			$sqlQuery = 'DELETE FROM ' . self::EVENT_TABLE_NAME
				.' WHERE ' . CalendarManager::EVENT_KEY_CALENDARID . ' = :' . CalendarManager::EVENT_KEY_CALENDARID;
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($data);
			
			// then delete calendar it self
			$data = array(
				CalendarManager::EVENT_KEY_ID => $event->getId()
			);
			$sqlQuery = 'DELETE FROM ' . self::CALENDAR_TABLE_NAME
				.' WHERE ' . CalendarManager::CALENDAR_KEY_ID . ' = :' . CalendarManager::CALENDAR_KEY_ID;
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($data);
		} catch (PDOException $e) {
			$this->destroyConnection();
			throw new EyeDBException('An error occured while deleting event from base.', 0, $e);
		}
		$this->destroyConnection();
	}
	
	public function deleteEvent(ICalendarEvent $event) {
		try {
			$dbHandler = $this->getConnection();
			
			$data = array(
				CalendarManager::EVENT_KEY_ID => $event->getId()
			);
			$sqlQuery = 'DELETE FROM ' . self::EVENT_TABLE_NAME
				.' WHERE ' . CalendarManager::EVENT_KEY_ID . ' = :' . CalendarManager::EVENT_KEY_ID;
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($data);
		} catch (PDOException $e) {
			$this->destroyConnection();
			throw new EyeDBException('An error occured while deleting event from base.', 0, $e);
		}
		$this->destroyConnection();
	}
	
	private function destroyConnection() {
		if (self::$Connection instanceof PDO) {
			self::$Connection = null;
		}
	}
	
	private function getConnection() {
		if (! isset(self::$Connection)) {
			$dbHandler = null;
			try {
				$dbHandler = new PDO(SQL_CONNECTIONSTRING, SQL_USERNAME, SQL_PASSWORD);
				$dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				throw new EyeDBException('An error occured while getting connection to the database.', 0, $e);
			}
			self::$Connection = $dbHandler;
		}
		return self::$Connection;
	}
	
	public static function getInstance() {
		if (self::$Instance === null) {
			$thisClass = __CLASS__;
			self::$Instance = new $thisClass;
		}
		return self::$Instance;
	}
	
	protected function retrieveAllCalendarsWithFilter(array $filter = null) {
		try {
			if ($filter === null) {
				$filter = array();
			}
			$dbHandler = $this->getConnection();
			
			$sqlQuery = 'SELECT * FROM ' . self::CALENDAR_TABLE_NAME . ' WHERE 1';
			foreach($filter as $key => $value) {
				$sqlQuery .= ' AND ' . $key . ' = :' . $key;
			}
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($filter);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			$this->destroyConnection();
			throw new EyeDBException('An error occured while retrieving data from base.', 0, $e);
		}
	}
	
	public function retrieveAllCalendarsFromOwner(IPrincipal $principal) {
		return self::convertResultsToCalendarObjects($this->retrieveAllCalendarsWithFilter(array(
			CalendarManager::CALENDAR_KEY_OWNERID => $principal->getId()
		)));
	}
	
	public function retrieveAllEventsFromCalendar(ICalendar $calendar, $timeFrom = null, $timeTo = null) {
		$filter = array(
			CalendarManager::EVENT_KEY_CALENDARID => $calendar->getId()
		);
		if ($timeFrom !== null) {
			$filter[CalendarManager::EVENT_KEY_TIMESTART] = array('>', $timeFrom);
		}
		if ($timeTo !== null) {
			$filter[CalendarManager::EVENT_KEY_TIMEEND] = array('<', $timeTo);
		}
		return self::convertResultsToEventObjects($this->retrieveAllEventsWithFilter($filter));
	}
	
	protected function retrieveAllEventsWithFilter(array $filter = null) {
		try {
			if ($filter === null) {
				$filter = array();
			}
			$dbHandler = $this->getConnection();
			
			$sqlFilter = array();
			
			$sqlQuery = 'SELECT * FROM ' . self::EVENT_TABLE_NAME . ' WHERE 1';
			foreach($filter as $key => $filterData) {
				// complex filter (=> more than a simple "=")
				if (is_array($filterData)) {
					if (!is_string($filterData[0])) {
						throw new EyeInvalidArgumentException('$filterData[0] must be string representing a SQL operator (LIKE, >, <, <=, ...).');
					}
					$operator = $filterData[0];
					$value = $filterData[1];
				} else {
					$operator = '=';
					$value = $filterData;
				}
				
				$sqlQuery .= ' AND ' . $key . ' ' . $operator . ' :' . $key;
				$sqlFilter[$key] = $value;
			}
			
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($filter);
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			$this->destroyConnection();
			throw new EyeDBException('An error occured while retrieving data from base.', 0, $e);
		}
		$this->destroyConnection();
	}
	
	public function retrieveCalendarById($calendarId) {
		if (!is_string($calendarId) || $calendarId == '') {
			throw new EyeInvalidArgumentException('$calendarId must be a non-empty string.');
		}
		
		$cal = $this->retrieveAllCalendarsWithFilter(array(
			CalendarManager::CALENDAR_KEY_ID => $calendarId
		));
		$this->destroyConnection();
		if (count($cal) === 0) {
			throw new EyeCalendarNotFoundException('Unknown calendar with ID "' . $calendarId . '".');
		}
		return current(self::convertResultsToCalendarObjects($cal));
	}
	
	public function retrieveCalendarPreferences($userId, $calendarId) {
		if (!is_string($userId) || $userId == '') {
			throw new EyeInvalidArgumentException('$userId must be a non-empty string.');
		}
		if (!is_string($calendarId) || $calendarId == '') {
			throw new EyeInvalidArgumentException('$calendarId must be a non-empty string.');
		}
		
		$dao = StorageManager::getInstance()->getHandler('SQL/EyeosDAO');
		
		$calendarPrefs = new CalendarPrefs();
		$calendarPrefs->setUserId($userId);
		$calendarPrefs->setCalendarId($calendarId);
		
		$calendarPrefs = $dao->search($calendarPrefs);
		unset($dao);
		if (count($calendarPrefs) === 0) {
			throw new EyeCalendarPrefsNotFoundException('Unknown calendar preferences with user ID "' . $userId . '" and calendar ID "' . $calendarId . '".');
		}
		return current($calendarPrefs);
	}
	
	public function retrieveEventById($eventId) {
		if (!is_string($eventId) || $eventId == '') {
			throw new EyeInvalidArgumentException('$eventId must be a non-empty string.');
		}
		
		$events = $this->retrieveAllEventsWithFilter(array(
			CalendarManager::EVENT_KEY_ID => $eventId
		));
		$this->destroyConnection();
		if (count($events) === 0) {
			throw new EyeEventNotFoundException('Unknown event with ID "' . $eventId . '"');
		}
		return current(self::convertResultsToEventObjects($events));
	}
	
	public function search($string, ICalendar $calendar) {
		throw new EyeNotImplementedException(__METHOD__);
	}
	
	public function updateCalendar(ICalendar $calendar) {
		try {
			$dbHandler = $this->getConnection();
			
			$data = array(
				CalendarManager::CALENDAR_KEY_ID => $calendar->getId(),
				CalendarManager::CALENDAR_KEY_NAME => $calendar->getName(),
				CalendarManager::CALENDAR_KEY_DESCRIPTION => $calendar->getDescription(),
				CalendarManager::CALENDAR_KEY_TIMEZONE => $calendar->getTimezone(),
				CalendarManager::CALENDAR_KEY_OWNERID => $calendar->getOwnerId()
			);
			
			$sqlQuery = 'UPDATE ' . self::CALENDAR_TABLE_NAME . ' SET ';
			foreach($data as $key => $value) {
				if ($key != CalendarManager::CALENDAR_KEY_ID) {
					$sqlQuery .= $key . ' = :' . $key . ', ';
				}
			}
			$sqlQuery = substr($sqlQuery, 0, -2);
			$sqlQuery .= ' WHERE ' . CalendarManager::CALENDAR_KEY_ID . ' = :' . CalendarManager::CALENDAR_KEY_ID;
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($data);			
		} catch (Exception $e) {
			$this->destroyConnection();
			throw new EyeDBException('An error occured while attempting to update calendar with ID "' . $calendar->getId() . '".', 0, $e);
		}
		$this->destroyConnection();
	}
	
	public function updateCalendarPreferences(ICalendarPrefs $calendarPrefs) {
		$dao = StorageManager::getInstance()->getHandler('SQL/EyeosDAO');
		$calendarPrefs = $dao->update($calendarPrefs);
		unset($dao);
	}
	
	public function updateEvent(ICalendarEvent $event) {
		try {
			$dbHandler = $this->getConnection();
			
			$data = array(
				CalendarManager::EVENT_KEY_ID => $event->getId(),
				CalendarManager::EVENT_KEY_SUBJECT => $event->getSubject(),
				CalendarManager::EVENT_KEY_LOCATION => $event->getLocation(),
				CalendarManager::EVENT_KEY_DESCRIPTION => $event->getDescription(),
				CalendarManager::EVENT_KEY_ISALLDAY => $event->getIsAllDay(),
				CalendarManager::EVENT_KEY_TIMESTART => $event->getTimeStart(),
				CalendarManager::EVENT_KEY_TIMEEND => $event->getTimeEnd(),
				CalendarManager::EVENT_KEY_TYPE => $event->getType(),
				CalendarManager::EVENT_KEY_PRIVACY => $event->getPrivacy(),
				CalendarManager::EVENT_KEY_REPETITION => $event->getRepetition(),
				CalendarManager::EVENT_KEY_CREATORID => $event->getCreatorId(),
				CalendarManager::EVENT_KEY_CALENDARID => $event->getCalendarId()
			);
			
			$sqlQuery = 'UPDATE ' . self::EVENT_TABLE_NAME . ' SET ';
			foreach($data as $key => $value) {
				if ($key != CalendarManager::EVENT_KEY_ID) {
					$sqlQuery .= $key . ' = :' . $key . ', ';
				}
			}
			$sqlQuery = substr($sqlQuery, 0, -2);
			$sqlQuery .= ' WHERE ' . CalendarManager::EVENT_KEY_ID . ' = :' . CalendarManager::EVENT_KEY_ID;
			$stmt = $dbHandler->prepare($sqlQuery);
			$stmt->execute($data);
		} catch (Exception $e) {
			$this->destroyConnection();
			throw new EyeDBException('An error occured while attempting to update event with ID "' . $event->getId() . '".', 0, $e);
		}
		$this->destroyConnection();
	}
}
?>
