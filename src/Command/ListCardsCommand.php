<?php

namespace App\Command;

use App\Entity\Card;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListCardsCommand extends Command
{
    protected static $defaultName = 'app:list:cards';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription(
            'List all available cards'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cards = $this->em->getRepository(Card::class)->findAll();

        dump(count($cards));
        exit();

        foreach ($cards as $card) {
            dump($card->getName());
        }
    }
}
