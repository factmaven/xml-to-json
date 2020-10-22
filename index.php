<?php
/**
 * XML to JSON API
 *
 * @author Ethan O'Sullivan
 * @link https://api.factmaven.com/xml-to-json
 * @version 1.1.0
 */

// tests:
// ensure a get passing innn the file https://api.factmaven.com/xml-to-json/qa?xml=https://medium.com/feed/@ethanosullivan works
// same as above but add the .xml extension
// ensure a get passinng in small xmls tirng works
// same as above but large xml string

// Lets the browser and tools such as Postman know it's JSON
header("Content-Type: application/json");

// Get XML source through the 'xml' parameter
if (!empty($_GET['xml']) && isset($_GET['xml'])) {
    if (strpos($_GET['xml'], "https://") === 0) { // is a file
        $path = $_GET['xml'];
        $xml = simplexml_load_file($_GET['xml']);
        $json = xmlToArray($xml);
    } else { // Assume it's an xml string
        $xml = simplexml_load_string($_GET['xml']);
        $json = xmlToArray($xml);
    }
} else {
    $json = [
        "errors" => [
            "id" => "404",
            "title" => "Missing Parameter",
            "detail" => "Please set the path to your XML by using the '?xml=' query string.",
        ],
        "meta" => [
        "version" => "1.1.0",
        "copyright" => "Copyright 2011-" . date("Y") . " Fact Maven",
        "link" => "https://factmaven.com/",
        "authors" => [
                "Ethan O'Sullivan",
            ],
        ],
    ];
}

function xmlToArray($xml, $options = array()) {
    $defaults = array(
        'namespaceRecursive' => false,  //setting to true will get xml doc namespaces recursively
        'removeNamespace' => false,     //set to true if you want to remove the namespace from resulting keys (recommend setting namespaceSeparator = '' when this is set to true)
        'namespaceSeparator' => ':',    //you may want this to be something other than a colon
        'attributePrefix' => '@',       //to distinguish between attributes and nodes with the same name
        'alwaysArray' => array(),       //array of xml tag names which should always become arrays
        'autoArray' => true,            //only create arrays for tags which appear more than once
        'textContent' => '#text',           //key used for the text content of elements
        'autoText' => true,             //skip textContent key if node has no attributes or child nodes
        'keySearch' => false,           //optional search and replace on tag and attribute names
        'keyReplace' => false           //replace values for above search values (as passed to str_replace())
    );
    $options = array_merge($defaults, $options);
    $namespaces = $xml->getDocNamespaces($options['namespaceRecursive']);
    $namespaces[''] = null; //add base (empty) namespace
 
    //get attributes from all namespaces
    $attributesArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        if ($options['removeNamespace']) {
            $prefix = '';
        }
        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
            //replace characters in attribute name
            if ($options['keySearch']) {
                $attributeName =
                    str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
            }
            $attributeKey = $options['attributePrefix']
                . ($prefix ? $prefix . $options['namespaceSeparator'] : '')
                . $attributeName;
            $attributesArray[$attributeKey] = (string)$attribute;
        }
    }
 
    //get child nodes from all namespaces
    $tagsArray = array();
    foreach ($namespaces as $prefix => $namespace) {
        if ($options['removeNamespace']) {
            $prefix = '';
        }

        foreach ($xml->children($namespace) as $childXml) {
            //recurse into child nodes
            $childArray = xmlToArray($childXml, $options);
            $childTagName = key($childArray);
            $childProperties = current($childArray);
 
            //replace characters in tag name
            if ($options['keySearch']) {
                $childTagName =
                    str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
            }

            //add namespace prefix, if any
            if ($prefix) {
                $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
            }
 
            if (!isset($tagsArray[$childTagName])) {
                //only entry with this key
                //test if tags of this type should always be arrays, no matter the element count
                $tagsArray[$childTagName] =
                        in_array($childTagName, $options['alwaysArray'], true) || !$options['autoArray']
                        ? array($childProperties) : $childProperties;
            } elseif (
                is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName])
                === range(0, count($tagsArray[$childTagName]) - 1)
            ) {
                //key already exists and is integer indexed array
                $tagsArray[$childTagName][] = $childProperties;
            } else {
                //key exists so convert to integer indexed array with previous value in position 0
                $tagsArray[$childTagName] = array($tagsArray[$childTagName], $childProperties);
            }
        }
    }
 
    //get text content of node
    $textContentArray = array();
    $plainText = trim((string)$xml);
    if ($plainText !== '') {
        $textContentArray[$options['textContent']] = $plainText;
    }
 
    //stick it all together
    $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || ($plainText === '')
        ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;
 
    //return node as array
    return array(
        $xml->getName() => $propertiesArray
    );
}

// Output JSON
echo json_encode($json);

// DEBUG: Array output
// print_r($json);