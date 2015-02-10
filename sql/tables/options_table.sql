/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table torrdex.options
DROP TABLE IF EXISTS `options`;
CREATE TABLE IF NOT EXISTS `options` (
  `type` varchar(6) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  `default` varchar(50) NOT NULL,
  `description` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table torrdex.options: ~11 rows (approximately)
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
INSERT INTO `options` (`type`, `name`, `value`, `default`, `description`) VALUES
	('bool', 'allow_signup', 'false', 'false', 'Allow New Sign-Up\'s'),
	('bool', 'only_seeder_uploads', 'true', 'true', 'Only Seeder Accounts Can Upload Torrents'),
	('bool', 'show_authors', 'true', 'true', 'Show Authors in Torrent Details'),
	('bool', 'hide_torrent_files', 'false', 'false', 'Hide Links to Torrent Files'),
	('bool', 'admin_only_removes', 'false', 'false', 'Only Admin Accounts Can Remove Torrents'),
	('bool', 'show_disclaimer', 'true', 'true', 'Show Disclaimer'),
	('bool', 'show_copyright', 'true', 'true', 'Show Copyright'),
	('bool', 'enable_pagination', 'false', 'false', 'Page Results from Torrents'),
	('int', 'torr_per_page', '5', '5', 'Torrents Listed Per Page'),
	('string', 'site_title', 'TorrDex', 'TorrDex', 'Site Title in Browser'),
	('string', 'table_caption', '. : = | Sorcerer Merlin\'s TORRent inDEXer | = : .', '. : = | Sorcerer Merlin\'s TORRent inDEXer | = : .', 'Caption of Top-Most Table'),
	('string', 'site_root', 'http://localhost/', 'http://localhost/', 'Full URL path to site'),
	('string', 'site_email', 'auto_admin@localhost', 'auto_admin@localhost', 'Email Address of Administrator'),
	('bool', 'only_admin_invites', 'true', 'true', 'Only Admin Accounts Can Invite New Users');
/*!40000 ALTER TABLE `options` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
