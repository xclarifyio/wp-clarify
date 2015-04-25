# WP-Clarify

Contributors:       Aaron Brazell; Clarify, Inc

Tags:               audio search, video search, podcasts, videocasts, video blogging

Requires at least:  ?

Tested up to:       4.1.1

Stable Tag:         ?

The Clarify plugin allows you to make any audio or video embedded in your posts, pages, etc searchable via the standard WordPress search box. No additional plugins are necessary. It is powered by Clarify's automatic speech recognition technology.

## Description

[Clarify](http://Clarify.io) is an API that makes audio and video searchable. It uses automatic speech recognition to extract spoken English, Spanish, and French and lets you retrieve detailed information about your media with simple API calls. Once you [sign up for an account](https://developer.clarify.io/accounts/signup/), you add your API key to the settings and the rest is handled automatically.

## Installation

1. Download the wp-clarify.zip file from Github
1. Visit Plugins > Add New > Upload Plugin and upload the zip file
1. Under Settings > Clarify add your API key available from the [Developer Portal](https://developer.clarify.io/apps/list/)

## Usage

Now whenever you add a URL to your audio or video file - either by directly copy/pasting the url into the post or using a media embed - it will automatically be sent to Clarify for indexing.

Once the file is done processing, using your on-site search will return results describing where your word is heard in the audio down to the second. If you're using the default embed/shortcode, these mentions will be clickable and jump you directly to that spot in the audio or video.

### Further Usage

If you have archives with media - audio or video - already embedded, this plugin will not automatically index it for you. We didn't want to surprise you with a massive bill. Instead, just open any of your old posts and re-save them. No editing or changes required. The media will get processed just like any other media.

## Changelog

### 1.0-RC1
*  This is the first candidate release that we're sharing with trusted beta testers.