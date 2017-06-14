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

	protected static function save($todos) {
		file_put_contents('./todo.json', json_encode($todos));
	} 

	public static function all() {

		$c = new CLImate;
		$todos = self::getTodos();

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
			$c->backgroundRed(' Oops, There are no tasks ');
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

	public static function completed($i = null) {

		$todos = self::getTodos();
		$c = new CLImate;

		if ((int) $i != $i ) {
			$offset = floor($i) - 1;
			$index = intval(substr($i - $offset, 2) - 1);
			$todos[$offset]['subtasks'][$index]['status'] = true;

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
		}

		self::save($todos);
		$c->comment('Task Completed')->br();

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
		self::save($todos);
		self::all();
	} 

}
