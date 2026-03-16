# atomboard &mdash; A lightweight and efficient PHP imageboard.

This project is a further development of an old (now defunc'd) [TinyIB](https://github.com/tslocum/TinyIB) engine version.

Features
------------

 - MySQL and PostgreSQL databases are supported.
 - Post complaints: users can report a selected post.
 - Passcodes system: the passcode allows the poster to increase the size limit on uploaded files.
 - Administration and moderation management:
   - Ban logs and moderation logs.
   - Admin can:
     - view and set up moderators/janitors accounts on the staff management page;
     - issue new passcodes;
     - rebuild all threads on the board.
   - Admin and moderators can:
     - ban posters by ip or subnet;
     - block existing passcodes.
   - Admin, moderators and janitors can:
     - delete/approve posts and threads (see `ATOM_REQMOD`);
     - delete all posts from ip or subnet;
     - view complaints on posts: reject reports or apply actions to posts accordingly;
     - view information on IP/subnet: bans, abuser IP status, posts;
     - lock threads for posting;
     - stick threads to the top of index page;
     - make endless threads (old posts will be deleted when new ones appear);
     - edit message text in posts;
     - delete attached files;
     - replace thumbnails of attached files by `spoiler.png` file;
     - post with color #Name when logged in and filling in the "name" field;
     - post using raw HTML;
     - change own account password.
 - Text formatting:
   - bbcode text formatting (multiline text and nested tags), wakabamark.
   - "Quote text" button that inserts selected text into the textarea with `>` symbols.
   - Named links formatting, like on github: `[atomboard](https://github.com/SthephanShinkufag/atomboard/)`
 - Posting:
   - YouTube, Vimeo and SoundCloud embedding.
   - CAPTCHA (a simple implementation or reCAPTCHA).
   - Protection by IP lookups using [ipregistry.co](https://ipregistry.co/) - block abusive IPs, IPs under proxy, VPN, TOR network and cloud providers.
   - Auto-replacement list for replacing words in messages.
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
   - Unique ID usernames based on IP address/subnet (currently only the Ukrainian and Russian are available).
   - Highlighting posts by clicking on unique ID's.
   - Displaying country flags for posters (using GeoIp2 library).
   - Custom default posts names.
   - Posts deletion via password.
   - Sending a message with the word "sage" to avoid bumping the thread.
   - Truncating long messages by size in bytes and by number of lines on index page.
 - Catalog page `catalog.html`.
 - Mobile-optimized CSS.

 Atomboard has a built-in extension, [Dolchan Extension Tools](https://github.com/SthephanShinkufag/Dollchan-Extension-Tools/tree/atomboard-extension), which adds additional client-side functionality via JS script:

 - Sending posts without reloading page.
 - Thread updater with different types of alerts (for example, blinking favicon or desktop notification when new posts appear).
 - Hiding posts and threads by a wide choice of expressions and rules.
 - Saving threads entirely with the originals of pictures.
 - Preloading full pictures into memory buffer.
 - Expanding threads directly on the index page.
 - Adding interesting threads to your Favorites to track new posts or responses to your posts.
 - "Endless scrolling" of index pages with threads.
 - Easy navigation through >>links as a posts tree.
 - Embedding player to YouTube and Vimeo links in message text.
 - Easy navigation through attached pictures / videos in posts.
 - Applying search services to pictures and stills from videos.
 - Reading metadata form webm files.
 - Adding pictures to the reply form with convenient preview thumbnails.
 - Posting pictures with random bytes added and custom names.
 - Embedding preview thumbnails to .jpg / .png / .gif links.
 - Adding your CSS rules.
 - Hotkeys for many functions.

Installing
------------

 1. Verify the following are installed:
    - [PHP 8.2+](https://php.net)
    - [GD Image Processing Library](https://php.net/gd)
      - This library is usually installed by default.
      - Alternatively, you can use [ImageMagick](https://imagemagick.org/) library instead of GD.
      - If you plan on disabling image uploads to use atomboard as a text board only, this library is not required.
 2. `cd` to the directory you wish to install atomboard.
 3. Run `git clone https://github.com/SthephanShinkufag/atomboard.git ./`
 4. Copy `settings.default.php` to `settings.php`.
 5. Configure `settings.php`
    - When setting `ATOM_DBMODE` to `pdo`, note that only the MySQL and PostgreSQL databases drivers have been tested. Theoretically it will work with any applicable driver, but this is not guaranteed. If you use an alternative driver, please report back.
    - To require moderation before displaying posts:
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
    - To show country flags for posters:
      - On Ubuntu, run `sudo apt-get install geoip geoip-dev`. On FreeBSD, run `pkg install geoip geoip-dev`.
      - If using geoip2, [download](https://dev.maxmind.com/geoip/geolite2-free-geolocation-data) `GeoLite2-Country.mmdb` library, and copy to `/usr/share/GeoIP/GeoLite2-Country.mmdb`
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
From this page you are able to delete the post, attached files or ban the author.
While you are logged in, the post moderation buttons will now be available.

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
