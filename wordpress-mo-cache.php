<?php
/**
 * Plugin Name: MO Cache
 * Plugin URI:  https://github.com/nasyrov/wordpress-mo-cache
 * Description: WordPress mu-plugin for a faster load_textdomain.
 * Author:      Evgenii Nasyrov
 * Author URI:  mailto:inasyrov@ya.ru
 * Version:     1.0.0
 */

add_filter('override_load_textdomain', function ($_, $domain, $mofile) {
    global $l10n;

    if (!is_readable($mofile)) {
        return false;
    }

    $cacheKey = sprintf('mo-cache-%s', md5($mofile));
    $cache    = get_transient($cacheKey);

    $mtime = filemtime($mofile);

    $mo = new MO;

    if (!$cache || !isset($cache['mtime']) || $mtime > $cache['mtime']) {
        if (!$mo->import_from_file($mofile)) {
            return false;
        }

        $cache = [
            'mtime'   => $mtime,
            'entries' => $mo->entries,
            'headers' => $mo->headers,
        ];

        set_transient($cacheKey, $cache);
    } else {
        $mo->entries = $cache['entries'];
        $mo->headers = $cache['headers'];
    }

    if (isset($l10n[$domain])) {
        $mo->merge_with($l10n[$domain]);
    }

    $l10n[$domain] =& $mo;

    return true;
}, 1, 3);
