<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\KernelEvent;

#[AsEventListener('kernel.request')]
final readonly class BackendStyleListener
{
    public function __construct(private ContaoFramework $framework, private ScopeMatcher $scopeMatcher)
    {
    }

    public function __invoke(KernelEvent $event): void
    {
        if (! $this->scopeMatcher->isBackendRequest($event->getRequest())) {
            return;
        }

        $this->framework->initialize();

        $GLOBALS['TL_CSS'][] = 'bundles/friendsofcontaoassociategroups/backend.css';
    }
}
