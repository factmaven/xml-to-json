<?php
/**
 * XML to JSON API
 *
 * @link https://api.factmaven.com/xml-to-json
 * @author Ethan O'Sullivan <https://ethanosullivan.com>
 * @version 2.0.2
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Get XML source through the 'xml' parameter
if (!empty($_GET['xml']) && isset($_GET['xml'])) {
    $xmlQueryString = $_GET['xml'];

    // For files over HTTP protocol
    if (filter_var($xmlQueryString, FILTER_VALIDATE_URL)) {
        // Check if XML file exists (Status Code = 200)
        $handle = curl_init($xmlQueryString);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        $statusCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        if ($statusCode == 200) {
            $path = $xmlQueryString;
            $xml = simplexml_load_file($path);
            $json = xmlToArray($xml);
            echo json_encode($json);
            return;
        } else {
            // Display error that XML isn't found with provided URL
            $statusCode = $statusCode;
            $title = get_headers($xmlQueryString, 1);
            $title = substr($title[0], 13);
            $detail = "The URL you provided does not point to an valid XML feed. Please check that you entered the correct URL.";
            $json = constructErrorResponse($statusCode, $title, $detail);
            echo json_encode($json);
            return;
        }
    } else {
        // Assume it's an XML string and check if string is valid XML
        libxml_use_internal_errors(true);
        if (simplexml_load_string($xmlQueryString)) {
            $xml = simplexml_load_string($xmlQueryString);
            $json = xmlToArray($xml);
            echo json_encode($json);
            return;
        } else {
            // Show all XML validation errors
            $statusCode = 400;
            $title = "Failed Loading XML";
            $detail = [];
            foreach (libxml_get_errors() as $error) {
                $detail[] = str_replace("\n", "", $error->message);
            }
            $json = constructErrorResponse($statusCode, $title, $detail);
            echo json_encode($json);
            return;
        }
    }
} else {
    // Display error that parameter is missing
    $statusCode = 404;
    $title = "Missing Parameter";
    $detail = "Please set the path to your XML by using the '?xml=' query string.";
    $json = constructErrorResponse($statusCode, $title, $detail);
    echo json_encode($json);
    return;
}

/**
 * @param string $xml XML code to convert to JSON
 * @param array $options Options of the API to change JSON output
 *
 * @return array The JSON response
 */
function xmlToArray($xml, $options = [])
{
    $defaults = [
        'namespaceRecursive' => false, // Get XML doc namespaces recursively
        'removeNamespace' => true, // Remove namespace from resulting keys
        'namespaceSeparator' => ':', // Change separator to something other than a colon
        'attributePrefix' => '', // Distinguish between attributes and nodes with the same name
        'alwaysArray' => [], // Array of XML tag names which should always become arrays
        'autoArray' => true, // Create arrays for tags which appear more than once
        'textContent' => 'text', // Key used for the text content of elements
        'autoText' => true, // Skip textContent key if node has no attributes or child nodes
        'keySearch' => false, // (Optional) search and replace on tag and attribute names
        'keyReplace' => false, // (Optional) replace values for above search values
    ];
    $options = array_merge($defaults, $options);
    $namespaces = $xml->getDocNamespaces($options['namespaceRecursive']);
    $namespaces[''] = null; // Add empty base namespace

    // Get attributes from all namespaces
    $attributesArray = [];
    foreach ($namespaces as $prefix => $namespace) {
        if ($options['removeNamespace']) {
            $prefix = '';
        }
        foreach ($xml->attributes($namespace) as $attributeName => $attribute) {
            // (Optional) replace characters in attribute name
            if ($options['keySearch']) {
                $attributeName = str_replace($options['keySearch'], $options['keyReplace'], $attributeName);
            }
            $attributeKey = $options['attributePrefix'] . ($prefix ? $prefix . $options['namespaceSeparator'] : '') . $attributeName;
            $attributesArray[$attributeKey] = (string) $attribute;
        }
    }

    // Get child nodes from all namespaces
    $tagsArray = [];
    foreach ($namespaces as $prefix => $namespace) {
        if ($options['removeNamespace']) {
            $prefix = '';
        }

        foreach ($xml->children($namespace) as $childXml) {
            // Recurse into child nodes
            $childArray = xmlToArray($childXml, $options);
            $childTagName = key($childArray);
            $childProperties = current($childArray);

            // Replace characters in tag name
            if ($options['keySearch']) {
                $childTagName = str_replace($options['keySearch'], $options['keyReplace'], $childTagName);
            }

            // Add namespace prefix, if any
            if ($prefix) {
                $childTagName = $prefix . $options['namespaceSeparator'] . $childTagName;
            }

            if (!isset($tagsArray[$childTagName])) {
                // Only entry with this key
                // Test if tags of this type should always be arrays, no matter the element count
                $tagsArray[$childTagName] = in_array($childTagName, $options['alwaysArray'], true) || !$options['autoArray'] ? [$childProperties] : $childProperties;
            } elseif (is_array($tagsArray[$childTagName]) && array_keys($tagsArray[$childTagName]) === range(0, count($tagsArray[$childTagName]) - 1)) {
                // Key already exists and is integer indexed array
                $tagsArray[$childTagName][] = $childProperties;
            } else {
                // Key exists so convert to integer indexed array with previous value in position 0
                $tagsArray[$childTagName] = [$tagsArray[$childTagName], $childProperties];
            }
        }
    }

    // Get text content of node
    $textContentArray = [];
    $plainText = trim((string) $xml);
    if ($plainText !== '') {
        $textContentArray[$options['textContent']] = $plainText;
    }

    // Stick it all together
    $propertiesArray = !$options['autoText'] || $attributesArray || $tagsArray || $plainText === '' ? array_merge($attributesArray, $tagsArray, $textContentArray) : $plainText;

    // Return node as array
    return [
        $xml->getName() => $propertiesArray,
    ];
}

/**
 * @param string $statusCode Status code to represent the response (eg 404)
 * @param string $title Title of the error, (eg "Missing Parameter") when `$_GET['xml']` doesn't exist
 * @param string $detail Description for the title
 *
 * @return array The error response
 */
function constructErrorResponse($statusCode, $title, $detail)
{
    // Set timestamp to New York
    $timestamp = (new DateTime("America/New_York"))->format("Y-m-d h:i:s ") . "EST";

    $json = [
        "error" => [
            "timestamp" => $timestamp,
            "status" => $statusCode,
            "title" => $title,
            "detail" => $detail,
            "url" => $_SERVER['REQUEST_URI'],
        ],
        "meta" => [
            "version" => "2.0.2",
            "copyright" => "Copyright 2011-" . date("Y") . " Fact Maven",
            "link" => "https://factmaven.com/",
            "authors" => ["Ethan O'Sullivan", "Edward Bebbington"],
        ],
    ];
    return $json;
}

// DEBUG: Array output
// print_r($json);