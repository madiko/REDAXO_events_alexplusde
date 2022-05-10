<?php

if (rex::isBackend() && rex_be_controller::getCurrentPage() == 'events/calendar') {
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/core/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/core/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/daygrid/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/daygrid/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/bootstrap/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/bootstrap/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/timegrid/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/timegrid/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/list/main.js'));
    rex_view::addCssFile($this->getAssetsUrl('fullcalendar/packages/list/main.css'));
    rex_view::addJsFile($this->getAssetsUrl('fullcalendar/packages/core/locales/de.js'));
    rex_view::addJsFile($this->getAssetsUrl('backend.js'));
}

rex_yform_manager_dataset::setModelClass(
    'rex_event_date',
    event_date::class
);
rex_yform_manager_dataset::setModelClass(
    'rex_event_location',
    event_location::class
);
rex_yform_manager_dataset::setModelClass(
    'rex_event_category',
    event_category::class
);
rex_yform_manager_dataset::setModelClass(
    'rex_event_date_offer',
    event_date_offer::class
);

if (rex_addon::get('cronjob')->isAvailable() && !rex::isSafeMode()) {
    rex_cronjob_manager::registerType('rex_cronjob_events_ics_import');
}

if (rex_plugin::get('yform', 'rest')->isAvailable() && !rex::isSafeMode()) {

/* YForm Rest API */
    $rex_event_date_route = new \rex_yform_rest_route(
        [
        'path' => '/v2.0/event/date/',
        'auth' => '\rex_yform_rest_auth_token::checkToken',
        'type' => \event_date::class,
        'query' => \event_date::query(),
        'get' => [
            'fields' => [
                'rex_event_date' => [
                    'id',
                    'name',
                    'description',
                    'location',
                    'image',
                    'startDate',
                    'doorTime',
                    'endDate',
                    'eventStatus',
                    'url'
                 ],
                 'rex_event_category' => [
                    'id',
                    'name',
                    'image'
                 ],
                 'rex_event_location' => [
                    'id',
                    'name',
                    'street',
                    'zip',
                    'locality',
                    'lat',
                    'lng'
                 ]
            ]
        ],
        'post' => [
            'fields' => [
                'rex_event_date' => [
                    'name',
                    'description',
                    'location',
                    'image',
                    'startDate',
                    'doorTime',
                    'endDate',
                    'eventStatus',
                ]
            ]
        ],
        'delete' => [
            'fields' => [
                'rex_event_date' => [
                    'id'
                ]
            ]
        ]
    ]
    );

    \rex_yform_rest::addRoute($rex_event_date_route);


    /* YForm Rest API */
    $rex_event_category_route = new \rex_yform_rest_route(
        [
        'path' => '/v2.0/event/category/',
        'auth' => '\rex_yform_rest_auth_token::checkToken',
        'type' => \event_category::class,
        'query' => \event_category::query(),
        'get' => [
            'fields' => [
                 'rex_event_category' => [
                    'id',
                    'name',
                    'image'
                 ]
            ]
        ],
        'post' => [
            'fields' => [
                'rex_event_category' => [
                    'name',
                    'image'
                ]
            ]
        ],
        'delete' => [
            'fields' => [
                'rex_event_category' => [
                    'id'
                ]
            ]
        ]
    ]
    );

    \rex_yform_rest::addRoute($rex_event_category_route);

    /* YForm Rest API */
    $rex_event_location_route = new \rex_yform_rest_route(
        [
        'path' => '/v2.0/event/location/',
        'auth' => '\rex_yform_rest_auth_token::checkToken',
        'type' => \event_location::class,
        'query' => \event_location::query(),
        'get' => [
            'fields' => [
                 'rex_event_location' => [
                    'id',
                    'name',
                    'street',
                    'zip',
                    'locality',
                    'lat',
                    'lng'
                 ]
            ]
        ],
        'post' => [
            'fields' => [
                'rex_event_location' => [
                    'name',
                    'name',
                    'street',
                    'zip',
                    'locality',
                    'lat',
                    'lng'
                ]
            ]
        ],
        'delete' => [
            'fields' => [
                'rex_event_location' => [
                    'id'
                ]
            ]
        ]
    ]
    );

    \rex_yform_rest::addRoute($rex_event_location_route);
}

rex_extension::register('REX_YFORM_SAVED', function (rex_extension_point $ep) {

    // darf nur bei passender Tabelle passieren.
//    $id = $ep->getParam('id');
//    $dataset = event_date::get($ep->getParam('id'));
//    rex_sql::factory()->setQuery("UPDATE rex_event_date SET uid = :uid WHERE id = :id", [":uid"=>$dataset->getUid(), ":id" => $id]);
});

rex_extension::register('YFORM_DATA_LIST', function ($ep) {
    if ($ep->getParam('table')->getTableName()=="rex_event_date") {
        $list = $ep->getSubject();

        $list->setColumnFormat(
            'name',
            'custom',
            function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_event_date')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = array();
                $params['table_name'] = 'rex_event_date';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';
    
                return '<a href="'.rex_url::backendPage('events/date', $params) .'">'. $a['value'].'</a>';
            }
        );
        $list->setColumnFormat(
            'event_category_id',
            'custom',
            function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_event_category')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = array();
                $params['table_name'] = 'rex_event_category';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';
    
                $return = [];

                $category_ids = array_filter(explode(",", $a['value']));

                foreach ($category_ids as $category_id) {
                    $event = event_category::get($category_id);
                    if ($event) {
                        $return[] = '<a href="'.rex_url::backendPage('events/category', $params) .'">'. $event->getName().'</a>';
                    }
                }
                return implode("<br>", $return);
            }
        );
        $list->setColumnFormat(
            'location',
            'custom',
            function ($a) {
                $_csrf_key = rex_yform_manager_table::get('rex_event_location')->getCSRFKey();
                $token = rex_csrf_token::factory($_csrf_key)->getUrlParams();

                $params = array();
                $params['table_name'] = 'rex_event_location';
                $params['rex_yform_manager_popup'] = '0';
                $params['_csrf_token'] = $token['_csrf_token'];
                $params['data_id'] = $a['list']->getValue('id');
                $params['func'] = 'edit';

                $location_ids = array_filter(explode(",", $a['value']));

                $return = [];
                
                foreach ($location_ids as $location_id) {
                    $location = event_location::get($location_id);
                    if ($location) {
                        $return[] = '<a href="'.rex_url::backendPage('events/location', $params) .'">'. $location->getValue('name').'</a>';
                    }
                }
                return implode("<br>", $return);
            }
        );
    }
});
