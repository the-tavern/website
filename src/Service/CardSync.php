<?php

namespace App\Service;

use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class CardSync
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    protected $cacheDir;

    protected $downloadZip;

    protected $importPath;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        $this->cacheDir = realpath(__DIR__. '/../../var/cache');
        $this->downloadZip = "{$this->cacheDir}/hearthstone-card-images.zip";
        $this->importPath = "{$this->cacheDir}/card-import";
    }

    public function sync()
    {
        $this->downloadCardImages();
        $this->extractImages();
        $this->import();
    }

    private function downloadCardImages()
    {
        $downloadRequest = (new GuzzleClient())->request(
            'GET',
            'https://api.github.com/repos/schmich/hearthstone-card-images/releases/latest'
        );

        $json = json_decode((string) $downloadRequest->getBody(), true);
        $url = $json['zipball_url'];

        (new GuzzleClient())->request('GET', $url, ['sink' => fopen($this->downloadZip, 'w')]);

        return true;
    }

    private function extractImages()
    {
        (new Filesystem())->remove($this->importPath);
        (new Filesystem())->mkdir($this->importPath, 0700);

        $zip = new ZipArchive;
        $zip->open($this->downloadZip);
        $zip->extractTo($this->importPath);
        $zip->close();

        return true;
    }

    private function import()
    {
        $cardImages = (new Finder())
            ->files()
            ->in("{$this->importPath}/*/rel/")
            ->name('*.png')
            ->sortByName(true)
        ;

        $repo = $this->em->getRepository(Card::class);

        foreach ($cardImages as $cardImage) {
            $card = $repo->findOneBy(['name' => $cardImage->getFileName()]);
            if (null === $card) {
                $card = new Card($cardImage);
            }

            $card->updateImageData($cardImage);
            $this->em->persist($card);
        }

        $this->em->flush();

        return true;
    }
}