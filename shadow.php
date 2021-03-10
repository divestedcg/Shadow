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

error_reporting(E_ERROR | E_PARSE);

include 'settings.php';

if($GLOBALS['honorDNT'] == false || $GLOBALS['honorDNT'] == true && !isset($_SERVER['HTTP_DNT']) && noHTML(!$_SERVER['HTTP_DNT']) == 1) {
	$landscape = noHTML($_POST["p"]);
	print("DNT DISABLED | ");
	if(!is_null($landscape) && strlen($landscape) >= 1) {
		print("LANDSCAPE VALID | ");
		if(in_array($landscape, getLandscapes())) {
			print("LANDSCAPE EXISTS | ");
			$split = "|";
			$fpidp = hash('sha512', $landscape . " " . $_SERVER['REMOTE_ADDR'] . " " . noHTML($_SERVER['HTTP_USER_AGENT']));
			$fpida = noHTML($_POST["fpid"]);
			if(is_null($fpida) || strlen($fpida) == 0) {
				$fpida = $fpidp;
			} else {
				$fpida = hash('sha512', $landscape . " " . noHTML($_POST["fpid"]));
			}
			$ref = noHTML($_POST["ref"]);
			if(is_null($ref) || strlen($ref) <= 3 || strlen($ref) > 300 || $ref == "undefined") {
				$ref = "D/U";
			}
			if(!file_exists($GLOBALS['landscapePath'] . $landscape . "/Analytics-Users.log")) {
				touch($GLOBALS['landscapePath'] . $landscape . "/Analytics-Users.log");
				print("CREATED USERS | ");
			}
			if(!file_exists($GLOBALS['landscapePath'] . $landscape . "/Analytics-Raw.csv")) {
				touch($GLOBALS['landscapePath'] . $landscape . "/Analytics-Raw.csv");
				print("CREATED RAW | ");
			}
			$geoIP = $_SERVER["MM_COUNTRY_CODE"];
			if(strlen($_SERVER["MM_CITY_NAME"] .  $_SERVER["MM_REGION_CODE"]) > 1) {
				$geoIP = $_SERVER["MM_CITY_NAME"] . ", " . $_SERVER["MM_REGION_CODE"] . " " . $_SERVER["MM_COUNTRY_CODE"];
			}
			if(!exec('grep ' . escapeshellarg($fpida) . $GLOBALS['landscapePath'] . $landscape . '/Analytics-Users.log')) {
				file_put_contents($GLOBALS['landscapePath'] . $landscape . "/Analytics-Users.log", $fpida . "\r\n", FILE_APPEND);
			}
			//Passive ID, Active ID, Page, Referrer, Time Stamp, IP Address, GeoIP, Lat, Lon, User Agent
			$entryOut = $fpidp . $split . $fpida . $split. noHTML($_SERVER['HTTP_REFERER']) . $split . $ref . $split . $_SERVER['REQUEST_TIME'] . $split . $_SERVER['REMOTE_ADDR'] . $split . $geoIP . $split . $_SERVER["MM_LATITUDE"] . $split . $_SERVER["MM_LONGITUDE"] . $split . noHTML($_SERVER['HTTP_USER_AGENT']) . PHP_EOL;
			file_put_contents($GLOBALS['landscapePath'] . $landscape . "/Analytics-Raw.csv", $entryOut, FILE_APPEND);
			print("SUCCESS");
		} else {
			print("LANDSCAPE DOES NOT EXIST");
		}
	} else {
		print("LANDSCAPE IS NOT VALID");
	}
} else {
	print("DO NOT TRACK ENABLED, NOT LOGGING");
}

function getLandscapes() {
	$landscapesArr = array();
	$landscapes = glob($GLOBALS['landscapePath'] . '*' , GLOB_ONLYDIR);
	foreach($landscapes as $landscape) {
		array_push($landscapesArr,noHTML(explode($GLOBALS['landscapePath'], $landscape)[1]));
	}
	return $landscapesArr;
}

//Credit (CC BY-SA 4.0): https://paragonie.com/blog/2015/06/preventing-xss-vulnerabilities-in-php-everything-you-need-know
function noHTML($input, $encoding = 'UTF-8') {
	return htmlentities($input, ENT_QUOTES | ENT_HTML5, $encoding);
}

?>
