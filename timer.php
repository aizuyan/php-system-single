<?php
class Timer
{
	/**
	 * @protected
	 * @var array
	 * _tasks 定时任务列表
	 */
	protected static $_tasks = [];

	public static function init()
	{
		pcntl_signal(SIGALRM, ['Timer', 'signalHandle'], false);
	}

	protected static function signalHandle()
	{
		self::tick();
		pcntl_alarm(1);
	}

	protected static function tick()
	{
		//如果任务数组为空的话，取消设置的下一个闹钟信号，免得空执行
		if(empty(self::$_tasks))
		{
			pcntl_alarm(0);
			return false;
		}

		//执行时间到了的任务
		$time_now = time();
		foreach(self::$_tasks as $time_runtime => $tasks)
		{
			if($time_now >= $time_runtime)
			{
				foreach($tasks as $index => $task)
				{
					list($time_interval, $func, $args, $persistent) = $task;
					call_user_func_array($func, $args);

					if($persistent)
					{
						self::add($time_interval, $func, $args, $persistent);
					}
				}
				unset(self::$_tasks[$time_runtime]);
			}
		}
	}

	public static function add($time_interval, $func, $args = [], $persistent = false)
	{
		if($time_interval <= 0)
		{
			echo "time interval must be great than zero!\n";
			return false;
		}
		if(!is_callable($func))
		{
			echo "func must be callable!\n";
			return false;
		}
		if(empty(self::$_tasks))
		{
			pcntl_alarm(1);
		}
		$time_now = time();
		$time_runtime = $time_now + $time_interval;
		if(!isset(self::$_tasks[$time_runtime]))
			self::$_tasks[$time_runtime] = [];
		self::$_tasks[$time_runtime][] = [$time_interval, $func, $args, $persistent];
		return true;
	}

	public static function run()
	{
		while(1)
		{
			sleep(1);
			pcntl_signal_dispatch();
		}
	}
}

Timer::init();
Timer::add(5, function(){
	sleep(10);
	echo "FUNC ".time(true)."\n";
}, [], true);
Timer::run();
