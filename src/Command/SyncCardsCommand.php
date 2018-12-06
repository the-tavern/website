<?php

namespace App\Command;

use App\Service\CardSync;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncCardsCommand extends Command
{
    protected static $defaultName = 'app:sync:cards';

    protected $cardSync;

    public function __construct(CardSync $cardSync)
    {
        $this->cardSync = $cardSync;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription(
            'Sync cards from github'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cardSync->sync();
    }
}
