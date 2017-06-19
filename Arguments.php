<?php 
	return [
		'new' => [
			'prefix'       => 'n',
			'longPrefix'   => 'new',
			'description'  => 'Add a top level task',
		],

		'subtask' => [
			'prefix'       => 's',
			'longPrefix'   => 'subtask',
			'description'  => 'Add new tasks as subtask to parent tasks',
		],

		'done' => [
			'prefix'      => 'd',
			'longPrefix'  => 'done',
			'description' => 'Mark a task as done. Reference a task Number',
			'castTo'      => 'float',
		],
		'undo' => [
			'prefix'      => 'u',
			'longPrefix'  => 'undo',
			'description' => 'Revert a complete tasks back to uncompleted status',
		],
		'remove' => [
			'prefix'      => 'r',
			'longPrefix'  => 'remove',
			'description' => 'Remove a task for your todo list',
		],
		'flush' => [
			'longPrefix'  => 'delete-all',
			'description' => 'Delete All Entries',
			'noValue'     => true,
		],
		'help' => [
			'longPrefix'  => 'help',
			'description' => 'Prints a usage statement',
			'noValue'     => true,
		],
	];
