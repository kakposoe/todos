<?php namespace Todo;

use League\CLimate\CLimate;

class Todo 
{

	protected $todos;

	public function __construct() {
	}

	public static function init() {

	}

	protected static function getTodos() {
		return json_decode(file_get_contents('./todo.json'), true);
	} 

	protected static function save($todos, $show = false) {
		file_put_contents('./todo.json', json_encode($todos));
		if ($show) self::all();
	} 

	public static function find($i) {
		$c = new CLImate;
		$todos = self::getTodos();
		$output = '';

		if ((int) $i != $i) {

			$offset = floor($i) - 1;
			$index = intval(substr($i - $offset, 2) - 1);
			$todos[$offset]['subtasks'][$index]['status'] ? $output .= '<green>✓</green> ' : $output .= '- ';
			$output .= $i . '. ';
			$todos[intval($i) - 1]['status'] ? $output .= '<dim>' . $todos[$offset]['subtasks'][$index]['task'] . '</dim>' : $output .= $todos[intval($i) - 1]['task']; 
			$c->out($output);

		} else {

			$todos[intval($i) - 1]['status'] ? $output .= '<green>✓</green> ' : $output .= '- ';
			$output .= $i . '. ';
			$todos[intval($i) - 1]['status'] ? $output .= '<dim>' . $todos[intval($i) - 1]['task'] . '</dim>' : $output .= $todos[intval($i) - 1]['task']; 
			$c->out($output);

			if (isset($todos[intval($i) - 1]['subtasks'])) {
				$output = '';
				foreach ($todos[intval($i) - 1]['subtasks'] as $key => $sub) {
					$sub['status'] ? $output .= '<green>✓</green> ' : $output .= '- ';
					$output .= ($key + 1) . '. ';
					$sub['status'] ? $output .= '<dim>' . $sub['task'] . '</dim>' : $output .= $sub['task']; 
					$c->tab()->out($output);
					$output = '';
				}
			}
		}

	}

	public static function all() {

		$c = new CLImate;
		$todos = self::getTodos();

		$c->br()->black()->backgroundCyan()->out(' To Do List! ')->br();

		if ($todos) {
			$count = 1;
			foreach ($todos as $todo) {


				if (isset($todo['subtasks']) && count($todo['subtasks']) > 0) {
					$completeCount = 0;
					foreach ($todo['subtasks'] as $sub) {
						if ($sub['status'] == true) $completeCount++;
					}
				}

				$todo['status'] ? $status = '<green>✓</green>' : $status = '-';
				$output = $status . ' ' . $count . '. ';
				$todo['status'] ? $output .= '<dim>' . $todo['task'] . '</dim>' : $output .= $todo['task']; 

				if (isset($todo['subtasks']) && count($todo['subtasks']) > 0) {
					$output .= ' [' . $completeCount . '/' . count($todo['subtasks']) . ']';
				}

				$c->out($output);

				if (isset($todo['subtasks'])) {
					$subCount = 1;
					foreach ($todo['subtasks'] as $sub) {
						$sub['status'] == true ? $status = '<green>✓</green>' : $status = '-';

						// Output
						$subout = $status . ' ' . $count . '.' . $subCount . '. ';
						$sub['status'] ? $subout .= '<dim>' . $sub['task'] . '</dim>' : $subout .= $sub['task']; 

						$c->tab()->out($subout);
						$subCount++;
					}
				}

				$count++;
			}
		} else {
			$c->green(' ✓ All Tasks Completed. ');
			$c->out(' Why not add a few tasks... ');
		}
	}

	public static function add($task, $subtask = null) {

		$todos = self::getTodos();

		$data = [
			'status' => false,
			'task' => $task,
		];

		if($subtask) {
			$index = intval($subtask); 
			$index--;
			$todos[$index]['subtasks'][] = $data;
		} else {
			$todos[] = $data;
		}

		self::save($todos);
	} 

	public static function addMany($tasks) {

		$c = new CLImate;
		array_shift($tasks);

		$first = false;
		$lister = [];

		foreach ($tasks as $key => $value) {
			if ($value !== 'and') {
				self::add($value);
				$lister[] = $value;
			}

			if(!$first) {
				$todos = self::getTodos();
				end($todos);
				$ref = key($todos) + 1;
				$first = true;
			}
		}

		$msg = ' ✓ ' . count($lister) . ' New Tasks Added ';

		$c->br()->black()->backgroundGreen()->out($msg)->br();

		foreach ($lister as $task) {
			$c->out('- ' . $ref . '. ' .  $task);
			$ref++;
		}

	} 

	public static function edit($i, $task) {

		$c = new CLImate;
		$todos = self::getTodos();

		if (is_numeric($i)) {

			if ((int) $i != $i) {
				$offset = floor($i) - 1;
				$index = intval(substr($i - $offset, 2) - 1);
				$todos[$offset]['subtasks'][$index]['task'] = $task;
			} else {
				$todos[intval($i) - 1]['task'] = $task;
			}

			self::save($todos);
			$c->black()->backgroundGreen()->out(' ✓ Task Updated ')->br();

		} else {

			$c->black()->backgroundRed(' No tasks index declared ');

		}

	}

	public static function completed($i = null) {

		$todos = self::getTodos();
		$c = new CLImate;

		if ((int) $i != $i ) {
			$offset = floor($i) - 1;
			$index = intval(substr($i - $offset, 2) - 1);
			$todos[$offset]['subtasks'][$index]['status'] = true;
			$task = $todos[$offset]['subtasks'][$index]['task'];
		} else {

			// Check if has children
			if(array_key_exists('subtasks', $todos[$i - 1])) {
				$input = $c->black()->backgroundRed()->confirm(' There are sub tasks, mark all as completed? ');
				if ($input->confirmed()) {
					foreach ($todos[$i - 1]['subtasks'] as &$sub) {
						$sub['status'] = true;
					}
				} 
			}
			
			$todos[intval($i) - 1]['status'] = true;
			$task = $todos[intval($i) - 1]['task'];
		}

		self::save($todos);
		$c->black()->backgroundGreen()->out(' Task marked as complete ')->br();
		$c->out(' <green>✓</green> ' . $i . '. ' . $task);

	}

	public static function remove($i = null) {

		$c = new CLImate;
		$todos = self::getTodos();

		if($i) {
			if ((int) $i != $i ) {
				$offset = floor($i) - 1;
				$index = substr($i - $offset, 2) - 1;
				$input = $c->black()->backgroundRed()->confirm(' Confirm to Delete ');
				if ($input->confirmed()) {
					array_splice($todos[$offset]['subtasks'], $index, 1);
				}

			} else {
				array_splice($todos, intval($i) - 1, 1);
			}
		} else {
			$select = [];
			foreach($todos as $key => $todo) {
				$select[] = ($key + 1) . '. ' . $todo['task'];
			}
			$input = $c->radio('Select a task to delete', $select);
			$response = $input->prompt();
			array_splice($todos, intval(substr($response, 1)), 1);
		}

		$c = new CLImate;
		$c->br()->green('Task removed!')->br();
		self::save($todos, true);
	} 

}