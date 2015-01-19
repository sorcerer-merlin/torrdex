# TorrDex

## What is TorrDex?

In short, TorrDex is a Semi-Private BitTorrent Indexing Community. It is licensed under the GNU GPLv3, and hosted on GitHub by its creator Sorcerer Merlin. TorrDex is NOT a main-stream entry-level web application developed by a team of dedicated programmers. It is a hobby-project developed by ONE intermediate-level+™ hobbyist programmer. It is therefore subject to bugs and other issues, which should be reported at the repository Issues page. Any feature requests and the like can also be submitted there as well.

## Technical Specs

TorrDex is built using HTML5, PHP5, MySQL, JavaScript , and AJAX. Account passwords are encrypted, using the PasswordHash class for PHP developed by Taylor Hornby. BitTorrent processing and support is provided by the PHP_BitTorrent library (in PHAR format) developed by Christer Edvartsen. The entire color scheme and theme for TorrDex is completely dynamic and achieved using CSS and Web Fonts (which MAY allow for additional theming support in the future!).

## Feature List

Below is a list of completely finished features incorporated into TorrDex. For incomplete or planned features, look at the next section.

- 3 types of User Accounts with encrypted Passwords and Display Names
- New User sign up's available only when enabled in the Administration panel
- Session-based login system, with modifiable User profiles
- Searchable database of Torrents with sortable Table columns
- Ability to Upload Torrents to database
- Administration panel with the ability to make changes and remove users, as well as enable/disable global options that change functionality of the site
- Customizable Torrent Description templates for new uploads to ease in the writing of Torrent Descriptions

## To-Do List

This list of features and ideas is not yet implemented in TorrDex. They may have partially working code, or not even be coded at all. Look for them in future releases of the site.

- Profile picture (avatar) support
- Theme support
- Torrent comment/rating system
- Certified Uploader (aka the Green Skull) system
- Email invite system using encrypted GUIDs
