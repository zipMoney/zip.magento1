<?php

class Zip_Payment_Model_Adminhtml_Notification_Feed extends Mage_AdminNotification_Model_Feed
{
    const XML_ENABLED_PATH = 'payment/zip_payment/admin_notification/enabled';
    const XML_FEED_URL_PATH = 'payment/zip_payment/admin_notification/feed_url';

    const RELEASE_FIELD = 'release';
    const VERSION_FIELD = 'version';
    const NOTIFICATION_FIELD = 'notifications';

    const DEFAULT_NOTIFICATION_TITLE = 'Zip Payment';

    protected $feedData = null;

    /**
     * Check feed for modification
     */
    public function checkUpdate()
    {
        if(Mage::getStoreConfigFlag(self::XML_ENABLED_PATH)) {

            if (($this->getFrequency() + $this->getLastUpdate()) > time()) {
                return $this;
            }
    
            $data = array();
            $this->feedData = $this->getFeedData();
    
            if(!empty($this->feedData) && isset($this->feedData[self::NOTIFICATION_FIELD])) {
    
                foreach($this->feedData[self::NOTIFICATION_FIELD] as $item) {
    
                    $data[] = array(
                        'severity'      => Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE,
                        'date_added'    => isset($item['date']) ? gmdate('Y-m-d H:i:s', strtotime($item['date'])) : date('Y-m-d H:i:s'),
                        'title'         => isset($item['title']) ? $item['title'] : self::DEFAULT_NOTIFICATION_TITLE,
                        'description'   => isset($item['description']) ? $item['description'] : '',
                        'url'           => isset($item['url']) ? $item['url'] : ''
                    );
                }

                $versionUpgradeNotification = $this->getVersionUpgradeNotification();
                if(!empty($versionUpgradeNotification)) {
                    $data[] = $versionUpgradeNotification;
                }

                if (!empty($data)) {
                    Mage::getModel('adminnotification/inbox')->parse(array_reverse($data));
                }
            }
    
            $this->setLastUpdate();
            
        }

        return $this;
    }

    public function getVersionUpgradeNotification() {

        $currentVersion = Mage::helper("zip_payment")->getCurrentVersion();

        if($this->feedData == null) {
            $this->feedData = $this->getFeedData();
        }

        if(!empty($currentVersion) && isset($this->feedData[self::RELEASE_FIELD]) && isset($this->feedData[self::RELEASE_FIELD][self::VERSION_FIELD])) {

            $item = $this->feedData[self::RELEASE_FIELD];
            $latestVersion = trim($item[self::VERSION_FIELD]);

            if(!empty($latestVersion) && $currentVersion < $latestVersion) {

                return array(
                    'severity'      => Mage_AdminNotification_Model_Inbox::SEVERITY_MAJOR,
                    'date_added'    => isset($item['date']) ? gmdate('Y-m-d H:i:s', strtotime($item['date'])) : date('Y-m-d H:i:s'),
                    'title'         => isset($item['title']) ? $item['title'] : 'New Zip Payment version ' . $latestVersion . ' is available now!',
                    'description'   => isset($item['description']) ? $item['description'] : '',
                    'url'           => isset($item['url']) ? $item['url'] : ''
                );
            }
        }

        return null;
    }

    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = Mage::getStoreConfig(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    public function getFeedData() {

        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig(array(
            'timeout' => 1
        ));

        $curl->write(Zend_Http_Client::GET, $this->getFeedUrl(), '1.0');
        $data = $curl->read();

        if ($data === false) {
            return false;
        }

        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();

        try {
            $json = json_decode($data, true);
        }
        catch (Exception $e) {
            return false;
        }

        return $json;
    }


}