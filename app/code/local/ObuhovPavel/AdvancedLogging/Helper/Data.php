<?php
class ObuhovPavel_AdvancedLogging_Helper_Data extends Mage_Core_Helper_Abstract
{
    const ADVANCE_LOGGING_NOT_SENDED = 0;
    const ADVANCE_LOGGING_REALTIME = 1;
    const ADVANCE_LOGGING_CRON = 2;

    const XML_PATH_EMAIL_LOG_CLEAN_TEMPLATE     = 'dev/log/cron_start/error_email_template';
    const XML_PATH_EMAIL_LOG_CLEAN_IDENTITY     = 'dev/log/error_email_identity';
    const XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT    = 'dev/log/emails';

    private $logFile = 'advancelog.log';

    public function isEnabled()
    {
        return Mage::getConfig()->getModuleConfig('ObuhovPavel_AdvancedLogging')->is('active', 'true');
    }

    public function syncronizeAdvanceLogging($event)
    {
        if($this->isEnabled()) {
            $message = $event['timestamp'] . ' ' .  $event['priorityName'] . '(' . $event['priority'] . '):' . $event['message'];
            
            switch($this->getLogsStatus()) {
                case self::ADVANCE_LOGGING_NOT_SENDED:
                break;
                case self::ADVANCE_LOGGING_REALTIME:
                    $this->sendRealtimeLog($message);
                break;
                case self::ADVANCE_LOGGING_CRON:
                    $this->sendLogOnceOfDay($message);
                break;
                default:
                break;
            }
        }
    } 

    public function sendRealtimeLog($message = '')
    {
        $this->sendLogEmail($message);
    }

    public function sendLogOnceOfDay($message)
    {
        $this->saveLog($message);         
    }

    private function saveLog($message)
    {
        $logDir  = Mage::getBaseDir('var') . DS . 'log' . DS . 'advancelog';
        $logFile = $logDir . DS . $this->logFile;

        if (!is_dir($logDir)) {
            mkdir($logDir);
            chmod($logDir, 0750);
        }

        if (!file_exists($logFile)) {
            $file = fopen($logFile, "w");
        } else {
            $file = fopen($logFile, "a+");
        }

        fwrite($file, $message . "\n");
        fclose($file);
    }
    
    private function getFilePath()
    {
        $logDir  = Mage::getBaseDir('var') . DS . 'log' . DS . 'advancelog';
        $logFile = $logDir . DS . $this->logFile;

        return $logFile;
    }

    public function isExceptionSend()
    {
        return Mage::getStoreConfig('dev/log/send_exceptionlog');
    }

    public function getLogSendAt()
    {
        return Mage::getStoreConfig('dev/log/cron_start');
    }

    public function getLogsStatus()
    {
        return Mage::getStoreConfig('dev/log/log_settings');
    }

    public function sendLogEmail($message = false)
    {
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $emailTemplate = Mage::getModel('core/email_template');
        
        if($message === false) {
            $logFile = $this->getFilePath();
            
            if (file_exists($logFile)) {
                $file = fopen($logFile, "r");
                $message = fread($file, filesize($logFile));
                fclose($file);

                $this->removeOldLog();
            }
        }

        $emailTemplate->setDesignConfig(array('area' => 'backend'))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_LOG_CLEAN_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_LOG_CLEAN_IDENTITY),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT),
                null,
                array('warnings' => join("\n", $message))
            );
        $translate->setTranslateInline(true);
    }

    private function removeOldLog()
    {
        $logDir  = Mage::getBaseDir('var') . DS . 'log' . DS . 'advancelog';
        $logFile = $logDir . DS . $this->logFile;
        unlink($logFile);
    }
}
