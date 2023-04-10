<?php
declare(strict_types=1);
namespace SimiCart\SimpifyManagement\Model\Clients;

interface ShopifyClientInterface
{
    /**
     * Header for access token (send).
     *
     * @var string
     */
    public const HEADER_ACCESS_TOKEN = 'x-shopify-access-token';
}
