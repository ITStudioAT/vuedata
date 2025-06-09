<?php

namespace Itstudioat\Vuedata\Services;

use Illuminate\Http\Response;
use Itstudioat\Vuedata\Enums\VuedataResult;

class VuedataService
{


    public function read($source)
    {
        if (!file_exists($source)) {
            return [
                'success' => false,
                'error' => VuedataResult::FILE_NOT_EXISTS->value,
                'source' => $source
            ];
        }

        $content = file_get_contents($source);

        if (!preg_match('/data\s*\(\)\s*{.*?return\s*{(.*?)};\s*}/s', $content, $matches)) {
            return [
                'success' => false,
                'error' => VuedataResult::NOT_DATA_BLOCK->value,
                'source' => $source
            ];
        }

        $rawObject = trim($matches[1]);

        // JS-Style Kommentare entfernen
        $rawObject = preg_replace('!/\*.*?\*/!s', '', $rawObject);
        $rawObject = preg_replace('/\/\/.*$/m', '', $rawObject);

        // Keys & Strings in JSON-Format umwandeln
        $converted = preg_replace_callback(
            "/'([^']*?)'/",
            fn($m) => '"' . addslashes($m[1]) . '"',
            $rawObject
        );
        $converted = preg_replace('/(\b\w+)\s*:/', '"$1":', $converted);
        $converted = preg_replace('/,\s*([\]}])/m', '$1', $converted);
        $converted = preg_replace('/,\s*$/', '', $converted);

        $json = '{' . $converted . '}';
        $parsed = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => VuedataResult::PARSE_ERROR->value,
                'message' => json_last_error_msg(),
                'json_attempt' => $json,
                'source' => $source
            ];
        }

        return [
            'success' => true,
            'data' => $parsed,
        ];
    }


    public function read90($source)
    {

        $path = $source;

        if (!file_exists($path)) {
            return response(VuedataResult::FILE_NOT_EXISTS->value);
        }

        $content = file_get_contents($path);

        // JS-like block extraction
        if (!preg_match('/data\s*\(\)\s*{.*?return\s*{(.*?)};\s*}/s', $content, $matches)) {
            return response(VuedataResult::NOT_DATA_BLOCK->value);
        }

        $rawObject = trim($matches[1]);

        // Remove JS-style comments
        $rawObject = preg_replace('!/\*.*?\*/!s', '', $rawObject); // remove /* */ comments
        $rawObject = preg_replace('/\/\/.*$/m', '', $rawObject);   // remove // comments

        // Convert to JSON-compatible format
        $converted = preg_replace_callback(
            "/'([^']*?)'/",
            fn($m) => '"' . addslashes($m[1]) . '"',
            $rawObject
        );



        $converted = preg_replace('/(\b\w+)\s*:/', '"$1":', $converted); // quote keys
        $converted = preg_replace('/,\s*([\]}])/m', '$1', $converted);   // remove trailing commas inside
        $converted = preg_replace('/,\s*$/', '', $converted);            // remove final trailing comma

        $json = '{' . $converted . '}';

        $parsed = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'status' => VuedataResult::PARSE_ERROR->value,
                'message' => json_last_error_msg(),
                'json_attempt' => $json,
            ], 400);
        }

        return $parsed;
    }

    public function write90($source, $data)
    {

        $commentMap = [];
        $path = $source;

        if (!file_exists($path)) {
            return response(VuedataResult::FILE_NOT_EXISTS->value);
        }

        $content = file_get_contents($path);

        // Extract the data() return block
        if (!preg_match('/data\s*\(\)\s*{\s*return\s*({.*?})\s*;\s*}/s', $content, $matches)) {
            return response(VuedataResult::NOT_DATA_BLOCK->value);
        }

        $oldJs = trim($matches[1]);

        // 🧹 Extract and store comments as placeholders
        $oldJsCleaned = $this->extractComments($oldJs, $commentMap);


        // Convert to JSON-like for decoding
        $jsToJson = preg_replace('/(\b\w+\b)\s*:/', '"$1":', $oldJsCleaned); // keys
        $jsToJson = preg_replace("/'([^']*?)'/", '"$1"', $jsToJson);         // strings
        $jsToJson = preg_replace('/,\s*([\]}])/m', '$1', $jsToJson);         // trailing commas
        $jsToJson = preg_replace('/,\s*$/', '', $jsToJson);                  // final comma


        $oldData = json_decode($jsToJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'status' => VuedataResult::PARSE_ERROR->value,
                'message' => json_last_error_msg(),
                'json_attempt' => $jsToJson,
            ], 400);
        }


        // Replace keys in data
        foreach ($data as $key => $value) {
            $oldData[$key] = $value;
        }

        // Convert PHP array back to JS object syntax
        $newJsObject = $this->phpArrayToJsObject($oldData);

        // 🔁 Reinsert preserved comments
        $finalJsObjectWithComments = $this->reinsertComments($newJsObject, $commentMap);

        // Replace the old data() block
        $newContent = preg_replace_callback(
            '/data\s*\(\)\s*{\s*return\s*{.*?}\s*;\s*}/s',
            function () use ($finalJsObjectWithComments) {
                return "data() {\n    return " . $finalJsObjectWithComments . ";\n}";
            },
            $content
        );


        // Write back the modified file
        file_put_contents($path, $newContent);

        return response()->json(['status' => 'ok']);
    }


    public function write($source, $data)
    {
        $commentMap = [];
        $path = $source;

        if (!file_exists($path)) {
            return [
                'status' => 'error',
                'error' => VuedataResult::FILE_NOT_EXISTS->value,
                'source' => $source
            ];
        }

        $content = file_get_contents($path);

        // Extract the data() return block
        if (!preg_match('/data\s*\(\)\s*{\s*return\s*({.*?})\s*;\s*}/s', $content, $matches)) {
            return [
                'status' => 'error',
                'error' => VuedataResult::NOT_DATA_BLOCK->value,
                'source' => $source
            ];
        }

        $oldJs = trim($matches[1]);

        // 🧹 Extract and store comments as placeholders
        $oldJsCleaned = $this->extractComments($oldJs, $commentMap);

        // Convert to JSON-like for decoding
        $jsToJson = preg_replace('/(\b\w+\b)\s*:/', '"$1":', $oldJsCleaned); // keys
        $jsToJson = preg_replace("/'([^']*?)'/", '"$1"', $jsToJson);         // strings
        $jsToJson = preg_replace('/,\s*([\]}])/m', '$1', $jsToJson);         // trailing commas
        $jsToJson = preg_replace('/,\s*$/', '', $jsToJson);                  // final comma

        $oldData = json_decode($jsToJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'status' => 'error',
                'error' => VuedataResult::PARSE_ERROR->value,
                'message' => json_last_error_msg(),
                'json_attempt' => $jsToJson,
                'source' => $source
            ];
        }

        // Replace keys in data
        foreach ($data as $key => $value) {
            $oldData[$key] = $value;
        }

        // Convert PHP array back to JS object syntax
        $newJsObject = $this->phpArrayToJsObject($oldData);

        // 🔁 Reinsert preserved comments
        $finalJsObjectWithComments = $this->reinsertComments($newJsObject, $commentMap);

        // Replace the old data() block
        $newContent = preg_replace_callback(
            '/data\s*\(\)\s*{\s*return\s*{.*?}\s*;\s*}/s',
            function () use ($finalJsObjectWithComments) {
                return "data() {\n    return " . $finalJsObjectWithComments . ";\n}";
            },
            $content
        );

        // Write back the modified file
        file_put_contents($path, $newContent);

        return [
            'status' => 'success',
            'written_keys' => array_keys($data),
        ];
    }





    protected function extractComments(string $js, array &$commentMap): string
    {
        $commentMap = [];
        $index = 0;

        // Regex für // Kommentare
        $js = preg_replace_callback('/\/\/(.*?)\n/', function ($matches) use (&$commentMap, &$index) {
            $key = "__COMMENT_BLOCK_{$index}__";
            $commentMap[$key] = '// ' . trim($matches[1]);
            $index++;
            return "\"$key\": \"__COMMENT__\",\n";
        }, $js);

        // Regex für /* */ Kommentare
        $js = preg_replace_callback('/\/\*.*?\*\//s', function ($matches) use (&$commentMap, &$index) {
            $key = "__COMMENT_BLOCK_{$index}__";
            $commentMap[$key] = trim($matches[0]);
            $index++;
            return "\"$key\": \"__COMMENT__\",\n";
        }, $js);

        return $js;
    }



    protected function reinsertComments(string $js, array $commentMap): string
    {
        foreach ($commentMap as $key => $originalComment) {
            // Entferne umgebende JSON-Syntax
            $pattern = '/"' . preg_quote($key, '/') . '":\s*"__COMMENT__",?\n?/';

            $js = preg_replace($pattern, $originalComment . "\n", $js);
        }

        return $js;
    }



    public function phpArrayToJsObject($data)
    {
        return $this->convertToJs($data);
    }

    private function convertToJs($value, $indentLevel = 1)
    {
        $indent = str_repeat("    ", $indentLevel); // 4 spaces
        $nextIndent = str_repeat("    ", $indentLevel + 1);

        if (is_array($value)) {
            $isList = array_keys($value) === range(0, count($value) - 1);

            if ($isList) {
                $items = array_map(fn($v) => $this->convertToJs($v, $indentLevel + 1), $value);
                return "[\n$nextIndent" . implode(",\n$nextIndent", $items) . "\n$indent]";
            } else {
                $items = [];
                foreach ($value as $k => $v) {
                    $items[] = $nextIndent . $this->escapeKey($k) . ": " . $this->convertToJs($v, $indentLevel + 1);
                }
                return "{\n" . implode(",\n", $items) . "\n$indent}";
            }
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            return 'null';
        } elseif (is_numeric($value)) {
            return $value;
        } else {
            return '"' . addslashes($value) . '"';
        }
    }


    private function escapeKey($key)
    {
        // Wenn der Key JS-kompatibel ist (z. B. keine Leerzeichen), dann kein Quote
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
            return $key;
        }
        return '"' . addslashes($key) . '"';
    }
}
