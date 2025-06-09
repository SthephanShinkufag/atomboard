# atomboard &mdash; A lightweight and efficient PHP imageboard.

This project is a further development of an old (now defunc'd) [TinyIB](https://github.com/tslocum/TinyIB) engine version.

Features
------------

 - Can store posts as text files for a portable set-up capable of running on virtually any PHP host.
 - Use [MySQL](https://mysql.com), [PostgreSQL](https://www.postgresql.org) or [SQLite](https://sqlite.org) for an efficient set-up able to handle high amounts of traffic.
 - Administration and moderation management:
   - Each post has its own management button.
   - You can set up list of moderators and janitors.
   - Janitors can delete/approve posts and threads.
   - Admin and moderators can:
     - ban offensive/abusive posters across all boards;
     - sticky threads, delete/approve posts and threads (see `ATOM_REQMOD`);
     - delete all posts and threads from the selected IP.
   - Admin, moderators and janitors can:
     - edit message text in posts;
     - delete attached files;
     - replace thumbnails of attached files by `spoiler.png` file;
     - post with red name when logged in;
     - post using raw HTML.
   - Ban logs and moderation logs.
 - Text formatting:
   - Markup buttons under the text area in postform.
   - bbcode formatting (multiline text and nested tags), wakabamark.
   - "Quote text" button that inserts selected text into the textarea with `>` symbols.
   - Named links formatting, like on github: `[atomboard](https://github.com/SthephanShinkufag/atomboard/)`
 - Posting:
   - YouTube, Vimeo and SoundCloud embedding.
   - CAPTCHA (a simple implementation or reCAPTCHA).
   - Protection by IP lookups using [ipregistry.co](https://ipregistry.co/) - block abusive IPs, IPs under proxy, VPN, TOR network and cloud providers.
 - Post files:
   - You can upload up to 4 files per post.
   - Supported GIF, JPG, PNG, AVIF, MP4, MOV, WebM and WebP upload.
   - You can also upload, view and expand videos without instaled `mediainfo` and `ffmpegthumbnailer`. Videos will be injected right in page without generated thumbnails.
   - Allow new threads without requiring a file, or even disallow sending of files entirely.
 - Posts:
   - Reference links >>1234 to posts.
   - Backlinks >>1234 from replying posts.
   - Likes (reactions to posts).
   - Displaying unique ID's
   - Displaying country flags for posters (using GeoIp2 library).
   - Highlighting posts by clicking on unique ID's.
   - Custom default posts names.
   - Posts deletion via password.
   - Sending posts with "sage" not to bump the thread.
   - Truncating long messages by size in bytes and by number of lines on index page.
 - Threads:
   - Threads can be locked for posting.
   - Threads can be sticked to the top of index page.
   - Endless thread mode, when old posts are deleted after the limit.
 - Catalog page `catalog.html`.
 - Mobile devices friendly CSS.

Installing
------------

 1. Verify the following are installed:
    - [PHP 7.4+](https://php.net)
    - [GD Image Processing Library](https://php.net/gd)
      - This library is usually installed by default.
      - If you plan on disabling image uploads to use atomboard as a text board only, this library is not required.
 2. `cd` to the directory you wish to install atomboard.
 3. Run `git clone https://github.com/SthephanShinkufag/atomboard.git ./`
 4. Copy `settings.default.php` to `settings.php`.
 5. Configure `settings.php`
    - When setting `ATOM_DBMODE` to `flatfile`, note that all post and ban data are exposed as the database is composed of standard text files. Access to `./inc/flatfile/` should be denied.
    - When setting `ATOM_DBMODE` to `pdo`, note that only the MySQL and PostgreSQL databases drivers have been tested. Theoretically it will work with any applicable driver, but this is not guaranteed. If you use an alternative driver, please report back.
    - To require moderation before displaying posts:
      - Ensure your `ATOM_DBMODE` is set to `mysql`, `mysqli`, or `pdo`.
      - Set `ATOM_REQMOD` to `files` to require moderation for posts with files attached.
      - Set `ATOM_REQMOD` to `all` to require moderation for all posts.
      - Moderate posts by visiting the management panel.
    - To allow thumbnails generation for video and embedded files:
      - Ensure your web host is running Linux or FreeBSD.
      - Install [mediainfo](https://mediaarea.net/en/MediaInfo) and [ffmpegthumbnailer](https://code.google.com/p/ffmpegthumbnailer/). On Ubuntu, run `sudo apt-get install mediainfo ffmpegthumbnailer`. On FreeBSD, run `pkg install mediainfo ffmpegthumbnailer`.
    - To remove the play icon from WebM/MP4/MOV thumbnails, delete or rename `icons/video_overlay.png` or set `ATOM_VIDEO_OVERLAY` to false.
    - To use ImageMagick instead of GD when creating thumbnails:
      - Install ImageMagick and ensure that the `convert` command is available.
      - Set `ATOM_FILE_THUMBDRIVER` to `imagemagick`.
      - **Note:** GIF files will have animated thumbnails, which will often have large file sizes.
    - To show contry flags for posters:
      - On Ubuntu, run `sudo apt-get install geoip geoip-dev`. On FreeBSD, run `pkg install geoip geoip-dev`.
      - If using geoip2, [download](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data) `GeoLite2-Country.mmdb` library, copy to `/usr/share/GeoIP/GeoLite2-Country.mmdb`
 6. [CHMOD](https://en.wikipedia.org/wiki/Chmod) write permissions to these directories:
    - `./` (the directory containing atomboard)
    - `./src/`
    - `./thumb/`
    - `./res/`
    - `./inc/flatfile/` (only if you use the `flatfile` database mode).
 7. Navigate your browser to `imgboard.php` and the following will take place:
    - The database structure will be created.
    - Directories will be verified to be writable.
    - The board index will be written to `ATOM_INDEX`.

Moderating
------------

If you are not logged in already, log in to the management panel by clicking `[Manage]`.
From this page you are able to delete the post, attached files and/or ban the author.
While you are logged in, the post moderation buttons will now be available.

Updating
------------

 1. Obtain the latest release.
    - If you installed via Git, run `git pull` in atomboard's directory.
    - Otherwise, [download](https://github.com/SthephanShinkufag/atomboard/archive/master.zip) and extract a zipped archive.
 2. Note which files were modified.
    - If `settings.default.php` was updated, migrate the changes to `settings.php`
      - Take care to not change the value of `ATOM_TRIPSEED`, as it would result in different secure tripcodes.
    - If other files were updated, and you have made changes yourself:
      - Visit [GitHub](https://github.com/SthephanShinkufag/atomboard) and review the changes made in the update.
      - Ensure the update does not interfere with your changes.

**Database structure was last modified on *04th Sep 2019*.**

Migrating
------------

atomboard includes a database migration tool, which currently only supports migrating from flat file mode to MySQL.
While the migration is in progress, visitors will not be able to create or delete posts.

 1. Edit `settings.php`:
    - Ensure `ATOM_DBMODE` is still set to `flatfile`.
    - Set `ATOM_DBMIGRATE` to `true`.
    - Configure all MySQL-related settings.
 2. Open the management panel.
 3. Click `Migrate Database`.
 4. Click `Start the migration`.
 5. If the migration was successful:
    - Edit `settings.php`
      - Set `ATOM_DBMODE` to `mysqli`.
      - Set `ATOM_DBMIGRATE` to `false`.
    - Click `Rebuild All` and ensure the board still looks the way it should.

If there was a warning about AUTO_INCREMENT not being updated, you'll need to update it manually via a more privileged MySQL user.
Run the following query for one or both of the tables, dependant of the warnings you were issued:

`ALTER TABLE (table name) AUTO_INCREMENT = (value to be set)`

Support
------------

 1. Ensure you are running the latest version of atomboard.
 2. Review the [open issues](https://github.com/SthephanShinkufag/atomboard/issues).
 3. Open a [new issue](https://github.com/SthephanShinkufag/atomboard/issues/new).

Contributing
------------

 2. Fork atomboard.
 3. Commit code changes to your forked repository.
 4. Submit a pull request describing your modifications.
