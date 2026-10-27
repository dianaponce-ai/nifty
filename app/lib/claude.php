<?php
/**
 * Cliente mínimo de la API de Claude (opcional).
 * Si no hay api_key en config.php, las funciones devuelven null y el sitio sigue funcionando.
 */
function claude_enabled(): bool { return (string) cfg('anthropic.api_key', '') !== ''; }

function claude_ask(string $system, string $prompt, int $maxTokens = 1500): ?string {
    if (!claude_enabled()) return null;
    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => [
            'content-type: application/json',
            'x-api-key: ' . cfg('anthropic.api_key'),
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_POSTFIELDS => jenc([
            'model' => cfg('anthropic.model', 'claude-sonnet-5-5'),
            'max_tokens' => $maxTokens,
            'system' => $system,
            'messages' => [['role' => 'user', 'content' => $prompt]],
        ]),
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($res === false || $code >= 300) { error_log('[Nifty Claude] HTTP ' . $code . ' ' . substr((string) $res, 0, 300)); return null; }
    $d = json_decode($res, true);
    $txt = '';
    foreach ($d['content'] ?? [] as $b) if (($b['type'] ?? '') === 'text') $txt .= $b['text'];
    return trim($txt) ?: null;
}

/** Pide JSON y lo decodifica (tolera ```json ... ```). */
function claude_json(string $system, string $prompt, int $maxTokens = 2500): ?array {
    $t = claude_ask($system . "\nRespondé SOLO con JSON válido, sin texto adicional.", $prompt, $maxTokens);
    if (!$t) return null;
    $t = preg_replace('/^```(?:json)?|```$/m', '', $t);
    $s = strpos($t, '{'); $e = strrpos($t, '}');
    if ($s === false || $e === false) return null;
    $d = json_decode(substr($t, $s, $e - $s + 1), true);
    return is_array($d) ? $d : null;
}
