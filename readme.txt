=== Podcast Searcher by Clarify ===

Contributors:       technosailor, caseysoftware
Tags:               audio search, video search, podcasts, videocasts, video blogging, clarify, podcasting, podcast, podcaster, itunes, enclosure, podcasting, audio, video, player, media, rss, mp3, music, embed, flv, flash, id3, episodes, webm, mp4, m4v
Requires at least:  2.5
Tested up to:       4.2
Stable Tag:         1.0.2
License:            MIT

The Clarify plugin allows you to make any audio or video embedded in your posts, pages, etc searchable via the standard WordPress search box.

== Description ==

The Podcast Searcher plugin uses [Clarify's](http://Clarify.io) technology to make audio and video searchable.  It uses automatic speech recognition to extract spoken English, Spanish, and French and lets you retrieve detailed information about your media with simple API calls. Getting started takes minutes.

= Key Features =

- Minimal setup: Adds search to your podcast without any additional plugins or custom theming required.
- Simple to Use: You just add the link to your podcast as you've always done. We handle the rest.
- No transcripts: Lets you skip the expensive and time-consuming step of getting transcripts.

= Usage =

Now whenever you add a URL to your audio or video file - either by directly copy/pasting the url into the post or using a media embed - it will automatically be sent to Clarify for indexing.

Once the file is done processing, using your on-site search will return results describing where your word is heard in the audio down to the second. If you're using the default embed/shortcode, these mentions will be clickable and jump you directly to that spot in the audio or video.

= Further Usage =

If you have archives with media - audio or video - already embedded, this plugin will not automatically index it for you. We didn't want to surprise you with a massive bill. Instead, just open any of your old posts and re-save them. No editing or changes required. The media will get processed just like any other media.

= Housekeeping =

While this is hosted in WordPress' SVN repository, no development is performed there. For the latest and greatest development version, check out the Github repository: https://github.com/Clarify/wp-clarify

== Installation ==

1. Download the wp-clarify.zip file from Github
1. Visit Plugins > Add New > Upload Plugin and upload the zip file
1. Under Settings > Clarify add your API key available from the [Developer Portal](https://developer.clarify.io/apps/list/)

== Frequently Asked Questions ==

= Can I use this with my theme? =

This plugin integrates with most themes with no modification. You don't have to add custom shortcodes or tags to your theme or posts.

= How do I theme the results? =

When you perform a search and click through to the results, the plugin will insert an HTML unordered list with the class "clarify-seek-handles" containing a list of links. Each link has the class "clarify-seek-handle" and clicking it will jump you to a specific place in the media player. This HTML can be themed just like any other.

= Is this free? =

The plugin itself is free. The Clarify service does charge per minute of audio or video. Pricing is available on [our site](http://clarify.io/pricing/).

= Where do I get support? =

You can reach us at any time via support@clarify.io and we're happy to help you with this plugin. If you have problems with WordPress or other plugins, please contact them.

= Do you support other podcast plugins? =

At this time, no but we would love to. Email us - support@clarfiy.io - to let us know what other plugin(s) you use and we'll look into it.

= What languages does Clarify support? =

At the time of this writing (May 2015), we support English, Spanish, and French. If we don't support your podcast's language, please contact us: support@clarify.io

= How does Clarify work? =

That is a longer question that is hard to answer here. Visit our [How it Works](http://clarify.io/how-clarify-works/) page for more details.

== Changelog ==

= 1.0.2 =
Updated the search result count to make sure the results of this plugin are combined with the normal result count.

= 1.0.1 =
We made some major cleanups to the docs to make the plugin easier to use.

= 1.0.0 =
This is the first formal release.

= 1.0-RC1 =
This is the first candidate release that we're sharing with trusted beta testers.