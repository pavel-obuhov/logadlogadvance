<?php
class ObuhovPavel_AdvancedLogging_Model_Observer
{
	public function sendLogs()
	{
		$status = Mage::helper('obuhovpavel_advancedlogging')->getLogsStatus();
		if($status == ObuhovPavel_AdvancedLogging_Helper_Data::ADVANCE_LOGGING_CRON) {
			if($this->isRighTimeToSend()) {
				Mage::helper('obuhovpavel_advancedlogging')->sendLogEmail(false);
			}
		}
	}

	private function isRighTimeToSend()
	{
		$shift = 120; //sec
		$logSendAt = Mage::helper('obuhovpavel_advancedlogging')->getLogSendAt();
		
		$explodedLogTime = explode(',', $logSendAt);        
        $timestampLogSend = ($explodedLogTime[0] * 3600) + ($explodedLogTime[1] * 60) + $explodedLogTime[2]; 
        $currentDate = strtotime(date("Y-m-d"));

        $logSendTime = $currentDate + $timestampLogSend;

        $currentTime = time();
        
        if($logSendTime > $currentTime && $logSendTime < $currentTime + $shift) {
        	return true;
        } else {
        	return false;
        }
	}
}