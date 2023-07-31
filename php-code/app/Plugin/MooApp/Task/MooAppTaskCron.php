<?php
/*
 * Copyright (c) SocialLOFT LLC
 * mooSocial - The Web 2.0 Social Network Software
 * @website: http://www.moosocial.com
 * @author: mooSocial
 * @license: https://moosocial.com/license/
 */
App::import('Cron.Task','CronTaskAbstract');
class MooAppTaskCron extends CronTaskAbstract
{
    public function execute()
    {
    	$model = MooCore::getInstance()->getModel("MooApp.MooAppNotification");
    	$model->cron();
    }
}