<?php

spl_autoload_register(
	function ($class) {
		require __DIR__ . '/classes/'.str_replace('\\', '/', $class).'.php';
	}
);
