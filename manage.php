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
include 'utils.php';

$action = noHTML($_POST["a"]);
$ct = noHTML($_POST["ct"]);
if($_SESSION['csrfToken'] == $ct) {
	if($loggedIn) {
		$landscape = noHTML($_POST["l"]);
		if($action == 'createLandscape') {
			if(!in_array($landscape, getLandscapes())) {
				createNewLandscape($landscape);
				print("Created new landscape: " . $landscape);
			} else {
				print("Landscape already exists");
			}
		} else if($action == 'createAccount') {
			if(createNewAccount(noHTML($_POST["usernameRaw"]), noHTML($_POST["username"]), noHTML($_POST["password"]))) {
				print("Created new account");
			} else {
				print("Account already exists");
			}
		} else if($action == 'logout') {
			logout();
			print("Logged out");
		} else if(in_array($landscape, getLandscapes())) {
			$landscapeNew = noHTML($_POST["ln"]);
			if($action == 'clear') {
				clearLandscape($landscape);
				print("Cleared landscape: " . $landscape);
			} else if($action == 'rename' && (strlen($landscapeNew) >= 1)) {
				renameLandscape($landscape, $landscapeNew);
				print("Renamed landscape " . $landscape . " to " . $landscapeNew);
			} else if($action == 'delete') {
				deleteLandscape($landscape);
				print("Deleted landscape: " . $landscape);
			}
		} else {
			print("Can't perform action.");
		}
	} else {
		if($action == 'login') {
			if(login()) {
				print("Logged in");
			} else {
				print("Failed to log in");
			}
		} else {
			print("<!DOCTYPE html><html lang=\"en\"><head><title>REQUEST DENIED</title></head><body><h1>REQUEST DENIED</h1></body></html>");
		}
	}
} else {
	print("<!DOCTYPE html><html lang=\"en\"><head><title>REQUEST DENIED</title></head><body><h1>REQUEST DENIED</h1></body></html>");
}
$_SESSION['csrfToken'] = bin2hex(random_bytes(64));

?>
