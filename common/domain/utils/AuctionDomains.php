<?php

namespace common\domain\utils;

class AuctionDomains
{

    public static function generateId(string $secret, int $auctionId): string
    {
        return str_rot13(strrev(strrev(md5($auctionId . $secret . $auctionId)).strrev(base64_encode(bin2hex($auctionId)))));
    }
}
