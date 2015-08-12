=== Spotify Embed ===
Contributors: ocean90
Tags: Spotify, WP Embed, Embed, Music, Spotfiy Play Button
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VR8YU922B7K46
Requires at least: 2.9
Tested up to: 3.9
Stable tag: 0.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily embed Spotify playlists, albums and tracks into your posts through the new Spotify Play Button.

== Description ==

Spotify Embed is based on the new [API by Spotify](https://developer.spotify.com/technologies/spotify-play-button/), better known as the Spotify Play Button.
With Spotify Embed you can easily share your favored music tracks, albums and playlists with other Spotify users.

= What's your part? =
Just place a Spotify link into your posts. It can be a link to your favored track or album, but also music playlists are possible.
**By default no shortcodes are necessary, just a link!**
And that was about your part. Easy, isn't it?

= What does the plugin? =
The plugin will fetch your links of your music and will convert it to a sexy Spotify Play Button.

= Some usage examples =
To embed a simple Track just embed a link like this:
`https://open.spotify.com/track/4IJUii1lg0IRLBs0yG1w2p`

To embed an album just embed a link like this:
`http://open.spotify.com/album/4pT0rlFvHYc46KyEhmCy88`

Or to embed a complete playlist just embed a link like this:
`http://open.spotify.com/user/sonymusic/playlist/1BVPSd4dynzdlIWehjvkPj`

For more examples please vist the [FAQ section](http://wordpress.org/extend/plugins/spotify-embed/faq/).

**Sounds pretty good? Install now!**

= Coming soon =
* Support for Spotify URLs like `spotify:album:4pT0rlFvHYc46KyEhmCy88`.


*Spotify Embed is not associated in any way with Spotify by Spotify Ltd.*


= Feedback =
If you want, you can drop me a line @[ocean90](http://twitter.com/ocean90) on Twitter or @[Dominik Schilling](https://plus.google.com/101675293278434581718/) on Google+.



= More =
If you want, you can try also some of my [other plugins](http://profiles.wordpress.org/users/ocean90) or visit my site [wpGrafie.de](http://wpgrafie.de/).




== Frequently Asked Questions ==

= Can I embed the light theme instead of the dark theme? =
Yes, you can. Just wrap your link into the `[embed][/embed]` shortcode and add the keyword 'light'.
Example:
`[embed light]http://open.spotify.com/user/spotify/playlist/3Yrvm5lBgnhzTYTXx2l55x[/embed]`


= I want to show the cover art, is that possible? =
Yes. Just wrap your link into the `[embed][/embed]` shortcode and add the keyword 'coverart'.
Example:
`[embed coverart]http://open.spotify.com/user/spotify/playlist/3Yrvm5lBgnhzTYTXx2l55x[/embed]`


= Where does the plugin get it's default height and width settings? =
Therefor take a look at the Media settings under `Settings > Media > Embed`.


= Can I customize the size of the embed box? =
Yes. Just wrap your link into the `[embed][/embed]` shortcode and add the keywords 'height' and/or 'width'.
(Note: Take also a look at the [Spotify documentation](https://developer.spotify.com/technologies/spotify-play-button/documentation/))
Example:
`[embed height="600" width="450"]http://open.spotify.com/user/spotify/playlist/3Yrvm5lBgnhzTYTXx2l55x[/embed]`

Another option is the use of the `size` keyword. You can use `size="compact"` (80x250px) or `size="large"` (720x640px).
Example:
`[embed size="compact"]http://open.spotify.com/user/spotify/playlist/3Yrvm5lBgnhzTYTXx2l55x[/embed]`


= Can I combine all these keywords? =
Yes. (But don't try to combine the size keywords, or the sky will fall on your head)




== Screenshots ==

1. An example of an embed track.
2. Here you can get the HTML link of your track, album or playlist, which you can insert into your content.




== Installation ==

Note: There will be NO settings page.

For an automatic installation through WordPress:

1. Go to the 'Add New' plugins screen in your WordPress admin area
1. Search for 'Spotify Embed'
1. Click 'Install Now' and activate the plugin


For a manual installation via FTP:

1. Upload the `spotify-embed` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' screen in your WordPress admin area


To upload the plugin through WordPress, instead of FTP:

1. Upload the downloaded zip file on the 'Add New' plugins screen (see the 'Upload' tab) in your WordPress admin area and activate.




== Changelog ==
= 0.2.1 =
* Removes one unused matching group to increase performance, thanks to Sergej Müller.

= 0.2.0 =
* Performance boost by reducing to only one regular expression, thanks to Jens Tautenhahn.
* New keyword: `size`. (Use `size="compact"` or `size="large"`.)

= 0.1.0 =
* First release.





== Upgrade Notice ==

= 0.2 =
Performance boost and a new keyword for default size. You can use size="compact" or size="large" now.

