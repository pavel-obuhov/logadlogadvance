<?php
class ObuhovPavel_AdvancedLogging_Model_Source_LoggingOptions
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('adminhtml')->__('Not sending')),
            array('value'=>1, 'label'=>Mage::helper('adminhtml')->__('Realtime sending')),
            array('value'=>2, 'label'=>Mage::helper('adminhtml')->__('Send unsing cron')),       
        );
    }
}
