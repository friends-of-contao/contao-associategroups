<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\Controller;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\Message;
use FriendsOfContao\AssociateGroups\AssociateGroups;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function sprintf;

#[Route('contao/{type}/sync-groups', requirements: ['type' => 'member|user'], defaults: ['_scope' => 'backend'])]
final readonly class SyncGroupsController
{
    public function __construct(
        private AssociateGroups $associateGroups,
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
        private TranslatorInterface $translator,
        private ContaoFramework $framework,
    ) {
    }

    public function __invoke(string $type): Response
    {
        if (! $this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, $type)) {
            throw new AccessDeniedHttpException();
        }

        $this->framework->initialize();
        $this->associateGroups->sync($type);

        Message::addConfirmation(
            $this->translator->trans(sprintf('MSC.%sGroupsSynchronized', $type), [], 'contao_default'),
        );

        return new RedirectResponse(
            $this->urlGenerator->generate('contao_backend', ['do' => $type]),
        );
    }
}
