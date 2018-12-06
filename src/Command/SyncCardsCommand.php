<?php

namespace App\Command;


use App\Entity\Card;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ZipArchive;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncCardsCommand extends Command
{
    protected static $defaultName = 'app:sync:cards';

    protected function configure()
    {
        $this->setDescription(
            'Add a short description for your command'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheDir = realpath(__DIR__. '/../../var/cache');
        $downloadZip = "{$cacheDir}/hearthstone-card-images.zip";

        dump($downloadZip);
/*
            $downloadRequest = (new GuzzleClient())->request(
                'GET',
                'https://api.github.com/repos/schmich/hearthstone-card-images/releases/latest'
            );

            $json = json_decode((string) $downloadRequest->getBody(), true);
            $url = $json['zipball_url'];

            $resource = fopen("{$cacheDir}/cache/hearthstone-card-images.zip", 'w');
            (new GuzzleClient())->request('GET', $url, ['sink' => $resource]);
*/

        $importPath = "{$cacheDir}/card-import";
/*
        $fs = new Filesystem();
        $fs->remove($importPath);
        $fs->mkdir($importPath, 0700);

        $zip = new ZipArchive;
        $zip->open($downloadZip);
        $zip->extractTo($importPath);
        $zip->close();
*/
        $cardImages = (new Finder())
            ->files()
            ->in("{$importPath}/*/rel/")
            ->name('*.png')
            ->sortByName(true)
        ;

        foreach ($cardImages as $cardImage) {
            $card = new Card($cardImage);


            dump($card);
            exit();
        }

        exit();
    }
}
