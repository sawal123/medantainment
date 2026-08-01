<?php

/**
 * Konfigurasi HTMLPurifier (mews/purifier) untuk sanitasi konten blog.
 *
 * Hanya tag dan atribut berikut yang diizinkan. Semua tag berbahaya
 * (script, object, embed, form, iframe bebas, event handler on*,
 * URL javascript:, data:) otomatis ditolak oleh HTMLPurifier.
 *
 * @link http://htmlpurifier.org/live/configdoc/plain.html
 */
return [
    'encoding'         => 'UTF-8',
    'finalize'         => true,
    'ignoreNonStrings' => false,
    'cachePath'        => storage_path('app/purifier'),
    'cacheFileMode'    => 0755,

    'settings' => [
        // Profil untuk konten blog — allowlist ketat
        'blog' => [
            'HTML.Doctype'  => 'HTML 4.01 Transitional',

            // Allowlist: hanya tag yang aman untuk konten artikel
            // Tidak ada <script>, <object>, <embed>, <form>, <iframe> bebas
            // Tidak ada event handler (on*), tidak ada SVG inline
            'HTML.Allowed' => implode(',', [
                'p',
                'br',
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'ul', 'ol', 'li',
                'strong', 'b', 'em', 'i',
                'blockquote',
                'pre', 'code',
                // Link: hanya protokol HTTP/HTTPS yang diizinkan
                'a[href|title|target]',
                // Gambar: atribut minimal
                'img[src|alt|width|height]',
            ]),

            // CSS yang diizinkan (sangat terbatas)
            'CSS.AllowedProperties' => '',

            // Tolak URL javascript: dan data: secara eksplisit
            'URI.AllowedSchemes' => [
                'http'  => true,
                'https' => true,
                'mailto' => true,
            ],

            // Hapus atribut kosong
            'AutoFormat.RemoveEmpty' => true,

            // Jangan buat paragraf otomatis — biarkan editor yang menentukan
            'AutoFormat.AutoParagraph' => false,

            // Tolak attribute target yang berbahaya — hanya _blank dan _self
            'Attr.AllowedFrameTargets' => ['_blank', '_self'],
        ],

        // Profil default (fallback)
        'default' => [
            'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'p,br,b,strong,i,em,ul,ol,li,a[href|title],blockquote',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => true,
        ],
    ],
];
