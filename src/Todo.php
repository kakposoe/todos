<?php namespace Todo;
use Todo\Message;

class Todo 
{

	protected $todos;

	protected static function getTodos() {
		return json_decode(file_get_contents('./todo.json'), true);
	} 

	protected static function save($todos, $show = false) {
		file_put_contents('./todo.json', json_encode($todos));
		if ($show) self::all();
	} 

	public static function find($i) {

		$todos = self::getTodos();
		$output = '';

		if ((int) $i != $i) {

			$offset = floor($i) - 1;
			$index = intval(substr($i - $offset, 2) - 1);

			if (array_key_exists($index, $todos)) {
				if (array_key_exists('subtasks', $todos[$index])) {
					if(!array_key_exists(intval($offset), $todos[$index]['subtasks'])) {
						Message::error('Oops! There is no task as this index');
						exit;
					}
				} else {
					Message::error('Oops! There are no subtasks for #' . ($index + 1));
					exit;
				}
			} else {
				Message::error('Oops! There is no top level task...');
				exit;
			}

			$todos[$offset]['subtasks'][$index]['status'] ? $output .= '<green>✓</green> ' : $output .= '- ';
			$output .= $i . '. ';
			$todos[intval($i) - 1]['status'] ? $output .= '<dim>' . $todos[$offset]['subtasks'][$index]['task'] . '</dim>' : $output .= $todos[intval($i) - 1]['task']; 
			Message::output($output);
		} else {

			if(!array_key_exists(intval($i) - 1, $todos)) {
				Message::error('Oops! There is no task as this index');
				exit;
			}

			$todos[intval($i) - 1]['status'] ? $output .= '<green>✓</green> ' : $output .= '- ';
			$output .= $i . '. ';
			$todos[intval($i) - 1]['status'] ? $output .= '<dim>' . $todos[intval($i) - 1]['task'] . '</dim>' : $output .= $todos[intval($i) - 1]['task']; 
			Message::output($output);

			if (isset($todos[intval($i) - 1]['subtasks'])) {
				$output = '';
				foreach ($todos[intval($i) - 1]['subtasks'] as $key => $sub) {
					$sub['status'] ? $output .= '<green>✓</green> ' : $output .= '- ';
					$output .= ($key + 1) . '. ';
					$sub['status'] ? $output .= '<dim>' . $sub['task'] . '</dim>' : $output .= $sub['task']; 
					Message::output($output, true);
					$output = '';
				}
			}
		}

	}

	public static function all() {

		$todos = self::getTodos();

		Message::intro();

		if ($todos) {
			$count = 1;
			foreach ($todos as $todo) {

				if (isset($todo['subtasks']) && count($todo['subtasks']) > 0) {
					$completeCount = 0;
					foreach ($todo['subtasks'] as $sub) {
						if ($sub['status'] == true) $completeCount++;
					}
				}

				if($todo['status']) {
					$status = '<green>✓</green>';
					$numb   = '<dim>' . $count . '.</dim> ';
				} else {
					$status = '-';
					$numb   = $count . '. ';
				}

				$output = $status . ' ' . $numb;
				$todo['status'] ? $output .= '<dim>' . $todo['task'] . '</dim>' : $output .= $todo['task']; 

				if (isset($todo['subtasks']) && count($todo['subtasks']) > 0) {
					$output .= ' [' . $completeCount . '/' . count($todo['subtasks']) . ']';
				}

				Message::output($output);

				if (isset($todo['subtasks'])) {
					$subCount = 1;
					foreach ($todo['subtasks'] as $sub) {

						if($sub['status'] == true) {
							$status  = '<green>✓</green>';
							$numb    = '<dim>' . $count . '.</dim>';
							$subNumb = '<dim>' . $subCount . '.</dim>';
						} else {
							$status  = '-';
							$numb    = $count . '.';
							$subNumb = $subCount . '.';
						}

						// Output
						$subout = $status . ' ' . $numb . $subNumb . ' ';
						$sub['status'] ? $subout .= '<dim>' . $sub['task'] . '</dim>' : $subout .= $sub['task']; 

						Message::output($subout, true);
						$subCount++;
					}
				}

				$count++;
			}
		} else {
			Message::comment('Currently No Tasks...');
			Message::output('Add some tasks to your todo list!');
		}
	}

