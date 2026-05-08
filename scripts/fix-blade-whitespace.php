<?php

$viewsPath = __DIR__ . '/resources/views';

if (!is_dir($viewsPath)) {
    echo "❌ resources/views not found.\n";
    exit(1);
}

/**
 * Normalize expressions (used for {{ ... }}, @json(...), and @if(...) etc.)
 */
function normalizeExpression(string $expr): string
{
    // Collapse whitespace outside of strings
    $expr = preg_replace_callback(
        '/(\'[^\']*\'|"[^"]*")|(\s+)/',
        function ($m) {
            if (!empty($m[1])) return $m[1]; // keep string literals as-is
            return ' ';
        },
        $expr
    );

    // Normalize all -> operators (including broken - >)
    $expr = preg_replace('/-\s*>/', '->', $expr);

    // Normalize => operator
    $expr = preg_replace('/\s*=>\s*/', ' => ', $expr);

    // Normalize parentheses
    $expr = preg_replace('/\(\s*/', '(', $expr);
    $expr = preg_replace('/\s*\)/', ')', $expr);

    // Remove spaces around dash inside single-quoted strings (route names)
    $expr = preg_replace_callback(
        "/'([^']*?)'/",
        function ($m) {
            $str = $m[1];
            $str = preg_replace('/\s*-\s*/', '-', $str);
            return "'" . $str . "'";
        },
        $expr
    );

    return trim($expr);
}

/**
 * Normalize Blade content
 */
function normalizeBlade(string $content): string
{
    // 1. Fix malformed Blade comments
    $content = preg_replace(
        '/\{\{\s*--\s*(.*?)\s*--\s*\}\}/s',
        '{{-- $1 --}}',
        $content
    );

    // 2. Normalize {{ ... }} echoes
    $content = preg_replace_callback(
        '/\{\{\s*(?!-)([\s\S]*?)\s*\}\}/',
        function ($m) {
            return '{{ ' . normalizeExpression($m[1]) . ' }}';
        },
        $content
    );

    // 3. Normalize @json(...)
    $content = preg_replace_callback(
        '/@json\s*\(([\s\S]*?)\)/',
        function ($m) {
            return '@json(' . normalizeExpression($m[1]) . ')';
        },
        $content
    );

    // 4. Normalize Blade directives with parentheses
    $content = preg_replace_callback(
        '/@(if|elseif|while|switch|isset|empty)\s*\(([\s\S]*?)\)/',
        function ($m) {
            $directive = $m[1];
            $expr = normalizeExpression($m[2]);
            return "@$directive($expr)";
        },
        $content
    );

    return $content;
}

/**
 * Show line diffs
 */
function diffLines(string $original, string $fixed): array
{
    $oLines = explode("\n", $original);
    $fLines = explode("\n", $fixed);

    $diff = [];
    $max = max(count($oLines), count($fLines));

    for ($i = 0; $i < $max; $i++) {
        $o = $oLines[$i] ?? '';
        $f = $fLines[$i] ?? '';

        if ($o !== $f) {
            $diff[] = [
                'line' => $i + 1,
                'before' => $o,
                'after'  => $f,
            ];
        }
    }

    return $diff;
}

// --- Main script ---

$issues = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsPath)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $path = $file->getPathname();
    $original = file_get_contents($path);
    $fixed = normalizeBlade($original);

    if ($original !== $fixed) {
        $issues[] = [
            'file' => $path,
            'diff' => diffLines($original, $fixed),
            'fixed' => $fixed,
        ];
    }
}

if (empty($issues)) {
    echo "✔ No Blade formatting issues detected.\n";
    exit(0);
}

echo "\n⚠ Blade formatting issues found:\n\n";

foreach ($issues as $i => $issue) {
    echo ($i + 1) . ". {$issue['file']}\n";

    foreach ($issue['diff'] as $change) {
        echo "   line {$change['line']}:\n";
        echo "     - {$change['before']}\n";
        echo "     + {$change['after']}\n";
    }

    echo "\n";
}

echo "Apply fixes? (yes/no): ";
$confirm = trim(fgets(STDIN));

if ($confirm !== 'yes') {
    echo "❌ Aborted. No files were modified.\n";
    exit(0);
}

foreach ($issues as $issue) {
    file_contents($issue['file'], $issue['fixed']);
    echo "✔ Fixed: {$issue['file']}\n";
}

echo "\n✅ Done.\n";
