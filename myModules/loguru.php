<?php

class loguru {

	private $green = "\x1b[32m";
	private $white = "\x1b[37m";
	private $blue = "\x1b[34m";
	private $cyan = "\x1b[36m";
	private $red = "\x1b[31m";

	private $reverse = "\x1b[7m";

	private $reset = "\x1b[0m";

	public function log(string $message) {
		echo $this->green . date('d-m-Y H:i:s') . "{$this->white} | {$this->blue} {$message} {$this->white}{$this->reset}\n";
	}

	public function error(string $message) {
		echo $this->green . date('d-m-Y H:i:s') . "{$this->white} | {$this->red} {$message} {$this->white}{$this->reset}\n";
	}

	public function debug(string $message) {
		echo $this->green . date('d-m-Y H:i:s') . "{$this->white} | {$this->reverse} {$message} {$this->white}{$this->reset}\n";
	}
}
