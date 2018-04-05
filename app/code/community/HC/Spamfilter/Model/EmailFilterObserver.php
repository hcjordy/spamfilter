<?php
/**
 * Created by PhpStorm.
 * User: martijn
 * Date: 31-01-18
 * Time: 09:21
 */
class HC_Spamfilter_Model_EmailFilterObserver {

    const DOMAIN_BLOCK_SETTING_PATH = 'hc_general/spamfilter/blocked_domains';
    const TEXT_BLOCK_SETTING_PATH = 'hc_general/spamfilter/blocked_text';

    public function checkNewsletterSubscription(Varien_Event_Observer $observer)
    {
        $subscriber = $observer->getControllerAction();

        if (!$subscriber instanceof Mage_Newsletter_SubscriberController) {
            return;
        }
        $request = $subscriber->getRequest();
        if (is_null($request->getPost('email'))) {
            return false;
        }

        if (!$this->checkEmail($request->getPost('email')) || !$this->checkText($request->getPost('email'))) {
            $this->session()->addError($this->helper()->__('You\'re not allowed to subscribe'));
            $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer()  : Mage::getUrl();

            $subscriber->getResponse()->setRedirect($url);
            $subscriber->getResponse()->sendResponse();
            exit(1);
        }

    }

    public function checkContactsForm(Varien_Event_Observer $observer)
    {
        $contact = $observer->getControllerAction();
        $request = $contact->getRequest();
        if (is_null($request->getPost('email'))) {
            return false;
        }

        if (!$this->checkEmail($request->getPost('email')) || !$this->checkText($request->getPost('email'), $request->getPost('name'), $request->getPost('comment'), $request->getPost('telephone'))) {
            $this->session()->addError($this->helper()->__('You\'re not allowed to contact us'));
            $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer()  : Mage::getUrl();

            $contact->getResponse()->setRedirect($url);
            $contact->getResponse()->sendResponse();
            exit(1);
        }
    }


    public function checkCreateAccount(Varien_Event_Observer $observer)
    {
        $account = $observer->getControllerAction();

        if (!$account instanceof Mage_Customer_AccountController) {
            return;
        }
        $request = $account->getRequest();
        if (is_null($request->getPost('email'))) {
            return false;
        }
        if (!$this->checkEmail($request->getPost('email')) || !$this->checkText($request->getPost('email'), $request->getPost('firstname'), $request->getPost('lastname'))) {
            $request->setPost('email', '');
            $session = Mage::getSingleton('customer/session');
            $session->addError($this->helper()->__('You\'re not allowed to create an account'));
            $url = Mage::helper('core/http')->getHttpReferer() ? Mage::helper('core/http')->getHttpReferer()  : Mage::getUrl();

            $account->getResponse()->setRedirect($url);
            $account->getResponse()->sendResponse();
            exit(1);
        }

    }

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function checkEmail($email)
    {
        $blockedDomainSetting = unserialize(Mage::getStoreConfig(self::DOMAIN_BLOCK_SETTING_PATH));
        foreach($blockedDomainSetting as $forbidden) {
            if (strpos($email, $forbidden['domain']) !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string ...$text
     *
     * @return bool
     */
    protected function checkText(...$text)
    {
        $blockedText = explode(",", strtolower(Mage::getStoreConfig(self::TEXT_BLOCK_SETTING_PATH)));
        foreach($text as $item) {
            if (empty($item)) {
                continue;
            }
            foreach ($blockedText as $block) {

                if (empty($block)) {
                    continue;
                }

                if (stripos($item, trim($block)) !== false) {
                    return false;
                }
            }
        }
        return false;
    }



    /**
     * @return Mage_Customer_Model_Session
     */
    private function session()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * @return HC_Spamfilter_Helper_Data
     */
    private function helper()
    {
        return Mage::helper('spamfilter');
    }
}