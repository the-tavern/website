<?php

namespace App\Service;

use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
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

    protected $output;

    public function __construct(EntityManagerInterface $em, LoggerInterface $log)
    {
        $this->em = $em;
        $this->log = $log;

        $this->cacheDir = realpath(__DIR__. '/../../var/cache');
        $this->downloadZip = "{$this->cacheDir}/hearthstone-card-images.zip";
        $this->importPath = "{$this->cacheDir}/card-import";
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    public function sync()
    {
        $this->log->info('Downloading cards from github.com/schmich/hearthstone-card-images');
        $this->downloadCardImages();

        $this->log->info('Extracting images');
        $this->extractImages();

        $this->log->info('Importing images');
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

        $progressBar = new ProgressBar($this->output, count($cardImages));
        $progressBar->setFormat('%current%/%max% %bar% (%memory%) %message%');

        foreach ($cardImages as $cardImage) {
            $name = $cardImage->getFileName();
            $progressBar->setMessage($name);

            $card = $repo->findOneBy(['name' => $name]);
            if (null === $card) {
                $card = new Card($cardImage);
            }

            $card->updateImageData($cardImage);
            $this->em->persist($card);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->log->info('');
        $this->log->info('Flushing database changes ...');
        $this->em->flush();

        return true;
    }
}
