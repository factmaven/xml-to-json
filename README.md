# XML to JSON API
---

Inspired by [Tamlyn's XML2JSON](https://github.com/tamlyn/xml2json) converter, this simple API converts dynamic XML feeds to JSON by entering the URL in a parameter (`xml`). This ensures that the most recent XML data is converted to a JSON.

## Example
---

This API is current available for use by visiting:

### https://api.factmaven.com/xml-to-json

Then, adding your XML URL like so:

```
https://api.factmaven.com/xml-to-json?xml=http://example.com/feed.xml
```