<?php

declare(strict_types=1);

$json = stream_get_contents(STDIN);

if ($json === false || trim($json) === '') {
    fwrite(STDERR, "Deptrac JSON input is empty.\n");
    exit(1);
}

$data = json_decode($json, true);

if (! is_array($data)) {
    fwrite(STDERR, "Unable to decode Deptrac JSON output.\n");
    exit(1);
}

$baseOwnedNamespaces = [
    'App\\',
    'Base\\',
    'Database\\',
    'Tests\\',
    'Modules\\',
];

$violations = [];

foreach (($data['files'] ?? []) as $file => $info) {
    foreach (($info['messages'] ?? []) as $message) {
        if (($message['type'] ?? null) !== 'warning') {
            continue;
        }

        $text = (string) ($message['message'] ?? '');

        if (! str_contains($text, 'uncovered dependency on ')) {
            continue;
        }

        if (! preg_match('/uncovered dependency on ([^\s(]+)/', $text, $matches)) {
            $violations[] = sprintf(
                '%s: unable to classify uncovered dependency: %s',
                $file,
                $text,
            );

            continue;
        }

        $dependency = $matches[1];

        foreach ($baseOwnedNamespaces as $namespace) {
            if (str_starts_with($dependency, $namespace)) {
                $violations[] = sprintf(
                    '%s: uncovered Base-owned dependency: %s',
                    $file,
                    $dependency,
                );

                break;
            }
        }
    }
}

if ($violations !== []) {
    fwrite(
        STDERR,
        "Architecture coverage failed:\n- "
        .implode("\n- ", $violations)
        ."\n",
    );

    exit(1);
}

fwrite(
    STDOUT,
    "Architecture coverage passed: no uncovered Base-owned dependencies.\n",
);

exit(0);