	public static function add($task, $subtask = null, $showMessage = true) {

		$todos = self::getTodos();

		$data = [
			'status' => false,
			'task' => $task,
		];

		if($subtask) {
			$index = intval($subtask); 
			$index--;

			if(!array_key_exists($index, $todos)) {
				Message::error('Oops. There is not task at this index');
				exit;
			} else {
				$todos[$index]['subtasks'][] = $data;
				Message::success('✓ New Subtask Added');
			}

		} else {
			$todos[] = $data;
			if ($showMessage) 
				Message::success('✓ New Task Added');
		}

		self::save($todos);
	} 

	public static function addMany($tasks) {

		$tasks = array_slice($tasks, 2);

		$first = false;
		$lister = [];

		foreach ($tasks as $key => $value) {
			if ($value !== 'and') {
				self::add($value, null, false);
				$lister[] = $value;
			}

			if(!$first) {
				$todos = self::getTodos();
				end($todos);
				$ref = key($todos) + 1;
				$first = true;
			}
		}

		Message::success('✓ ' . count($lister) . ' New Tasks Added', true);

		foreach ($lister as $task) {
			Message::output('- ' . $ref . '. ' .  $task);
			$ref++;
		}

	} 

	public static function edit($i, $task) {

		$todos = self::getTodos();

		if (is_numeric($i)) {

			if ((int) $i != $i) {
				$offset = floor($i) - 1;
				$index  = intval(substr($i - $offset, 2) - 1);
				$todos[$offset]['subtasks'][$index]['task'] = $task;
			} else {
				$todos[intval($i) - 1]['task'] = $task;
			}

			self::save($todos);
			Message::success('✓ Task Updated');
		} else {
			Message::error('No tasks index declared');

		}

	}

	public static function completed($i = null) {

		$todos = self::getTodos();

		if ((int) $i != $i) {
			$offset = floor($i) - 1;
			$index  = intval(substr($i - $offset, 2) - 1);
			$todos[$offset]['subtasks'][$index]['status'] = true;

			// Check if all sibling tasks marked as complete

			$subDoneCount  = count($todos[$offset]['subtasks']);
			$completeCount = 0;

			for ($i = 0; $i < count($todos[$offset]['subtasks']); $i++) {
				if ($todos[$offset]['subtasks'][$i]['status']) $completeCount++;
			}

			if ($subDoneCount === $completeCount) {
				$input = Message::warningConfirm('All Sub Tasks completed! Would you like to mark main task as completed?');
				if ($input->confirmed()) {
					$todos[$offset]['status'] = true;
				} 
			}

			$number = ($offset + 1) . '.' . ($index + 1);
			$task = $todos[$offset]['subtasks'][$index]['task'];


		} else {

			// Check if has children
			if(array_key_exists('subtasks', $todos[$i - 1])) {
				$input = Message::warningConfirm('There are sub tasks, mark all as completed?');
				if ($input->confirmed()) {
					foreach ($todos[$i - 1]['subtasks'] as &$sub) {
						$sub['status'] = true;
					}
				} 
			}
			
			$todos[intval($i) - 1]['status'] = true;
			$task   = $todos[intval($i) - 1]['task'];
			$number = intval($i);

		}

		self::save($todos);
		Message::success('Task marked as complete');
		Message::output('<green>✓</green> ' . $number . '. ' . $task);
	}

	public static function selectComplete($i = null) {
		Message::error('Oops! You have not added an index to mark as completed');
		Message::comment('e.g. todo done 1.1');
	}

	public static function remove($i = null) {
		$todos = self::getTodos();

		if (count($todos) == 0) {
			Message::error('There are no tasks!');
			exit;
		}

		if($i) {
			if ((int) $i != $i ) {
				$offset = floor($i) - 1;
				$index  = substr($i - $offset, 2) - 1;
				$input  = Message::warningConfirm(' Confirm to Delete ');
				if ($input->confirmed()) {
					array_splice($todos[$offset]['subtasks'], $index, 1);
				}

			} else {
				// If has subtasks, ask if you would like to delete
				if (count($todos[intval($i) - 1]['subtasks']) >= 1) {
					$input  = Message::warningConfirm(' Task has subtasks! Would you like to delete all tasks? ');
					if ($input->confirmed()) {
						array_splice($todos, intval($i) - 1, 1);
					} else {
						Message::comment('Task not removed...');
						exit;
					}
				} else {
					array_splice($todos, intval($i) - 1, 1);
				}
			}
		} else {

			$select = [];
			foreach($todos as $key => $todo) {
				$select[] = ($key + 1) . '. ' . $todo['task'];
			}
			$input = Message::radio('Select a task to delete', $select);
			$response = $input->prompt();
			array_splice($todos, intval(substr($response, 1)), 1);

		}


		Message::success('Task removed!', true);
		self::save($todos, true);
	} 

}
