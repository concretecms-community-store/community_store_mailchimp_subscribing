<?php
namespace Concrete\Package\CommunityStoreMailchimpSubscribing\Src\Event;

use Concrete\Core\Support\Facade\Application;

class Order
{
    private $apiKey;

    public function orderPaymentComplete($event)
    {
        $order = $event->getOrder();
        if ($order) {
            $this->getOrderInfo($order);
        }
    }

    private function getOrderInfo($order)
    {
        $app = Application::getFacadeApplication();
        $config = $app->make('config');
        $this->apiKey = $config->get('mailchimp_subscribing.apiKey');
        $defaultListID = $config->get('mailchimp_subscribing.defaultListID');

        $customerInfo = array();
        $customerInfo['email'] = $order->getAttribute('email');
        $customerInfo['firstName'] = $order->getAttribute('billing_first_name');
        $customerInfo['lastName'] = $order->getAttribute('billing_last_name');

        $productListIDs = array();

        if ($defaultListID) {
            $productListIDs[] = $defaultListID;
        }

        $items = $order->getOrderItems();
        if ($items) {
            foreach ($items as $item) {
                $mailchimpListID = $item->getProductObject()->getAttribute('mailchimp_list_id');
                if ($mailchimpListID) {
                    $productListIDs[] = trim($mailchimpListID);
                }
            }
        }

        if (!empty($productListIDs)) {
            $this->buildRequest($customerInfo, array_unique($productListIDs));
        }
    }

    private function getDataCenter($apiKey)
    {
        $dataCenter = substr($apiKey, strpos($apiKey, '-') + 1);

        return $dataCenter;
    }

    private function buildRequest($customerInfo, $productListIDs)
    {
        $url = '';
        $data = array();

        $dataCenter = $this->getDataCenter($this->apiKey);

        if (count($productListIDs) > 1) {
            // batch
            $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/batches';

            $data = array(
                'operations' => array()
            );

            $i = 0;
            foreach ($productListIDs as $productListID) {
                $data['operations'][$i] = array(
                    'method' => 'POST',
                    'path' => 'lists/' . $productListID . '/members',
                    'body' => "{\"email_address\":\"{$customerInfo['email']}\",\"status\":\"subscribed\",\"merge_fields\":{\"FNAME\":\"{$customerInfo['firstName']}\", \"LNAME\":\"{$customerInfo['lastName']}\"}}"
                );
                $i++;
            }
        } else {
            // single
            $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $productListIDs[0] . '/members';
            $data = array(
                'email_address' => $customerInfo['email'],
                'status' => 'subscribed',
                'merge_fields' => array(
                    'FNAME' => $customerInfo['firstName'],
                    'LNAME' => $customerInfo['lastName']
                )
            );
        }

        $this->sendRequest($url, $data);
    }

    private function sendRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, 'mailchimp_subscribing:' . $this->apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
    }
}
