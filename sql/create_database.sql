/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for torrdex
CREATE DATABASE IF NOT EXISTS `torrdex` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `torrdex`;


-- Dumping structure for table torrdex.certified
CREATE TABLE IF NOT EXISTS `certified` (
  `user` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping structure for table torrdex.members
CREATE TABLE IF NOT EXISTS `members` (
  `user` varchar(32) NOT NULL,
  `pass` varchar(72) NOT NULL,
  `acct_type` int(1) NOT NULL DEFAULT '0',
  `fullname` varchar(72) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping structure for table torrdex.options
CREATE TABLE IF NOT EXISTS `options` (
  `name` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  `desc` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table torrdex.options: ~7 rows (approximately)
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
INSERT INTO `options` (`name`, `value`, `desc`) VALUES
	('allow_signup', 'true', 'Allow New Sign-Up\'s'),
	('only_seeder_uploads', 'true', 'Only Seeder Accounts Can Upload Torrents'),
	('show_authors', 'true', 'Show Authors in Torrent Details'),
	('hide_torrent_files', 'false', 'Hide Links to Torrent Files'),
	('admin_only_removes', 'false', 'Only Admin Accounts Can Remove Torrents'),
	('show_disclaimer', 'true', 'Show Disclaimer'),
	('show_copyright', 'true', 'Show Copyright');
/*!40000 ALTER TABLE `options` ENABLE KEYS */;


-- Dumping structure for table torrdex.torrents
CREATE TABLE IF NOT EXISTS `torrents` (
  `name` tinytext,
  `hash` tinytext,
  `type` tinytext,
  `uploaded` tinytext,
  `files` text,
  `comment` tinytext,
  `description` text,
  `magnet` text,
  `size` int(11) DEFAULT NULL,
  `filecount` int(11) DEFAULT NULL,
  `author` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;