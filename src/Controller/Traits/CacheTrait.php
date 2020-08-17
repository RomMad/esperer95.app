<?php

namespace App\Controller\Traits;

use App\Entity\SupportGroup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

trait CacheTrait
{
    public function discachedSupport(SupportGroup $supportGroup)
    {
        $cache = new FilesystemAdapter();

        $cacheSupport = $cache->getItem('support_group_full.'.$supportGroup->getId());
        $cache->deleteItem($cacheSupport->getKey());
    }
}
