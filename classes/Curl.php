<?php

class Curl
{
	private $ch;

	private $cookie;

	private $closed = false;

	public function __construct($url, $cookie = '')
	{
		$this->ch = curl_init($url);
		$this->cookie = $cookie;
	}

	public function setOpt($opt = [])
	{
		$defopt = [
			CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:56.0) Gecko/20100101 Firefox/56.0',
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
		];
		if (! empty($this->cookie)) {
			$defopt[CURLOPT_HTTPHEADER] = [
				'Cookie: '.$this->cookie
			];
		}
		foreach ($opt as $key => $value) {
			$defopt[$key] = $value;
		}
		curl_setopt_array($this->ch, $defopt);
	}

	public function exec()
	{
		return curl_exec($this->ch);
	}

	public function error()
	{
		return curl_error($this->ch);
	}

	public function getInfo()
	{
		return curl_getinfo($this->ch);
	}

	public function close()
	{
		$this->closed = true;
		return curl_close($this->ch);
	}

	public function __destruct()
	{
		if (! $this->closed) {
			$this->close();
		}
	}
}