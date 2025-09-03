-- Adminer 5.3.0 MariaDB 11.7.2-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `smf`;

DROP TABLE IF EXISTS `smf_admin_info_files`;
CREATE TABLE `smf_admin_info_files` (
  `id_file` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `parameters` varchar(255) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  `filetype` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_file`),
  KEY `idx_filename` (`filename`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_admin_info_files` (`id_file`, `filename`, `path`, `parameters`, `data`, `filetype`) VALUES
(1,	'current-version.js',	'/smf/',	'version=%3$s',	'window.smfVersion = \"SMF 2.1.6\";',	'text/javascript'),
(2,	'detailed-version.js',	'/smf/',	'language=%1$s&version=%3$s',	'',	'text/javascript'),
(3,	'latest-news.js',	'/smf/',	'language=%1$s&format=%2$s',	'\nwindow.smfAnnouncements = [\n	{\n		subject: \'SMF 2.1.6 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=592074.0\',\n		time: \'Jun 26, 2025, 03:39 PM\',\n		author: \'Sesquipedalian\',\n		message: \'SMF 2.1.6 contains fixes for a few minor (but annoying) bugs that were introduced in 2.1.5. We recommend updating as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.1.5 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=592035.0\',\n		time: \'Jun 24, 2025, 04:42 PM\',\n		author: \'Sesquipedalian\',\n		message: \'SMF 2.1.5 includes important security updates and many bug fixes. We recommend updating as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.1.4 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=586097.0\',\n		time: \'Jun 10, 2023, 05:21 PM\',\n		author: \'shawnb61\',\n		message: \'SMF 2.1.4 includes security updates and numerous bug fixes. We recommend updating as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.1.3 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=584230.0\',\n		time: \'Nov 21, 2022, 07:00 PM\',\n		author: \'shawnb61\',\n		message: \'SMF 2.1.3 includes security updates and numerous bug fixes. We recommend updating as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.1.2 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=582201.0\',\n		time: \'May 09, 2022, 04:33 PM\',\n		author: \'Sesquipedalian\',\n		message: \'SMF 2.1.2 includes security updates and numerous bug fixes. We recommend updating as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.1.1 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=580657.0\',\n		time: \'Feb 12, 2022, 01:25 AM\',\n		author: \'Sesquipedalian\',\n		message: \'SMF 2.1.1 restores support for PHP 7.0–7.2.\'\n	},\n	{\n		subject: \'SMF 2.1.0 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=580585.0\',\n		time: \'Feb 09, 2022, 05:45 PM\',\n		author: \'Sesquipedalian\',\n		message: \'SMF 2.1 is here! Please upgrade to start enjoying all the benefits of our new recommended version as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.0.19 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=579982.0\',\n		time: \'Dec 21, 2021, 09:45 PM\',\n		author: \'Sesquipedalian\',\n		message: \'SMF 2.0.19 includes security updates and several bug fixes. We recommend updating as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.1 RC4 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=578135.0\',\n		time: \'Jul 10, 2021, 03:14 PM\',\n		author: \'Suki\',\n		message: \'Simple Machines is pleased to announce SMF 2.1 RC4. This fourth release candidate brings a number of bugfixes and improvements over SMF 2.1 RC3.\'\n	},\n	{\n		subject: \'SMF 2.0.18 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=576577.0\',\n		time: \'Feb 01, 2021, 06:55 PM\',\n		author: \'Suki\',\n		message: \'SMF 2.0.18 adds compatibility to PHP 7.4 version as well as fixes a few bugs in 2.0.17. We recommend updating as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.1 RC3 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=575228.0\',\n		time: \'Oct 15, 2020, 10:16 AM\',\n		author: \'Suki\',\n		message: \'Simple Machines is pleased to announce SMF 2.1 RC3. This third release candidate brings a number of bugfixes and improvements over SMF 2.1 RC2.\'\n	},\n	{\n		subject: \'SMF 2.0.17 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=571067.0\',\n		time: \'Dec 31, 2019, 12:43 AM\',\n		author: \'Sesquipedalian\',\n		message: \'SMF 2.0.17 fixes a bug in 2.0.16 that could cause significant performance issues when retrieving RSS feeds, and fixes some warning messages that could appear when using SSI.php. We recommend updating as soon as possible.\'\n	},\n	{\n		subject: \'SMF 2.0.16 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=570986.0\',\n		time: \'Dec 28, 2019, 12:44 AM\',\n		author: \'Sesquipedalian\',\n		message: \'SMF 2.0.16 fixes some important security issues and adds support for the EU\\\'s General Data Protection Regulation (GDPR) requirements.\'\n	},\n	{\n		subject: \'SMF 2.1 RC2 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=566669.0\',\n		time: \'Mar 30, 2019, 04:27 PM\',\n		author: \'Sesquipedalian\',\n		message: \'Simple Machines is pleased to announce SMF 2.1 RC2. This second release candidate brings a number of bugfixes and improvements over SMF 2.1 RC1.\'\n	},\n	{\n		subject: \'SMF 2.1 RC1 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=564881.0\',\n		time: \'Feb 05, 2019, 01:02 AM\',\n		author: \'Sesquipedalian\',\n		message: \'Simple Machines is proud to announce the first release candidate of the next version of SMF, which contains many bugfixes and a number of new features since 2.1 Beta 3.\'\n	},\n	{\n		subject: \'SMF 2.0.15 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=557176.0\',\n		time: \'Nov 20, 2017, 02:03 AM\',\n		author: \'Colin\',\n		message: \'A patch has been released, addressing a few vulnerabilities in SMF 2.0.14 and fixing several bugs as well. We urge all forum administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.1 Beta 3 released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=554301.0\',\n		time: \'Jun 01, 2017, 01:21 AM\',\n		author: \'Colin\',\n		message: \'Simple Machines is proud to announce the third beta of the next version of SMF, which contains many bugfixes and a few new features since 2.1 Beta 2.\'\n	},\n	{\n		subject: \'SMF 2.0.14 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=553855.0\',\n		time: \'May 14, 2017, 09:23 PM\',\n		author: \'Colin\',\n		message: \'A patch has been released, addressing a few vulnerabilities in SMF 2.0.13 and fixing several bugs as well. We urge all forum administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.13 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=551061.0\',\n		time: \'Jan 05, 2017, 12:00 AM\',\n		author: \'Oldiesmann\',\n		message: \'A patch has been released, addressing a few vulnerabilities in SMF 2.0.12 and fixing several bugs as well. We urge all forum administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.12 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=548871.0\',\n		time: \'Sep 27, 2016, 11:00 AM\',\n		author: \'CoreISP\',\n		message: \'A patch has been released, addressing a vulnerability in SMF 2.0.11 and fixing several bugs as well. We urge all forum administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.11 has been released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=539888.0\',\n		time: \'Sep 19, 2015, 02:56 AM\',\n		author: \'Oldiesmann\',\n		message: \'A patch has been released, addressing a vulnerability in SMF 2.0.10. We urge all forum administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.1 Beta 2 released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=538198.0\',\n		time: \'Jul 16, 2015, 09:45 PM\',\n		author: \'Oldiesmann\',\n		message: \'Simple Machines is proud to announce the second beta of the next version of SMF, which contains many bugfixes and a few new features since 2.1 Beta 1!\'\n	},\n	{\n		subject: \'SMF 2.0.10 and 1.1.21 have been released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=535828.0\',\n		time: \'Apr 24, 2015, 02:09 PM\',\n		author: \'Oldiesmann\',\n		message: \'A patch has been released, addressing a few bugs in SMF 2.0.x and SMF 1.1.x. We urge all forum administrators to upgrade to SMF 2.0.10 or 1.1.21&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.1 Beta 1 released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=530233.0\',\n		time: \'Nov 21, 2014, 12:40 AM\',\n		author: \'Oldiesmann\',\n		message: \'Simple Machines is proud to announce the first beta of the next version of SMF, with many improvements and new features!\'\n	},\n	{\n		subject: \'SMF 2.0.9 and 1.1.20 security patches have been released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=528448.0\',\n		time: \'Oct 02, 2014, 11:13 PM\',\n		author: \'Oldiesmann\',\n		message: \'Critical security patches have been released, addressing a few vulnerabilities in SMF 2.0.x and SMF 1.1.x. We urge all administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.8 released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=524016.0\',\n		time: \'Jun 18, 2014, 02:11 PM\',\n		author: \'Oldiesmann\',\n		message: \'A patch has been released, addressing memory issues with 2.0.7, MySQL 5.6 compatibility issues and a rare memberlist search bug. We urge all forum administrators to upgrade to SMF 2.0.8&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.7 released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=517205.0\',\n		time: \'Jan 21, 2014, 02:48 AM\',\n		author: \'Oldiesmann\',\n		message: \'A patch has been released, addressing several bugs, including PHP 5.5 compatibility.  We urge all forum administrators to upgrade to SMF 2.0.7&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.6 and 1.1.19 security patches have been released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=512964.0\',\n		time: \'Oct 22, 2013, 01:00 PM\',\n		author: \'Illori\',\n		message: \'Critical security patches have been released, addressing few vulnerabilities in SMF 2.0.x and SMF 1.1.x. We urge all administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.5 security patches has been released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=509417.0\',\n		time: \'Aug 13, 2013, 12:34 AM\',\n		author: \'Oldiesmann\',\n		message: \'A critical security patch has been released, addressing a few vulnerabilities in SMF 2.0.x. We urge all administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.4 and 1.1.18 security patches have been released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=496403.0\',\n		time: \'Feb 01, 2013, 10:27 PM\',\n		author: \'emanuele\',\n		message: \'Critical security patches have been released, addressing few vulnerabilities in SMF 2.0.x and SMF 1.1.x. We urge all administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.3, 1.1.17 and 1.0.23 security patches have been released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=492786.0\',\n		time: \'Dec 17, 2012, 04:41 AM\',\n		author: \'emanuele\',\n		message: \'Security patches have been released, addressing a vulnerability in SMF 2.0.x, SMF 1.1.x and SMF 1.0.x. We urge all administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.2 and 1.1.16 security patches have been released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=463103.0\',\n		time: \'Dec 23, 2011, 05:41 AM\',\n		author: \'Norv\',\n		message: \'Critical security patches have been released, addressing vulnerabilities in SMF 2.0.x and SMF 1.1.x. We urge all administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0.1 and 1.1.15 security patches have been released.\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=452888.0\',\n		time: \'Sep 18, 2011, 08:48 PM\',\n		author: \'Norv\',\n		message: \'Critical security patches have been released, addressing vulnerabilities in SMF 2.0 and SMF 1.1.x. We urge all administrators to upgrade as soon as possible. Just visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0 Gold\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=421547.0\',\n		time: \'Jun 04, 2011, 09:00 PM\',\n		author: \'Norv\',\n		message: \'SMF 2.0 has gone Gold! Please upgrade your forum from older versions, as 2.0 is now the stable version, and mods and themes will be built on it.\'\n	},\n	{\n		subject: \'SMF 1.1.13, 2.0 RC4 security patch and SMF 2.0 RC5 released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=421547.0\',\n		time: \'Feb 11, 2011, 08:16 PM\',\n		author: \'Norv\',\n		message: \'Simple Machines announces the release of important security patches for SMF 1.1.x and SMF 2.0 RC4, along with the fifth Release Candidate of SMF 2.0. Please visit the Simple Machines site for more information on how you can help test this new release.\'\n	},\n	{\n		subject: \'SMF 2.0 RC4 and SMF 1.1.12 released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=407256.0\',\n		time: \'Nov 01, 2010, 04:14 PM\',\n		author: \'Norv\',\n		message: \'Simple Machines is pleased to announce the release of the fourth Release Candidate of SMF 2.0, along with an important security patch for SMF 1.1.x. Please visit the Simple Machines site for more information on how you can help test this new release.\'\n	},\n	{\n		subject: \'SMF 2.0 RC3 Public released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=369616.0\',\n		time: \'Mar 08, 2010, 11:03 PM\',\n		author: \'Aaron\',\n		message: \'Simple Machines is pleased to announce the release of the third Release Candidate of SMF 2.0. Please visit the Simple Machines site for more information on how you can help test this new release.\'\n	},\n	{\n		subject: \'SMF 1.1.11 released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=351341.0\',\n		time: \'Dec 01, 2009, 10:59 PM\',\n		author: \'SleePy\',\n		message: \'A patch has been released, addressing multiple vulnerabilites.  We urge all forum administrators to upgrade to 1.1.11. Simply visit the package manager to install the patch. Also for those still using the 1.0 branch, version 1.0.19 has been released.\'\n	},\n	{\n		subject: \'SMF 2.0 RC2 Public released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=346813.0\',\n		time: \'Nov 09, 2009, 12:10 AM\',\n		author: \'Aaron\',\n		message: \'Simple Machines is very pleased to announce the release of the second Release Candidate of SMF 2.0. Please visit the Simple Machines site for more information on how you can help test this new release.\'\n	},\n	{\n		subject: \'SMF 1.1.10 and 2.0 RC1.2 released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=324169.0\',\n		time: \'Jul 14, 2009, 11:05 PM\',\n		author: \'Compuart\',\n		message: \'A patch has been released, addressing a few security vulnerabilites.  We urge all forum administrators to upgrade to either 1.1.10 or 2.0 RC1.2, depending on the current version. Simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 1.1.9 and 2.0 RC1-1 released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=311899.0\',\n		time: \'May 21, 2009, 12:40 AM\',\n		author: \'Compuart\',\n		message: \'A patch has been released, addressing multiple security vulnerabilites.  We urge all forum administrators to upgrade to either 1.1.9 or 2.0 RC1-1, depending on the current version. Simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0 RC1 Public Released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=290609.0\',\n		time: \'Feb 05, 2009, 04:10 AM\',\n		author: \'Compuart\',\n		message: \'Simple Machines are very pleased to announce the release of the first Release Candidate of SMF 2.0. Please visit the Simple Machines site for more information on how you can help test this new release.\'\n	},\n	{\n		subject: \'SMF 1.1.8\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=290608.0\',\n		time: \'Feb 05, 2009, 04:08 AM\',\n		author: \'Compuart\',\n		message: \'A patch has been released, addressing multiple security vulnerabilites.  We urge all forum administrators to upgrade to SMF 1.1.8&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 1.1.7\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=272861.0\',\n		time: \'Nov 07, 2008, 07:15 PM\',\n		author: \'Compuart\',\n		message: \'A patch has been released, addressing multiple security vulnerabilites.  We urge all forum administrators to upgrade to SMF 1.1.7&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 1.1.6\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=260145.0\',\n		time: \'Sep 07, 2008, 08:38 AM\',\n		author: \'Compuart\',\n		message: \'A patch has been released fixing a few bugs and addressing a security vulnerability.  We urge all forum administrators to upgrade to SMF 1.1.6&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 1.1.5\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=236816.0\',\n		time: \'Apr 21, 2008, 01:56 AM\',\n		author: \'Compuart\',\n		message: \'A patch has been released fixing a few bugs and addressing some security vulnerabilities.  We urge all forum administrators to upgrade to SMF 1.1.5&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0 Beta 3 Public Released\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=228921.0\',\n		time: \'Mar 17, 2008, 07:20 PM\',\n		author: \'Grudge\',\n		message: \'Simple Machines are very pleased to announce the release of the first public beta of SMF 2.0. Please visit the Simple Machines site for more information on how you can help test this new release.\'\n	},\n	{\n		subject: \'SMF 1.1.4\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=196380.0\',\n		time: \'Sep 25, 2007, 01:07 AM\',\n		author: \'Compuart\',\n		message: \'A patch has been released to address some security vulnerabilities discovered in SMF 1.1.3.  We urge all forum administrators to upgrade to SMF 1.1.4&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 2.0 Beta 1 Released to Charter Members\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=190812.0\',\n		time: \'Aug 25, 2007, 11:29 AM\',\n		author: \'Grudge\',\n		message: \'Simple Machines are pleased to announce the first beta of SMF 2.0 has been released to our Charter Members. Visit the Simple Machines site for information on what\\\'s new\'\n	},\n	{\n		subject: \'SMF 1.1.3\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=178757.0\',\n		time: \'Jun 25, 2007, 01:52 AM\',\n		author: \'Thantos\',\n		message: \'A number of small bugs and a potential security issue have been discovered in SMF 1.1.2.  We urge all forum administrators to upgrade to SMF 1.1.3&mdash;simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 1.1.2\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=149553.0\',\n		time: \'Feb 11, 2007, 01:35 PM\',\n		author: \'Grudge\',\n		message: \'A patch has been released to address a number of outstanding bugs in SMF 1.1.1, including several around UTF-8 language support. In addition this patch offers improved image verification support and fixes a couple of low risk security related bugs. If you need any help upgrading please visit our forum.\'\n	},\n	{\n		subject: \'SMF 1.1.1\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=134971.0\',\n		time: \'Dec 17, 2006, 02:33 PM\',\n		author: \'Grudge\',\n		message: \'A number of small bugs and a potential security issue have been discovered in SMF 1.1. We urge all forum administrators to upgrade to SMF 1.1.1 - simply visit the package manager to install the patch.\'\n	},\n	{\n		subject: \'SMF 1.1\',\n		href: \'https://www.simplemachines.org/community/index.php?topic=131008.0\',\n		time: \'Dec 02, 2006, 07:53 PM\',\n		author: \'Grudge\',\n		message: \'SMF 1.1 has gone gold!  If you are using an older version, please upgrade as soon as possible - many things have been changed and fixed, and mods and packages will expect you to be using 1.1.  If you need any help upgrading custom modifications to the new version, please feel free to ask us at our forum.\'\n	}\n];\nif (window.smfVersion < \"SMF 2.1\")\n{\n	window.smfUpdateNotice = \'SMF 2.1.0 has now been released. To take advantage of the improvements available in SMF 2.1 we recommend upgrading as soon as is practical.\';\n	window.smfUpdateCritical = false;\n}\n\nif (document.getElementById(\"yourVersion\"))\n{\n	var yourVersion = getInnerHTML(document.getElementById(\"yourVersion\"));\n	if (yourVersion == \"SMF 1.0.4\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_1-0-5_package.tar.gz\";\n	else if (yourVersion == \"SMF 1.0.5\" || yourVersion == \"SMF 1.0.6\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.7_1.1-RC2-1.tar.gz\";\n		window.smfUpdateCritical = false;\n	}\n	else if (yourVersion == \"SMF 1.0.7\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_1-0-8_package.tar.gz\";\n	else if (yourVersion == \"SMF 1.0.8\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1-0-9_1-1-rc3-1.tar.gz\";\n	else if (yourVersion == \"SMF 1.0.9\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_1-0-10_patch.tar.gz\";\n	else if (yourVersion == \"SMF 1.0.10\" || yourVersion == \"SMF 1.1.2\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.1.3_1.0.11.tar.gz\";\n	else if (yourVersion == \"SMF 1.0.11\" || yourVersion == \"SMF 1.1.3\" || yourVersion == \"SMF 2.0 beta 1\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.12_1.1.4_2.0.b1.1.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.0.12\" || yourVersion == \"SMF 1.1.4\" || yourVersion == \"SMF 2.0 beta 3 Public\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.13_1.1.5_2.0-b3.1.zip\";\n	else if (yourVersion == \"SMF 1.0.13\" || yourVersion == \"SMF 1.1.5\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.14_1.1.6.zip\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.0.14\" || yourVersion == \"SMF 1.1.6\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.15_1.1.7.zip\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.0.15\" || yourVersion == \"SMF 1.1.7\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.16_1.1.8.zip\";\n		window.smfUpdateCritical = false;\n	}\n	else if (yourVersion == \"SMF 1.0.16\" || yourVersion == \"SMF 1.1.8\" || yourVersion == \"SMF 2.0 RC1\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.17_1.1.9_2.0-RC1-1.zip\";\n	else if (yourVersion == \"SMF 1.0.17\" || yourVersion == \"SMF 1.1.9\" || yourVersion == \"SMF 2.0 RC1-1\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.18_1.1.10-2.0-RC1.2.zip\";\n	else if (yourVersion == \"SMF 1.0.18\" || yourVersion == \"SMF 1.1.10\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.19_1.1.11.zip\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.0.19\" || yourVersion == \"SMF 1.1.11\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.20_1.1.12.tar.gz\";\n	}\n	else if (yourVersion == \"SMF 1.0.20\" || yourVersion == \"SMF 1.1.12\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.21_1.1.13.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.1.14\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.1.15.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.1.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.1.15\" || yourVersion == \"SMF 1.0.21\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.22_1.1.16.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.1\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.2.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.1.16\" || yourVersion == \"SMF 1.0.22\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.0.23_1.1.17.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.1.17\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.1.18.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.2\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.3.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.3\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.4.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.4\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.5.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.1.18\" || yourVersion == \"SMF 2.0.5\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.1.19_2.0.6.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.1.19\" || yourVersion == \"SMF 2.0.8\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.1.20_2.0.9.zip\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.1.20\" || yourVersion == \"SMF 2.0.9\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_1.1.21_2.0.10.zip\";\n	else if (yourVersion == \"SMF 2.0.10\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.11.zip\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 1.1\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_1-1-1_patch.tar.gz\";\n	else if (yourVersion == \"SMF 1.1.1\")\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_1-1-2_patch.tar.gz\";\n	else if (yourVersion == \"SMF 2.0.11\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.12.zip\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.12\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.13.zip\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.13\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.14.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.14\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.15.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.15\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.16.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.16\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.17.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.17\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.18.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.0.18\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_patch_2.0.19.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.1.0\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_2-1-1_patch.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.1.1\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_2-1-2_patch.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.1.2\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_2-1-3_patch.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.1.3\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_2-1-4_patch.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.1.4\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_2-1-5_patch.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n	else if (yourVersion == \"SMF 2.1.5\")\n	{\n		window.smfUpdatePackage = \"http://custom.simplemachines.org/mods/downloads/smf_2-1-6_patch.tar.gz\";\n		window.smfUpdateCritical = true;\n	}\n}\n\nif (document.getElementById(\'credits\'))\n	setInnerHTML(document.getElementById(\'credits\'), getInnerHTML(document.getElementById(\'credits\')).replace(/anyone we may have missed/, \'<span title=\"And you thought you had escaped the credits, hadn\\\'t you, Zef Hemel?\">anyone we may have missed</span>\'));\n\n',	'text/javascript'),
(4,	'latest-versions.txt',	'/smf/',	'version=%3$s',	'[\"SMF 2.0 RC2\", \"SMF 2.0 RC3\", \"SMF 2.0 RC4\", \"SMF 2.0 RC5\", \"SMF 2.0\", \"SMF 2.0.1\", \"SMF 2.0.2\", \"SMF 2.0.3\", \"SMF 2.0.4\", \"SMF 2.0.5\", \"SMF 2.0.6\", \"SMF 2.0.7\", \"SMF 2.0.8\", \"SMF 2.0.9\", \"SMF 2.0.10\", \"SMF 2.0.11\", \"SMF 2.0.12\", \"SMF 2.0.13\", \"SMF 2.0.14\", \"SMF 2.0.15\", \"SMF 2.0.16\", \"SMF 2.0.17\", \"SMF 2.0.18\", \"SMF 2.0.19\", \"SMF 2.1 Beta 1\", \"SMF 2.1 Beta 2\", \"SMF 2.1 Beta 3\", \"SMF 2.1 RC1\", \"SMF 2.1 RC2\", \"SMF 2.1 RC3\", \"SMF 2.1 RC4\", \"SMF 2.1.0\", \"SMF 2.1.1\", \"SMF 2.1.2\", \"SMF 2.1.3\", \"SMF 2.1.4\"]',	'text/plain');

DROP TABLE IF EXISTS `smf_approval_queue`;
CREATE TABLE `smf_approval_queue` (
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_attach` int(10) unsigned NOT NULL DEFAULT 0,
  `id_event` smallint(5) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_attachments`;
CREATE TABLE `smf_attachments` (
  `id_attach` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_thumb` int(10) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_folder` tinyint(4) NOT NULL DEFAULT 1,
  `attachment_type` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `file_hash` varchar(40) NOT NULL DEFAULT '',
  `fileext` varchar(8) NOT NULL DEFAULT '',
  `size` int(10) unsigned NOT NULL DEFAULT 0,
  `downloads` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `width` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `height` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `mime_type` varchar(128) NOT NULL DEFAULT '',
  `approved` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_attach`),
  UNIQUE KEY `idx_id_member` (`id_member`,`id_attach`),
  KEY `idx_id_msg` (`id_msg`),
  KEY `idx_attachment_type` (`attachment_type`),
  KEY `idx_id_thumb` (`id_thumb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_background_tasks`;
CREATE TABLE `smf_background_tasks` (
  `id_task` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_file` varchar(255) NOT NULL DEFAULT '',
  `task_class` varchar(255) NOT NULL DEFAULT '',
  `task_data` mediumtext NOT NULL,
  `claimed_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_task`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_ban_groups`;
CREATE TABLE `smf_ban_groups` (
  `id_ban_group` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `ban_time` int(10) unsigned NOT NULL DEFAULT 0,
  `expire_time` int(10) unsigned DEFAULT NULL,
  `cannot_access` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cannot_register` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cannot_post` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `cannot_login` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `reason` varchar(255) NOT NULL DEFAULT '',
  `notes` text NOT NULL,
  PRIMARY KEY (`id_ban_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_ban_items`;
CREATE TABLE `smf_ban_items` (
  `id_ban` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_ban_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ip_low` varbinary(16) DEFAULT NULL,
  `ip_high` varbinary(16) DEFAULT NULL,
  `hostname` varchar(255) NOT NULL DEFAULT '',
  `email_address` varchar(255) NOT NULL DEFAULT '',
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `hits` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ban`),
  KEY `idx_id_ban_group` (`id_ban_group`),
  KEY `idx_id_ban_ip` (`ip_low`,`ip_high`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_boards`;
CREATE TABLE `smf_boards` (
  `id_board` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `id_cat` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `child_level` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_parent` smallint(5) unsigned NOT NULL DEFAULT 0,
  `board_order` smallint(6) NOT NULL DEFAULT 0,
  `id_last_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_msg_updated` int(10) unsigned NOT NULL DEFAULT 0,
  `member_groups` varchar(255) NOT NULL DEFAULT '-1,0',
  `id_profile` smallint(5) unsigned NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `num_topics` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `num_posts` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `count_posts` tinyint(4) NOT NULL DEFAULT 0,
  `id_theme` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `override_theme` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `unapproved_posts` smallint(6) NOT NULL DEFAULT 0,
  `unapproved_topics` smallint(6) NOT NULL DEFAULT 0,
  `redirect` varchar(255) NOT NULL DEFAULT '',
  `deny_member_groups` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_board`),
  UNIQUE KEY `idx_categories` (`id_cat`,`id_board`),
  KEY `idx_id_parent` (`id_parent`),
  KEY `idx_id_msg_updated` (`id_msg_updated`),
  KEY `idx_member_groups` (`member_groups`(48))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_boards` (`id_board`, `id_cat`, `child_level`, `id_parent`, `board_order`, `id_last_msg`, `id_msg_updated`, `member_groups`, `id_profile`, `name`, `description`, `num_topics`, `num_posts`, `count_posts`, `id_theme`, `override_theme`, `unapproved_posts`, `unapproved_topics`, `redirect`, `deny_member_groups`) VALUES
(1,	1,	0,	0,	1,	1,	1,	'-1,0,2',	1,	'General Discussion',	'Feel free to talk about anything and everything in this board.',	1,	1,	0,	0,	0,	0,	0,	'',	'');

DROP TABLE IF EXISTS `smf_board_permissions`;
CREATE TABLE `smf_board_permissions` (
  `id_group` smallint(6) NOT NULL DEFAULT 0,
  `id_profile` smallint(5) unsigned NOT NULL DEFAULT 0,
  `permission` varchar(30) NOT NULL DEFAULT '',
  `add_deny` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_group`,`id_profile`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_board_permissions` (`id_group`, `id_profile`, `permission`, `add_deny`) VALUES
(-1,	1,	'poll_view',	1),
(-1,	2,	'poll_view',	1),
(-1,	3,	'poll_view',	1),
(-1,	4,	'poll_view',	1),
(0,	1,	'delete_own',	1),
(0,	1,	'lock_own',	1),
(0,	1,	'modify_own',	1),
(0,	1,	'poll_add_own',	1),
(0,	1,	'poll_edit_own',	1),
(0,	1,	'poll_lock_own',	1),
(0,	1,	'poll_post',	1),
(0,	1,	'poll_view',	1),
(0,	1,	'poll_vote',	1),
(0,	1,	'post_attachment',	1),
(0,	1,	'post_draft',	1),
(0,	1,	'post_new',	1),
(0,	1,	'post_reply_any',	1),
(0,	1,	'post_reply_own',	1),
(0,	1,	'post_unapproved_attachments',	1),
(0,	1,	'post_unapproved_replies_any',	1),
(0,	1,	'post_unapproved_replies_own',	1),
(0,	1,	'post_unapproved_topics',	1),
(0,	1,	'remove_own',	1),
(0,	1,	'report_any',	1),
(0,	1,	'view_attachments',	1),
(0,	2,	'delete_own',	1),
(0,	2,	'lock_own',	1),
(0,	2,	'modify_own',	1),
(0,	2,	'poll_view',	1),
(0,	2,	'poll_vote',	1),
(0,	2,	'post_attachment',	1),
(0,	2,	'post_draft',	1),
(0,	2,	'post_new',	1),
(0,	2,	'post_reply_any',	1),
(0,	2,	'post_reply_own',	1),
(0,	2,	'post_unapproved_attachments',	1),
(0,	2,	'post_unapproved_replies_any',	1),
(0,	2,	'post_unapproved_replies_own',	1),
(0,	2,	'post_unapproved_topics',	1),
(0,	2,	'remove_own',	1),
(0,	2,	'report_any',	1),
(0,	2,	'view_attachments',	1),
(0,	3,	'delete_own',	1),
(0,	3,	'lock_own',	1),
(0,	3,	'modify_own',	1),
(0,	3,	'poll_view',	1),
(0,	3,	'poll_vote',	1),
(0,	3,	'post_attachment',	1),
(0,	3,	'post_reply_any',	1),
(0,	3,	'post_reply_own',	1),
(0,	3,	'post_unapproved_attachments',	1),
(0,	3,	'post_unapproved_replies_any',	1),
(0,	3,	'post_unapproved_replies_own',	1),
(0,	3,	'remove_own',	1),
(0,	3,	'report_any',	1),
(0,	3,	'view_attachments',	1),
(0,	4,	'poll_view',	1),
(0,	4,	'poll_vote',	1),
(0,	4,	'report_any',	1),
(0,	4,	'view_attachments',	1),
(2,	1,	'approve_posts',	1),
(2,	1,	'delete_any',	1),
(2,	1,	'delete_own',	1),
(2,	1,	'lock_any',	1),
(2,	1,	'lock_own',	1),
(2,	1,	'make_sticky',	1),
(2,	1,	'merge_any',	1),
(2,	1,	'moderate_board',	1),
(2,	1,	'modify_any',	1),
(2,	1,	'modify_own',	1),
(2,	1,	'move_any',	1),
(2,	1,	'poll_add_any',	1),
(2,	1,	'poll_edit_any',	1),
(2,	1,	'poll_lock_any',	1),
(2,	1,	'poll_post',	1),
(2,	1,	'poll_remove_any',	1),
(2,	1,	'poll_view',	1),
(2,	1,	'poll_vote',	1),
(2,	1,	'post_attachment',	1),
(2,	1,	'post_draft',	1),
(2,	1,	'post_new',	1),
(2,	1,	'post_reply_any',	1),
(2,	1,	'post_reply_own',	1),
(2,	1,	'post_unapproved_attachments',	1),
(2,	1,	'post_unapproved_replies_any',	1),
(2,	1,	'post_unapproved_replies_own',	1),
(2,	1,	'post_unapproved_topics',	1),
(2,	1,	'remove_any',	1),
(2,	1,	'report_any',	1),
(2,	1,	'split_any',	1),
(2,	1,	'view_attachments',	1),
(2,	2,	'approve_posts',	1),
(2,	2,	'delete_any',	1),
(2,	2,	'delete_own',	1),
(2,	2,	'lock_any',	1),
(2,	2,	'lock_own',	1),
(2,	2,	'make_sticky',	1),
(2,	2,	'merge_any',	1),
(2,	2,	'moderate_board',	1),
(2,	2,	'modify_any',	1),
(2,	2,	'modify_own',	1),
(2,	2,	'move_any',	1),
(2,	2,	'poll_add_any',	1),
(2,	2,	'poll_edit_any',	1),
(2,	2,	'poll_lock_any',	1),
(2,	2,	'poll_post',	1),
(2,	2,	'poll_remove_any',	1),
(2,	2,	'poll_view',	1),
(2,	2,	'poll_vote',	1),
(2,	2,	'post_attachment',	1),
(2,	2,	'post_draft',	1),
(2,	2,	'post_new',	1),
(2,	2,	'post_reply_any',	1),
(2,	2,	'post_reply_own',	1),
(2,	2,	'post_unapproved_attachments',	1),
(2,	2,	'post_unapproved_replies_any',	1),
(2,	2,	'post_unapproved_replies_own',	1),
(2,	2,	'post_unapproved_topics',	1),
(2,	2,	'remove_any',	1),
(2,	2,	'report_any',	1),
(2,	2,	'split_any',	1),
(2,	2,	'view_attachments',	1),
(2,	3,	'approve_posts',	1),
(2,	3,	'delete_any',	1),
(2,	3,	'delete_own',	1),
(2,	3,	'lock_any',	1),
(2,	3,	'lock_own',	1),
(2,	3,	'make_sticky',	1),
(2,	3,	'merge_any',	1),
(2,	3,	'moderate_board',	1),
(2,	3,	'modify_any',	1),
(2,	3,	'modify_own',	1),
(2,	3,	'move_any',	1),
(2,	3,	'poll_add_any',	1),
(2,	3,	'poll_edit_any',	1),
(2,	3,	'poll_lock_any',	1),
(2,	3,	'poll_post',	1),
(2,	3,	'poll_remove_any',	1),
(2,	3,	'poll_view',	1),
(2,	3,	'poll_vote',	1),
(2,	3,	'post_attachment',	1),
(2,	3,	'post_draft',	1),
(2,	3,	'post_new',	1),
(2,	3,	'post_reply_any',	1),
(2,	3,	'post_reply_own',	1),
(2,	3,	'post_unapproved_attachments',	1),
(2,	3,	'post_unapproved_replies_any',	1),
(2,	3,	'post_unapproved_replies_own',	1),
(2,	3,	'post_unapproved_topics',	1),
(2,	3,	'remove_any',	1),
(2,	3,	'report_any',	1),
(2,	3,	'split_any',	1),
(2,	3,	'view_attachments',	1),
(2,	4,	'approve_posts',	1),
(2,	4,	'delete_any',	1),
(2,	4,	'delete_own',	1),
(2,	4,	'lock_any',	1),
(2,	4,	'lock_own',	1),
(2,	4,	'make_sticky',	1),
(2,	4,	'merge_any',	1),
(2,	4,	'moderate_board',	1),
(2,	4,	'modify_any',	1),
(2,	4,	'modify_own',	1),
(2,	4,	'move_any',	1),
(2,	4,	'poll_add_any',	1),
(2,	4,	'poll_edit_any',	1),
(2,	4,	'poll_lock_any',	1),
(2,	4,	'poll_post',	1),
(2,	4,	'poll_remove_any',	1),
(2,	4,	'poll_view',	1),
(2,	4,	'poll_vote',	1),
(2,	4,	'post_attachment',	1),
(2,	4,	'post_draft',	1),
(2,	4,	'post_new',	1),
(2,	4,	'post_reply_any',	1),
(2,	4,	'post_reply_own',	1),
(2,	4,	'post_unapproved_attachments',	1),
(2,	4,	'post_unapproved_replies_any',	1),
(2,	4,	'post_unapproved_replies_own',	1),
(2,	4,	'post_unapproved_topics',	1),
(2,	4,	'remove_any',	1),
(2,	4,	'report_any',	1),
(2,	4,	'split_any',	1),
(2,	4,	'view_attachments',	1),
(3,	1,	'approve_posts',	1),
(3,	1,	'delete_any',	1),
(3,	1,	'delete_own',	1),
(3,	1,	'lock_any',	1),
(3,	1,	'lock_own',	1),
(3,	1,	'make_sticky',	1),
(3,	1,	'merge_any',	1),
(3,	1,	'moderate_board',	1),
(3,	1,	'modify_any',	1),
(3,	1,	'modify_own',	1),
(3,	1,	'move_any',	1),
(3,	1,	'poll_add_any',	1),
(3,	1,	'poll_edit_any',	1),
(3,	1,	'poll_lock_any',	1),
(3,	1,	'poll_post',	1),
(3,	1,	'poll_remove_any',	1),
(3,	1,	'poll_view',	1),
(3,	1,	'poll_vote',	1),
(3,	1,	'post_attachment',	1),
(3,	1,	'post_draft',	1),
(3,	1,	'post_new',	1),
(3,	1,	'post_reply_any',	1),
(3,	1,	'post_reply_own',	1),
(3,	1,	'post_unapproved_attachments',	1),
(3,	1,	'post_unapproved_replies_any',	1),
(3,	1,	'post_unapproved_replies_own',	1),
(3,	1,	'post_unapproved_topics',	1),
(3,	1,	'remove_any',	1),
(3,	1,	'report_any',	1),
(3,	1,	'split_any',	1),
(3,	1,	'view_attachments',	1),
(3,	2,	'approve_posts',	1),
(3,	2,	'delete_any',	1),
(3,	2,	'delete_own',	1),
(3,	2,	'lock_any',	1),
(3,	2,	'lock_own',	1),
(3,	2,	'make_sticky',	1),
(3,	2,	'merge_any',	1),
(3,	2,	'moderate_board',	1),
(3,	2,	'modify_any',	1),
(3,	2,	'modify_own',	1),
(3,	2,	'move_any',	1),
(3,	2,	'poll_add_any',	1),
(3,	2,	'poll_edit_any',	1),
(3,	2,	'poll_lock_any',	1),
(3,	2,	'poll_post',	1),
(3,	2,	'poll_remove_any',	1),
(3,	2,	'poll_view',	1),
(3,	2,	'poll_vote',	1),
(3,	2,	'post_attachment',	1),
(3,	2,	'post_draft',	1),
(3,	2,	'post_new',	1),
(3,	2,	'post_reply_any',	1),
(3,	2,	'post_reply_own',	1),
(3,	2,	'post_unapproved_attachments',	1),
(3,	2,	'post_unapproved_replies_any',	1),
(3,	2,	'post_unapproved_replies_own',	1),
(3,	2,	'post_unapproved_topics',	1),
(3,	2,	'remove_any',	1),
(3,	2,	'report_any',	1),
(3,	2,	'split_any',	1),
(3,	2,	'view_attachments',	1),
(3,	3,	'approve_posts',	1),
(3,	3,	'delete_any',	1),
(3,	3,	'delete_own',	1),
(3,	3,	'lock_any',	1),
(3,	3,	'lock_own',	1),
(3,	3,	'make_sticky',	1),
(3,	3,	'merge_any',	1),
(3,	3,	'moderate_board',	1),
(3,	3,	'modify_any',	1),
(3,	3,	'modify_own',	1),
(3,	3,	'move_any',	1),
(3,	3,	'poll_add_any',	1),
(3,	3,	'poll_edit_any',	1),
(3,	3,	'poll_lock_any',	1),
(3,	3,	'poll_post',	1),
(3,	3,	'poll_remove_any',	1),
(3,	3,	'poll_view',	1),
(3,	3,	'poll_vote',	1),
(3,	3,	'post_attachment',	1),
(3,	3,	'post_draft',	1),
(3,	3,	'post_new',	1),
(3,	3,	'post_reply_any',	1),
(3,	3,	'post_reply_own',	1),
(3,	3,	'post_unapproved_attachments',	1),
(3,	3,	'post_unapproved_replies_any',	1),
(3,	3,	'post_unapproved_replies_own',	1),
(3,	3,	'post_unapproved_topics',	1),
(3,	3,	'remove_any',	1),
(3,	3,	'report_any',	1),
(3,	3,	'split_any',	1),
(3,	3,	'view_attachments',	1),
(3,	4,	'approve_posts',	1),
(3,	4,	'delete_any',	1),
(3,	4,	'delete_own',	1),
(3,	4,	'lock_any',	1),
(3,	4,	'lock_own',	1),
(3,	4,	'make_sticky',	1),
(3,	4,	'merge_any',	1),
(3,	4,	'moderate_board',	1),
(3,	4,	'modify_any',	1),
(3,	4,	'modify_own',	1),
(3,	4,	'move_any',	1),
(3,	4,	'poll_add_any',	1),
(3,	4,	'poll_edit_any',	1),
(3,	4,	'poll_lock_any',	1),
(3,	4,	'poll_post',	1),
(3,	4,	'poll_remove_any',	1),
(3,	4,	'poll_view',	1),
(3,	4,	'poll_vote',	1),
(3,	4,	'post_attachment',	1),
(3,	4,	'post_draft',	1),
(3,	4,	'post_new',	1),
(3,	4,	'post_reply_any',	1),
(3,	4,	'post_reply_own',	1),
(3,	4,	'post_unapproved_attachments',	1),
(3,	4,	'post_unapproved_replies_any',	1),
(3,	4,	'post_unapproved_replies_own',	1),
(3,	4,	'post_unapproved_topics',	1),
(3,	4,	'remove_any',	1),
(3,	4,	'report_any',	1),
(3,	4,	'split_any',	1),
(3,	4,	'view_attachments',	1);

DROP TABLE IF EXISTS `smf_board_permissions_view`;
CREATE TABLE `smf_board_permissions_view` (
  `id_group` smallint(6) NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL,
  `deny` smallint(6) NOT NULL,
  PRIMARY KEY (`id_group`,`id_board`,`deny`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_board_permissions_view` (`id_group`, `id_board`, `deny`) VALUES
(-1,	1,	0),
(0,	1,	0),
(2,	1,	0);

DROP TABLE IF EXISTS `smf_calendar`;
CREATE TABLE `smf_calendar` (
  `id_event` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL DEFAULT '1004-01-01',
  `end_date` date NOT NULL DEFAULT '1004-01-01',
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `timezone` varchar(80) DEFAULT NULL,
  `location` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_event`),
  KEY `idx_start_date` (`start_date`),
  KEY `idx_end_date` (`end_date`),
  KEY `idx_topic` (`id_topic`,`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_calendar_holidays`;
CREATE TABLE `smf_calendar_holidays` (
  `id_holiday` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `event_date` date NOT NULL DEFAULT '1004-01-01',
  `title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_holiday`),
  KEY `idx_event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_calendar_holidays` (`id_holiday`, `event_date`, `title`) VALUES
(1,	'1004-01-01',	'New Year\'s'),
(2,	'1004-12-25',	'Christmas'),
(3,	'1004-02-14',	'Valentine\'s Day'),
(4,	'1004-03-17',	'St. Patrick\'s Day'),
(5,	'1004-04-01',	'April Fools'),
(6,	'1004-04-22',	'Earth Day'),
(7,	'1004-10-24',	'United Nations Day'),
(8,	'1004-10-31',	'Halloween'),
(9,	'2010-05-09',	'Mother\'s Day'),
(10,	'2011-05-08',	'Mother\'s Day'),
(11,	'2012-05-13',	'Mother\'s Day'),
(12,	'2013-05-12',	'Mother\'s Day'),
(13,	'2014-05-11',	'Mother\'s Day'),
(14,	'2015-05-10',	'Mother\'s Day'),
(15,	'2016-05-08',	'Mother\'s Day'),
(16,	'2017-05-14',	'Mother\'s Day'),
(17,	'2018-05-13',	'Mother\'s Day'),
(18,	'2019-05-12',	'Mother\'s Day'),
(19,	'2020-05-10',	'Mother\'s Day'),
(20,	'2021-05-09',	'Mother\'s Day'),
(21,	'2022-05-08',	'Mother\'s Day'),
(22,	'2023-05-14',	'Mother\'s Day'),
(23,	'2024-05-12',	'Mother\'s Day'),
(24,	'2025-05-11',	'Mother\'s Day'),
(25,	'2026-05-10',	'Mother\'s Day'),
(26,	'2027-05-09',	'Mother\'s Day'),
(27,	'2028-05-14',	'Mother\'s Day'),
(28,	'2029-05-13',	'Mother\'s Day'),
(29,	'2030-05-12',	'Mother\'s Day'),
(30,	'2010-06-20',	'Father\'s Day'),
(31,	'2011-06-19',	'Father\'s Day'),
(32,	'2012-06-17',	'Father\'s Day'),
(33,	'2013-06-16',	'Father\'s Day'),
(34,	'2014-06-15',	'Father\'s Day'),
(35,	'2015-06-21',	'Father\'s Day'),
(36,	'2016-06-19',	'Father\'s Day'),
(37,	'2017-06-18',	'Father\'s Day'),
(38,	'2018-06-17',	'Father\'s Day'),
(39,	'2019-06-16',	'Father\'s Day'),
(40,	'2020-06-21',	'Father\'s Day'),
(41,	'2021-06-20',	'Father\'s Day'),
(42,	'2022-06-19',	'Father\'s Day'),
(43,	'2023-06-18',	'Father\'s Day'),
(44,	'2024-06-16',	'Father\'s Day'),
(45,	'2025-06-15',	'Father\'s Day'),
(46,	'2026-06-21',	'Father\'s Day'),
(47,	'2027-06-20',	'Father\'s Day'),
(48,	'2028-06-18',	'Father\'s Day'),
(49,	'2029-06-17',	'Father\'s Day'),
(50,	'2030-06-16',	'Father\'s Day'),
(51,	'2010-06-21',	'Summer Solstice'),
(52,	'2011-06-21',	'Summer Solstice'),
(53,	'2012-06-20',	'Summer Solstice'),
(54,	'2013-06-21',	'Summer Solstice'),
(55,	'2014-06-21',	'Summer Solstice'),
(56,	'2015-06-21',	'Summer Solstice'),
(57,	'2016-06-20',	'Summer Solstice'),
(58,	'2017-06-20',	'Summer Solstice'),
(59,	'2018-06-21',	'Summer Solstice'),
(60,	'2019-06-21',	'Summer Solstice'),
(61,	'2020-06-20',	'Summer Solstice'),
(62,	'2021-06-21',	'Summer Solstice'),
(63,	'2022-06-21',	'Summer Solstice'),
(64,	'2023-06-21',	'Summer Solstice'),
(65,	'2024-06-20',	'Summer Solstice'),
(66,	'2025-06-21',	'Summer Solstice'),
(67,	'2026-06-21',	'Summer Solstice'),
(68,	'2027-06-21',	'Summer Solstice'),
(69,	'2028-06-20',	'Summer Solstice'),
(70,	'2029-06-21',	'Summer Solstice'),
(71,	'2030-06-21',	'Summer Solstice'),
(72,	'2010-03-20',	'Vernal Equinox'),
(73,	'2011-03-20',	'Vernal Equinox'),
(74,	'2012-03-20',	'Vernal Equinox'),
(75,	'2013-03-20',	'Vernal Equinox'),
(76,	'2014-03-20',	'Vernal Equinox'),
(77,	'2015-03-20',	'Vernal Equinox'),
(78,	'2016-03-20',	'Vernal Equinox'),
(79,	'2017-03-20',	'Vernal Equinox'),
(80,	'2018-03-20',	'Vernal Equinox'),
(81,	'2019-03-20',	'Vernal Equinox'),
(82,	'2020-03-20',	'Vernal Equinox'),
(83,	'2021-03-20',	'Vernal Equinox'),
(84,	'2022-03-20',	'Vernal Equinox'),
(85,	'2023-03-20',	'Vernal Equinox'),
(86,	'2024-03-20',	'Vernal Equinox'),
(87,	'2025-03-20',	'Vernal Equinox'),
(88,	'2026-03-20',	'Vernal Equinox'),
(89,	'2027-03-20',	'Vernal Equinox'),
(90,	'2028-03-20',	'Vernal Equinox'),
(91,	'2029-03-20',	'Vernal Equinox'),
(92,	'2030-03-20',	'Vernal Equinox'),
(93,	'2010-12-21',	'Winter Solstice'),
(94,	'2011-12-22',	'Winter Solstice'),
(95,	'2012-12-21',	'Winter Solstice'),
(96,	'2013-12-21',	'Winter Solstice'),
(97,	'2014-12-21',	'Winter Solstice'),
(98,	'2015-12-22',	'Winter Solstice'),
(99,	'2016-12-21',	'Winter Solstice'),
(100,	'2017-12-21',	'Winter Solstice'),
(101,	'2018-12-21',	'Winter Solstice'),
(102,	'2019-12-22',	'Winter Solstice'),
(103,	'2020-12-21',	'Winter Solstice'),
(104,	'2021-12-21',	'Winter Solstice'),
(105,	'2022-12-21',	'Winter Solstice'),
(106,	'2023-12-22',	'Winter Solstice'),
(107,	'2024-12-21',	'Winter Solstice'),
(108,	'2025-12-21',	'Winter Solstice'),
(109,	'2026-12-21',	'Winter Solstice'),
(110,	'2027-12-22',	'Winter Solstice'),
(111,	'2028-12-21',	'Winter Solstice'),
(112,	'2029-12-21',	'Winter Solstice'),
(113,	'2030-12-21',	'Winter Solstice'),
(114,	'2010-09-23',	'Autumnal Equinox'),
(115,	'2011-09-23',	'Autumnal Equinox'),
(116,	'2012-09-22',	'Autumnal Equinox'),
(117,	'2013-09-22',	'Autumnal Equinox'),
(118,	'2014-09-23',	'Autumnal Equinox'),
(119,	'2015-09-23',	'Autumnal Equinox'),
(120,	'2016-09-22',	'Autumnal Equinox'),
(121,	'2017-09-22',	'Autumnal Equinox'),
(122,	'2018-09-23',	'Autumnal Equinox'),
(123,	'2019-09-23',	'Autumnal Equinox'),
(124,	'2020-09-22',	'Autumnal Equinox'),
(125,	'2021-09-22',	'Autumnal Equinox'),
(126,	'2022-09-23',	'Autumnal Equinox'),
(127,	'2023-09-23',	'Autumnal Equinox'),
(128,	'2024-09-22',	'Autumnal Equinox'),
(129,	'2025-09-22',	'Autumnal Equinox'),
(130,	'2026-09-23',	'Autumnal Equinox'),
(131,	'2027-09-23',	'Autumnal Equinox'),
(132,	'2028-09-22',	'Autumnal Equinox'),
(133,	'2029-09-22',	'Autumnal Equinox'),
(134,	'2030-09-22',	'Autumnal Equinox'),
(135,	'1004-07-04',	'Independence Day'),
(136,	'1004-05-05',	'Cinco de Mayo'),
(137,	'1004-06-14',	'Flag Day'),
(138,	'1004-11-11',	'Veterans Day'),
(139,	'1004-02-02',	'Groundhog Day'),
(140,	'2010-11-25',	'Thanksgiving'),
(141,	'2011-11-24',	'Thanksgiving'),
(142,	'2012-11-22',	'Thanksgiving'),
(143,	'2013-11-28',	'Thanksgiving'),
(144,	'2014-11-27',	'Thanksgiving'),
(145,	'2015-11-26',	'Thanksgiving'),
(146,	'2016-11-24',	'Thanksgiving'),
(147,	'2017-11-23',	'Thanksgiving'),
(148,	'2018-11-22',	'Thanksgiving'),
(149,	'2019-11-28',	'Thanksgiving'),
(150,	'2020-11-26',	'Thanksgiving'),
(151,	'2021-11-25',	'Thanksgiving'),
(152,	'2022-11-24',	'Thanksgiving'),
(153,	'2023-11-23',	'Thanksgiving'),
(154,	'2024-11-28',	'Thanksgiving'),
(155,	'2025-11-27',	'Thanksgiving'),
(156,	'2026-11-26',	'Thanksgiving'),
(157,	'2027-11-25',	'Thanksgiving'),
(158,	'2028-11-23',	'Thanksgiving'),
(159,	'2029-11-22',	'Thanksgiving'),
(160,	'2030-11-28',	'Thanksgiving'),
(161,	'2010-05-31',	'Memorial Day'),
(162,	'2011-05-30',	'Memorial Day'),
(163,	'2012-05-28',	'Memorial Day'),
(164,	'2013-05-27',	'Memorial Day'),
(165,	'2014-05-26',	'Memorial Day'),
(166,	'2015-05-25',	'Memorial Day'),
(167,	'2016-05-30',	'Memorial Day'),
(168,	'2017-05-29',	'Memorial Day'),
(169,	'2018-05-28',	'Memorial Day'),
(170,	'2019-05-27',	'Memorial Day'),
(171,	'2020-05-25',	'Memorial Day'),
(172,	'2021-05-31',	'Memorial Day'),
(173,	'2022-05-30',	'Memorial Day'),
(174,	'2023-05-29',	'Memorial Day'),
(175,	'2024-05-27',	'Memorial Day'),
(176,	'2025-05-26',	'Memorial Day'),
(177,	'2026-05-25',	'Memorial Day'),
(178,	'2027-05-31',	'Memorial Day'),
(179,	'2028-05-29',	'Memorial Day'),
(180,	'2029-05-28',	'Memorial Day'),
(181,	'2030-05-27',	'Memorial Day'),
(182,	'2010-09-06',	'Labor Day'),
(183,	'2011-09-05',	'Labor Day'),
(184,	'2012-09-03',	'Labor Day'),
(185,	'2013-09-02',	'Labor Day'),
(186,	'2014-09-01',	'Labor Day'),
(187,	'2015-09-07',	'Labor Day'),
(188,	'2016-09-05',	'Labor Day'),
(189,	'2017-09-04',	'Labor Day'),
(190,	'2018-09-03',	'Labor Day'),
(191,	'2019-09-02',	'Labor Day'),
(192,	'2020-09-07',	'Labor Day'),
(193,	'2021-09-06',	'Labor Day'),
(194,	'2022-09-05',	'Labor Day'),
(195,	'2023-09-04',	'Labor Day'),
(196,	'2024-09-02',	'Labor Day'),
(197,	'2025-09-01',	'Labor Day'),
(198,	'2026-09-07',	'Labor Day'),
(199,	'2027-09-06',	'Labor Day'),
(200,	'2028-09-04',	'Labor Day'),
(201,	'2029-09-03',	'Labor Day'),
(202,	'2030-09-02',	'Labor Day'),
(203,	'1004-06-06',	'D-Day');

DROP TABLE IF EXISTS `smf_categories`;
CREATE TABLE `smf_categories` (
  `id_cat` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `cat_order` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `can_collapse` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_cat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_categories` (`id_cat`, `cat_order`, `name`, `description`, `can_collapse`) VALUES
(1,	0,	'General Category',	'',	1);

DROP TABLE IF EXISTS `smf_custom_fields`;
CREATE TABLE `smf_custom_fields` (
  `id_field` smallint(6) NOT NULL AUTO_INCREMENT,
  `col_name` varchar(12) NOT NULL DEFAULT '',
  `field_name` varchar(40) NOT NULL DEFAULT '',
  `field_desc` varchar(255) NOT NULL DEFAULT '',
  `field_type` varchar(8) NOT NULL DEFAULT 'text',
  `field_length` smallint(6) NOT NULL DEFAULT 255,
  `field_options` text NOT NULL,
  `field_order` smallint(6) NOT NULL DEFAULT 0,
  `mask` varchar(255) NOT NULL DEFAULT '',
  `show_reg` tinyint(4) NOT NULL DEFAULT 0,
  `show_display` tinyint(4) NOT NULL DEFAULT 0,
  `show_mlist` smallint(6) NOT NULL DEFAULT 0,
  `show_profile` varchar(20) NOT NULL DEFAULT 'forumprofile',
  `private` tinyint(4) NOT NULL DEFAULT 0,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `bbc` tinyint(4) NOT NULL DEFAULT 0,
  `can_search` tinyint(4) NOT NULL DEFAULT 0,
  `default_value` varchar(255) NOT NULL DEFAULT '',
  `enclose` text NOT NULL,
  `placement` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_field`),
  UNIQUE KEY `idx_col_name` (`col_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_custom_fields` (`id_field`, `col_name`, `field_name`, `field_desc`, `field_type`, `field_length`, `field_options`, `field_order`, `mask`, `show_reg`, `show_display`, `show_mlist`, `show_profile`, `private`, `active`, `bbc`, `can_search`, `default_value`, `enclose`, `placement`) VALUES
(1,	'cust_icq',	'{icq}',	'{icq_desc}',	'text',	12,	'',	1,	'regex~[1-9][0-9]{4,9}~i',	0,	1,	0,	'forumprofile',	0,	1,	0,	0,	'',	'<a class=\"icq\" href=\"//www.icq.com/people/{INPUT}\" target=\"_blank\" rel=\"noopener\" title=\"ICQ - {INPUT}\"><img src=\"{DEFAULT_IMAGES_URL}/icq.png\" alt=\"ICQ - {INPUT}\"></a>',	1),
(2,	'cust_skype',	'{skype}',	'{skype_desc}',	'text',	32,	'',	2,	'nohtml',	0,	1,	0,	'forumprofile',	0,	1,	0,	0,	'',	'<a href=\"skype:{INPUT}?call\"><img src=\"{DEFAULT_IMAGES_URL}/skype.png\" alt=\"{INPUT}\" title=\"{INPUT}\" /></a> ',	1),
(3,	'cust_loca',	'{location}',	'{location_desc}',	'text',	50,	'',	4,	'nohtml',	0,	1,	0,	'forumprofile',	0,	1,	0,	0,	'',	'',	0),
(4,	'cust_gender',	'{gender}',	'{gender_desc}',	'radio',	255,	'{gender_0},{gender_1},{gender_2}',	5,	'nohtml',	1,	1,	0,	'forumprofile',	0,	1,	0,	0,	'{gender_0}',	'<span class=\" main_icons gender_{KEY}\" title=\"{INPUT}\"></span>',	1);

DROP TABLE IF EXISTS `smf_group_moderators`;
CREATE TABLE `smf_group_moderators` (
  `id_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_group`,`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_actions`;
CREATE TABLE `smf_log_actions` (
  `id_action` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_log` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `action` varchar(30) NOT NULL DEFAULT '',
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `extra` text NOT NULL,
  PRIMARY KEY (`id_action`),
  KEY `idx_id_log` (`id_log`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_id_board` (`id_board`),
  KEY `idx_id_msg` (`id_msg`),
  KEY `idx_id_topic_id_log` (`id_topic`,`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_log_actions` (`id_action`, `id_log`, `log_time`, `id_member`, `ip`, `action`, `id_board`, `id_topic`, `id_msg`, `extra`) VALUES
(1,	3,	1756827329,	1,	UNHEX('7F000001'),	'install',	0,	0,	0,	'{\"version\":\"SMF 2.1.6\"}'),
(2,	3,	1756827506,	1,	UNHEX('7F000001'),	'install_package',	0,	0,	0,	'{\"package\":\"Light Portal\",\"version\":\"2.9.5\"}');

DROP TABLE IF EXISTS `smf_log_activity`;
CREATE TABLE `smf_log_activity` (
  `date` date NOT NULL,
  `hits` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `topics` smallint(5) unsigned NOT NULL DEFAULT 0,
  `posts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `registers` smallint(5) unsigned NOT NULL DEFAULT 0,
  `most_on` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_log_activity` (`date`, `hits`, `topics`, `posts`, `registers`, `most_on`) VALUES
('2025-09-02',	0,	1,	1,	1,	1);

DROP TABLE IF EXISTS `smf_log_banned`;
CREATE TABLE `smf_log_banned` (
  `id_ban_log` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_ban_log`),
  KEY `idx_log_time` (`log_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_boards`;
CREATE TABLE `smf_log_boards` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`id_board`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_log_boards` (`id_member`, `id_board`, `id_msg`) VALUES
(1,	1,	1);

DROP TABLE IF EXISTS `smf_log_comments`;
CREATE TABLE `smf_log_comments` (
  `id_comment` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `member_name` varchar(80) NOT NULL DEFAULT '',
  `comment_type` varchar(8) NOT NULL DEFAULT 'warning',
  `id_recipient` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `recipient_name` varchar(255) NOT NULL DEFAULT '',
  `log_time` int(11) NOT NULL DEFAULT 0,
  `id_notice` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `counter` tinyint(4) NOT NULL DEFAULT 0,
  `body` text NOT NULL,
  PRIMARY KEY (`id_comment`),
  KEY `idx_id_recipient` (`id_recipient`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_comment_type` (`comment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_digest`;
CREATE TABLE `smf_log_digest` (
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `note_type` varchar(10) NOT NULL DEFAULT 'post',
  `daily` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `exclude` mediumint(8) unsigned NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_errors`;
CREATE TABLE `smf_log_errors` (
  `id_error` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `url` text NOT NULL,
  `message` text NOT NULL,
  `session` varchar(128) NOT NULL DEFAULT '',
  `error_type` char(15) NOT NULL DEFAULT 'general',
  `file` varchar(255) NOT NULL DEFAULT '',
  `line` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `backtrace` varchar(10000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_error`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_floodcontrol`;
CREATE TABLE `smf_log_floodcontrol` (
  `ip` varbinary(16) NOT NULL,
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  `log_type` varchar(30) NOT NULL DEFAULT 'post',
  PRIMARY KEY (`ip`,`log_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_group_requests`;
CREATE TABLE `smf_log_group_requests` (
  `id_request` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `time_applied` int(10) unsigned NOT NULL DEFAULT 0,
  `reason` text NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_member_acted` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `member_name_acted` varchar(255) NOT NULL DEFAULT '',
  `time_acted` int(10) unsigned NOT NULL DEFAULT 0,
  `act_reason` text NOT NULL,
  PRIMARY KEY (`id_request`),
  KEY `idx_id_member` (`id_member`,`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_mark_read`;
CREATE TABLE `smf_log_mark_read` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`id_board`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_member_notices`;
CREATE TABLE `smf_log_member_notices` (
  `id_notice` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  PRIMARY KEY (`id_notice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_notify`;
CREATE TABLE `smf_log_notify` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `sent` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`id_topic`,`id_board`),
  KEY `idx_id_topic` (`id_topic`,`id_member`),
  KEY `id_board` (`id_board`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_online`;
CREATE TABLE `smf_log_online` (
  `session` varchar(128) NOT NULL DEFAULT '',
  `log_time` int(11) NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_spider` smallint(5) unsigned NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `url` varchar(2048) NOT NULL DEFAULT '',
  PRIMARY KEY (`session`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_id_member` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_log_online` (`session`, `log_time`, `id_member`, `id_spider`, `ip`, `url`) VALUES
('45hfvu96ao2mn6i42olu2qdn2o90m6r1',	1756827592,	1,	0,	UNHEX('7F000001'),	'{\"action\":\"admin\",\"area\":\"repairboards\",\"USER_AGENT\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko\\/20100101 Firefox\\/143.0\"}');

DROP TABLE IF EXISTS `smf_log_packages`;
CREATE TABLE `smf_log_packages` (
  `id_install` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `package_id` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `version` varchar(255) NOT NULL DEFAULT '',
  `id_member_installed` mediumint(9) NOT NULL DEFAULT 0,
  `member_installed` varchar(255) NOT NULL DEFAULT '',
  `time_installed` int(11) NOT NULL DEFAULT 0,
  `id_member_removed` mediumint(9) NOT NULL DEFAULT 0,
  `member_removed` varchar(255) NOT NULL DEFAULT '',
  `time_removed` int(11) NOT NULL DEFAULT 0,
  `install_state` tinyint(4) NOT NULL DEFAULT 1,
  `failed_steps` text NOT NULL,
  `themes_installed` varchar(255) NOT NULL DEFAULT '',
  `db_changes` text NOT NULL,
  `credits` text NOT NULL,
  `sha256_hash` text DEFAULT NULL,
  PRIMARY KEY (`id_install`),
  KEY `idx_filename` (`filename`(15))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_log_packages` (`id_install`, `filename`, `package_id`, `name`, `version`, `id_member_installed`, `member_installed`, `time_installed`, `id_member_removed`, `member_removed`, `time_removed`, `install_state`, `failed_steps`, `themes_installed`, `db_changes`, `credits`, `sha256_hash`) VALUES
(1,	'light_portal_2.9.5.tgz',	'Bugo:LightPortal',	'Light Portal',	'2.9.5',	1,	'Test',	1756827505,	0,	'0',	0,	1,	'[]',	'1',	'[[\"remove_table\",\"smf_lp_blocks\"],[\"remove_table\",\"smf_lp_categories\"],[\"remove_table\",\"smf_lp_comments\"],[\"remove_table\",\"smf_lp_page_tag\"],[\"remove_table\",\"smf_lp_pages\"],[\"remove_table\",\"smf_lp_params\"],[\"remove_table\",\"smf_lp_plugins\"],[\"remove_table\",\"smf_lp_tags\"],[\"remove_table\",\"smf_lp_titles\"]]',	'',	'ad78e39eb30926a0e3d3935ddc503351bd71ec6a52b85d32b35b589d0ea1ed74');

DROP TABLE IF EXISTS `smf_log_polls`;
CREATE TABLE `smf_log_polls` (
  `id_poll` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_choice` tinyint(3) unsigned NOT NULL DEFAULT 0,
  KEY `idx_id_poll` (`id_poll`,`id_member`,`id_choice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_reported`;
CREATE TABLE `smf_log_reported` (
  `id_report` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `membername` varchar(255) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NOT NULL,
  `time_started` int(11) NOT NULL DEFAULT 0,
  `time_updated` int(11) NOT NULL DEFAULT 0,
  `num_reports` mediumint(9) NOT NULL DEFAULT 0,
  `closed` tinyint(4) NOT NULL DEFAULT 0,
  `ignore_all` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_report`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_id_topic` (`id_topic`),
  KEY `idx_closed` (`closed`),
  KEY `idx_time_started` (`time_started`),
  KEY `idx_id_msg` (`id_msg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_reported_comments`;
CREATE TABLE `smf_log_reported_comments` (
  `id_comment` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `id_report` mediumint(9) NOT NULL DEFAULT 0,
  `id_member` mediumint(9) NOT NULL,
  `membername` varchar(255) NOT NULL DEFAULT '',
  `member_ip` varbinary(16) DEFAULT NULL,
  `comment` varchar(255) NOT NULL DEFAULT '',
  `time_sent` int(11) NOT NULL,
  PRIMARY KEY (`id_comment`),
  KEY `idx_id_report` (`id_report`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_time_sent` (`time_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_scheduled_tasks`;
CREATE TABLE `smf_log_scheduled_tasks` (
  `id_log` mediumint(9) NOT NULL AUTO_INCREMENT,
  `id_task` smallint(6) NOT NULL DEFAULT 0,
  `time_run` int(11) NOT NULL DEFAULT 0,
  `time_taken` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_log_scheduled_tasks` (`id_log`, `id_task`, `time_run`, `time_taken`) VALUES
(1,	3,	1756827335,	1),
(2,	5,	1756827339,	0),
(3,	6,	1756827341,	0),
(4,	9,	1756827349,	0),
(5,	7,	1756827350,	3),
(6,	11,	1756827356,	0),
(7,	12,	1756827364,	0),
(8,	13,	1756827367,	0);

DROP TABLE IF EXISTS `smf_log_search_messages`;
CREATE TABLE `smf_log_search_messages` (
  `id_search` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_search`,`id_msg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_search_results`;
CREATE TABLE `smf_log_search_results` (
  `id_search` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `relevance` smallint(5) unsigned NOT NULL DEFAULT 0,
  `num_matches` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_search`,`id_topic`,`id_msg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_search_subjects`;
CREATE TABLE `smf_log_search_subjects` (
  `word` varchar(20) NOT NULL DEFAULT '',
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`word`,`id_topic`),
  KEY `idx_id_topic` (`id_topic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_log_search_subjects` (`word`, `id_topic`) VALUES
('smf',	1),
('to',	1),
('welcome',	1);

DROP TABLE IF EXISTS `smf_log_search_topics`;
CREATE TABLE `smf_log_search_topics` (
  `id_search` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_search`,`id_topic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_spider_hits`;
CREATE TABLE `smf_log_spider_hits` (
  `id_hit` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_spider` smallint(5) unsigned NOT NULL DEFAULT 0,
  `log_time` int(10) unsigned NOT NULL DEFAULT 0,
  `url` varchar(1024) NOT NULL DEFAULT '',
  `processed` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_hit`),
  KEY `idx_id_spider` (`id_spider`),
  KEY `idx_log_time` (`log_time`),
  KEY `idx_processed` (`processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_spider_stats`;
CREATE TABLE `smf_log_spider_stats` (
  `id_spider` smallint(5) unsigned NOT NULL DEFAULT 0,
  `page_hits` int(11) NOT NULL DEFAULT 0,
  `last_seen` int(10) unsigned NOT NULL DEFAULT 0,
  `stat_date` date NOT NULL DEFAULT '1004-01-01',
  PRIMARY KEY (`stat_date`,`id_spider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_subscribed`;
CREATE TABLE `smf_log_subscribed` (
  `id_sublog` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_subscribe` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_member` int(11) NOT NULL DEFAULT 0,
  `old_id_group` smallint(6) NOT NULL DEFAULT 0,
  `start_time` int(11) NOT NULL DEFAULT 0,
  `end_time` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `payments_pending` tinyint(4) NOT NULL DEFAULT 0,
  `pending_details` text NOT NULL,
  `reminder_sent` tinyint(4) NOT NULL DEFAULT 0,
  `vendor_ref` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_sublog`),
  UNIQUE KEY `id_subscribe` (`id_subscribe`,`id_member`),
  KEY `idx_end_time` (`end_time`),
  KEY `idx_reminder_sent` (`reminder_sent`),
  KEY `idx_payments_pending` (`payments_pending`),
  KEY `idx_status` (`status`),
  KEY `idx_id_member` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_log_topics`;
CREATE TABLE `smf_log_topics` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `unwatched` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`id_topic`),
  KEY `idx_id_topic` (`id_topic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_log_topics` (`id_member`, `id_topic`, `id_msg`, `unwatched`) VALUES
(1,	1,	1,	0);

DROP TABLE IF EXISTS `smf_lp_blocks`;
CREATE TABLE `smf_lp_blocks` (
  `block_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `placement` varchar(10) NOT NULL,
  `priority` tinyint(1) unsigned DEFAULT 0,
  `permissions` tinyint(1) unsigned DEFAULT 0,
  `status` tinyint(1) unsigned DEFAULT 1,
  `areas` varchar(255) NOT NULL DEFAULT 'all',
  `title_class` varchar(255) DEFAULT NULL,
  `content_class` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_lp_categories`;
CREATE TABLE `smf_lp_categories` (
  `category_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `priority` tinyint(1) unsigned DEFAULT 0,
  `status` tinyint(1) unsigned DEFAULT 1,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_lp_comments`;
CREATE TABLE `smf_lp_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT 0,
  `page_id` smallint(5) unsigned DEFAULT NULL,
  `author_id` mediumint(8) unsigned DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_lp_pages`;
CREATE TABLE `smf_lp_pages` (
  `page_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT 0,
  `author_id` mediumint(8) unsigned DEFAULT 0,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `type` varchar(10) NOT NULL DEFAULT 'bbc',
  `entry_type` varchar(10) NOT NULL DEFAULT 'default',
  `permissions` tinyint(1) unsigned DEFAULT 0,
  `status` tinyint(1) unsigned DEFAULT 1,
  `num_views` int(10) unsigned DEFAULT 0,
  `num_comments` int(10) unsigned DEFAULT 0,
  `created_at` int(10) unsigned DEFAULT 0,
  `updated_at` int(10) unsigned DEFAULT 0,
  `deleted_at` int(10) unsigned DEFAULT 0,
  `last_comment_id` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `slug` (`slug`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_lp_pages` (`page_id`, `category_id`, `author_id`, `slug`, `description`, `content`, `type`, `entry_type`, `permissions`, `status`, `num_views`, `num_comments`, `created_at`, `updated_at`, `deleted_at`, `last_comment_id`) VALUES
(1,	0,	1,	'home',	NULL,	'<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc porttitor posuere accumsan. Aliquam erat volutpat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Phasellus vel blandit dui. Aliquam nunc est, vehicula sit amet eleifend in, scelerisque quis sem. In aliquam nec lorem nec volutpat. Sed eu blandit erat. Suspendisse elementum lectus a ligula commodo, at lobortis justo accumsan. Aliquam mollis lectus ultricies, semper urna eu, fermentum eros. Sed a interdum odio. Quisque sit amet feugiat enim. Curabitur aliquam lectus at metus tristique tempus. Sed vitae nisi ultricies, tincidunt lacus non, ultrices ante.</p><p><br></p>\n				<p>Duis ac ex sed dolor suscipit vulputate at eu ligula. Aliquam efficitur ac ante convallis ultricies. Nullam pretium vitae purus dapibus tempor. Aenean vel fringilla eros. Proin lectus velit, tristique ut condimentum eu, semper sed ipsum. Duis venenatis dolor lectus, et ullamcorper tortor varius eu. Vestibulum quis nisi ut nunc mollis fringilla. Sed consectetur semper magna, eget blandit nulla commodo sed. Aenean sem ipsum, auctor eget enim id, scelerisque malesuada nibh. Nulla ornare pharetra laoreet. Phasellus dignissim nisl nec arcu cursus luctus.</p><p><br></p>\n				<p>Aliquam in quam ut diam consectetur semper. Aliquam commodo mi purus, bibendum laoreet massa tristique eget. Suspendisse ut purus nisi. Mauris euismod dolor nec scelerisque ullamcorper. Praesent imperdiet semper neque, ac luctus nunc ultricies eget. Praesent sodales ante sed dignissim vulputate. Ut vel ligula id sem feugiat sollicitudin non at metus. Aliquam vel est non sapien sodales semper. Suspendisse potenti. Sed convallis quis turpis eu pulvinar. Vivamus nulla elit, condimentum vitae commodo eu, pellentesque ullamcorper enim. Maecenas faucibus dolor nec enim interdum, quis iaculis lacus suscipit. Pellentesque aliquam, lectus id volutpat euismod, ante tellus mollis dui, sed placerat erat arcu sit amet purus.</p>',	'html',	'default',	3,	1,	1,	0,	1756827505,	0,	0,	0);

DROP TABLE IF EXISTS `smf_lp_page_tag`;
CREATE TABLE `smf_lp_page_tag` (
  `page_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`page_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_lp_params`;
CREATE TABLE `smf_lp_params` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'block',
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id_type_name` (`item_id`,`type`,`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_lp_params` (`id`, `item_id`, `type`, `name`, `value`) VALUES
(1,	1,	'page',	'show_author_and_date',	'0');

DROP TABLE IF EXISTS `smf_lp_plugins`;
CREATE TABLE `smf_lp_plugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `config` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_config` (`name`,`config`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_lp_plugins` (`id`, `name`, `config`, `value`) VALUES
(1,	'hello_portal',	'keyboard_navigation',	'1'),
(2,	'hello_portal',	'show_buttons',	'1'),
(3,	'hello_portal',	'show_progress',	'1'),
(4,	'hello_portal',	'theme',	'flattener'),
(5,	'blog_mode',	'blog_action',	'blog'),
(6,	'blog_mode',	'show_blogs_in_profiles',	''),
(7,	'search',	'min_chars',	'3');

DROP TABLE IF EXISTS `smf_lp_simple_chat_messages`;
CREATE TABLE `smf_lp_simple_chat_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `block_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` int(10) unsigned DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_lp_tags`;
CREATE TABLE `smf_lp_tags` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT 1,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_lp_titles`;
CREATE TABLE `smf_lp_titles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'block',
  `lang` varchar(20) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id_type_lang` (`item_id`,`type`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_lp_titles` (`id`, `item_id`, `type`, `lang`, `value`) VALUES
(1,	1,	'page',	'english',	'My Community');

DROP TABLE IF EXISTS `smf_mail_queue`;
CREATE TABLE `smf_mail_queue` (
  `id_mail` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time_sent` int(11) NOT NULL DEFAULT 0,
  `recipient` varchar(255) NOT NULL DEFAULT '',
  `body` mediumtext NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `headers` text NOT NULL,
  `send_html` tinyint(4) NOT NULL DEFAULT 0,
  `priority` tinyint(4) NOT NULL DEFAULT 1,
  `private` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_mail`),
  KEY `idx_time_sent` (`time_sent`),
  KEY `idx_mail_priority` (`priority`,`id_mail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_membergroups`;
CREATE TABLE `smf_membergroups` (
  `id_group` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(80) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `online_color` varchar(20) NOT NULL DEFAULT '',
  `min_posts` mediumint(9) NOT NULL DEFAULT -1,
  `max_messages` smallint(5) unsigned NOT NULL DEFAULT 0,
  `icons` varchar(255) NOT NULL DEFAULT '',
  `group_type` tinyint(4) NOT NULL DEFAULT 0,
  `hidden` tinyint(4) NOT NULL DEFAULT 0,
  `id_parent` smallint(6) NOT NULL DEFAULT -2,
  `tfa_required` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_group`),
  KEY `idx_min_posts` (`min_posts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_membergroups` (`id_group`, `group_name`, `description`, `online_color`, `min_posts`, `max_messages`, `icons`, `group_type`, `hidden`, `id_parent`, `tfa_required`) VALUES
(1,	'Administrator',	'',	'#FF0000',	-1,	0,	'5#iconadmin.png',	1,	0,	-2,	0),
(2,	'Global Moderator',	'',	'#0000FF',	-1,	0,	'5#icongmod.png',	0,	0,	-2,	0),
(3,	'Moderator',	'',	'',	-1,	0,	'5#iconmod.png',	0,	0,	-2,	0),
(4,	'Newbie',	'',	'',	0,	0,	'1#icon.png',	0,	0,	-2,	0),
(5,	'Jr. Member',	'',	'',	50,	0,	'2#icon.png',	0,	0,	-2,	0),
(6,	'Full Member',	'',	'',	100,	0,	'3#icon.png',	0,	0,	-2,	0),
(7,	'Sr. Member',	'',	'',	250,	0,	'4#icon.png',	0,	0,	-2,	0),
(8,	'Hero Member',	'',	'',	500,	0,	'5#icon.png',	0,	0,	-2,	0);

DROP TABLE IF EXISTS `smf_members`;
CREATE TABLE `smf_members` (
  `id_member` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `member_name` varchar(80) NOT NULL DEFAULT '',
  `date_registered` int(10) unsigned NOT NULL DEFAULT 0,
  `posts` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `lngfile` varchar(255) NOT NULL DEFAULT '',
  `last_login` int(10) unsigned NOT NULL DEFAULT 0,
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `instant_messages` smallint(6) NOT NULL DEFAULT 0,
  `unread_messages` smallint(6) NOT NULL DEFAULT 0,
  `new_pm` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `alerts` int(10) unsigned NOT NULL DEFAULT 0,
  `buddy_list` text NOT NULL,
  `pm_ignore_list` text DEFAULT NULL,
  `pm_prefs` mediumint(9) NOT NULL DEFAULT 0,
  `mod_prefs` varchar(20) NOT NULL DEFAULT '',
  `passwd` varchar(64) NOT NULL DEFAULT '',
  `email_address` varchar(255) NOT NULL DEFAULT '',
  `personal_text` varchar(255) NOT NULL DEFAULT '',
  `birthdate` date NOT NULL DEFAULT '1004-01-01',
  `website_title` varchar(255) NOT NULL DEFAULT '',
  `website_url` varchar(255) NOT NULL DEFAULT '',
  `show_online` tinyint(4) NOT NULL DEFAULT 1,
  `time_format` varchar(80) NOT NULL DEFAULT '',
  `signature` text NOT NULL,
  `time_offset` float NOT NULL DEFAULT 0,
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `usertitle` varchar(255) NOT NULL DEFAULT '',
  `member_ip` varbinary(16) DEFAULT NULL,
  `member_ip2` varbinary(16) DEFAULT NULL,
  `secret_question` varchar(255) NOT NULL DEFAULT '',
  `secret_answer` varchar(64) NOT NULL DEFAULT '',
  `id_theme` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_activated` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `validation_code` varchar(10) NOT NULL DEFAULT '',
  `id_msg_last_visit` int(10) unsigned NOT NULL DEFAULT 0,
  `additional_groups` varchar(255) NOT NULL DEFAULT '',
  `smiley_set` varchar(48) NOT NULL DEFAULT '',
  `id_post_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  `total_time_logged_in` int(10) unsigned NOT NULL DEFAULT 0,
  `password_salt` varchar(255) NOT NULL DEFAULT '',
  `ignore_boards` text NOT NULL,
  `warning` tinyint(4) NOT NULL DEFAULT 0,
  `passwd_flood` varchar(12) NOT NULL DEFAULT '',
  `pm_receive_from` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `timezone` varchar(80) NOT NULL DEFAULT '',
  `tfa_secret` varchar(24) NOT NULL DEFAULT '',
  `tfa_backup` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_member`),
  KEY `idx_member_name` (`member_name`),
  KEY `idx_real_name` (`real_name`),
  KEY `idx_email_address` (`email_address`),
  KEY `idx_date_registered` (`date_registered`),
  KEY `idx_id_group` (`id_group`),
  KEY `idx_birthdate` (`birthdate`),
  KEY `idx_posts` (`posts`),
  KEY `idx_last_login` (`last_login`),
  KEY `idx_lngfile` (`lngfile`(30)),
  KEY `idx_id_post_group` (`id_post_group`),
  KEY `idx_warning` (`warning`),
  KEY `idx_total_time_logged_in` (`total_time_logged_in`),
  KEY `idx_id_theme` (`id_theme`),
  KEY `idx_active_real_name` (`is_activated`,`real_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_members` (`id_member`, `member_name`, `date_registered`, `posts`, `id_group`, `lngfile`, `last_login`, `real_name`, `instant_messages`, `unread_messages`, `new_pm`, `alerts`, `buddy_list`, `pm_ignore_list`, `pm_prefs`, `mod_prefs`, `passwd`, `email_address`, `personal_text`, `birthdate`, `website_title`, `website_url`, `show_online`, `time_format`, `signature`, `time_offset`, `avatar`, `usertitle`, `member_ip`, `member_ip2`, `secret_question`, `secret_answer`, `id_theme`, `is_activated`, `validation_code`, `id_msg_last_visit`, `additional_groups`, `smiley_set`, `id_post_group`, `total_time_logged_in`, `password_salt`, `ignore_boards`, `warning`, `passwd_flood`, `pm_receive_from`, `timezone`, `tfa_secret`, `tfa_backup`) VALUES
(1,	'Test',	1756827326,	0,	1,	'',	1756827562,	'Test',	0,	0,	0,	0,	'',	'',	0,	'',	'$2y$10$dDRm9XlsVgnJxy8//B5mHuTvrm7l4GOm6Je2SPPxhrV6NTE7AgiJW',	'test@test.com',	'',	'1004-01-01',	'',	'',	1,	'',	'',	0,	'',	'',	UNHEX('7F000001'),	UNHEX('7F000001'),	'',	'',	0,	1,	'',	1,	'',	'',	0,	229,	'db9c170e0efa6fc28d2043a223a02607',	'',	0,	'',	1,	'',	'',	'');

DROP TABLE IF EXISTS `smf_member_logins`;
CREATE TABLE `smf_member_logins` (
  `id_login` int(11) NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(9) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  `ip` varbinary(16) DEFAULT NULL,
  `ip2` varbinary(16) DEFAULT NULL,
  PRIMARY KEY (`id_login`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_time` (`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_mentions`;
CREATE TABLE `smf_mentions` (
  `content_id` int(11) NOT NULL DEFAULT 0,
  `content_type` varchar(10) NOT NULL DEFAULT '',
  `id_mentioned` int(11) NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`content_id`,`content_type`,`id_mentioned`),
  KEY `content` (`content_id`,`content_type`),
  KEY `mentionee` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_messages`;
CREATE TABLE `smf_messages` (
  `id_msg` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `poster_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_msg_modified` int(10) unsigned NOT NULL DEFAULT 0,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `poster_name` varchar(255) NOT NULL DEFAULT '',
  `poster_email` varchar(255) NOT NULL DEFAULT '',
  `poster_ip` varbinary(16) DEFAULT NULL,
  `smileys_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `modified_time` int(10) unsigned NOT NULL DEFAULT 0,
  `modified_name` varchar(255) NOT NULL DEFAULT '',
  `modified_reason` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `icon` varchar(16) NOT NULL DEFAULT 'xx',
  `approved` tinyint(4) NOT NULL DEFAULT 1,
  `likes` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_msg`),
  UNIQUE KEY `idx_id_board` (`id_board`,`id_msg`,`approved`),
  UNIQUE KEY `idx_id_member` (`id_member`,`id_msg`),
  KEY `idx_ip_index` (`poster_ip`,`id_topic`),
  KEY `idx_participation` (`id_member`,`id_topic`),
  KEY `idx_show_posts` (`id_member`,`id_board`),
  KEY `idx_id_member_msg` (`id_member`,`approved`,`id_msg`),
  KEY `idx_current_topic` (`id_topic`,`id_msg`,`id_member`,`approved`),
  KEY `idx_related_ip` (`id_member`,`poster_ip`,`id_msg`),
  KEY `idx_likes` (`likes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_messages` (`id_msg`, `id_topic`, `id_board`, `poster_time`, `id_member`, `id_msg_modified`, `subject`, `poster_name`, `poster_email`, `poster_ip`, `smileys_enabled`, `modified_time`, `modified_name`, `modified_reason`, `body`, `icon`, `approved`, `likes`) VALUES
(1,	1,	1,	1756827311,	0,	1,	'Welcome to SMF!',	'Simple Machines',	'info@simplemachines.org',	NULL,	1,	0,	'',	'',	'Welcome to Simple Machines Forum!<br><br>We hope you enjoy using your forum.&nbsp; If you have any problems, please feel free to [url=https://www.simplemachines.org/community/index.php]ask us for assistance[/url].<br><br>Thanks!<br>Simple Machines',	'xx',	1,	0);

DROP TABLE IF EXISTS `smf_message_icons`;
CREATE TABLE `smf_message_icons` (
  `id_icon` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL DEFAULT '',
  `filename` varchar(80) NOT NULL DEFAULT '',
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `icon_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_icon`),
  KEY `idx_id_board` (`id_board`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_message_icons` (`id_icon`, `title`, `filename`, `id_board`, `icon_order`) VALUES
(1,	'Standard',	'xx',	0,	0),
(2,	'Thumb Up',	'thumbup',	0,	1),
(3,	'Thumb Down',	'thumbdown',	0,	2),
(4,	'Exclamation point',	'exclamation',	0,	3),
(5,	'Question mark',	'question',	0,	4),
(6,	'Lamp',	'lamp',	0,	5),
(7,	'Smiley',	'smiley',	0,	6),
(8,	'Angry',	'angry',	0,	7),
(9,	'Cheesy',	'cheesy',	0,	8),
(10,	'Grin',	'grin',	0,	9),
(11,	'Sad',	'sad',	0,	10),
(12,	'Wink',	'wink',	0,	11),
(13,	'Poll',	'poll',	0,	12);

DROP TABLE IF EXISTS `smf_moderators`;
CREATE TABLE `smf_moderators` (
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_board`,`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_moderator_groups`;
CREATE TABLE `smf_moderator_groups` (
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_group` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_board`,`id_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_package_servers`;
CREATE TABLE `smf_package_servers` (
  `id_server` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `validation_url` varchar(255) NOT NULL DEFAULT '',
  `extra` text DEFAULT NULL,
  PRIMARY KEY (`id_server`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_package_servers` (`id_server`, `name`, `url`, `validation_url`, `extra`) VALUES
(1,	'Simple Machines Third-party Mod Site',	'https://custom.simplemachines.org/packages/mods',	'https://custom.simplemachines.org/api.php?action=validate;version=v1;smf_version={SMF_VERSION}',	NULL),
(2,	'Simple Machines Downloads Site',	'https://download.simplemachines.org/browse.php?api=v1;smf_version={SMF_VERSION}',	'https://download.simplemachines.org/validate.php?api=v1;smf_version={SMF_VERSION}',	NULL);

DROP TABLE IF EXISTS `smf_permissions`;
CREATE TABLE `smf_permissions` (
  `id_group` smallint(6) NOT NULL DEFAULT 0,
  `permission` varchar(30) NOT NULL DEFAULT '',
  `add_deny` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_group`,`permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_permissions` (`id_group`, `permission`, `add_deny`) VALUES
(-1,	'calendar_view',	1),
(-1,	'search_posts',	1),
(-1,	'view_stats',	1),
(0,	'calendar_view',	1),
(0,	'pm_draft',	1),
(0,	'pm_read',	1),
(0,	'pm_send',	1),
(0,	'profile_blurb_own',	1),
(0,	'profile_displayed_name_own',	1),
(0,	'profile_extra_own',	1),
(0,	'profile_forum_own',	1),
(0,	'profile_identity_own',	1),
(0,	'profile_password_own',	1),
(0,	'profile_remote_avatar',	1),
(0,	'profile_remove_own',	1),
(0,	'profile_server_avatar',	1),
(0,	'profile_signature_own',	1),
(0,	'profile_upload_avatar',	1),
(0,	'profile_view',	1),
(0,	'profile_website_own',	1),
(0,	'search_posts',	1),
(0,	'send_email_to_members',	1),
(0,	'view_mlist',	1),
(0,	'view_stats',	1),
(0,	'who_view',	1),
(2,	'access_mod_center',	1),
(2,	'calendar_edit_any',	1),
(2,	'calendar_post',	1),
(2,	'calendar_view',	1),
(2,	'pm_draft',	1),
(2,	'pm_read',	1),
(2,	'pm_send',	1),
(2,	'profile_blurb_own',	1),
(2,	'profile_displayed_name_own',	1),
(2,	'profile_extra_own',	1),
(2,	'profile_forum_own',	1),
(2,	'profile_identity_own',	1),
(2,	'profile_password_own',	1),
(2,	'profile_remote_avatar',	1),
(2,	'profile_remove_own',	1),
(2,	'profile_server_avatar',	1),
(2,	'profile_signature_own',	1),
(2,	'profile_title_own',	1),
(2,	'profile_upload_avatar',	1),
(2,	'profile_view',	1),
(2,	'profile_website_own',	1),
(2,	'search_posts',	1),
(2,	'send_email_to_members',	1),
(2,	'view_mlist',	1),
(2,	'view_stats',	1),
(2,	'who_view',	1);

DROP TABLE IF EXISTS `smf_permission_profiles`;
CREATE TABLE `smf_permission_profiles` (
  `id_profile` smallint(6) NOT NULL AUTO_INCREMENT,
  `profile_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_profile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_permission_profiles` (`id_profile`, `profile_name`) VALUES
(1,	'default'),
(2,	'no_polls'),
(3,	'reply_only'),
(4,	'read_only');

DROP TABLE IF EXISTS `smf_personal_messages`;
CREATE TABLE `smf_personal_messages` (
  `id_pm` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pm_head` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member_from` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `deleted_by_sender` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `from_name` varchar(255) NOT NULL DEFAULT '',
  `msgtime` int(10) unsigned NOT NULL DEFAULT 0,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  PRIMARY KEY (`id_pm`),
  KEY `idx_id_member` (`id_member_from`,`deleted_by_sender`),
  KEY `idx_msgtime` (`msgtime`),
  KEY `idx_id_pm_head` (`id_pm_head`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_pm_labeled_messages`;
CREATE TABLE `smf_pm_labeled_messages` (
  `id_label` int(10) unsigned NOT NULL DEFAULT 0,
  `id_pm` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_label`,`id_pm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_pm_labels`;
CREATE TABLE `smf_pm_labels` (
  `id_label` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `name` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_pm_recipients`;
CREATE TABLE `smf_pm_recipients` (
  `id_pm` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `bcc` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_read` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_new` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `in_inbox` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_pm`,`id_member`),
  UNIQUE KEY `idx_id_member` (`id_member`,`deleted`,`id_pm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_pm_rules`;
CREATE TABLE `smf_pm_rules` (
  `id_rule` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `rule_name` varchar(60) NOT NULL,
  `criteria` text NOT NULL,
  `actions` text NOT NULL,
  `delete_pm` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `is_or` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_rule`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_delete_pm` (`delete_pm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_polls`;
CREATE TABLE `smf_polls` (
  `id_poll` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL DEFAULT '',
  `voting_locked` tinyint(4) NOT NULL DEFAULT 0,
  `max_votes` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `expire_time` int(10) unsigned NOT NULL DEFAULT 0,
  `hide_results` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `change_vote` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `guest_vote` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `num_guest_voters` int(10) unsigned NOT NULL DEFAULT 0,
  `reset_poll` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `poster_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_poll`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_poll_choices`;
CREATE TABLE `smf_poll_choices` (
  `id_poll` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_choice` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `label` varchar(255) NOT NULL DEFAULT '',
  `votes` smallint(5) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_poll`,`id_choice`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_qanda`;
CREATE TABLE `smf_qanda` (
  `id_question` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `lngfile` varchar(255) NOT NULL DEFAULT '',
  `question` varchar(255) NOT NULL DEFAULT '',
  `answers` text NOT NULL,
  PRIMARY KEY (`id_question`),
  KEY `idx_lngfile` (`lngfile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_scheduled_tasks`;
CREATE TABLE `smf_scheduled_tasks` (
  `id_task` smallint(6) NOT NULL AUTO_INCREMENT,
  `next_time` int(11) NOT NULL DEFAULT 0,
  `time_offset` int(11) NOT NULL DEFAULT 0,
  `time_regularity` smallint(6) NOT NULL DEFAULT 0,
  `time_unit` varchar(1) NOT NULL DEFAULT 'h',
  `disabled` tinyint(4) NOT NULL DEFAULT 0,
  `task` varchar(24) NOT NULL DEFAULT '',
  `callable` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_task`),
  UNIQUE KEY `idx_task` (`task`),
  KEY `idx_next_time` (`next_time`),
  KEY `idx_disabled` (`disabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_scheduled_tasks` (`id_task`, `next_time`, `time_offset`, `time_regularity`, `time_unit`, `disabled`, `task`, `callable`) VALUES
(3,	1756857660,	60,	1,	'd',	0,	'daily_maintenance',	''),
(5,	1756857600,	0,	1,	'd',	0,	'daily_digest',	''),
(6,	1757376000,	0,	1,	'w',	0,	'weekly_digest',	''),
(7,	1756882500,	111343,	1,	'd',	1,	'fetchSMfiles',	''),
(8,	0,	0,	1,	'd',	1,	'birthdayemails',	''),
(9,	1757376000,	0,	1,	'w',	0,	'weekly_maintenance',	''),
(10,	0,	120,	1,	'd',	1,	'paid_subscriptions',	''),
(11,	1756857720,	120,	1,	'd',	0,	'remove_temp_attachments',	''),
(12,	1756857780,	180,	1,	'd',	0,	'remove_topic_redirect',	''),
(13,	1756857840,	240,	1,	'd',	0,	'remove_old_drafts',	''),
(14,	0,	0,	1,	'w',	1,	'prune_log_topics',	'');

DROP TABLE IF EXISTS `smf_sessions`;
CREATE TABLE `smf_sessions` (
  `session_id` varchar(128) NOT NULL DEFAULT '',
  `last_update` int(10) unsigned NOT NULL DEFAULT 0,
  `data` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_settings`;
CREATE TABLE `smf_settings` (
  `variable` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`variable`(30))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_settings` (`variable`, `value`) VALUES
('additional_options_collapsable',	'1'),
('adminlog_enabled',	'1'),
('alerts_auto_purge',	'30'),
('allow_editDisplayName',	'1'),
('allow_expire_redirect',	'1'),
('allow_guestAccess',	'1'),
('allow_hideOnline',	'1'),
('attachmentCheckExtensions',	'0'),
('attachmentDirFileLimit',	'1000'),
('attachmentDirSizeLimit',	'10240'),
('attachmentEnable',	'1'),
('attachmentExtensions',	'doc,gif,jpg,mpg,pdf,png,txt,zip'),
('attachmentNumPerPostLimit',	'4'),
('attachmentPostLimit',	'1920'),
('attachmentShowImages',	'1'),
('attachmentSizeLimit',	'1280'),
('attachments_21_done',	'1'),
('attachmentThumbHeight',	'150'),
('attachmentThumbnails',	'1'),
('attachmentThumbWidth',	'150'),
('attachmentUploadDir',	'{\"1\":\"/var/www/html/attachments\"}'),
('attachment_image_paranoid',	'0'),
('attachment_image_reencode',	'1'),
('attachment_thumb_png',	'1'),
('autoFixDatabase',	'1'),
('autoLinkUrls',	'1'),
('avatar_action_too_large',	'option_css_resize'),
('avatar_directory',	'/var/www/html/avatars'),
('avatar_download_png',	'1'),
('avatar_max_height_external',	'65'),
('avatar_max_height_upload',	'65'),
('avatar_max_width_external',	'65'),
('avatar_max_width_upload',	'65'),
('avatar_paranoid',	'0'),
('avatar_reencode',	'1'),
('avatar_resize_upload',	'1'),
('avatar_url',	'https://localhost/avatars'),
('banLastUpdated',	'0'),
('bcrypt_hash_cost',	'14'),
('birthday_email',	'happy_birthday'),
('boardindex_max_depth',	'5'),
('board_manager_groups',	'1'),
('browser_cache',	'1756827509'),
('cache_enable',	'1'),
('cal_daysaslink',	'0'),
('cal_days_for_index',	'7'),
('cal_defaultboard',	''),
('cal_disable_prev_next',	'0'),
('cal_display_type',	'0'),
('cal_enabled',	'0'),
('cal_maxspan',	'0'),
('cal_maxyear',	'2030'),
('cal_minyear',	'2008'),
('cal_prev_next_links',	'1'),
('cal_short_days',	'0'),
('cal_short_months',	'0'),
('cal_showbdays',	'1'),
('cal_showevents',	'1'),
('cal_showholidays',	'1'),
('cal_showInTopic',	'1'),
('cal_week_links',	'2'),
('censorIgnoreCase',	'1'),
('censor_proper',	''),
('censor_vulgar',	''),
('compactTopicPagesContiguous',	'5'),
('compactTopicPagesEnable',	'1'),
('cookieTime',	'3153600'),
('currentAttachmentUploadDir',	'1'),
('custom_avatar_dir',	'/var/www/html/custom_avatar'),
('custom_avatar_url',	'https://localhost/custom_avatar'),
('databaseSession_enable',	'1'),
('databaseSession_lifetime',	'2880'),
('databaseSession_loose',	'1'),
('defaultMaxListItems',	'15'),
('defaultMaxMembers',	'30'),
('defaultMaxMessages',	'15'),
('defaultMaxTopics',	'20'),
('default_personal_text',	''),
('default_timezone',	'Asia/Karachi'),
('disabledBBC',	'acronym,bdo,black,blue,flash,ftp,glow,green,move,red,shadow,tt,white'),
('displayFields',	'[{\"col_name\":\"cust_icq\",\"title\":\"ICQ\",\"type\":\"text\",\"order\":\"1\",\"bbc\":\"0\",\"placement\":\"1\",\"enclose\":\"<a class=\\\"icq\\\" href=\\\"\\/\\/www.icq.com\\/people\\/{INPUT}\\\" target=\\\"_blank\\\" title=\\\"ICQ - {INPUT}\\\"><img src=\\\"{DEFAULT_IMAGES_URL}\\/icq.png\\\" alt=\\\"ICQ - {INPUT}\\\"><\\/a>\",\"mlist\":\"0\"},{\"col_name\":\"cust_skype\",\"title\":\"Skype\",\"type\":\"text\",\"order\":\"2\",\"bbc\":\"0\",\"placement\":\"1\",\"enclose\":\"<a href=\\\"skype:{INPUT}?call\\\"><img src=\\\"{DEFAULT_IMAGES_URL}\\/skype.png\\\" alt=\\\"{INPUT}\\\" title=\\\"{INPUT}\\\" \\/><\\/a> \",\"mlist\":\"0\"},{\"col_name\":\"cust_loca\",\"title\":\"Location\",\"type\":\"text\",\"order\":\"4\",\"bbc\":\"0\",\"placement\":\"0\",\"enclose\":\"\",\"mlist\":\"0\"},{\"col_name\":\"cust_gender\",\"title\":\"Gender\",\"type\":\"radio\",\"order\":\"5\",\"bbc\":\"0\",\"placement\":\"1\",\"enclose\":\"<span class=\\\" main_icons gender_{KEY}\\\" title=\\\"{INPUT}\\\"><\\/span>\",\"mlist\":\"0\",\"options\":[\"None\",\"Male\",\"Female\"]}]'),
('dont_repeat_buddylists',	'1'),
('dont_repeat_smileys_20',	'1'),
('dont_repeat_theme_core',	'1'),
('drafts_autosave_enabled',	'1'),
('drafts_keep_days',	'7'),
('drafts_pm_enabled',	'1'),
('drafts_post_enabled',	'1'),
('drafts_show_saved_enabled',	'1'),
('edit_disable_time',	'0'),
('edit_wait_time',	'90'),
('enableAllMessages',	'0'),
('enableBBC',	'1'),
('enableCompressedOutput',	'1'),
('enableErrorLogging',	'1'),
('enableParticipation',	'1'),
('enablePostHTML',	'0'),
('enablePreviousNext',	'1'),
('enableThemes',	'1'),
('enable_ajax_alerts',	'1'),
('enable_buddylist',	'1'),
('export_dir',	'/var/www/html/exports'),
('export_expiry',	'7'),
('export_min_diskspace_pct',	'5'),
('export_rate',	'250'),
('failed_login_threshold',	'3'),
('force_ssl',	'1'),
('frame_security',	'SAMEORIGIN'),
('global_character_set',	'UTF-8'),
('gravatarAllowExtraEmail',	'1'),
('gravatarEnabled',	'1'),
('gravatarMaxRating',	'PG'),
('gravatarOverride',	'0'),
('httponlyCookies',	'1'),
('integrate_pre_include',	'$sourcedir/LightPortal/app.php'),
('json_done',	'1'),
('jquery_source', 'local'),
('knownThemes',	'1'),
('lastActive',	'15'),
('last_mod_report_action',	'0'),
('latestMember',	'1'),
('latestRealName',	'Test'),
('loginHistoryDays',	'30'),
('lp_comment_block',	'none'),
('lp_enabled_plugins',	'CodeMirror,HelloPortal,ThemeSwitcher,UserInfo'),
('lp_fa_source',	'css_cdn'),
('lp_frontpage_article_sorting',	'1'),
('lp_frontpage_chosen_page',	'home'),
('lp_frontpage_layout',	'default.blade.php'),
('lp_frontpage_mode',	'chosen_page'),
('lp_frontpage_title',	'My Community'),
('lp_num_items_per_page',	'10'),
('lp_show_views_and_comments',	'1'),
('lp_standalone_url',	'https://localhost/portal.php'),
('mail_limit',	'5'),
('mail_next_send',	'0'),
('mail_quantity',	'5'),
('mail_recent',	'0000000000|0'),
('mail_type',	'0'),
('mark_read_beyond',	'90'),
('mark_read_delete_beyond',	'365'),
('mark_read_max_users',	'500'),
('maxMsgID',	'1'),
('max_image_height',	'0'),
('max_image_width',	'0'),
('max_messageLength',	'20000'),
('memberlist_updated',	'1756827326'),
('minimize_files',	'1'),
('modlog_enabled',	'1'),
('mostDate',	'1756827436'),
('mostOnline',	'1'),
('mostOnlineToday',	'1'),
('mostOnlineUpdated',	'2025-09-02'),
('news',	'SMF - Just Installed!'),
('next_task_time',	'1756857600'),
('number_format',	'1234.00'),
('oldTopicDays',	'120'),
('onlineEnable',	'0'),
('package_make_backups',	''),
('package_port',	'21'),
('package_server',	'localhost'),
('permission_enable_deny',	'0'),
('permission_enable_postgroups',	'0'),
('pm_spam_settings',	'10,5,20'),
('pollMode',	'1'),
('pruningOptions',	'30,180,180,180,30,0'),
('rand_seed',	'1756827333.4042'),
('recycle_board',	'0'),
('recycle_enable',	'0'),
('registration_method',	'0'),
('reg_verification',	'1'),
('requireAgreement',	'1'),
('requirePolicyAgreement',	'0'),
('reserveCase',	'1'),
('reserveName',	'1'),
('reserveNames',	'Admin\nWebmaster\nGuest\nroot'),
('reserveUser',	'1'),
('reserveWord',	'0'),
('samesiteCookies',	'lax'),
('search_cache_size',	'50'),
('search_floodcontrol_time',	'5'),
('search_max_results',	'1200'),
('search_results_per_page',	'30'),
('search_weight_age',	'25'),
('search_weight_first_message',	'10'),
('search_weight_frequency',	'30'),
('search_weight_length',	'20'),
('search_weight_subject',	'15'),
('securityDisable',	'1'),
('securityDisable_moderate',	'1'),
('send_validation_onChange',	'0'),
('send_welcomeEmail',	'1'),
('settings_updated',	'1756827519'),
('show_blurb',	'1'),
('show_modify',	'1'),
('show_profile_buttons',	'1'),
('show_user_images',	'1'),
('signature_settings',	'1,300,0,0,0,0,0,0:'),
('smfVersion',	'2.1.6'),
('smileys_dir',	'/var/www/html/Smileys'),
('smileys_url',	'https://localhost/Smileys'),
('smiley_sets_default',	'fugue'),
('smiley_sets_known',	'fugue,alienine'),
('smiley_sets_names',	'Fugue\'s Set\nAlienine\'s Set'),
('smtp_host',	''),
('smtp_password',	''),
('smtp_port',	'25'),
('smtp_username',	''),
('spamWaitTime',	'5'),
('tfa_mode',	'1'),
('theme_allow',	'1'),
('theme_default',	'1'),
('theme_guests',	'1'),
('timeLoadPageEnable',	'0'),
('time_format',	'%b %d, %Y, %I:%M %p'),
('titlesEnable',	'1'),
('tld_regex',	'(?>சிங்கப்பூர்|پاکستان|فلسطين|ファッション|ישראל|همراه|संगठन|বাংলা|భారత్|ഭാരതം|дети|تونس|شبكة|ڀارت|ਭਾਰਤ|ભારત|ଭାରତ|ಭಾರತ|ලංකා|アマゾン|クラウド|グーグル|ポイント|组织机构|電訊盈科|укр|қаз|հայ|קום|قطر|कॉम|नेट|भार(?>ोत|त(?>म्|))|คอม|ไทย|ລາວ|みんな|ストア|セール|亚马逊|天主教|我爱你|淡马锡|飞利浦|ею|سو(?>دان|رية)|ভা(?>রত|ৰত)|გე|コム|世界|企业|佛山|信息|健康|八卦|嘉里(?>大酒店|)|在线|大拿|娱乐|家電|广东|微博|慈善|手机|招聘|时尚|書籍|机构|游戏|澳門|点看|移动|联通|谷歌|购物|通販|集团|食品|餐厅|삼성|한국|a(?>kdn|a(?>rp|a)|b(?>udhabi|ogado|le|b(?>ott|vie|)|c)|c(?>ademy|tor|c(?>ountant(?>s|)|enture)|o|)|d(?>ult|s|)|e(?>tna|ro|g|)|f(?>rica|l|)|g(?>akhan|ency|)|i(?>g|r(?>force|bus|tel)|)|l(?>i(?>baba|pay)|l(?>finanz|state|y)|s(?>ace|tom)|)|m(?>sterdam|azon|fam|ica|e(?>rican(?>express|family)|x)|)|n(?>alytics|droid|quan|z)|o(?>l|)|p(?>artments|p(?>le|))|q(?>uarelle|)|r(?>chi|my|pa|a(?>mco|b)|t(?>e|)|)|s(?>sociates|da|ia|)|t(?>torney|hleta|)|u(?>ction|spost|di(?>ble|o|)|t(?>hor|o(?>s|))|)|w(?>s|)|x(?>a|)|z(?>ure|))|b(?>a(?>uhaus|yern|idu|by|n(?>amex|d|k)|r(?>efoot|gains|c(?>elona|lay(?>card|s))|)|s(?>ketball|eball)|)|b(?>va|c|t|)|c(?>g|n)|d|e(?>rlin|er|st(?>buy|)|a(?>uty|ts)|t|)|f|g|h(?>arti|)|i(?>ble|ke|ng(?>o|)|d|o|z|)|j|l(?>ack(?>friday|)|ue|o(?>ckbuster|omberg|g))|m(?>s|w|)|n(?>pparibas|)|o(?>ehringer|utique|ats|fa|nd|m|o(?>k(?>ing|)|)|s(?>ch|t(?>ik|on))|t|x|)|r(?>idgestone|adesco|ussels|o(?>adway|ther|ker)|)|s|t|u(?>siness|ild(?>ers|)|zz|y)|v|w|y|z(?>h|))|c(?>pa|a(?>non|fe|b|l(?>vinklein|l|)|m(?>era|p|)|p(?>etown|ital(?>one|))|r(?>avan|ds|e(?>er(?>s|)|)|s|)|s(?>ino|a|e|h)|t(?>ering|holic|)|)|b(?>re|a|n)|c|d|e(?>nter|rn|o)|f(?>a|d|)|g|h(?>intai|urch|eap|a(?>rity|se|n(?>nel|el)|t)|r(?>istmas|ome)|)|i(?>priani|rcle|sco|t(?>adel|i(?>c|)|y)|)|k|l(?>eaning|aims|ub(?>med|)|i(?>ck|ni(?>que|c))|o(?>thing|ud)|)|m|n|o(?>rsica|ffee|ach|des|l(?>lege|ogne)|m(?>sec|m(?>unity|bank)|p(?>uter|a(?>ny|re))|)|n(?>dos|s(?>truction|ulting)|t(?>ractors|act))|o(?>king|l|p)|u(?>ntry|rses|pon(?>s|))|)|r(?>icket|edit(?>union|card|)|uise(?>s|)|own|s|)|u(?>isinella|)|v|w|x|y(?>mru|ou|)|z)|d(?>rive|clk|ds|hl|np|tv|a(?>nce|d|t(?>ing|sun|a|e)|y)|e(?>mocrat|gree|al(?>er|s|)|nt(?>ist|al)|si(?>gn|)|l(?>ivery|oitte|ta|l)|v|)|i(?>amonds|gital|rect(?>ory|)|et|s(?>co(?>unt|ver)|h)|y)|j|k|m|o(?>wnload|mains|c(?>tor|s)|g|t|)|u(?>nlop|pont|rban|bai)|v(?>ag|r)|z)|e(?>quipment|vents|pson|a(?>rth|t)|c(?>o|)|d(?>eka|u(?>cation|))|e|g|m(?>erck|ail)|n(?>terprises|gineer(?>ing|)|ergy)|r(?>icsson|ni|)|s(?>tate|q|)|t|u(?>rovision|s|)|x(?>traspace|change|p(?>osed|ress|ert)))|f(?>tr|yi|a(?>mily|ge|rm(?>ers|)|i(?>rwinds|th|l)|n(?>s|)|s(?>hion|t))|e(?>edback|dex|rr(?>ari|ero))|i(?>lm|na(?>nc(?>ial|e)|l)|sh(?>ing|)|d(?>elity|o)|r(?>mdale|e(?>stone|))|t(?>ness|)|)|j|k|l(?>i(?>ghts|ckr|r)|o(?>rist|wers)|y)|m|o(?>undation|o(?>tball|d|)|r(?>sale|ex|um|d)|x|)|r(?>e(?>senius|e)|l|o(?>ntier|gans)|)|u(?>rniture|jitsu|tbol|n(?>d|)))|g(?>a(?>rden|me(?>s|)|l(?>l(?>ery|up|o)|)|p|y|)|b(?>iz|)|d(?>n|)|e(?>orge|nt(?>ing|)|a|)|f|g(?>ee|)|h|i(?>ft(?>s|)|v(?>ing|es)|)|l(?>ass|ob(?>al|o)|e|)|m(?>ail|bh|o|x|)|n|o(?>daddy|l(?>d(?>point|)|f)|o(?>dyear|g(?>le|)|)|p|t|v)|p|q|r(?>een|ipe|a(?>inger|phics|tis)|o(?>cery|up)|)|s|t|u(?>cci|ge|ru|i(?>tars|de)|)|w|y)|h(?>dfc(?>bank|)|sbc|bo|a(?>mburg|ngout|ir|us)|e(?>alth(?>care|)|l(?>sinki|p)|r(?>mes|e))|i(?>samitsu|tachi|phop|v)|k(?>t|)|m|n|o(?>ckey|nda|rse|use|me(?>depot|goods|s(?>ense|))|l(?>dings|iday)|s(?>pital|t(?>ing|))|t(?>mail|els|)|w)|r|t|u(?>ghes|)|y(?>undai|att))|i(?>piranga|kano|bm|fm|c(?>bc|e|u)|d|e(?>ee|)|l|m(?>amat|db|mo(?>bilien|)|)|n(?>vestments|dustries|c|f(?>initi|o)|g|k|s(?>titute|ur(?>ance|e))|t(?>ernational|uit|)|)|o|q|r(?>ish|)|s(?>maili|t(?>anbul|)|)|t(?>au|v|))|j(?>cb|io|ll|nj|a(?>guar|va)|e(?>welry|tzt|ep|)|m(?>p|)|o(?>b(?>urg|s)|t|y|)|p(?>morgan|rs|)|u(?>niper|egos))|k(?>uokgroup|aufen|ddi|fh|e(?>rry(?>properties|hotels)|)|g|h|i(?>tchen|ndle|ds|wi|a|m|)|m|n|o(?>matsu|sher|eln)|p(?>mg|n|)|r(?>ed|d|)|w|y(?>oto|)|z)|l(?>gbt|ds|pl(?>financial|)|a(?>caixa|salle|m(?>borghini|er)|n(?>xess|d(?>rover|))|t(?>robe|ino|)|w(?>yer|)|)|b|c|e(?>clerc|frak|ase|xus|g(?>al|o))|i(?>ghting|lly|dl|fe(?>insurance|style|)|ke|m(?>ited|o)|n(?>coln|k)|v(?>ing|e)|)|k|l(?>c|p)|o(?>ndon|an(?>s|)|tt(?>e|o)|ve|c(?>ker|al|us)|l)|r|s|t(?>d(?>a|)|)|u(?>ndbeck|x(?>ury|e)|)|v|y)|m(?>ba|a(?>drid|keup|ttel|i(?>son|f)|n(?>agement|go|)|p|r(?>shalls|riott|ket(?>ing|s|))|)|c(?>kinsey|)|d|e(?>lbourne|rckmsd|et|d(?>ia|)|m(?>orial|e)|n(?>u|)|)|g|h|i(?>crosoft|ami|l|n(?>i|t)|t(?>subishi|))|k|l(?>b|s|)|m(?>a|)|n|o(?>scow|bi(?>le|)|da|to(?>rcycles|)|e|i|m|n(?>ster|ash|ey)|r(?>tgage|mon)|v(?>ie|)|)|p|q|r|s(?>d|)|t(?>n|r|)|u(?>s(?>eum|ic)|)|v|w|x|y|z)|n(?>ba|hk|tt|yc|a(?>goya|me|vy|b|)|c|e(?>ustar|c|t(?>bank|flix|work|)|w(?>s|)|x(?>us|t(?>direct|))|)|f(?>l|)|g(?>o|)|i(?>nja|ssa(?>n|y)|co|k(?>on|e)|)|l|o(?>rton|kia|w(?>ruz|tv|)|)|p|r(?>a|w|)|u|z)|o(?>kinawa|ffice|saka|pen|oo|vh|b(?>server|i)|l(?>ayan(?>group|)|lo)|m(?>ega|)|n(?>ion|e|g|l(?>ine|))|r(?>igins|a(?>cle|nge)|g(?>anic|))|t(?>suka|t))|p(?>ccw|ub|a(?>nasonic|ge|r(?>is|s|t(?>ners|s|y))|y|)|e(?>t|)|f(?>izer|)|g|h(?>armacy|ilips|ysio|d|o(?>ne|to(?>graphy|s|))|)|i(?>oneer|zza|c(?>s|t(?>ures|et))|d|n(?>g|k|))|k|l(?>a(?>ce|y(?>station|))|u(?>mbing|s)|)|m|n(?>c|)|o(?>litie|ker|hl|rn|st)|r(?>axi|ess|ime|o(?>gressive|tection|pert(?>ies|y)|mo|d(?>uctions|)|f|)|u(?>dential|)|)|s|t|w(?>c|)|y)|q(?>pon|ue(?>bec|st)|a)|r(?>yukyu|a(?>cing|dio)|e(?>liance|cipes|xroth|view(?>s|)|hab|st(?>aurant|)|a(?>d|l(?>estate|t(?>or|y)))|d(?>umbrella|)|i(?>se(?>n|)|t)|n(?>t(?>als|)|)|p(?>ublican|air|ort)|)|i(?>c(?>oh|h(?>ardli|))|l|o|p)|o(?>gers|cks|deo|om|)|s(?>vp|)|u(?>gby|hr|n|)|w(?>e|))|s(?>fr|a(?>arland|kura|fe(?>ty|)|ms(?>club|ung)|rl|ve|xo|l(?>on|e)|n(?>dvik(?>coromant|)|ofi)|p|s|)|b(?>i|s|)|c(?>ience|ot|b|h(?>aeffler|midt|warz|ule|o(?>larships|ol))|)|d|e(?>rvices|lect|cur(?>ity|e)|ner|ven|ek|a(?>rch|t)|w|x(?>y|)|)|g|h(?>ell|a(?>ngrila|rp)|i(?>ksha|a)|o(?>uji|es|p(?>ping|)|w)|)|i(?>lk|te|n(?>gles|a)|)|j|k(?>i(?>n|)|y(?>pe|)|)|l(?>ing|)|m(?>art|ile|)|n(?>cf|)|o(?>ft(?>bank|ware)|hu|c(?>cer|ial)|l(?>utions|ar)|n(?>g|y)|y|)|p(?>a(?>ce|)|o(?>rt|t))|r(?>l|)|s|t(?>ream|yle|ud(?>io|y)|a(?>ples|da|te(?>bank|farm)|r)|c(?>group|)|o(?>ckholm|r(?>age|e))|)|u(?>zuki|cks|pp(?>ort|l(?>ies|y))|r(?>gery|f)|)|v|w(?>atch|iss)|x|y(?>stems|dney|)|z)|t(?>a(?>ipei|obao|rget|lk|b|t(?>too|a(?>motors|r))|x(?>i|))|c(?>i|)|d(?>k|)|e(?>masek|nnis|am|ch(?>nology|)|st|va|l)|f|g|h(?>eat(?>er|re)|d|)|i(?>ckets|enda|aa|ps|r(?>es|ol))|j(?>maxx|x|)|k(?>maxx|)|l|m(?>all|)|n|o(?>shiba|day|kyo|ols|ray|tal|urs|wn|p|y(?>ota|s)|)|r(?>ust|a(?>ining|vel(?>ers(?>insurance|)|)|d(?>ing|e))|v|)|t|u(?>nes|shu|be|i)|v(?>s|)|w|z)|u(?>ol|ps|a|b(?>ank|s)|g|k|n(?>i(?>versity|com)|o)|s|y|z)|v(?>laanderen|a(?>cations|n(?>guard|a)|)|c|e(?>ntures|gas|r(?>mögensberat(?>ung|er)|sicherung|isign)|t|)|g|i(?>ajes|king|llas|rgin|deo|g|n|p|s(?>ion|a)|v(?>a|o)|)|n|o(?>yage|dka|lvo|t(?>ing|e|o))|u)|w(?>hoswho|me|a(?>tch(?>es|)|ng(?>gou|)|l(?>mart|ter|es))|e(?>ather(?>channel|)|b(?>site|cam|er)|d(?>ding|)|i(?>bo|r))|f|i(?>lliamhill|en|ki|n(?>dows|ners|e|))|o(?>lterskluwer|odside|r(?>ld|k(?>s|))|w)|s|t(?>c|f))|x(?>erox|box|xx|yz|i(?>huan|n))|y(?>un|a(?>maxun|chts|ndex|hoo)|e|o(?>dobashi|kohama|ga|u(?>tube|))|t)|z(?>uerich|ero|one|ip|a(?>ppos|ra|)|m|w)|ε(?>λ|υ)|б(?>ел|г)|к(?>атолик|ом)|м(?>кд|о(?>сква|н))|о(?>нлайн|рг)|р(?>ус|ф)|с(?>айт|рб)|ا(?>بوظبي|رامكو|مارات|یران|ل(?>سعودية|بحرين|جزائر|عليان|اردن|مغرب))|ب(?>ھارت|يتك|ا(?>زار|رت))|ع(?>مان|ر(?>اق|ب))|ك(?>اثوليك|وم)|م(?>ليسيا|صر|و(?>ريتانيا|قع))|இ(?>ந்தியா|லங்கை)|中(?>文网|信|国|國)|公(?>司|益)|台(?>湾|灣)|商(?>城|店|标)|政(?>务|府)|新(?>加坡|闻)|网(?>址|店|站|络)|香(?>格里拉|港)|닷(?>넷|컴))'),
('todayMod',	'1'),
('topicSummaryPosts',	'15'),
('topic_move_any',	'0'),
('totalMembers',	'1'),
('totalMessages',	'1'),
('totalTopics',	'1'),
('trackStats',	'1'),
('unapprovedMembers',	'0'),
('userLanguage',	'1'),
('use_subdirectories_for_attachments',	'1'),
('visual_verification_type',	'3'),
('warning_moderate',	'35'),
('warning_mute',	'60'),
('warning_settings',	'1,20,0'),
('warning_watch',	'10'),
('who_enabled',	'1'),
('xmlnews_enable',	'1'),
('xmlnews_maxlen',	'255');

DROP TABLE IF EXISTS `smf_smileys`;
CREATE TABLE `smf_smileys` (
  `id_smiley` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL DEFAULT '',
  `description` varchar(80) NOT NULL DEFAULT '',
  `smiley_row` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `smiley_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `hidden` tinyint(3) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_smiley`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_smileys` (`id_smiley`, `code`, `description`, `smiley_row`, `smiley_order`, `hidden`) VALUES
(1,	':)',	'Smiley',	0,	0,	0),
(2,	';)',	'Wink',	0,	1,	0),
(3,	':D',	'Cheesy',	0,	2,	0),
(4,	';D',	'Grin',	0,	3,	0),
(5,	'>:(',	'Angry',	0,	4,	0),
(6,	':(',	'Sad',	0,	5,	0),
(7,	':o',	'Shocked',	0,	6,	0),
(8,	'8)',	'Cool',	0,	7,	0),
(9,	'???',	'Huh?',	0,	8,	0),
(10,	'::)',	'Roll Eyes',	0,	9,	0),
(11,	':P',	'Tongue',	0,	10,	0),
(12,	':-[',	'Embarrassed',	0,	11,	0),
(13,	':-X',	'Lips Sealed',	0,	12,	0),
(14,	':-\\',	'Undecided',	0,	13,	0),
(15,	':-*',	'Kiss',	0,	14,	0),
(16,	':\'(',	'Cry',	0,	15,	0),
(17,	'>:D',	'Evil',	0,	16,	1),
(18,	'^-^',	'Azn',	0,	17,	1),
(19,	'O0',	'Afro',	0,	18,	1),
(20,	':))',	'Laugh',	0,	19,	1),
(21,	'C:-)',	'Police',	0,	20,	1),
(22,	'O:-)',	'Angel',	0,	21,	1);

DROP TABLE IF EXISTS `smf_smiley_files`;
CREATE TABLE `smf_smiley_files` (
  `id_smiley` smallint(6) NOT NULL DEFAULT 0,
  `smiley_set` varchar(48) NOT NULL DEFAULT '',
  `filename` varchar(48) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_smiley`,`smiley_set`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_smiley_files` (`id_smiley`, `smiley_set`, `filename`) VALUES
(1,	'alienine',	'smiley.png'),
(1,	'fugue',	'smiley.png'),
(2,	'alienine',	'wink.png'),
(2,	'fugue',	'wink.png'),
(3,	'alienine',	'cheesy.png'),
(3,	'fugue',	'cheesy.png'),
(4,	'alienine',	'grin.png'),
(4,	'fugue',	'grin.png'),
(5,	'alienine',	'angry.png'),
(5,	'fugue',	'angry.png'),
(6,	'alienine',	'sad.png'),
(6,	'fugue',	'sad.png'),
(7,	'alienine',	'shocked.png'),
(7,	'fugue',	'shocked.png'),
(8,	'alienine',	'cool.png'),
(8,	'fugue',	'cool.png'),
(9,	'alienine',	'huh.png'),
(9,	'fugue',	'huh.png'),
(10,	'alienine',	'rolleyes.png'),
(10,	'fugue',	'rolleyes.png'),
(11,	'alienine',	'tongue.png'),
(11,	'fugue',	'tongue.png'),
(12,	'alienine',	'embarrassed.png'),
(12,	'fugue',	'embarrassed.png'),
(13,	'alienine',	'lipsrsealed.png'),
(13,	'fugue',	'lipsrsealed.png'),
(14,	'alienine',	'undecided.png'),
(14,	'fugue',	'undecided.png'),
(15,	'alienine',	'kiss.png'),
(15,	'fugue',	'kiss.png'),
(16,	'alienine',	'cry.png'),
(16,	'fugue',	'cry.png'),
(17,	'alienine',	'evil.png'),
(17,	'fugue',	'evil.png'),
(18,	'alienine',	'azn.png'),
(18,	'fugue',	'azn.png'),
(19,	'alienine',	'afro.png'),
(19,	'fugue',	'afro.png'),
(20,	'alienine',	'laugh.png'),
(20,	'fugue',	'laugh.png'),
(21,	'alienine',	'police.png'),
(21,	'fugue',	'police.png'),
(22,	'alienine',	'angel.png'),
(22,	'fugue',	'angel.png');

DROP TABLE IF EXISTS `smf_spiders`;
CREATE TABLE `smf_spiders` (
  `id_spider` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `spider_name` varchar(255) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `ip_info` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_spider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_spiders` (`id_spider`, `spider_name`, `user_agent`, `ip_info`) VALUES
(1,	'Google',	'googlebot',	''),
(2,	'Yahoo!',	'slurp',	''),
(3,	'Bing',	'bingbot',	''),
(4,	'Google (Mobile)',	'Googlebot-Mobile',	''),
(5,	'Google (Image)',	'Googlebot-Image',	''),
(6,	'Google (AdSense)',	'Mediapartners-Google',	''),
(7,	'Google (Adwords)',	'AdsBot-Google',	''),
(8,	'Yahoo! (Mobile)',	'YahooSeeker/M1A1-R2D2',	''),
(9,	'Yahoo! (Image)',	'Yahoo-MMCrawler',	''),
(10,	'Bing (Preview)',	'BingPreview',	''),
(11,	'Bing (Ads)',	'adidxbot',	''),
(12,	'Bing (MSNBot)',	'msnbot',	''),
(13,	'Bing (Media)',	'msnbot-media',	''),
(14,	'Cuil',	'twiceler',	''),
(15,	'Ask',	'Teoma',	''),
(16,	'Baidu',	'Baiduspider',	''),
(17,	'Gigablast',	'Gigabot',	''),
(18,	'InternetArchive',	'ia_archiver-web.archive.org',	''),
(19,	'Alexa',	'ia_archiver',	''),
(20,	'Omgili',	'omgilibot',	''),
(21,	'EntireWeb',	'Speedy Spider',	''),
(22,	'Yandex',	'yandex',	'');

DROP TABLE IF EXISTS `smf_subscriptions`;
CREATE TABLE `smf_subscriptions` (
  `id_subscribe` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `cost` text NOT NULL,
  `length` varchar(6) NOT NULL DEFAULT '',
  `id_group` smallint(6) NOT NULL DEFAULT 0,
  `add_groups` varchar(40) NOT NULL DEFAULT '',
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `repeatable` tinyint(4) NOT NULL DEFAULT 0,
  `allow_partial` tinyint(4) NOT NULL DEFAULT 0,
  `reminder` tinyint(4) NOT NULL DEFAULT 0,
  `email_complete` text NOT NULL,
  PRIMARY KEY (`id_subscribe`),
  KEY `idx_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_themes`;
CREATE TABLE `smf_themes` (
  `id_member` mediumint(9) NOT NULL DEFAULT 0,
  `id_theme` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `variable` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id_theme`,`id_member`,`variable`(30)),
  KEY `idx_id_member` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_themes` (`id_member`, `id_theme`, `variable`, `value`) VALUES
(-1,	1,	'drafts_show_saved_enabled',	'1'),
(-1,	1,	'posts_apply_ignore_list',	'1'),
(-1,	1,	'return_to_post',	'1'),
(0,	1,	'enable_news',	'1'),
(0,	1,	'images_url',	'https://localhost/Themes/default/images'),
(0,	1,	'name',	'SMF Default Theme - Curve2'),
(0,	1,	'newsfader_time',	'3000'),
(0,	1,	'number_recent_posts',	'0'),
(0,	1,	'show_latest_member',	'1'),
(0,	1,	'show_newsfader',	'0'),
(0,	1,	'show_stats_index',	'1'),
(0,	1,	'theme_dir',	'/var/www/html/Themes/default'),
(0,	1,	'theme_url',	'https://localhost/Themes/default'),
(0,	1,	'use_image_buttons',	'1');

DROP TABLE IF EXISTS `smf_topics`;
CREATE TABLE `smf_topics` (
  `id_topic` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `is_sticky` tinyint(4) NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_first_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_last_msg` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member_started` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_member_updated` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_poll` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_previous_board` smallint(6) NOT NULL DEFAULT 0,
  `id_previous_topic` mediumint(9) NOT NULL DEFAULT 0,
  `num_replies` int(10) unsigned NOT NULL DEFAULT 0,
  `num_views` int(10) unsigned NOT NULL DEFAULT 0,
  `locked` tinyint(4) NOT NULL DEFAULT 0,
  `redirect_expires` int(10) unsigned NOT NULL DEFAULT 0,
  `id_redirect_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `unapproved_posts` smallint(6) NOT NULL DEFAULT 0,
  `approved` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_topic`),
  UNIQUE KEY `idx_last_message` (`id_last_msg`,`id_board`),
  UNIQUE KEY `idx_first_message` (`id_first_msg`,`id_board`),
  UNIQUE KEY `idx_poll` (`id_poll`,`id_topic`),
  KEY `idx_is_sticky` (`is_sticky`),
  KEY `idx_approved` (`approved`),
  KEY `idx_member_started` (`id_member_started`,`id_board`),
  KEY `idx_last_message_sticky` (`id_board`,`is_sticky`,`id_last_msg`),
  KEY `idx_board_news` (`id_board`,`id_first_msg`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_topics` (`id_topic`, `is_sticky`, `id_board`, `id_first_msg`, `id_last_msg`, `id_member_started`, `id_member_updated`, `id_poll`, `id_previous_board`, `id_previous_topic`, `num_replies`, `num_views`, `locked`, `redirect_expires`, `id_redirect_topic`, `unapproved_posts`, `approved`) VALUES
(1,	0,	1,	1,	1,	0,	0,	0,	0,	0,	0,	1,	0,	0,	0,	0,	1);

DROP TABLE IF EXISTS `smf_user_alerts`;
CREATE TABLE `smf_user_alerts` (
  `id_alert` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alert_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_member_started` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `member_name` varchar(255) NOT NULL DEFAULT '',
  `content_type` varchar(255) NOT NULL DEFAULT '',
  `content_id` int(10) unsigned NOT NULL DEFAULT 0,
  `content_action` varchar(255) NOT NULL DEFAULT '',
  `is_read` int(10) unsigned NOT NULL DEFAULT 0,
  `extra` text NOT NULL,
  PRIMARY KEY (`id_alert`),
  KEY `idx_id_member` (`id_member`),
  KEY `idx_alert_time` (`alert_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_user_alerts_prefs`;
CREATE TABLE `smf_user_alerts_prefs` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `alert_pref` varchar(32) NOT NULL DEFAULT '',
  `alert_value` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_member`,`alert_pref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `smf_user_alerts_prefs` (`id_member`, `alert_pref`, `alert_value`) VALUES
(0,	'alert_timeout',	10),
(0,	'announcements',	0),
(0,	'birthday',	2),
(0,	'board_notify',	1),
(0,	'buddy_request',	1),
(0,	'groupr_approved',	3),
(0,	'groupr_rejected',	3),
(0,	'member_group_request',	1),
(0,	'member_register',	1),
(0,	'member_report',	3),
(0,	'member_report_reply',	3),
(0,	'msg_auto_notify',	0),
(0,	'msg_like',	1),
(0,	'msg_mention',	1),
(0,	'msg_notify_pref',	1),
(0,	'msg_notify_type',	1),
(0,	'msg_quote',	1),
(0,	'msg_receive_body',	0),
(0,	'msg_report',	1),
(0,	'msg_report_reply',	1),
(0,	'pm_new',	1),
(0,	'pm_notify',	1),
(0,	'pm_reply',	1),
(0,	'request_group',	1),
(0,	'topic_notify',	1),
(0,	'unapproved_attachment',	1),
(0,	'unapproved_post',	1),
(0,	'unapproved_reply',	3),
(0,	'warn_any',	1);

DROP TABLE IF EXISTS `smf_user_drafts`;
CREATE TABLE `smf_user_drafts` (
  `id_draft` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_topic` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `id_board` smallint(5) unsigned NOT NULL DEFAULT 0,
  `id_reply` int(10) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `poster_time` int(10) unsigned NOT NULL DEFAULT 0,
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `subject` varchar(255) NOT NULL DEFAULT '',
  `smileys_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `body` mediumtext NOT NULL,
  `icon` varchar(16) NOT NULL DEFAULT 'xx',
  `locked` tinyint(4) NOT NULL DEFAULT 0,
  `is_sticky` tinyint(4) NOT NULL DEFAULT 0,
  `to_list` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_draft`),
  UNIQUE KEY `idx_id_member` (`id_member`,`id_draft`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


DROP TABLE IF EXISTS `smf_user_likes`;
CREATE TABLE `smf_user_likes` (
  `id_member` mediumint(8) unsigned NOT NULL DEFAULT 0,
  `content_type` char(6) NOT NULL DEFAULT '',
  `content_id` int(10) unsigned NOT NULL DEFAULT 0,
  `like_time` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`content_id`,`content_type`,`id_member`),
  KEY `content` (`content_id`,`content_type`),
  KEY `liker` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;


-- 2025-09-02 15:41:17 UTC
