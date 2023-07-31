<?php 
App::uses('MooAppAppModel', 'MooApp.Model');
App::uses('MooAppListener','MooApp.Lib');
class MooAppNotification extends MooAppAppModel{
    public function cron()
    {
    	$rows = $this->find('all',array('limit'=>10,'order'=>'id'));
    	if (count($rows))
    	{
    		$notificationModel = MooCore::getInstance()->getModel('Notification');
    		$listener = new MooAppListener();
    		foreach ($rows as $row)
    		{
    			$notification = $notificationModel->findById($row['MooAppNotification']['notification_id']);
    			if ($notification)
    			{
    				$listener->sendNotificationsToAndroid($notification);
    				$listener->sendNotificationsToIOS($notification);
    			}
    			
    			$this->delete($row['MooAppNotification']['id']);
    		}
    	}
    }
}