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

ini_set("allow_url_fopen", "Off");
ini_set("allow_url_include", "Off");
ini_set("display_errors", "Off");
ini_set("file_uploads", "Off");
ini_set("post_max_size", "4K");
ini_set("session.cookie_httponly", "true");
ini_set("session.cookie_lifetime", "0");
ini_set("session.cookie_secure", "1");
ini_set("session.entropy_length", "64");
ini_set("session.hash_bits_per_character", "5");
ini_set("session.hash_function", "sha512");
ini_set("session.name", "aeraitheephiyaeh");
ini_set("session.referer_check", "divested.dev");
ini_set("session.use_cookies", "1");
ini_set("session.use_only_cookies", "1");
ini_set("session.use_strict_mode", "1");
ini_set("session.use_trans_sid", "0");

$baseURL = "https://divested.dev/shadow/"; //STRING The default URL used for requests
$dataPath = "/var/www/secrets/shadow/"; //STRING The root path to the data store
$landscapePath = $dataPath . "landscape/"; //STRING The path to the landscape storage
$accountsFile = $dataPath . "accounts.shd"; //STRING The path to the accounts database
$authFile = $dataPath . "auth_tokens.shd"; //STRING The path to the account sessions store
$accountSalt = 'TEdhvARuvxtdCCEbF2YzkxbrHoohxqfHRAdqMx3JTduXAnIIqOih2DM1BqND8y1O'; //STRING Salt added to stored accounts.
$authTokenCookieName = 'seizuvahthiceemi'; //STRING The cookie where the users authentication token will be stored
$captchaEnabled = true && extension_loaded('gd'); //BOOLEAN Whether or not the captcha is enabled on the login page
$defaultPage = "Dashboard"; //STRING The default page to be presented
$defaultTheme = "darkly.min.css"; //STRING The default theme to be used
$defaultTimeZone = "America/New_York"; //STRING The default time zone
$honorDNT = true; //BOOLEAN Whether or not to respect the user's Do Not Track preference
$ipLookupURL = "https://invalid.invalid/"; //STRING The URL for looking up an IP address
$maxSessionsPerUser = 4; //INTEGER The maximum sessions per user allowed at a time
$tfaRequired = 1; //INTEGER:0-2 Whether or not 2fa is required. 0 == No, 1 == Admins Only, 2 == Yes
$trimIPvSix = true; //BOOLEAN Whether or not to trim IPv6 addresses

?>
