<?php

return [
    'plugin' => [
        'name' => 'Oldaltérkép',
        'description' => 'A weboldalhoz sitemap.xml fájl generálása.',
        'permissions' => [
            'access_settings' => 'Oldaltérkép beállításainak kezelése',
            'access_definitions' => 'Oldaltérkép elemeinek kezelése',
        ],
    ],
    'item' => [
        'location' => 'Cím:',
        'priority' => 'Prioritás',
        'changefreq' => 'Gyakoriság',
        'always' => 'mindig',
        'hourly' => 'óránkénti',
        'daily' => 'napi',
        'weekly' => 'heti',
        'monthly' => 'havi',
        'yearly' => 'évi',
        'never' => 'soha',
        'editor_title' => 'Módosítás',
        'type' => 'Típus',
        'allow_nested_items' => 'Allapok megjelenítése',
        'allow_nested_items_comment' => 'A kapcsolódó lapok automatikus listázása, amennyiben támogatott ez a funkció.',
        'url' => 'Webcím',
        'reference' => 'Hivatkozás',
        'unknown_type' => 'Ismeretlen elem típus',
        'unnamed' => 'Névtelen elem',
        'add_item' => 'Hozzáadás',
        'cms_page' => 'Oldal',
        'cms_page_comment' => 'Válassza ki, hogy melyik oldal tartozik a fenti hivatkozáshoz.',
        'reference_required' => 'A hivatkozó elem megadása kötelező.',
        'url_required' => 'A webcím megadása kötelező.',
        'cms_page_required' => 'Válasszon egy oldalt.',
        'page' => 'Oldal',
        'check' => 'Ellenőrzés',
        'definition' => 'Elemek',
    ],
    'definition' => [
        'not_found' => 'Nincs elkészítve az oldaltérkép. Adjon hozzá legalább egy elemet.'
    ]
];
