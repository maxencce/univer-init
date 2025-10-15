<?php

namespace App\EventListener;

use App\Entity\Site;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class SiteCodeGesecListener
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $site = $args->getObject(); // récupérer l'entité
        if (!$site instanceof Site) {
            return; // ignorer si ce n'est pas un Site
        }

        $this->generateCodeGesec($site, $args);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $site = $args->getObject();
        if (!$site instanceof Site) {
            return;
        }

        if ($args->hasChangedField('client') || $args->hasChangedField('codeSite')) {
            $this->generateCodeGesec($site, $args);
        }
    }

    private function generateCodeGesec(Site $site, $args): void
    {
        if (!$site instanceof Site) {
            return;
        }

        if (!$site->getClient() || !$site->getCodeSite()) {
            return;
        }

        $clientNom = strtoupper(substr($site->getClient()->getNom(), 0, 3));

        $site->setCodeGesec($clientNom . 'S' . $site->getCodeSite());

        // Si preUpdate, recompute change set
        if ($args instanceof PreUpdateEventArgs) {
            $em = $args->getObjectManager();
            if ($em instanceof EntityManagerInterface) {
                $uow = $em->getUnitOfWork();
                $meta = $em->getClassMetadata(Site::class);
                $uow->recomputeSingleEntityChangeSet($meta, $site);
            }
        }
    }
}
