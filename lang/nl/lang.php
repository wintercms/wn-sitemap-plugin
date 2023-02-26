<?php

return [
    'plugin' => [
        'name' => 'Sitemap',
        'description' => 'Genereer een sitemap.xml bestand voor je website.',
        'permissions' => [
            'access_settings' => 'Toegang sitemap configuratie-instellingen',
            'access_definitions' => 'Toegang sitemap-definities pagina',
        ],
    ],
    'item' => [
        'location' => 'Locatie:',
        'priority' => 'Prioriteit',
        'changefreq' => 'Verander frequentie',
        'always' => 'altijd',
        'hourly' => 'uurlijks',
        'daily' => 'dagelijks',
        'weekly' => 'wekelijks',
        'monthly' => 'maandelijks',
        'yearly' => 'jaarlijks',
        'never' => 'nooit',
        'editor_title' => 'Bewerk Sitemap-item',
        'type' => 'Type',
        'allow_nested_items' => 'Geneste items toestaan',
        'allow_nested_items_comment' => 'Geneste items kunnen dynamisch worden gegenereerd door statische pagina\'s en sommige andere item-typen',
        'url' => 'URL',
        'reference' => 'Referentie',
        'title_required' => 'De titel is vereist',
        'unknown_type' => 'Onbekend type item',
        'unnamed' => 'Naamloos item',
        'add_item' => '<u>I</u>tem toevoegen',
        'new_item' => 'Nieuw item',
        'cms_page' => 'CMS Pagina',
        'cms_page_comment' => 'Selecteer de pagina die voor het URL-adres moet worden gebruikt.',
        'reference_required' => 'De item-referentie is vereist.',
        'url_required' => 'De URL is vereist',
        'cms_page_required' => 'Selecteer een CMS-pagina',
        'page' => 'Pagina',
        'check' => 'Controleren',
        'definition' => 'Definitie',
        'save_definition' => 'Definitie opslaan...',
        'load_indicator' => 'Definitie leegmaken...',
        'empty_confirm' => 'Deze definitie leegmaken?'
    ],
    'definition' => [
        'not_found' => 'Geen sitemap-definitie gevonden. Probeer er eerst één te maken.'
    ]
];
