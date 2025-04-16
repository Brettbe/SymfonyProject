<?php

namespace App\EventListener;

use App\Entity\Offer;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OfferFileDeleteListener
{
    private string $cvUploadDir;

    public function __construct(ParameterBagInterface $params)
    {
        $this->cvUploadDir = $params->get('kernel.project_dir') . '/public/uploads/cv';
    }

    public function preRemove(Offer $offer, PreRemoveEventArgs $event): void
    {
        $cvPath = $offer->getFilename();

        if($cvPath) {
          $filePath = $this->cvUploadDir . '/' . $cvPath;

          $fs = new Filesystem();
          if ($fs->exists($filePath)) {
              $fs->remove($filePath);
          }
        }
    }
}