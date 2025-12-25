<?php

namespace App\TikTokShop;

use EcomPHP\TiktokShop\Client;
use Exception;

class TikTokShop
{
    protected $client;
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
        $this->client = new Client($config['app_key'], $config['app_secret']);
        $this->client->setShopCipher($config['shop_cipher']);
        $this->client->useVersion($config['api_version']);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getAuthUrl($state)
    {
        $auth = $this->client->auth();
        return $auth->createAuthRequest($state, true);
    }

    public function getAccessToken($code)
    {
        try {
            $auth = $this->client->auth();
            return $auth->getToken($code);
        } catch (Exception $e) {
            throw new Exception("Error getting access token: " . $e->getMessage());
        }
    }

    public function refreshToken($refreshToken)
    {
        try {
            $auth = $this->client->auth();
            return $auth->refreshNewToken($refreshToken);
        } catch (Exception $e) {
            throw new Exception("Error refreshing token: " . $e->getMessage());
        }
    }

    public function getOrderList($params = [])
    {
        try {
            $defaultParams = [
                'page_size' => 50,
                'order_status' => 'AWAITING_SHIPMENT' // default status
            ];

            $params = array_merge($defaultParams, $params);
            return $this->client->Order->getOrderList($params);
        } catch (Exception $e) {
            throw new Exception("Error fetching orders: " . $e->getMessage());
        }
    }

    public function getAllOrders($statuses = [])
    {
        $allOrders = [];
        
        if (empty($statuses)) {
            $statuses = ['AWAITING_COLLECTION', 'AWAITING_SHIPMENT', 'IN_TRANSIT', 'CANCELLED'];
        }

        foreach ($statuses as $status) {
            try {
                $orders = $this->getOrderList([
                    'order_status' => $status
                ]);
                
                if (!empty($orders['orders'])) {
                    $allOrders[$status] = $orders['orders'];
                }
                
                usleep(200000); // 200ms delay
                
            } catch (Exception $e) {
                error_log("Error fetching orders for status {$status}: " . $e->getMessage());
                continue;
            }
        }

        return $allOrders;
    }
}