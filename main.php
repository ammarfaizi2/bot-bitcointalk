<?php

require __DIR__ . '/autoloader.php';

$socks = [];
$socksOffset = 0;
$cookie = file_get_contents(__DIR__.'/cookie.txt');
$logs = __DIR__.'/logs.json';

/*for ($page = 0; $page <= 44680; $page+=40) {*/
	if (! count($socks)) {
		print "Generating socks ".($url = "https://www.socks-proxy.net/")." ...\n";
		$ch = new Curl($url, $cookie);
		$ch->setOpt();
		$out = $ch->exec();
		$ch->close();
		preg_match_all('/<tr><td>([\d\.]{4,12})<\/td><td>([\d]{1,10})<\/td>.+<td>Socks(.*)<\/td>.+<\/tr>/Usi', $out, $matches);
		$socksOffset = 0;
		foreach ($matches[1] as $key => $val) {
			$socks[] = [
				"socks" => $val.":".$matches[2][$key],
				"version" => $matches[3][$key]
			];
		}
	}

	print "Scanning thread ".($url = "https://bitcointalk.org/index.php?board=1.".$page)." ...\n\n\n";
	$ch = new Curl($url, $cookie);
	$ch->setOpt();
	$out = $ch->exec();
	$ch->close();

	//file_put_contents('b.tmp', $out);
	//$out = file_get_contents('b.tmp');

	preg_match_all('/href="(https:\/\/bitcointalk.org\/index.php\?topic=[\d\.]+)"/Usi', $out, $matches);

	if (isset($matches[1]) && count($matches[1])) {
		$matches[1] = array_unique($matches[1]);
		foreach ($matches[1] as $key => $val) {

			if (! count($socks)) {
				print "Generating socks ".($url = "https://www.socks-proxy.net/")." ...\n";
				$ch = new Curl($url, $cookie);
				$ch->setOpt();
				$out = $ch->exec();
				$ch->close();
				preg_match_all('/<tr><td>([\d\.]{4,12})<\/td><td>([\d]{1,10})<\/td>.+<td>Socks(.*)<\/td>.+<\/tr>/Usi', $out, $matches);
				$socksOffset = 0;
				foreach ($matches[1] as $key => $val) {
					$socks[] = [
						"socks" => $val.":".$matches[2][$key],
						"version" => $matches[3][$key]
					];
				}
			}

			print "Opening $val ...\n";
			$ch = new Curl($val, $cookie);
			$ch->setOpt();
			$out = $ch->exec();
			$q = $ch->error();
			$ch->close();

			//file_put_contents('c.tmp', $out);
			//$out = file_get_contents('c.tmp');

			$out = explode('<td class="maintab_back">', $out, 2);
			if (count($out) > 1) {
				$out = explode('</td>', $out[1], 2);
				if (preg_match('/<a href="(.+)" >Reply<\/a>/Usi', $out[0], $matches)) {
					$url = html_entity_decode($matches[1]);
					print "Opening reply page...\n";
					$ch = new Curl($url, $cookie);
					$ch->setOpt();
					$out = $ch->exec();
					$ch->close();

					//file_put_contents('d.tmp', $out);
					//$out = file_get_contents('d.tmp');

					$out = $_out = explode("enctype=\"multipart/form-data\"", $out, 2);
					$out = explode("<form", $out[0]);
					$out = explode("action=\"", $out[count($out) - 1], 2);
					$out = explode("\"", $out[1], 2);
					$url = html_entity_decode($out[0], ENT_QUOTES, 'UTF-8');
					$out = $_out[1]; $_out = null;
					$out = explode("</form>", $out);
					preg_match_all('/<input type="hidden" name="(.*)" value="(.*)"/Usi', $out[0], $matches);
					preg_match('/<input type="text" name="subject" value="(.*)"/Usi', $out[0], $matches2);
					$subject = html_entity_decode($matches2[1]);
					array_walk($matches[1], function (&$matches) {
						$matches = html_entity_decode($matches, ENT_QUOTES, 'UTF-8');
					});
					array_walk($matches[2], function (&$matches) {
						$matches = html_entity_decode($matches, ENT_QUOTES, 'UTF-8');
					});
					$postContext = array_combine($matches[1], $matches[2]);
					$postContext = array_merge($postContext, [
						"goback" => 1,
						"subject" => "",
						"icon" => "xx",
						"message" => rstr(500)
					]);
					$postContext['subject'] = $subject;
					$ch = new Curl($url, $cookie);
					$opt = [
						CURLOPT_POST => true,
						CURLOPT_POSTFIELDS => $postContext,
						CURLOPT_TIMEOUT => 10,
						CURLOPT_CONNECTTIMEOUT => 10
					];
					if ($socks[$socksOffset]['version'] == 5) {
						$opt[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
					} else {
						$opt[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS4;
					}
					if (! isset($socks[$socksOffset])) {
						if (! count($socks)) {
							print "Generating socks ".($url = "https://www.socks-proxy.net/")." ...\n";
							$ch = new Curl($url, $cookie);
							$ch->setOpt();
							$out = $ch->exec();
							$ch->close();
							preg_match_all('/<tr><td>([\d\.]{4,12})<\/td><td>([\d]{1,10})<\/td>.+<td>Socks(.*)<\/td>.+<\/tr>/Usi', $out, $matches);
							$socksOffset = 0;
							foreach ($matches[1] as $key => $val) {
								$socks[] = [
									"socks" => $val.":".$matches[2][$key],
									"version" => $matches[3][$key]
								];
							}
						}
					}
					$opt[CURLOPT_PROXY] = $socks[$socksOffset]['socks'];
					print "Sending reply with socks ".$opt[CURLOPT_PROXY]." ...\n";
					unset($socks[$socksOffset]);
					$socksOffset++;
					$ch->setOpt($opt);
					$out = $ch->exec();
					if ($err = preg_match('/The last posting from your IP was less than 360 seconds ago./Ui', $out) or $err = $ch->error()) {
						errorQ();
						print "Error! ".($err === 1 ? fresh($opt[CURLOPT_PROXY])."The last posting from your IP was less than 360 seconds ago." : $err)."\n\n";
					} else {
						successQ($opt[CURLOPT_PROXY]);
						print "Success!\n\n";
					}
					$ch->close();
				}
			}
		}
	}
/*}*/

function rstr($n = 32, $e = null)
{
	if (! is_string($e)) {
		$e = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890___   ";
	}
	$rn = '';
	$ln = strlen($e) - 1;
	for ($i=0; $i < $n; $i++) { 
		$rn .= $e[rand(0, $ln)];
	}
	return $rn;
}

function errorQ()
{
	global $logs;
	$a = json_decode(file_get_contents($logs), true);
	if (! isset($a['error'])) {
		$a['error'] = 0;
	}
	$a['error']++;
	file_put_contents($logs, json_encode($a, 128));
}
function successQ($freshSocks = "")
{
	global $logs;
	fresh($freshSocks);
	$a = json_decode(file_get_contents($logs), true);
	if (! isset($a['success'])) {
		$a['success'] = 0;
	}
	$a['success']++;
	file_put_contents($logs, json_encode($a, 128));
}

function fresh($freshSocks)
{
	empty($freshSocks) or file_put_contents('fresh_socks.txt', $freshSocks."\n", FILE_APPEND);
}