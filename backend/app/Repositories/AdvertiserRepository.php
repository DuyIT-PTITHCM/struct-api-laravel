<?php

namespace App\Repositories;

use App\Models\Advertiser;

class AdvertiserRepository extends BaseRepository
{
    public function __construct(Advertiser $advertiser)
    {
        parent::__construct($advertiser);
    }

}