<?php
//Copyright (c) 2015-2021 Divested Computing Group
//
//This program is free software: you can redistribute it and/or modify
//it under the terms of the GNU Lesser General Public License as published by
//the Free Software Foundation, either version 3 of the License, or
//(at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU Lesser General Public License for more details.
//
//You should have received a copy of the GNU Lesser General Public License
//along with this program.  If not, see <https://www.gnu.org/licenses/>.

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting(E_ERROR | E_PARSE);

include 'settings.php';
$cookieName = $authTokenCookieName;
$fpidp = hash('sha512', $_SERVER['REMOTE_ADDR'] . " " . noHTML($_SERVER['HTTP_USER_AGENT']));
$authToken = noHTML($_COOKIE[$cookieName]);
$accountsDB = file($accountsFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
$allowedAuthTokens = file($authFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
session_start();
$loggedIn = file_exists($authFile) && in_array($_SESSION['usernameRaw'] . ":" . $authToken . ":" . session_id() . ":" . $fpidp, $allowedAuthTokens);

function login() {
	include 'captcha.php';
	$captcha = noHTML($_POST["captcha"]);
	if($GLOBALS['captchaEnabled'] == false && strlen($captcha) > 0 && $captcha == 'DISABLED' || strlen($captcha) > 0 && checkCaptchaAnswer($captcha, true)) {
		$username = str_rot13(noHTML($_POST["username"]));
		$password = str_rot13(noHTML($_POST["password"]));
		$tfa = noHTML($_POST["tfa"]);
		foreach($GLOBALS['accountsDB'] as $account) {
			$account = explode(":", $account);
			if($username === substr(base64_encode(hash('sha512', $account[1] . $_SESSION['authSaltOne'])), 0, 171)) {
				if($password === substr(base64_encode(hash('sha512', $account[2] . $_SESSION['authSaltTwo'])), 0, 171)) {
					if($GLOBALS['tfaRequired'] === 0 || $tfa === $account[3]) {
						$_SESSION['usernameRaw'] = $account[0];
						$authTokenC = bin2hex(random_bytes(1024));
						setCookie($GLOBALS['cookieName'], $authTokenC, 0, null, null, true, true);
						trimAuthTokens();
						if(!file_exists($GLOBALS['authFile'])) {
							touch($GLOBALS['authFile']);
						}
						session_regenerate_id();
						$authDB = fopen($GLOBALS['authFile'], "a") or die("Unable to open file!");
						fwrite($authDB, $_SESSION['usernameRaw'] . ":" . $authTokenC . ":" . session_id() . ":" . $GLOBALS['fpidp'] . PHP_EOL);
						fclose($authDB);
						$GLOBALS['loggedIn'] = true;
						return true;
					}
				}
			}
		}
	}
	$_SESSION['authSalt'] = bin2hex(random_bytes(64));
	$_SESSION['SBNR_CAPTCHA_ANSWER'] = bin2hex(random_bytes(64));
	session_regenerate_id();
	return false;
}

function logout() {
	$allowedAuthTokensW = fopen($GLOBALS['authFile'], "w") or die("Unable to open file!");
	foreach($GLOBALS['allowedAuthTokens'] as $authTokenC) {
		if(!startsWith($authTokenC, $_SESSION['usernameRaw'] . ":")) {
			fwrite($allowedAuthTokensW, $authTokenC . PHP_EOL);
		}
	}
	fclose($allowedAuthTokensW);
	setCookie($GLOBALS['cookieName'], "INVALIDATED", time() - 1);
	session_destroy();
	$GLOBALS['loggedIn'] = false;
}

function trimAuthTokens() {
	$curSessions = countLines($GLOBALS['authFile']);
	$maxTokens = countLines($GLOBALS['accountsFile']) * 4;
	if($maxTokens < 4) {
		$maxTokens = 4;
	}
	if($curSessions > $maxTokens) {
		unlink($GLOBALS['authFile']);
	}
}

function isAccount($username) {
	foreach($GLOBALS['accountsDB'] as $account) {
		$account = explode(":", $account);
		if($username === $account[1]) {
			return true;
		}
	}
	return false;
}

function createNewAccount($usernameRaw, $username, $password) {
	if(!isAccount($username)) {
		file_put_contents($GLOBALS['accountsFile'], $usernameRaw . ":" . $username . ":" . $password . ":DISABLED:" . PHP_EOL, FILE_APPEND);
		return true;
	} else {
		return false;
	}
}

function getThemes() {
	$themesArr = array();
	$files = glob('assets/css/themes/*');
	foreach($files as $file){
		if(is_file($file)) {
			array_push($themesArr, noHTML(explode("assets/css/themes/", $file)[1]));
		}
	}
	return $themesArr;
}

function getLandscapes() {
	$landscapesArr = array();
	$landscapes = glob($GLOBALS['landscapePath'] . '*' , GLOB_ONLYDIR);
	foreach($landscapes as $landscape) {
		array_push($landscapesArr,noHTML( explode($GLOBALS['landscapePath'], $landscape)[1]));
	}
	return $landscapesArr;
}

function createNewLandscape($landscape) {
	mkdir($GLOBALS['landscapePath'] . $landscape);
}

function clearLandscape($landscape) {
	$files = glob($GLOBALS['landscapePath'] . $landscape . '/*');
	foreach($files as $file){
		if(is_file($file)) {
			unlink($file);
		}
	}
}

function renameLandscape($landscape, $landscapeNew) {
	rename($GLOBALS['landscapePath'] . $landscape, $GLOBALS['landscapePath'] . $landscapeNew);
}

function deleteLandscape($landscape) {
	rmdir($GLOBALS['landscapePath'] . $landscape);
}

function cCustom($custom) {
	return $custom;
}

function cLink($title, $url, $rel = '') {
	return "<a target=\"_blank\" rel=\"" . $rel . "\" href=\"". $url . "\">" . $title . "</a>";
}

function cDownload($title, $url) {
	return "<a href=\"". $url . "\">" . $title . "</a>";
}

function cImage($imagePath, $altTag = '', $width = '', $height = '') {
	$style = "style=\"margin-left:auto;margin-right:auto;width:" . $width . ";height:" . $height . ";\"";
	if($width == "" || $height == "") {
		$style = "style=\"margin-left:auto;margin-right:auto;\"";
	}
	return "<img src=\"" . $imagePath . "\" class=\"image\" " .  $style . " alt=\"" . $altTag . "\">";
}

//START OF EXTRA FUNCTIONS
//Credit (CC BY-SA 3.0): https://stackoverflow.com/a/10473026
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
//Credit (CC BY-SA 3.0): https://stackoverflow.com/a/10473026
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
}

//Credit (CC BY-SA 2.5): https://stackoverflow.com/a/4478788
function getRandomFile($dir) {
    $files = glob($dir . '/*.*');
    $file = array_rand($files);
    return $files[$file];
}

//Credit (CC BY-SA 3.0): http://stackoverflow.com/a/23874239
function isMobileDevice(){
    $aMobileUA = array(
        '/iphone/i' => 'iPhone',
        '/ipod/i' => 'iPod',
        '/ipad/i' => 'iPad',
        '/android/i' => 'Android',
        '/blackberry/i' => 'BlackBerry',
        '/webos/i' => 'Mobile'
    );

    //Return true if Mobile User Agent is detected
    foreach($aMobileUA as $sMobileKey => $sMobileOS){
        if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
            return true;
        }
    }
    //Otherwise return false...
    return false;
}

//Credit (CC BY-SA 2.5): http://stackoverflow.com/a/2162528
function countLines($file) {
	if(file_exists($file)) {
		$linecount = 0;
		$handle = fopen($file, "r");
		while(!feof($handle)){
		  $line = fgets($handle);
		  $linecount++;
		}
		fclose($handle);
		return $linecount - 1;
	}
	return 0;
}

//Credit (CC BY-SA 2.5): https://stackoverflow.com/a/4356295
function generateRandomString($length = 512) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//Credit (CC BY-SA 4.0): https://paragonie.com/blog/2015/06/preventing-xss-vulnerabilities-in-php-everything-you-need-know
function noHTML($input, $encoding = 'UTF-8') {
	return htmlentities($input, ENT_QUOTES | ENT_HTML5, $encoding);
}

//Credit: https://secure.php.net/manual/en/function.str-rot13.php#107475
function rott($s, $n = 13) {
	static $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
	$n = (int)$n % 26;
	if (!$n) return $s;
	if ($n < 0) $n += 26;
	if ($n == 13) return str_rot13($s);
	$rep = substr($letters, $n * 2) . substr($letters, 0, $n * 2);
	return strtr($s, $letters, $rep);
}
//END OF EXTRA FUNCTIONS

?>
