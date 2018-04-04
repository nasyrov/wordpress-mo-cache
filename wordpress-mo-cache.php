<?php
/**
 * Plugin Name: MO Cache
 * Plugin URI:  https://github.com/nasyrov/wordpress-mo-cache
 * Description: WordPress mu-plugin for a faster load_textdomain.
 * Author:      Evgenii Nasyrov
 * Author URI:  mailto:inasyrov@ya.ru
 * Version:     0.0.0
 */

add_filter('override_load_textdomain', function ($_, $domain, $mofile) {
    global $l10n;

    if (!is_readable($mofile)) {
        return false;
    }

    $data  = get_transient(md5($mofile));
    $mtime = filemtime($mofile);

    $mo = new MO;

    if (!$data || !isset($data['mtime']) || $mtime > $data['mtime']) {
        if (!$mo->import_from_file($mofile)) {
            return false;
        }

        $data = [
            'mtime'   => $mtime,
            'entries' => $mo->entries,
            'headers' => $mo->headers,
        ];

        set_transient(md5($mofile), $data);
    } else {
        $mo->entries = $data['entries'];
        $mo->headers = $data['headers'];
    }

    if (isset($l10n[$domain])) {
        $mo->merge_with($l10n[$domain]);
    }

    $l10n[$domain] =& $mo;

    return true;
}, 1, 3);
