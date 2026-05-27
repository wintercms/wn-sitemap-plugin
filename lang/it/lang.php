<?php

return [
    'plugin' => [
        'name' => 'Sitemap',
        'description' => 'Genera un file sitemap.xml per il tuo sito web.',
        'permissions' => [
            'access_settings' => 'Accedi alle impostazioni di configurazione del sitemap',
            'access_definitions' => 'Accedi alla pagina delle definizioni del sitemap'
        ]
    ],
    'item' => [
        'location' => 'Posizione:',
        'priority' => 'Priorità',
        'changefreq' => 'Frequenza di modifica',
        'omitted' => 'Omessa',
        'always' => 'sempre',
        'hourly' => 'oraria',
        'daily' => 'giornaliera',
        'weekly' => 'settimanale',
        'monthly' => 'mensile',
        'yearly' => 'annuale',
        'never' => 'mai',
        'editor_title' => 'Modifica Elemento Sitemap',
        'type' => 'Tipo',
        'allow_nested_items' => 'Consenti elementi nidificati',
        'allow_nested_items_comment' => 'Gli elementi nidificati possono essere generati dinamicamente da pagine statiche e alcuni altri tipi di elemento',
        'url' => 'URL',
        'reference' => 'Riferimento',
        'unknown_type' => 'Tipo di elemento sconosciuto',
        'unnamed' => 'Elemento senza nome',
        'add_item' => 'Aggiungi Elemento',
        'cms_page' => 'Pagina CMS',
        'cms_page_comment' => 'Seleziona la pagina da utilizzare per l\'indirizzo URL.',
        'reference_required' => 'Il riferimento dell\'elemento è obbligatorio.',
        'url_required' => 'L\'URL è obbligatorio',
        'cms_page_required' => 'Seleziona una pagina CMS',
        'page' => 'Pagina',
        'check' => 'Controlla',
        'definition' => 'Definizione'
    ],
    'definition' => [
        'not_found' => 'Nessuna definizione di sitemap trovata. Prova a crearne una prima.'
    ]
];
