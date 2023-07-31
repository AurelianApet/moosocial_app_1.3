<?php
Configure::write('Exception.renderer', 'Api.AppExceptionRenderer');
App::uses('AppExceptionRenderer', 'Api.Lib/Error');
App::uses('ApiBadRequestException', 'Api.Lib/Error/Exceptions');
App::uses('ApiNotFoundException', 'Api.Lib/Error/Exceptions');
App::uses('ApiUnauthorizedException', 'Api.Lib/Error/Exceptions');
App::uses('ApiListener','Api.Lib');
CakeEventManager::instance()->attach(new ApiListener());