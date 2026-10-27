<?php
/** Íconos de línea (mismo set que la home actual). */
function icon(string $k, int $size = 20): string {
    $a = 'width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
    $p = [
        'target' => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r=".8" fill="currentColor" stroke="none"/>',
        'megaphone' => '<path d="M3 10v4a1 1 0 0 0 1 1h2l5 4V5l-5 4H4a1 1 0 0 0-1 1Z"/><path d="M16 8.5c1.2 1 1.2 6 0 7"/><path d="M19 6c2 2 2 10 0 12"/>',
        'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3"/><line x1="7.5" y1="10" x2="7.5" y2="17"/><circle cx="7.5" cy="6.7" r="1" fill="currentColor" stroke="none"/><path d="M11.5 17v-4.2c0-1.6 1-2.6 2.3-2.6 1.3 0 2.2 1 2.2 2.6V17"/>',
        'play' => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m10 9 5 3-5 3Z" fill="currentColor" stroke="none"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><line x1="20" y1="20" x2="15.3" y2="15.3"/>',
        'pen' => '<path d="M4 20h4L18.5 9.5a2.1 2.1 0 0 0-3-3L5 17v3Z"/><line x1="13.5" y1="6.5" x2="17.5" y2="10.5"/>',
        'brand' => '<circle cx="12" cy="12" r="9"/><circle cx="8.5" cy="9" r="1.3" fill="currentColor" stroke="none"/><circle cx="12.5" cy="7.5" r="1.3" fill="currentColor" stroke="none"/><circle cx="16" cy="10.5" r="1.3" fill="currentColor" stroke="none"/><path d="M12 21c-1.5 0-2-1-2-2s1-2 2.5-2H15a3 3 0 0 0 3-3"/>',
        'browser' => '<rect x="3" y="4.5" width="18" height="15" rx="2.2"/><line x1="3" y1="8.5" x2="21" y2="8.5"/><circle cx="6" cy="6.5" r=".6" fill="currentColor" stroke="none"/><circle cx="8" cy="6.5" r=".6" fill="currentColor" stroke="none"/>',
        'code' => '<path d="m8 8-4 4 4 4"/><path d="m16 8 4 4-4 4"/><line x1="13.5" y1="5" x2="10.5" y2="19"/>',
        'chart' => '<path d="M4 19V5"/><path d="M4 19h16"/><path d="m7 15 4-4 3 3 5-6"/>',
        'gear' => '<circle cx="12" cy="12" r="3"/><path d="M12 3v2.4M12 18.6V21M21 12h-2.4M5.4 12H3M18.1 5.9l-1.7 1.7M7.6 16.4l-1.7 1.7M18.1 18.1l-1.7-1.7M7.6 7.6 5.9 5.9"/>',
        'phone' => '<path d="M5 4h3.2l1.3 4.2-2 1.6a13 13 0 0 0 6.7 6.7l1.6-2 4.2 1.3V19a2 2 0 0 1-2.2 2A16 16 0 0 1 3 6.2 2 2 0 0 1 5 4Z"/>',
        'factory' => '<path d="M4 20V11l5 3.2V11l5 3.2V11l5 3.2V20Z"/><line x1="3" y1="20" x2="21" y2="20"/><line x1="7" y1="7" x2="7" y2="4"/>',
        'medical' => '<circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>',
        'building' => '<rect x="5" y="3" width="14" height="18" rx="1"/><rect x="8" y="6.5" width="2.2" height="2.2" fill="currentColor" stroke="none"/><rect x="13.8" y="6.5" width="2.2" height="2.2" fill="currentColor" stroke="none"/><rect x="8" y="11" width="2.2" height="2.2" fill="currentColor" stroke="none"/><rect x="13.8" y="11" width="2.2" height="2.2" fill="currentColor" stroke="none"/><path d="M9.5 21v-4.5h5V21"/>',
        'cart' => '<circle cx="9" cy="20" r="1.3" fill="currentColor" stroke="none"/><circle cx="17" cy="20" r="1.3" fill="currentColor" stroke="none"/><path d="M3 4h2.2l2 11.5h10.3L19.5 8H7"/>',
        'book' => '<path d="M12 6.5c-1.6-1.3-4-2-7-2v12.5c3 0 5.4.7 7 2 1.6-1.3 4-2 7-2V4.5c-3 0-5.4.7-7 2Z"/><line x1="12" y1="6.5" x2="12" y2="19"/>',
        'briefcase' => '<rect x="3" y="8" width="18" height="11" rx="1.5"/><path d="M8 8V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="3" y1="13" x2="21" y2="13"/>',
        'cpu' => '<rect x="6" y="6" width="12" height="12" rx="1.5"/><line x1="9" y1="2.5" x2="9" y2="6"/><line x1="15" y1="2.5" x2="15" y2="6"/><line x1="9" y1="18" x2="9" y2="21.5"/><line x1="15" y1="18" x2="15" y2="21.5"/><line x1="2.5" y1="9" x2="6" y2="9"/><line x1="2.5" y1="15" x2="6" y2="15"/><line x1="18" y1="9" x2="21.5" y2="9"/><line x1="18" y1="15" x2="21.5" y2="15"/>',
        'scooter' => '<circle cx="6" cy="19" r="2.1"/><circle cx="17" cy="19" r="2.1"/><path d="M6 19h8l3-10h3M9 19l3-10"/>',
        'institutional' => '<path d="M4 9 12 4l8 5"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="6" y1="9" x2="6" y2="18"/><line x1="10" y1="9" x2="10" y2="18"/><line x1="14" y1="9" x2="14" y2="18"/><line x1="18" y1="9" x2="18" y2="18"/><line x1="3" y1="20" x2="21" y2="20"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2"/><line x1="3.5" y1="10" x2="20.5" y2="10"/><line x1="8" y1="3" x2="8" y2="7"/><line x1="16" y1="3" x2="16" y2="7"/>',
        'users' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><circle cx="17.5" cy="9" r="2.4"/><path d="M16 14.6c2.9.2 5 2.2 5 5.4"/>',
        'scissors' => '<circle cx="6" cy="7" r="2.6"/><circle cx="6" cy="17" r="2.6"/><path d="M8.2 8.4 20 17M8.2 15.6 20 7"/>',
        'box' => '<path d="M3.5 7.5 12 3l8.5 4.5v9L12 21l-8.5-4.5Z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>',
        'coins' => '<ellipse cx="9" cy="7" rx="5.5" ry="2.5"/><path d="M3.5 7v4c0 1.4 2.5 2.5 5.5 2.5s5.5-1.1 5.5-2.5V7"/><path d="M9.5 15.9c.9.4 2.1.6 3.5.6 3 0 5.5-1.1 5.5-2.5V10"/><path d="M14.5 9.6c2.3.3 4 1.2 4 2.4"/>',
        'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2Z"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="9" y1="12" x2="15" y2="12"/>',
        'chat' => '<path d="M4 5h16v11H9l-5 4Z"/><line x1="8" y1="9.5" x2="16" y2="9.5"/><line x1="8" y1="12.5" x2="13" y2="12.5"/>',
        'cap' => '<path d="M22 9 12 4 2 9l10 5 10-5Z"/><path d="M6 11v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>',
        'cert' => '<rect x="3" y="4" width="18" height="12" rx="1.5"/><circle cx="12" cy="17" r="3"/><path d="m10.5 19.5-1 3 2.5-1 2.5 1-1-3"/>',
        'check' => '<polyline points="20 6 9 17 4 12"/>',
        'lock' => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/>',
        'puzzle' => '<path d="M10 4a2 2 0 1 1 4 0v2h4v4h-2a2 2 0 1 0 0 4h2v4h-4v-2a2 2 0 1 0-4 0v2H6v-4H4a2 2 0 1 1 0-4h2V6h4Z"/>',
        'rocket' => '<path d="M14 4c3.5 0 6 2.5 6 6-2 4-5 6.5-9 8l-5-5c1.5-4 4-7 8-9Z"/><circle cx="14.5" cy="9.5" r="1.6"/><path d="M6 13l-3 1 2 2M11 18l-1 3-2-2"/>',
        'star' => '<path d="m12 2.8 2.8 5.8 6.3.9-4.6 4.4 1.1 6.3L12 17.2l-5.6 3 1.1-6.3L2.9 9.5l6.3-.9L12 2.8Z"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3Z"/>',
        'layers' => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'sparkle' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M6 18l2.5-2.5M15.5 8.5 18 6"/>',
    ];
    return '<svg ' . $a . '>' . ($p[$k] ?? $p['sparkle']) . '</svg>';
}

/** Elige un ícono a partir del texto de un servicio (para casos de éxito cargados desde el admin). */
function icon_for(string $text): string {
    $t = mb_strtolower($text);
    $map = [
        'pauta' => 'chart', 'ads' => 'chart', 'performance' => 'chart', 'campañ' => 'chart',
        'ugc' => 'users', 'creador' => 'users', 'contenido' => 'pen', 'brand' => 'brand', 'marca' => 'brand',
        'sitio' => 'globe', 'web' => 'globe', 'seo' => 'search', 'geo' => 'search',
        'contact' => 'phone', 'whatsapp' => 'chat', 'crm' => 'layers', 'odoo' => 'layers', 'integra' => 'puzzle', 'netegia' => 'puzzle', 'erp' => 'puzzle',
        'alumno' => 'cap', 'lms' => 'cap', 'administra' => 'receipt', 'venta' => 'cart', 'tienda' => 'cart', 'app' => 'phone', 'sistema' => 'code', 'gestión' => 'code',
    ];
    foreach ($map as $k => $ic) if (str_contains($t, $k)) return $ic;
    return 'sparkle';
}
