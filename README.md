TinyIB &mdash; a lightweight and efficient [imageboard](https://en.wikipedia.org/wiki/Imageboard) engine

Features
------------

**Got database? Get speed.**
Use [MySQL](https://mysql.com) for an efficient set-up able to handle high amounts of traffic.

**Not looking for an image board script?**
TinyIB settings is able to allow new threads without requiring an image, or even disallow sending of images entirely.

[Original TinyIB](https://gitlab.com/tslocum/tinyib) features:
 - GIF, JPG, PNG, SWF and WebM upload.
 - YouTube, Vimeo and SoundCloud embedding.
 - CAPTCHA (A simple implementation is included, reCAPTCHA is also supported)
 - Reference links >>###
 - Delete post via password.
 - Management panel:
   - Administrators and moderators use separate passwords.
   - Moderators are only able to sticky threads, delete posts, and approve posts when necessary.  (See ``TINYIB_REQMOD``)
   - Ban offensive/abusive posters across all boards.
   - Post using raw HTML.
   - Upgrade automatically when installed via git. (Tested on Linux only)
 
This fork features:
 - Markdown text formatting: bbcode (multiline text and nested tags), wakabamark.
 - Markup buttons under the text area in postform.
 - "Quote text" button that inserts selected text into the textarea with `>` symbols.
 - Named links formatting, like on github: `[TinyIB](https://github.com/SthephanShinkufag/TinyIB/)`
 - Upload, view and expand WEBM **and MP4** without instaled `mediainfo` and `ffmpegthumbnailer`. Videos are injected right in page without generated thumbnails.
 - Convenient css support for mobile devices.
 - Custom default post names.
 - Truncating of long posts by size in bytes and by number of lines.
 - Admin: posting with red name if admin is logged in.
 - "Sage" indicator in posts.
 - Post likes system (initial implementation, still in progress).

This fork of the fork features:
 - Fork is based on [TinyIB from SthephanShinkufag](https://github.com/SthephanShinkufag/TinyIB/)
 - At this moment works only with PDO+MySQL (Flatfile / PostgreSQL / SQlite is not supported yet).
 - DB schema changed!
 - Bugfixes.
 - You can paste 4 files per post.
 - Administrator and Moderators can selectively delete attached files.
 - Threads can be locked for posting.
 - Administrator and Moderators can edit message text in posts.
 - Preview for attached files can be replaced by filler image.

Installing
------------

 1. Verify the following are installed:
    - [PHP 4.3+](https://php.net)
    - [GD Image Processing Library](https://php.net/gd)
      - This library is usually installed by default.
      - If you plan on disabling image uploads to use TinyIB as a text board only, this library is not required.
 2. CD to the directory you wish to install TinyIB.
 3. Run the command:
    - `git clone https://github.com/nolifer1337/TinyIB.git ./`
 4. Copy **settings.default.php** to **settings.php**
 5. Configure **settings.php**
    - When setting ``TINYIB_DBMODE`` to ``pdo``, note that only the MySQL databases drivers have been tested. Theoretically it will work with any applicable driver, but this is not guaranteed.  If you use an alternative driver, please report back.
    - To require moderation before displaying posts:
      - Ensure your ``TINYIB_DBMODE`` is set to ``mysql``, ``mysqli``, or ``pdo``.
      - Set ``TINYIB_REQMOD`` to ``files`` to require moderation for posts with files attached.
      - Set ``TINYIB_REQMOD`` to ``all`` to require moderation for all posts.
      - Moderate posts by visiting the management panel.
    - To allow WebM upload:
      - Ensure your web host is running Linux or FreeBSD.
      - Install [mediainfo](https://mediaarea.net/en/MediaInfo) and [ffmpegthumbnailer](https://code.google.com/p/ffmpegthumbnailer/).  On Ubuntu, run ``sudo apt-get install mediainfo ffmpegthumbnailer``. On FreeBSD run ``pkg install mediainfo ffmpegthumbnailer``.
    - To remove the play icon from .SWF and .WebM thumbnails, delete or rename **video_overlay.png** or set ADD_VIDEO_OVERLAY_IMAGE to false.
    - To use ImageMagick instead of GD when creating thumbnails:
      - Install ImageMagick and ensure that the ``convert`` command is available.
      - Set ``TINYIB_THUMBNAIL`` to ``imagemagick``.
      - **Note:** GIF files will have animated thumbnails, which will often have large file sizes.
 6. [CHMOD](https://en.wikipedia.org/wiki/Chmod) write permissions to these directories:
    - ./ (the directory containing TinyIB)
    - ./src/
    - ./thumb/
    - ./res/
    - ./inc/flatfile/ (only if you use the ``flatfile`` database mode)
 7. Navigate your browser to **imgboard.php** and the following will take place:
    - The database structure will be created.
    - Directories will be verified to be writable.
    - The board index will be written to ``TINYIB_INDEX``.

Moderating
------------

 1. If you are not logged in already, log in to the management panel by clicking **[Manage]**.
 2. On the board, tick the checkbox next to the offending post.
 3. Scroll to the bottom of the page.
 4. Click **Delete** with the password field blank.
    - From this page you are able to delete the post, attached files and/or ban the author.

Updating
------------

 1. Obtain the latest release.
    - If you installed via Git, run the following command in TinyIB's directory:
      - `git pull`
    - Otherwise, [download](https://github.com/nolifer1337/TinyIB/archive/master.zip) and extract a zipped archive.
 2. Note which files were modified.
    - If **settings.default.php** was updated, migrate the changes to **settings.php**
      - Take care to not change the value of **TINYIB_TRIPSEED**, as it would result in different secure tripcodes.
    - If other files were updated, and you have made changes yourself:
      - Visit [GitHub](https://github.com/nolifer1337/TinyIB) and review the changes made in the update.
      - Ensure the update does not interfere with your changes.

**Database structure was last modified on *19th Jun 2019*.** 


Support
------------

 1. Ensure you are running the latest version of TinyIB.
 2. Review the [open issues](https://github.com/nolifer1337/TinyIB/issues).
 3. Open a [new issue](https://github.com/nolifer1337/TinyIB/issues/new).

Contributing
------------

 2. Fork TinyIB.
 3. Commit code changes to your forked repository.
 4. Submit a pull request describing your modifications.
