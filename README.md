atomboard &mdash; a lightweight and efficient [imageboard](https://en.wikipedia.org/wiki/Imageboard) engine

Features
------------

 - Store posts as text files for a portable set-up capable of running on virtually any PHP host.
 - Use [MySQL](https://mysql.com), [PostgreSQL](https://www.postgresql.org) or [SQLite](https://sqlite.org) for an efficient set-up able to handle high amounts of traffic.
 - Allow new threads without requiring an image, or even disallow sending of images entirely.
 - GIF, JPG, PNG, SWF, WebM and WebP upload.
 - YouTube, Vimeo and SoundCloud embedding.
 - CAPTCHA (A simple implementation is included, reCAPTCHA is also supported).
 - Reference links >>###
 - Delete post via password.
 - Management panel:
   - Administrators and moderators use separate passwords.
   - Moderators are only able to sticky threads, delete posts, and approve posts when necessary. (See `TINYIB_REQMOD`)
   - Ban offensive/abusive posters across all boards.
   - Post using raw HTML.
 - Text formatting:
   - Markup buttons under the text area in postform.
   - bbcode formatting (multiline text and nested tags), wakabamark.
   - "Quote text" button that inserts selected text into the textarea with `>` symbols.
   - Named links formatting, like on github: `[atomboard](https://github.com/SthephanShinkufag/atomboard/)`
 - Administration:
   - You can set list of Moderators.
   - Moderation log.
   - Admins and Mods can edit message text in posts.
   - Admins and Mods can selectively delete attached files.
   - Admins and Mods can selectively replace thumbnails of attached files by `spoiler.png` file.
   - Posting with red name if Admin is logged in.
 - Post files:
   - You can upload 4 files per post.
   - Upload, view and expand WEBM and MP4 videos without instaled `mediainfo` and `ffmpegthumbnailer`. Videos are injected right in page without generated thumbnails.
 - Posts and threads:
   - Added likes to posts.
   - "Sage" indicator in posts.
   - Threads can be locked for posting.
   - Truncating of long posts by size in bytes and by number of lines.
   - Custom default posts names.
 - Catalog page `catalog.html`.
 - Convenient css support for mobile devices.
 - Displaying unique hash ID's and country flags for posters

Installing
------------

 1. Verify the following are installed:
    - [PHP 7.4+](https://php.net)
    - [GD Image Processing Library](https://php.net/gd)
      - This library is usually installed by default.
      - If you plan on disabling image uploads to use atomboard as a text board only, this library is not required.
 2. CD to the directory you wish to install atomboard.
 3. Run the command:
    - `git clone https://github.com/SthephanShinkufag/atomboard.git ./`
 4. Copy `settings.default.php` to `settings.php`.
 5. Configure `settings.php`
    - When setting `TINYIB_DBMODE` to `flatfile`, note that all post and ban data are exposed as the database is composed of standard text files.  Access to `./inc/flatfile/` should be denied.
    - When setting `TINYIB_DBMODE` to `pdo`, note that only the MySQL and PostgreSQL databases drivers have been tested. Theoretically it will work with any applicable driver, but this is not guaranteed.  If you use an alternative driver, please report back.
    - To require moderation before displaying posts:
      - Ensure your `TINYIB_DBMODE` is set to `mysql`, `mysqli`, or `pdo`.
      - Set `TINYIB_REQMOD` to `files` to require moderation for posts with files attached.
      - Set `TINYIB_REQMOD` to `all` to require moderation for all posts.
      - Moderate posts by visiting the management panel.
    - To allow WebM upload:
      - Ensure your web host is running Linux or FreeBSD.
      - Install [mediainfo](https://mediaarea.net/en/MediaInfo) and [ffmpegthumbnailer](https://code.google.com/p/ffmpegthumbnailer/). On Ubuntu, run `sudo apt-get install mediainfo ffmpegthumbnailer`. On FreeBSD run `pkg install mediainfo ffmpegthumbnailer`.
    - To remove the play icon from .SWF and .WebM thumbnails, delete or rename `icons/video_overlay.png` or set `TINYIB_VIDEO_OVERLAY` to false.
    - To use ImageMagick instead of GD when creating thumbnails:
      - Install ImageMagick and ensure that the `convert` command is available.
      - Set `TINYIB_THUMBNAIL` to `imagemagick`.
      - **Note:** GIF files will have animated thumbnails, which will often have large file sizes.
 6. [CHMOD](https://en.wikipedia.org/wiki/Chmod) write permissions to these directories:
    - `./` (the directory containing atomboard)
    - `./src/`
    - `./thumb/`
    - `./res/`
    - `./inc/flatfile/` (only if you use the `flatfile` database mode).
 7. Navigate your browser to `imgboard.php` and the following will take place:
    - The database structure will be created.
    - Directories will be verified to be writable.
    - The board index will be written to `TINYIB_INDEX`.

Moderating
------------

If you are not logged in already, log in to the management panel by clicking `[Manage]`.
From this page you are able to delete the post, attached files and/or ban the author.

Updating
------------

 1. Obtain the latest release.
    - If you installed via Git, run `git pull` in atomboard's directory.
    - Otherwise, [download](https://github.com/SthephanShinkufag/atomboard/archive/master.zip) and extract a zipped archive.
 2. Note which files were modified.
    - If `settings.default.php` was updated, migrate the changes to `settings.php`
      - Take care to not change the value of `TINYIB_TRIPSEED`, as it would result in different secure tripcodes.
    - If other files were updated, and you have made changes yourself:
      - Visit [GitHub](https://github.com/SthephanShinkufag/atomboard) and review the changes made in the update.
      - Ensure the update does not interfere with your changes.

**Database structure was last modified on *04th Sep 2019*.**

Migrating
------------

atomboard includes a database migration tool, which currently only supports migrating from flat file mode to MySQL.
While the migration is in progress, visitors will not be able to create or delete posts.

 1. Edit `settings.php`:
    - Ensure `TINYIB_DBMODE` is still set to `flatfile`.
    - Set `TINYIB_DBMIGRATE` to `true`.
    - Configure all MySQL-related settings.
 2. Open the management panel.
 3. Click `Migrate Database`.
 4. Click `Start the migration`.
 5. If the migration was successful:
    - Edit `settings.php`
      - Set `TINYIB_DBMODE` to `mysqli`.
      - Set `TINYIB_DBMIGRATE` to `false`.
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
