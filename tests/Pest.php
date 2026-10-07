<?php

// Read exported or plain OPENAI_* variables without executing the .env file.
$environmentFile = dirname(__DIR__) . '/.env';
if (is_file($environmentFile)) {
    foreach (file($environmentFile, FILE_IGNORE_NEW_LINES) as $line) {
        if (! preg_match('/^\s*(?:export\s+)?(OPENAI_[A-Z0-9_]+)\s*=\s*(.*)$/', $line, $matches)) {
            continue;
        }

        if (getenv($matches[1]) !== false) {
            continue;
        }

        $value = trim($matches[2]);
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && substr($value, -1) === $value[0]) {
            $value = substr($value, 1, -1);
        } else {
            $value = preg_replace('/\s+#.*$/', '', $value);
        }

        putenv($matches[1] . '=' . $value);
    }
}
