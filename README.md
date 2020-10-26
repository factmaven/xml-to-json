# XML to JSON API
---

* [Overview](#overview)
* [How to Use](#how-to-use)
 * [Option 1: Submitting the XML URL](#option-1-submitting-the-xml-url)
 * [Option 2: Paste Raw XML Data](#option-2-paste-raw-xml-data)
* [Contributors](#contributors)

## Overview

This is an API that converts XML to JSON. If you are using an XML and don't want to go through the hassle of modifying it to be a JSON, this is the API for you. Simply submit the URL or raw data of the XML through the API and it will instantly convert your old feed to a JSON.

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

```json
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

## How to Use

This API is currently available for use by visiting:

### [`https://api.factmaven.com/xml-to-json`](https://api.factmaven.com/xml-to-json)

Then you can do one of the following:

### Option 1: Submitting the XML URL

Add the URL to your XML by adding the `?xml=` parameter and then the full URL of your feed. It's important that you must start with `http://` or `https://` for it to work.

#### Example

```
https://api.factmaven.com/xml-to-json?xml=https://example.com/feed.xml

```

### Option 2: Paste Raw XML Data

Paste the raw XML data after the `?xml=` parameter. Do note that there is a limit to how much you can load, it's best for smaller XMLs.

#### Example

```
https://api.factmaven.com/xml-to-json?xml=<item contentType="tv_episode" contentId="df9c946a-e891-11ea-adc1-0242ac120002"><pubDate>2020-08-27T11:39:57-05:00</pubDate><title locale="en-US">Episode Title</title><description locale="en-US">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</description><rating systemCode="us-tv">TV-14</rating><artwork url="https://example.com/image.jpg" type="tile_artwork" locales="en-US" /></item>

```

## Contributors

We would like to thank those who [helped us improve our API](https://github.com/factmaven/xml-to-json/graphs/contributors) with new features and fixing bugs. If you have any suggestions to improve our API or find any bugs, let us know [here](https://github.com/factmaven/xml-to-json/issues). Your feedback and suggestions are always welcome.

### [Ethan O'Sullivan](https://github.com/ethanosullivan), [Edward Bebbington](https://github.com/ebebbington)