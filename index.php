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
putenv('GDFONTPATH=' . realpath('.') . '/captcha_fonts/');
include 'settings.php';
include 'utils.php';


//START OF VARIABLES
$page = str_replace("&colon;", ":", str_replace("&nbsp;", " ", noHTML($_GET["p"])));
if ($page == '') {
	$page = $defaultPage;
}
$eid = noHTML($_GET["eid"]);
$theme = $defaultTheme;
if(strlen($_COOKIE["theme"]) >= 1) {
	$theme = str_replace("&period;", ".", noHTML($_COOKIE["theme"]));
}
session_start();
$_SESSION['csrfToken'] = bin2hex(random_bytes(32));
//END OF VARIABLES


//START OF PAGE
print(cTop());
if($loggedIn) {
	print(cNavBar());
	$landscape = explode("L: ", $page)[1];
	if($page == 'Dashboard') {
		print(cHeader("Dashboard", "Brief Overview of Shadow") . cDashboard());
	} else if($page == 'About') {
		print(cHeader("About", "About this project") . cAbout());
	} else if($page == 'LM: New Landscape') {
		print(cHeader("Landscape", "Creating new landscape") . cNewLandScape());
	} else if(startsWith($page, "L: ") && in_array($landscape, getLandscapes())) {
		print(cHeader("Landscape", "Viewing " . $landscape) . cAnalytics($landscape));
	} else if ($page == 'AM: My Account') {
		print(cHeader("Accounts", "Managing your account") . cMyAccount());
	} else if ($page == 'AM: New Account') {
		print(cHeader("Accounts", "Creating new account") . cNewAccount());
	} else if ($page == 'AM: Manage Accounts') {
		print(cHeader("Accounts", "Managing accounts"));
	} else {
		print(cHeader("You've taken a wrong turn mate", "Error " + $eid));
	}
} else {
	print(cHeader("Login", "You'll need to login first") . cLogin());
}
print(cBottom());
//END OF PAGE

//START OF SITE FUNCTIONS
function cTop() {
	return "
		<!DOCTYPE html>
		<html lang=\"en\">

			<head>
				<meta charset=\"utf-8\">
				<meta name=\"robots\" content=\"noindex\">
				<meta name=\"theme-color\" content=\"#3D4348\">
				<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
				<meta name=\"robots\" content=\"noindex, nofollow, noarchive, nosnippet, noodp, notranslate, noimageindex\">
				<meta name=\"author\" content=\"Divested Computing Group\">
				<title>" . $GLOBALS['page'] . " || Shadow Administration</title>
				<link href=\"assets/img/favicon-round.png\" rel=\"shortcut icon\">
				<link href=\"assets/css/themes/" . $GLOBALS['theme'] . "\" rel=\"stylesheet\" type=\"text/css\" id=\"theme\">
			</head>
			<body>
		";
}

