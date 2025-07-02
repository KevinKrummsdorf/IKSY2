## Projekt-Audit (2025-07-02)

### ab.txt
```
This is ApacheBench, Version 2.3 <$Revision: 1903618 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking localhost (be patient)


Server Software:        Apache/2.4.58
Server Hostname:        localhost
Server Port:            80

Document Path:          /
Document Length:        10918 bytes

Concurrency Level:      25
Time taken for tests:   0.430 seconds
Complete requests:      500
Failed requests:        0
Total transferred:      5596000 bytes
HTML transferred:       5459000 bytes
Requests per second:    1162.03 [#/sec] (mean)
Time per request:       21.514 [ms] (mean)
Time per request:       0.861 [ms] (mean, across all concurrent requests)
Transfer rate:          12700.61 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:        0    3   4.5      2      25
Processing:     3   13  13.0      9     133
Waiting:        3   11  11.8      8     100
Total:          5   16  16.2     11     138

Percentage of the requests served within a certain time (ms)
  50%     11
  66%     14
  75%     16
  80%     19
  90%     24
  95%     43
  98%     75
  99%    106
 100%    138 (longest request)
```

### duplication.txt
```
phpcpd 6.0.3 by Sebastian Bergmann.

No clones found.

Time: 00:00.002, Memory: 6.00 MB
```

### infection.txt
```

    ____      ____          __  _
   /  _/___  / __/__  _____/ /_(_)___  ____
   / // __ \/ /_/ _ \/ ___/ __/ / __ \/ __ \
 _/ // / / / __/  __/ /__/ /_/ / /_/ / / / /
/___/_/ /_/_/  \___/\___/\__/_/\____/_/ /_/

#StandWithUkraine

Infection - PHP Mutation Testing Framework version 0.29.14

```

### metrics.txt
```
phpmetrics not installed (composer global require phpmetrics/phpmetrics)
```

### outdated.txt
```
phpmetrics/phpmetrics  v3.0.0rc8 ~ dev-master c43217c Static analyzer tool for PHP : Coupling, ...
phpunit/phpunit        3.7.32    ~ 12.2.5             The PHP Unit Testing framework.
robthree/twofactorauth 1.8.0     ~ v3.0.2             Two Factor Authentication
sebastian/phpcpd       6.0.3     = 6.0.3              Copy/Paste Detector (CPD) for PHP code.
Package sebastian/phpcpd is abandoned, you should avoid using it. No replacement was suggested.
twbs/bootstrap         v5.3.6    ! v5.3.7             The most popular front-end framework for ...
```

### phpcs.txt
```

FILE: /var/www/html/iksy05/StudyHub/src/PasswordController.php
---------------------------------------------------------------------------------------------------
FOUND 2 ERRORS AND 1 WARNING AFFECTING 2 LINES
---------------------------------------------------------------------------------------------------
 1 | WARNING | [ ] A file should declare new symbols (classes, functions, constants, etc.) and
   |         |     cause no other side effects, or it should execute logic with side effects, but
   |         |     should not do both. The first symbol is defined on line 5 and the first side
   |         |     effect is on line 2.
 1 | ERROR   | [x] Header blocks must be separated by a single blank line
 5 | ERROR   | [ ] Each class must be in a namespace of at least one level (a top-level vendor
   |         |     name)
---------------------------------------------------------------------------------------------------
PHPCBF CAN FIX THE 1 MARKED SNIFF VIOLATIONS AUTOMATICALLY
---------------------------------------------------------------------------------------------------

Time: 298ms; Memory: 12MB

```

### phpstan.txt
```
/var/www/html/iksy05/StudyHub/src/PasswordController.php:9:Call to static method fetchUserByIdentifier() on an unknown class DbFunctions.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:15:Call to static method storePasswordResetToken() on an unknown class DbFunctions.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:16:Call to static method db_connect() on an unknown class DbFunctions.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:16:Function sendPasswordResetEmail not found.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:21:Call to static method fetchPasswordResetUser() on an unknown class DbFunctions.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:25:Function password_meets_requirements not found.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:29:Call to static method updatePassword() on an unknown class DbFunctions.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:30:Call to static method deletePasswordResetToken() on an unknown class DbFunctions.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:31:Call to static method db_connect() on an unknown class DbFunctions.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:31:Function sendPasswordResetSuccessEmail not found.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:36:Call to static method fetchUserById() on an unknown class DbFunctions.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:37:Function verifyPassword not found.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:40:Function password_meets_requirements not found.
/var/www/html/iksy05/StudyHub/src/PasswordController.php:44:Call to static method updatePassword() on an unknown class DbFunctions.
```

### phpunit_security.txt
```
Kein PHPUnit-Config-File gefunden – Test-Suite übersprungen
```

### raw_superglobals.txt
```
```

### zap.txt
```
npm ERR! code E404
npm ERR! 404 Not Found - GET https://registry.npmjs.org/@zaproxy%2fzap-cli - Not found
npm ERR! 404 
npm ERR! 404  '@zaproxy/zap-cli@*' is not in this registry.
npm ERR! 404 
npm ERR! 404 Note that you can also install from a
npm ERR! 404 tarball, folder, http url, or git url.

npm ERR! A complete log of this run can be found in:
npm ERR!     /home/iksy/.npm/_logs/2025-07-02T08_40_52_247Z-debug-0.log
```

