Shadow
======

Shadow is a PHP based analytics system.

Features
--------
- Lightweight
- 'Basic' and 'Active' fingerprinting
- Respects 'Do Not Track'
- Captcha for login
- Basic two-factor support
- Multiple "landscapes" ie. different websites or apps
- Multiple account support
- CSV databases!

Status
------
- Many functions are not implemented.
- There are no permissions.
- Security is likely lacking.
- The code is an absolute mess.

Other things
------------
- This was originally a page hit counter from a blog system.
- Most of this code is from 2015/2016.
- There was an unfinished recode started in 2016.
- It might be rebased onto SBNR someday.
- Please don't re-use the account login code. Something something "don't roll your own crypto"... :)

Running
-------
0. Question your sanity
1. Consider using an actually well-implemented analytics system unlike this one
2. `dnf install httpd mod_ssl mod_session php php-gd php-mbstring`
3. git clone this into `/var/www/shadow.domain.tld`
4. Put some .ttf fonts into `/var/www/shadow.domain.tld/captcha_fonts`
5. `mkdir -p /var/www/secrets/shadow/landscape`
6. `cp /var/www/shadow.domain.tld/accounts.shd.example /var/www/secrets/shadow/accounts.shd`
7. `chown apache:apache /var/www/secrets/ -R`
8. SELinux only: `chcon -R -t httpd_sys_rw_content_t /var/www/secrets/`
9. Optional: Install and configure mod_maxminddb for Apache/httpd to have GeoIP information
10. Create a VirtualHost for the domain.
11. Set `<FilesMatch "shadow.php"> Header always Set Access-Control-Allow-Origin "*" </FilesMatch>`
12. Navigate to `https://shadow.domain.tld` in your browser and login!
13. Default account is `admin:adminpassword`.

Credits
-------
See the `LICENSE` file or `About` page

Donate
-------
- https://divested.dev/donate