function cNavbar() {
	$landscapesDropDown = '';
	foreach(getLandscapes() as $landscape) {
		$landscapesDropDown = $landscapesDropDown . "<li><a href=\"index.php?p=L:%20" . $landscape . "\">" . $landscape . "</a></li>";
	}
	$themesDropDown = '';
	foreach(getThemes() as $themeS) {
		$suffix = "";
		if($themeS == $GLOBALS['theme']) {
			$suffix = " - Current";
		}
		$themesDropDown = $themesDropDown . "<li><a href=\"javascript:changeTheme('" . $themeS . "')\">" . $themeS . $suffix . "</a></li>";
	}
	return "
				<script type=\"text/javascript\">
					function changeTheme(theme) {
						document.cookie = \"theme=\" + theme;
						document.getElementById('theme').href = \"assets/css/themes/\" + theme;
					}
					function logout() {
						var req = new XMLHttpRequest();
						req.open(\"POST\", \"manage.php\", true);
						req.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");
						req.send(\"a=logout\" + \"&ct=" . $_SESSION['csrfToken'] . "\");
						setTimeout(function() {window.location.href = \"index.php\"}, 500);
					}
				</script>
				<nav class=\"navbar navbar-default\">
					<div class=\"container-fluid\">
						<div class=\"navbar-header\">
							<button type=\"button\" class=\"navbar-toggle collapsed\" data-toggle=\"collapse\" data-target=\"#main-navbar-collapse-1\" aria-expanded=\"false\">
								<span class=\"sr-only\">Toggle navigation</span>
								<span class=\"icon-bar\"></span>
								<span class=\"icon-bar\"></span>
								<span class=\"icon-bar\"></span>
							</button>
							<a class=\"navbar-brand\" href=\"index.php?p=Dashboard\">Shadow Administration Panel</a>
						</div>
						<div class=\"collapse navbar-collapse\" id=\"main-navbar-collapse-1\">
							<ul class=\"nav navbar-nav navbar-right\">
								<li><a href=\"index.php?p=Dashboard\">Dashboard</a></li>
								<li class=\"dropdown\">
									<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Landscapes <b class=\"caret\"></b></a>
									<ul class=\"dropdown-menu\">
										<li><a href=\"index.php?p=LM:%20New%20Landscape\">New Landscape</a></li>
										<li role=\"separator\" class=\"divider\"></li>
										" . $landscapesDropDown . "
									</ul>
								</li>
								<li class=\"dropdown\">
									<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Accounts <b class=\"caret\"></b></a>
									<ul class=\"dropdown-menu\">
										<li><a href=\"index.php?p=AM:%20My%20Account\">My Account</a></li>
										<li role=\"separator\" class=\"divider\"></li>
										<li><a href=\"index.php?p=AM:%20New%20Account\">New Account</a></li>
										<li><a href=\"index.php?p=AM:%20Manage%20Accounts\">Manage Accounts</a></li>
									</ul>
								</li>
								<li class=\"dropdown\">
									<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Themes <b class=\"caret\"></b></a>
									<ul class=\"dropdown-menu\">
										" . $themesDropDown . "
									</ul>
								</li>
								<li><a href=\"index.php?p=About\">About</a></li>
								<li><a href=\"javascript:logout()\">Logout</a></li>
							</ul>
						</div>
					</div>
				</nav>
		";
}

function cHeader($title, $subtitle) {
	return "
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"page-header\">
								<h1>" . $title . " <small>" . $subtitle . "</small></h1>
							</div>
						</div>
					</div>
				</div>
		";
}

function cLogin() {
	include 'captcha.php';
	$captchaField = "<input type=\"hidden\" class=\"form-control\" id=\"txtCaptcha\" placeholder=\"Captcha\" autocomplete=\"off\">";
	if($GLOBALS['captchaEnabled']) {
		$_SESSION['authSaltOne'] = bin2hex(random_bytes(32));
		$_SESSION['authSaltTwo'] = bin2hex(random_bytes(32));
		$captchaField = "
									<div class=\"form-group\">
										<label for=\"txtCaptcha\">Captcha</label>
										<br><img for=\"txtCaptcha\" src=\"" . getImageBase64(getCaptchaText()) . "\"/><br><br>
										<input type=\"text\" class=\"form-control\" id=\"txtCaptcha\" placeholder=\"Captcha\" autocomplete=\"off\">
									</div>";
	}
	$fake = "|";
	for($counter = 0; $counter < rand(6, 12); $counter ++) {
		$fake = $fake . bin2hex(random_bytes(32)) . "|";
	}
	$localJS = "			function rot(s, i) {
						return s.replace(/[a-zA-Z]/g, function (c) {
							return String.fromCharCode((c <= \"Z\" ? 90 : 122) >= (c = c.charCodeAt(0) + i) ? c : c - 26);
						});
					}
					function login() {
						var username = document.getElementById('txtUsername').value;
						var usernameHash = forge.md.sha512.create();
						usernameHash.update(username + \"" . $GLOBALS['accountSalt'] . "\");
						var usernameOut = forge.util.encode64(usernameHash.digest().toHex());
						usernameOut = usernameOut.substring(0, usernameOut.length-1);
						usernameHash = forge.md.sha512.create();
						usernameHash.update(usernameOut + \"" . $_SESSION['authSaltOne'] . "\");
						usernameOut = forge.util.encode64(usernameHash.digest().toHex());
						usernameOut = usernameOut.substring(0, usernameOut.length-1);
						usernameOut = rot(usernameOut, 13);

						var password = document.getElementById('txtPassword').value;
						var passwordHash = forge.md.sha512.create();
						passwordHash.update(password + \"" . $GLOBALS['accountSalt'] . "\");
						var passwordOut = forge.util.encode64(passwordHash.digest().toHex());
						passwordOut = passwordOut.substring(0, passwordOut.length-1);
						passwordHash = forge.md.sha512.create();
						passwordHash.update(passwordOut + \"" . $_SESSION['authSaltTwo'] . "\");
						passwordOut = forge.util.encode64(passwordHash.digest().toHex());
						passwordOut = passwordOut.substring(0, passwordOut.length-1);
						passwordOut = rot(passwordOut, 13);

						var tfa = document.getElementById('txtTfa').value;
						var tfaOut = tfa;
						if(tfaOut.length == 0) {
							tfaOut = \"DISABLED\";
						}

						var captcha = document.getElementById('txtCaptcha').value;
						var captchaOut = captcha;
						if(captchaOut.length == 0) {
							captchaOut = \"DISABLED\";
						}
						var req = new XMLHttpRequest();
						req.onreadystatechange = function() {
							if (req.readyState == 4 && req.status == 200) {
								setTimeout(function() {window.location.href = \"index.php\"}, 500);
							}
						};
						req.open(\"POST\", \"manage.php\", true);
						req.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");
						req.send(\"a=login&username=\" + usernameOut + \"&password=\" + passwordOut + \"&captcha=\" + captchaOut + \"&tfa=\" + tfaOut + \"&ct=" . $_SESSION['csrfToken'] . "\");
					}";
	return "
				<script type=\"text/javascript\">
				" . $localJS . "
				</script>
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"col-md-4 col-md-offset-4\">
								<form role=\"form\" name=\"frmLogin\" method=\"post\" action=\"javascript:login()\">
									<div class=\"form-group\">
										<label for=\"txtUsername\">Username</label>
										<input type=\"username\" class=\"form-control\" id=\"txtUsername\" placeholder=\"Username\" autocomplete=\"off\" autofocus>
									</div>
									<div class=\"form-group\">
										<label for=\"txtPassword\">Password</label>
										<input type=\"password\" class=\"form-control\" id=\"txtPassword\" placeholder=\"Password\" autocomplete=\"off\">
									</div>
									<div class=\"form-group\">
										<label for=\"txtTfa\">Two-Factor Authentication</label>
										<input type=\"password\" class=\"form-control\" id=\"txtTfa\" placeholder=\"Two-Factor Authentication\" autocomplete=\"off\">
									</div>
									" . $captchaField . "
									<button type=\"submit\" class=\"btn btn-default\">Submit</button>
								</form>
							</div>
						</div>
					</div>
				</div>
		";
}

function cDashboard() {
	return "
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"panel panel-info\">
								<div class=\"panel-heading\">
									<h3 class=\"panel-title\">Project Status</h3>
								</div>
								<div class=\"panel-body\">
									<div class=\"progress progress-striped active\">
										<div class=\"progress-bar progress-bar-warning\" style=\"width: " . (35.5/53)*100 . "%\"></div>
									</div>
									<p>
										&emsp;[COMPLETE - 2015-10-06]    - Create a basic interface<br>
										&emsp;[COMPLETE - 2015-10-06]    - Create a login/logout mechanism<br>
										&emsp;[COMPLETE - 2015-10-06]    - Track the current page<br>
										&emsp;[COMPLETE - 2015-10-06]    - Track the time stamp of the request<br>
										&emsp;[COMPLETE - 2015-10-06]    - Track the user's IP address<br>
										&emsp;[COMPLETE - 2015-10-06]    - Track the user's user agent<br>
										&emsp;[COMPLETE - 2016-01-15]    - Generate identicons for tracking IDs<br>
										&emsp;[COMPLETE - 2016-01-15]    - Make landscapes dynamic<br>
										&emsp;[COMPLETE - 2016-01-15]    - Retrieve country from IP<br>
										&emsp;[COMPLETE - 2016-01-15]    - Retrieve OS and browser from user agent<br>
										&emsp;[COMPLETE - 2016-01-15]    - Use passive and active fingerprinting over cookies<br>
										&emsp;[COMPLETE - 2016-01-16]    - Implement ability to add and delete landsacpes<br>
										&emsp;[COMPLETE - 2016-01-16]    - Implement ability to clear landscape data<br>
										&emsp;[COMPLETE - 2016-01-16]    - Implement ability to rename landscapes<br>
										&emsp;[COMPLETE - 2016-01-16]    - Implement dynamic theme changing<br>
										&emsp;[COMPLETE - 2016-01-16]    - Implement theme support<br>
										&emsp;[COMPLETE - 2016-01-17]    - Create a test page<br>
										&emsp;[COMPLETE - 2016-01-17]    - Display code snippets<br>
										&emsp;[COMPLETE - 2016-01-17]    - Implement account creation<br>
										&emsp;[COMPLETE - 2016-01-17]    - Implement multiple user support<br>
										&emsp;[COMPLETE - 2016-01-17]    - Make accounts dynamic<br>
										&emsp;[COMPLETE - 2016-01-18]    - XSS prevention<br>
										&emsp;[COMPLETE - 2016-01-23]    - Authentication token renewal on request<br>
										&emsp;[COMPLETE - 2016-01-23]    - Create an Android web wrapper app<br>
										&emsp;[COMPLETE - 2016-01-23]    - Frequently clear valid auth tokens<br>
										&emsp;[COMPLETE - 2016-01-26]    - Implement captchas on login page<br>
										&emsp;[COMPLETE - 2016-01-27]    - Implement basic 2FA<br>
										&emsp;[COMPLETE - 2016-01-27]    - Implement global settings<br>
										&emsp;[COMPLETE - 2016-01-28]    - Track the true referrer<br>
										&emsp;[COMPLETE - 2016-03-24]    - Change from GET to POST requests<br>
										&emsp;[COMPLETE - 2016-03-24]    - Create an about page<br>
										&emsp;[COMPLETE - 2016-03-24]    - Retrieve city/state/lat/lon from IP<br>
										&emsp;[COMPLETE - 2016-03-24]    - Implement landscape auto refresh<br>
										&emsp;[COMPLETE - 2016-03-25]    - Improve security of login page<br>
										&emsp;[COMPLETE - 2016-03-26]    - Implement basic CSRF protection<br>
										&emsp;[PARTIAL]                  - Implement account management<br>
										&emsp;[NOT STARTED]              - Allow account creation when no accounts exist without login<br>
										&emsp;[NOT STARTED]              - Calculate landscape statistics<br>
										&emsp;[NOT STARTED]              - Create an actual Android app<br>
										&emsp;[NOT STARTED]              - Implement account permissions<br>
										&emsp;[NOT STARTED]              - Implement advanced 2FA<br>
										&emsp;[NOT STARTED]              - Implement global settings page<br>
										&emsp;[NOT STARTED]              - Implement rate limiting<br>
										&emsp;[NOT STARTED]              - Implement user map<br>
										&emsp;[NOT STARTED]              - Implement user searching<br>
										&emsp;[NOT STARTED]              - Intelligently clear valid auth tokens<br>
										&emsp;[NOT STARTED]    	         - Offload more actions to client<br>
										&emsp;[NOT STARTED]    	         - Perform code cleanup<br>
										&emsp;[NOT STARTED]    	         - Perform code optimization<br>
										&emsp;[NOT STARTED]    	         - Track if an adblocker is in use<br>
										&emsp;[NOT STARTED]              - Track interaction level<br>
										&emsp;[NOT STARTED]              - Track time spent<br>
										&emsp;[NOT STARTED]    	         - Use a real database backend<br>
									</p>
								</div>
							</div>
						</div>
					</div>
				</div>
		";
}

function cNewAccount() {
	$status = noHTML($_GET["s"]);
	if(strlen($status) >= 1) {
		$errorMessage = "
						<div class=\"alert alert-dismissible alert-info\">
							<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
							<h4>Result</h4>
							<p>" . $status . "</p>
						</div>
				";
	}
	return "
				<script type=\"text/javascript\">
					function createAccount() {
						var username = document.getElementById('txtUsername').value;
						var password = document.getElementById('txtPassword').value;
						var passwordConfirm = document.getElementById('txtPasswordConfirm').value;

						if(password != passwordConfirm) {
							alert(\"Passwords are not the same!\");
						} else {
							var usernameHash = forge.md.sha512.create();
							usernameHash.update(username + \"" . $GLOBALS['accountSalt'] . "\");
							var usernameOut = forge.util.encode64(usernameHash.digest().toHex());
							usernameOut = usernameOut.substring(0, usernameOut.length-1);

							var passwordHash = forge.md.sha512.create();
							passwordHash.update(password + \"" . $GLOBALS['accountSalt'] . "\");
							var passwordOut = forge.util.encode64(passwordHash.digest().toHex());
							passwordOut = passwordOut.substring(0, passwordOut.length-1);

							var req = new XMLHttpRequest();
							req.open(\"POST\", \"manage.php\", true);
							req.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");
							req.send(\"a=createAccount&usernameRaw=\" + username + \"&username=\" + usernameOut + \"&password=\" + passwordOut + \"&ct=" . $_SESSION['csrfToken'] . "\");
							setTimeout(function() {window.location.href = \"index.php?p=AM:%20New%20Account&s=\" + req.responseText}, 500);
						}
					}
				</script>
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"col-md-4 col-md-offset-4\">
								" . $errorMessage . "
								<form role=\"form\" name=\"frmLogin\" method=\"post\" action=\"javascript:createAccount();\">
									<div class=\"form-group\">
										<label for=\"txtUsername\">Username</label>
										<input type=\"username\" class=\"form-control\" id=\"txtUsername\" placeholder=\"Username\" autocomplete=\"off\" autofocus>
									</div>
									<div class=\"form-group\">
										<label for=\"txtPassword\">Password</label>
										<input type=\"password\" class=\"form-control\" id=\"txtPassword\" placeholder=\"Password\" autocomplete=\"off\">
									</div>
									<div class=\"form-group\">
										<label for=\"txtPassword\">Confirm Password</label>
										<input type=\"password\" class=\"form-control\" id=\"txtPasswordConfirm\" placeholder=\"Password\" autocomplete=\"off\">
									</div>
									<button type=\"submit\" class=\"btn btn-default\">Submit</button>
								</form>
							</div>
						</div>
					</div>
				</div>
		";
}

function cMyAccount() {
	$status = noHTML($_POST["s"]);
	if(strlen($status) >= 1) {
		$statusMessage = "
						<div class=\"alert alert-dismissible alert-info\">
							<button type=\"button\" class=\"close\" data-dismiss=\"alert\">×</button>
							<h4>Result</h4>
							<p>" . $status . "</p>
						</div>
				";
	}
	return "
				<script type=\"text/javascript\">

				</script>
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"col-md-4 col-md-offset-4\">
								" . $statusMessage . "
								<h3>Current Password</h3>
								<input type=\"password\" class=\"form-control\" id=\"txtPassword\" placeholder=\"Current Password\" autocomplete=\"off\" autofocus>
							</div>
						</div>
						<div class=\"row\">
							<div class=\"col-sm-6 col-md-4\">
								<h3>Change Password</h3>
								<form role=\"form\" name=\"frmChangePassword\" method=\"post\" action=\"javascript:changePassword()\">
									<div class=\"form-group\">
										<label for=\"txtChangePasswordNewPassword\">New Password</label>
										<input type=\"password\" class=\"form-control\" id=\"txtChangePasswordNewPassword\" placeholder=\"New Password\" autocomplete=\"off\">
									</div>
									<div class=\"form-group\">
										<label for=\"txtChangePasswordNewPasswordConfirm\">Confirm New Password</label>
										<input type=\"password\" class=\"form-control\" id=\"txtChangePasswordNewPasswordConfirm\" placeholder=\"Confirm New Password\" autocomplete=\"off\">
									</div>
									<button type=\"submit\" class=\"btn btn-default\">Change Password</button>
								</form>
							</div>
							<div class=\"col-sm-6 col-md-4\">
								<h3>Enable 2FA</h3>
								<form role=\"form\" name=\"frmEnabledTfa\" method=\"post\" action=\"javascript:enableTfa()\">
									<div class=\"form-group\">
										<label for=\"txtEnableTfaToken\">2FA Token</label>
										<input type=\"text\" class=\"form-control\" id=\"txtEnableTfaToken\" placeholder=\"2FA Token\" autocomplete=\"off\">
									</div>
									<button type=\"submit\" class=\"btn btn-default\">Enable 2FA</button>
								</form>
							</div>
							<div class=\"col-sm-6 col-md-4\">
								<h3>Disable 2FA</h3>
								<form role=\"form\" name=\"frmDisableTfa\" method=\"post\" action=\"javascript:disableTfa()\">
									<div class=\"form-group\">
										<label for=\"txtDisableTfaToken\">Current 2FA Token</label>
										<input type=\"text\" class=\"form-control\" id=\"txtDisableTfaToken\" placeholder=\"Current 2FA Token\" autocomplete=\"off\">
									</div>
									<button type=\"submit\" class=\"btn btn-default\">Disable 2FA</button>
								</form>
							</div>
						</div>
					</div>
				</div>
		";
}

function cNewLandscape() {
	return "
				<script type=\"text/javascript\">
					function newLandscape() {
						var landscape = document.getElementById('txtLandscape').value
						if(landscape != null && landscape.length >= 1) {
							var req = new XMLHttpRequest();
							req.open(\"POST\", \"manage.php\", true);
							req.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");
							req.send(\"a=createLandscape&l=\" + landscape + \"&ct=" . $_SESSION['csrfToken'] . "\");
							setTimeout(function() {window.location.href = \"index.php?p=L:%20\" + landscape}, 500);
						}
					}
				</script>
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"col-md-4 col-md-offset-4\">
								<form role=\"form\" name=\"frmNewLandscape\" method=\"post\" action=\"javascript:newLandscape();\">
									<div class=\"form-group\">
										<label for=\"txtLandscape\">Landscape Name</label>
										<input type=\"text\" class=\"form-control\" id=\"txtLandscape\" placeholder=\"Landscape Name\" autocomplete=\"off\" autofocus>
									</div>
									<div class=\"form-group\">
										<label for=\"txtUniqueViewerGoal\">Unique Viewer Goal</label>
										<input type=\"number\"class=\"form-control\" id=\"txtUniqueViewerGoal\" placeholder=\"Unique Viewer Goal\" autocomplete=\"off\" disabled>
									</div>
									<div class=\"form-group\">
										<label for=\"txtTotalViewsGoal\">Total Views Goal</label>
										<input type=\"number\"class=\"form-control\" id=\"txtTotalViewsGoal\" placeholder=\"Total Views Goal\" autocomplete=\"off\" disabled>
									</div>
									<div class=\"form-group\">
										<label for=\"txtCustomFieldsCount\">Custom Fields Count</label>
										<input type=\"number\"class=\"form-control\" id=\"txtCustomFieldsCount\" placeholder=\"0\" autocomplete=\"off\" disabled>
									</div>
									<button type=\"submit\" class=\"btn btn-default\">Create</button>
								</form>
							</div>
						</div>
					</div>
				</div>
		";
}

function cAnalytics($landscape) {
	$views = countLines($GLOBALS['landscapePath'] . $landscape . "/Analytics-Raw.csv");
	$users = countLines($GLOBALS['landscapePath'] . $landscape . "/Analytics-Users.log");
	$clientsDB = file($GLOBALS['landscapePath'] . $landscape . "/Analytics-Raw.csv", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
	$clientsDB = array_reverse($clientsDB);
	date_default_timezone_set($GLOBALS['defaultTimeZone']);
	$counter = 0;
	$crawlers = 0;
	$personal = 0;
	foreach($clientsDB as $client) {
		if($counter > 250) {
			break;
		}
		$clientInfo = explode("|", $client);
		if($clientInfo[9] == 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' || $clientInfo[9] == 'Mozilla/5.0 (compatible; CloudFlare-AlwaysOnline/1.0; +http://www.cloudflare.com/always-online) AppleWebKit/534.34' || startsWith($clientInfo['5'], '204.79.180') || startsWith($clientInfo['5'], '66.102.6')) {
			$crawlers++;
		} else if(startsWith($clientInfo['5'], $_SERVER['REMOTE_ADDR'])) {
			$personal++;
		} else {
			$ipLookupTitle = $clientInfo[5];
			if($GLOBALS['trimIPvSix'] && strlen($clientInfo[5]) > 16) {
				$ipLookupTitle = "Lookup";
			}
			$clients = $clients . "
									<tr>
										<td><canvas width=\"34\" height=\"34\" data-jdenticon-hash=\"" . $clientInfo[0] . "\" title=\"" . $clientInfo[0] . "\"></canvas></td>
										<td><canvas width=\"34\" height=\"34\" data-jdenticon-hash=\"" . $clientInfo[1] . "\" title=\"" . $clientInfo[1] . "\"></canvas></td>
										<td><a href=\"" . $clientInfo[2] . "\" target=\"_blank\">View</a></td>
										<td>" . $clientInfo[3] . "</td>
										<td>" . date('Y/m/d h:i:s A', $clientInfo[4]) . "</td>
										<td><a href=\"" . $GLOBALS['ipLookupURL'] . $clientInfo[5] . "\" target=\"_blank\" >" . $ipLookupTitle . "</a></td>
										<td>" . $clientInfo[6] . "</td>
										<script>var parser = new UAParser();parser.setUA(\"" . $clientInfo[9] . "\");var result = parser.getResult();var os = result.os.name + \" \" + result.os.version;if(os == \"undefined undefined\"){os = \"Unknown\";}var device = result.device.vendor + \" \" + result.device.model; if(device == \"undefined undefined\"){device = \"Unknown\";}var browser = result.browser.name + \" \" + result.browser.version;if(browser == \"undefined undefined\"){browser = \"Unknown\";}document.write(\"<td title='\" + parser.getUA() + \"'>\" + browser + \"</td><td title='\" + parser.getUA() + \"'>\" + device + \"</td><td title='\" + parser.getUA() + \"'>\" + os + \"</td>\");</script>
									</tr>
					";
			$counter++;
		}
	}
	$autoRefresh = "false";
	$autoRefreshStatus = "btn-danger";
	if(strlen($_COOKIE["autoRefresh"]) >= 1) {
		$autoRefresh = noHTML($_COOKIE["autoRefresh"]);
	}
	if($autoRefresh == "true") {
		$autoRefreshStatus = "btn-success";
	}
	return "
				<script type=\"text/javascript\">
					var landscape = \"" . $landscape . "\";
					if(" . $autoRefresh . ") {
						autoRefresh();
					}
					function autoRefresh() {
						setTimeout(function() {
							if(document.visibilityState == \"visible\" && document.scrollingElement.scrollTop == 0) {
								window.location.href = \"index.php?p=L:%20\" + landscape
							}
						}, 30000);
						autoRefresh();
					}
					function toggleAutoRefresh() {
						if(" . $autoRefresh . " == true)
							document.cookie = \"autoRefresh=false\";
						if(" . $autoRefresh . " == false)
							document.cookie = \"autoRefresh=true\";
						setTimeout(function() {window.location.href = \"index.php?p=L:%20\" + landscape}, 500);
					}
					function clearLandscape() {
						if(window.confirm(\"Are you sure you want to clear this landscape?\")) {
							var req = new XMLHttpRequest();
							req.open(\"POST\", \"manage.php\", true);
							req.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");
							req.send(\"a=clear&l=\" + landscape + \"&ct=" . $_SESSION['csrfToken'] . "\");
							setTimeout(function() {window.location.href = \"index.php?p=L:%20\" + landscape}, 500);
						}
					}
					function renameLandscape(){
						var landscapeNew = prompt(\"What would you like to rename this landscape to?\", landscape);
						if(landscapeNew != null && landscapeNew != landscape) {
							var req = new XMLHttpRequest();
							req.open(\"POST\", \"manage.php\", true);
							req.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");
							req.send(\"a=rename&l=\" + landscape + \"&ln=\" + landscapeNew + \"&ct=" . $_SESSION['csrfToken'] . "\");
							setTimeout(function() {window.location.href = \"index.php?p=L:%20\" + landscapeNew}, 500);
						}
					}
					function deleteLandscape() {
						if(window.confirm(\"Are you sure you want to delete this landscape?\")) {
							var req = new XMLHttpRequest();
							req.open(\"POST\", \"manage.php\", true);
							req.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\");
							req.send(\"a=delete&l=\" + landscape + \"&ct=" . $_SESSION['csrfToken'] . "\");
							setTimeout(function() {window.location.href = \"index.php?p=Dashboard\"}, 500);
						}
					}
				</script>
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"col-sm-6 col-md-4\">
								<div class=\"panel panel-info\">
									<div class=\"panel-heading\">
										<h3 class=\"panel-title\">Popularity Contest</h3>
									</div>
									<div class=\"panel-body\">
										Crawler Views: " . $crawlers . "<br>
										Personal Views: " . $personal . "<br>
										Most Popular Browser: [TODO]<br>
										Most Popular Country: [TODO]<br>
										Most Popular ISP: [TODO]<br>
										Most Popular OS: [TODO]<br>
										Most Popular Page: [TODO]
									</div>
								</div>
							</div>
							<div class=\"col-sm-6 col-md-4\">
								<div class=\"panel panel-info\">
									<div class=\"panel-heading\">
										<h3 class=\"panel-title\">Goals</h3>
									</div>
									<div class=\"panel-body\">
										<h4>Unique Users: " . $users . "/250</h4>
										<div class=\"progress progress-striped active\">
											<div class=\"progress-bar progress-bar-info\" style=\"width: " . ($users/250)*100 . "%\"></div>
										</div>
										<h4>Total Views: " . ($views-$crawlers-$personal) . "/1,000</h4>
										<div class=\"progress progress-striped active\">
											<div class=\"progress-bar progress-bar-info\" style=\"width: " . (($views-$crawlers-$personal)/1000)*100 . "%\"></div>
										</div>
									</div>
								</div>
							</div>
							<div class=\"col-sm-6 col-md-4\">
								<div class=\"panel panel-primary\">
									<div class=\"panel-heading\">
										<h3 class=\"panel-title\">Management</h3>
									</div>
									<div class=\"panel-body\">
										<div class=\"btn-group\" role=\"group\">
											<button type=\"button\" class=\"btn " . $autoRefreshStatus . "\" onclick=\"toggleAutoRefresh()\">Refresh</button>
											<button type=\"button\" class=\"btn btn-primary\" data-toggle=\"collapse\" data-target=\"#tracking-code-snippets\">Code</button>
										</div>
										<br><br>
										<div class=\"btn-group\" role=\"group\">
											<button type=\"button\" class=\"btn btn-warning\" onclick=\"clearLandscape()\">Clear</button>
											<button type=\"button\" class=\"btn btn-warning\" onclick=\"renameLandscape()\">Rename</button>
											<button type=\"button\" class=\"btn btn-danger\" onclick=\"deleteLandscape()\">Delete</button>
										</div>

									</div>
								</div>
							</div>
						</div>
					</div>
					<div class=\"container\">
						<div class=\"row collapse\" id=\"tracking-code-snippets\">
							<h2>Code Snippets</h2>
							<h3>Javascript</h3>
							<p><pre><code>&lt;script type=\"text/javascript\" src=\"" . $GLOBALS['baseURL'] . "assets/js/fingerprint2.min.js\"&gt;<br>&lt;/script&gt;&lt;script type=\"text/javascript\"&gt;new Fingerprint2().get(function(result, components){var atr = new XMLHttpRequest(); atr.open(\"POST\", \"" . $GLOBALS['baseURL'] . "shadow.php\", true); atr.setRequestHeader(\"Content-Type\", \"application/x-www-form-urlencoded\"); atr.send(\"p=" . $landscape . "&fpid=\" + result + \"&ref=\" + document.referrer.split('/')[2]);});&lt;/script&gt;</code></pre></p>
							<h3>Java</h3>
							<p><pre><code>HttpURLConnection shadowRequest = (HttpURLConnection) new URL(\"" . $GLOBALS['baseURL'] . "shadow.php?p=" . $landscape . "&fpid=\" + UUID).openConnection();//Replace UUID with constant UUID stored locally<br>shadowRequest.setConnectTimeout(5000);<br>shadowRequest.setReadTimeout(5000);<br>shadowRequest.addRequestProperty(\"User-Agent\", \"Application/Version\");//Replace app and version with actual app and version<br>shadowRequest.connect();<br></code></pre></p>
						</div>
						<div class=\"row\">
							<h2>Requests</h2>
							<div class=\"table-responsive\">
								<table class=\"table table-striped table-hover table-condensed\">
									<thead>
										<tr>
											<td>Passive ID</td>
											<td>Active ID</td>
											<td>Page</td>
											<td>Referrer</td>
											<td>Time Stamp</td>
											<td>IP Address</td>
											<td>GeoIP</td>
											<td>Browser</td>
											<td>Device</td>
											<td>Operating System</td>

										</tr>
									</thead>
									<script src=\"assets/js/ua-parser.min.js\" type=\"text/javascript\"></script>
									<script defer src=\"assets/js/jdenticon.min.js\" type=\"text/javascript\"></script>
									<tbody>
									" . $clients . "
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
		";
}

function cAbout() {
	return "
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<div class=\"col-sm-6 col-md-4\">
								<div class=\"panel panel-info\">
									<div class=\"panel-heading\">
										<h3 class=\"panel-title\">Project Credits</h3>
									</div>
									<div class=\"panel-body\">
										Divested Computing Group
									</div>
								</div>
							</div>
							<div class=\"col-sm-6 col-md-4\">
								<div class=\"panel panel-info\">
									<div class=\"panel-heading\">
										<h3 class=\"panel-title\">Apache/JS/PHP Credits</h3>
									</div>
									<div class=\"panel-body\">
										" . cLink("Bootstrap (MIT)", "https://github.com/twbs/bootstrap", "nofollow") . "<br>" . cLink("FingerprintJS2 (MIT)", "https://github.com/fingerprintjs/fingerprintjs", "nofollow") . "<br>" . cLink("Forge (BSD-2-clause or GPL-2)", "https://github.com/digitalbazaar/forge", "nofollow") . "<br>" . cLink("Jdenticon (MIT)", "https://github.com/dmester/jdenticon", "nofollow") . "<br>" . cLink("jQuery (MIT)", "https://jquery.com", "nofollow") . "<br>" . cLink("MaxMindDB (Apache-2.0)", "https://github.com/maxmind/mod_maxminddb", "nofollow") . "<br>" . cLink("UA-Parser-JS (MIT)", "https://github.com/faisalman/ua-parser-js", "nofollow") . "
									</div>
								</div>
							</div>
							<div class=\"col-sm-6 col-md-4\">
								<div class=\"panel panel-info\">
									<div class=\"panel-heading\">
										<h3 class=\"panel-title\">CSS/Font Credits</h3>
									</div>
									<div class=\"panel-body\">
										" . cLink("Bootstrap (MIT)", "https://github.com/twbs/bootstrap", "nofollow") . "<br>" . cLink("Bootswatch (MIT)", "https://github.com/thomaspark/bootswatch", "nofollow") . "
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>";
}

function cBottom() {
	return "
				<div class=\"container-fluid\">
					<div class=\"container\">
						<div class=\"row\">
							<hr>
							<footer>
								<p>Divested Computing Group &copy; 2015-2021</p>
							</footer>
						</div>
					</div>
				</div>

				<script src=\"assets/js/jquery-3.6.4.min.js\" type=\"text/javascript\"></script>
				<script src=\"assets/js/bootstrap.min.js\" type=\"text/javascript\"></script>
				<script defer src=\"assets/js/forge.min.js\" type=\"text/javascript\"></script>
			</body>

		</html>
		";
}
//END OF SITE FUNCTIONS

?>
