# XML to JSON API
---

Inspired by [Tamlyn's XML2JSON](https://github.com/tamlyn/xml2json) converter, this simple API converts dynamic XML feeds to JSON by entering the URL in a parameter (`xml`). This ensures that the most recent XML data is converted to a JSON.

## How to Use

This API is current available for use by visiting:

### https://api.factmaven.com/xml-to-json

Then, adding your XML URL like so:

```
https://api.factmaven.com/xml-to-json?xml=http://example.com/feed.xml

```

Just add you XML feed URL in the parameter (`xml=...`).

## Overview

> *"Unfortunately, XML exists. For whatever reason, somebody once thought it was a good idea and now we’re stuck with it. Since most modern applications and APIs use JSON instead, it’s often necessary to convert XML into JSON..."*
>
> – [Tamlyn Rhodes](https://outlandish.com/blog/tutorial/xml-to-json/)

Some tools are not great at parsing XML data (such as `en-US` from `locale`). Specifically when there is data that within the attributes for example:

```xml
<item contentType="tv_episode" contentId="df9c946a-e891-11ea-adc1-0242ac120002">
    <pubDate>2020-08-27T11:39:57-05:00</pubDate>
    <title locale="en-US">Episode Title</title>
    <description locale="en-US">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</description>
    <rating systemCode="us-tv">TV-14</rating>
    <artwork url="https://example.com/image.jpg" type="tile_artwork" locales="en-US" />
</item>

```

While there are plenty of [XML to JSON converters available](https://www.google.com/search?q=xml+to+json+converter), none of them would convert a feed with live data that's constantly changing and updating its layout.

That's where the **XML to JSON API** comes in, it converts dynamic XML feeds to JSON. As a result, it will be able to parse those attribute values in a JSON format:

```
"item": [
    {
        "@contentType": "tv_episode",
        "@contentId": "df9c946a-e891-11ea-adc1-0242ac120002",
        "pubDate": "2020-03-26T11:39:57-05:00",
        "title": {
            "@locale": "en-US",
            "#text": "Episode Title"
        },
        "description": {
            "@locale": "en-US",
            "#text": "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
        },
        "rating": {
            "@systemCode": "us-tv",
            "#text": "TV-14"
        },
        "artwork": {
            "@url": "https://example.com/image.jpg",
            "@type": "tile_artwork",
            "@locales": "en-US"
        }
    }
]

```