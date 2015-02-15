/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table torrdex.torrents
CREATE TABLE IF NOT EXISTS `torrents` (
  `name` tinytext NOT NULL,
  `hash` varchar(40) NOT NULL,
  `type` int(11) NOT NULL,
  `uploaded` varchar(10) NOT NULL,
  `files` text NOT NULL,
  `comment` tinytext NOT NULL,
  `description` text NOT NULL,
  `magnet` text NOT NULL,
  `size` int(11) NOT NULL,
  `filecount` int(11) NOT NULL,
  `author` varchar(32) NOT NULL,
  `created` varchar(10) NOT NULL,
  `trackers` text NOT NULL,
  `scrape_date` varchar(10) NOT NULL,
  `seeders` int(11) NOT NULL,
  `leechers` int(11) NOT NULL,
  `download_count` int(11) NOT NULL,
  `working_tracker` text NOT NULL,
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40000 ALTER TABLE `torrents` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
