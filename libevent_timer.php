<?php
class Timer
{
	/**
	 * @var resource
	 *
	 * _eventBase
	 */
	protected static $_eventBase = null;

	protected static $_tasks = [];

	public static function init()
	{
		if(self::$_eventBase == null)
		{
			self::$_eventBase = event_base_new();
		}
	}

	protected static function timerCallback($fd, $events, $timer_id)
	{
		call_user_func_array(self::$_tasks[$timer_id][1], self::$_tasks[$timer_id][2]);
		if(self::$_tasks[$timer_id][3])
		{
			event_add(self::$_tasks[$timer_id][4], self::$_tasks[$timer_id][0]);
		}
		else
		{
			self::del($timer_id);
		}
	}

	protected static function del($timer_id)
	{
		if(isset(self::$_tasks[$timer_id]))
		{
			event_del(self::$_tasks[$timer_id][4]);
			unset(self::$_tasks[$timer_id]);
		}
	}

	public static function add($time_interval, $func, $args = [], $persistent = false)
	{
		$event = event_new();
		$timer_id = (int)$event;
		event_set($event, 0, EV_TIMEOUT, ['Timer', 'timerCallback'], $timer_id);
		event_base_set($event, self::$_eventBase);
		$time_interval = 1000000 * $time_interval;
		event_add($event, $time_interval);

		self::$_tasks[$timer_id] = [$time_interval, $func, $args, $persistent, $event];
	}

	public static function run()
	{
		event_base_loop(self::$_eventBase);
	}

}


Timer::init();
Timer::add(2, function(){
	echo "This is a Libevent timer!\n";
}, [], true);
Timer::add(1, function(){
	echo "Every Seconds!\n";
}, [], true);
Timer::add(2, function(){
	echo "two!\n";
});
Timer::run();
