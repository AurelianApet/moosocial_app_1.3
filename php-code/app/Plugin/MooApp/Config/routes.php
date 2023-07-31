<?php
Router::connect('/moo_apps/:action/*', array(
    'plugin' => 'MooApp',
    'controller' => 'moo_apps'
));

Router::connect('/moo_apps/*', array(
    'plugin' => 'MooApp',
    'controller' => 'moo_apps',
    'action' => 'index'
));
