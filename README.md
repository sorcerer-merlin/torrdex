# TorrDex

Check out the [TorrDex Wiki](https://github.com/sorcerer-merlin/torrdex/wiki/) for [screenshots](https://github.com/sorcerer-merlin/torrdex/wiki/Screenshots) and other useful information!

## What is TorrDex?

In short, TorrDex is a Semi-Private BitTorrent Indexing Community. It is licensed under the [GNU GPLv3](http://www.gnu.org/licenses/gpl-3.0-standalone.html), and hosted on [GitHub](https://github.com/sorcerer-merlin/torrdex) by its creator [Sorcerer Merlin](https://github.com/sorcerer-merlin/). TorrDex is NOT a main-stream entry-level web application developed by a team of dedicated programmers. It is a hobby-project developed by ONE intermediate-level+™ hobbyist programmer. It is therefore subject to bugs and other issues, which should be reported at the repository [Issues](https://github.com/sorcerer-merlin/torrdex/issues) page. Any feature requests and the like can also be submitted there as well.

## Technical Specs

TorrDex is built using [HTML5](http://en.wikipedia.org/wiki/HTML5), [PHP5](http://php.net/), [MySQL](http://www.mysql.com/), [JavaScript](http://en.wikipedia.org/wiki/JavaScript) , and [AJAX](http://en.wikipedia.org/wiki/Ajax_%28programming%29). Account passwords are encrypted, using the [PasswordHash](https://github.com/defuse/password-hashing) class for PHP developed by [Taylor Hornby](https://github.com/defuse). BitTorrent processing and support is provided by the [PHP_BitTorrent](https://github.com/christeredvartsen/php-bittorrent) library (in PHAR format) developed by [Christer Edvartsen](https://github.com/christeredvartsen). The entire color scheme and theme for TorrDex is completely dynamic and achieved using [CSS](http://en.wikipedia.org/wiki/Cascading_Style_Sheets) and [Web Fonts](http://www.cssfontstack.com/Web-Fonts) (which MAY allow for additional theming support in the future!). TorrDex also makes use of the [Parsedown](https://github.com/erusev/parsedown) library for PHP developed by [Emanuil Rusev](https://github.com/erusev) to implement [MarkDown](http://en.wikipedia.org/wiki/Markdown) support for Torrent Descriptions. TorrDex also uses CAPTCHA-style verification codes, provided by the [simple-php-captcha](https://github.com/claviska/simple-php-captcha) script developed by [Cory LaViska](https://github.com/claviska).  Tracker scraping support is provided by the [PHP-Torrent-Scraper](https://github.com/johannes85/PHP-Torrent-Scraper) library developed by [Johannes](https://github.com/johannes85).

## Feature List

Below is a list of completely finished features incorporated into TorrDex. For incomplete or planned features, look at the next section.

- Member Accounts with 3 Levels of Access, Encrypted Passwords & Avatars
- New User Sign Up's (Only If Enabled in Administration Panel)
- Session-Based Login System With Modifiable User Profiles
- Searchable Database of Torrents with Sorting and Pagination
- Ability to Upload New Torrents to Database with 2 Levels of Access
- Administration Panel with Access to TorrDex Options and User Administration and Removal
- Customizable Torrent Description Templates for New Uploads with MarkDown Support
- Password Reset System with Email Encrypted Verification Link and CAPTCHA code
- Member Invites with Email Encrypted Verification Link and CAPTCHA code
- Complex Torrent Commenting / Rating System with Administration Support
- Theme Support

## To-Do List

This list of features and ideas is not yet implemented in TorrDex. They may have partially working code, or not even be coded at all. Look for them in future releases of the site.

- Certified Uploader (AKA The Green Skull) System
- Torrents Time Support (Video Streaming)
- Data Scraper Plug-In's
