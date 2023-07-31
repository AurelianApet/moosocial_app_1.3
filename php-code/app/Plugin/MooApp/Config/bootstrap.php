<?php
App::uses('MooAppListener','MooApp.Lib');
CakeEventManager::instance()->attach(new MooAppListener());