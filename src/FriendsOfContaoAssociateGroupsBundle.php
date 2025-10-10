<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups;

use Override;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use function dirname;

final class FriendsOfContaoAssociateGroupsBundle extends Bundle
{
    #[Override]
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
