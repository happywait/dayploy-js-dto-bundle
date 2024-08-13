<?php

namespace Dayploy\JsDtoBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Dayploy\JsDtoBundle\Generator\Generator;

#[AsCommand(name: 'generate:jsdto')]
class EntitiesCommand extends Command
{
    public function __construct(
        private Generator $generator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->generator->generate(['src']);

        return Command::SUCCESS;
    }
}
