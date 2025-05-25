<?php

namespace Itstudioat\Vuedata\Services;

use Illuminate\Http\Response;
use Itstudioat\Vuedata\Enums\VuedataResult;

class VuedataService
{

    public function read($source)
    {
        $path = resource_path($source);

        if (!file_exists($path)) {
            return response(VuedataResult::FILE_NOT_EXISTS->value);
        }

        $content = file_get_contents($path);

        // JS-like block extraction
        if (!preg_match('/data\s*\(\)\s*{.*?return\s*{(.*?)};\s*}/s', $content, $matches)) {
            return response(VuedataResult::NOT_DATA_BLOCK->value);
        }

        $rawObject = trim($matches[1]);



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

    public function write($source, $data)
    {
        $path = resource_path($source);

        if (!file_exists($path)) {
            return response(VuedataResult::FILE_NOT_EXISTS->value);
        }

        $content = file_get_contents($path);

        // extrahiere Inhalt des return-Objekts innerhalb data()
        if (!preg_match('/data\s*\(\)\s*{\s*return\s*({.*?})\s*;\s*}/s', $content, $matches)) {
            return response(VuedataResult::NOT_DATA_BLOCK->value);
        }

        $oldJs = $matches[1];

        // konvertiere den JS-Objekttext in JSON-ähnlich
        $jsToJson = preg_replace('/(\b\w+\b)\s*:/', '"$1":', $oldJs); // keys
        $jsToJson = preg_replace("/'([^']*?)'/", '"$1"', $jsToJson); // strings
        $jsToJson = preg_replace('/,\s*([\]}])/m', '$1', $jsToJson); // trailing commas
        $jsToJson = preg_replace('/,\s*$/', '', $jsToJson);

        $oldData = json_decode($jsToJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'status' => VuedataResult::PARSE_ERROR->value,
                'message' => json_last_error_msg(),
                'json_attempt' => $jsToJson,
            ], 400);
        }

        // nur Keys aus $data ersetzen
        foreach ($data as $key => $value) {
            $oldData[$key] = $value;
        }

        // konvertiere zurück zu JS-kompatiblem Objekttext
        $newJsObject = $this->phpArrayToJsObject($oldData);

        // ersetze den alten data()-Block
        $newContent = preg_replace_callback(
            '/data\s*\(\)\s*{\s*return\s*{.*?}\s*;\s*}/s',
            function () use ($newJsObject) {
                return "data() {\n    return " . $newJsObject . ";\n}";
            },
            $content
        );

        // 🔁 Datei aktualisieren
        file_put_contents($path, $newContent);

        // ✅ Rückmeldung
        return response()->json(['status' => 'ok']);
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
