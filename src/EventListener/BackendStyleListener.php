<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\HttpFoundation\Request;

final readonly class BackendStyleListener
{
    public function __construct(private ContaoFramework $framework, private ScopeMatcher $scopeMatcher)
    {
    }

    public function __invoke(Request $request): void
    {
        if (! $this->scopeMatcher->isBackendRequest($request)) {
            return;
        }

        $this->framework->initialize();

        $GLOBALS['TL_CSS'][] = 'bundles/friendsocontaoassociategroups/css/backend.css';
    }
}
