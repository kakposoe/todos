<?php namespace Todo; 

use League\CLimate\CLimate;

class Message
{
	
	public static function intro() {
		$c = new CLImate;
		$c->br()->black()->backgroundCyan()->out(' To Do List! ')->br();
	}

	public static function output($msg, $tab = null) {
		$c = new CLImate;
		if ($tab) {
			$c->tab();
		}
		$c->out(' ' . $msg . ' ');
	}


	public static function success($msg, $break = null) {
		$c = new CLImate;
		if ($break) {
			$c->br();
		}
		$c->black()->backgroundGreen()->out(' ' . $msg . ' ')->br();
	}

	public static function comment($msg) {
		$c = new CLImate;
		return $c->comment(' ' . $msg . ' ');
	}

	public static function error($msg) {
		$c = new CLImate;
		return $c->black()->backgroundRed()->out(' ' . $msg . ' ');
	}

	public static function radio($msg, $select) {
		$c = new CLImate;
		return $c->radio($msg, $select);
	}

	public static function warningConfirm($msg) {
		$c = new CLImate;
		return $c->black()->backgroundYellow()->confirm(' ' . $msg . ' ');
	}

}
