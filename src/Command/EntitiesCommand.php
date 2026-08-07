<?php

namespace Dayploy\JsDtoBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    protected function configure(): void
    {
        $this
            ->addOption(
                'uri-prefix',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Only generate DTOs whose uriTemplate starts with this prefix (repeatable). Referenced enums are pulled in automatically. Without it, everything is generated.',
            )
            ->setHelp(<<<'HELP'
Generate TypeScript types from PHP DTOs carrying #[JsDto].

  <info>%command.full_name%</info>
    Everything.

  <info>%command.full_name% --uri-prefix=/v4/customer</info>
    Only the DTOs serving /v4/customer routes, plus the enums they use.

⚠️ An operation without an explicit <comment>uriTemplate</comment> can never match a
prefix — its path is derived by API Platform and is unknowable from the
attribute. Such DTOs are silently skipped when filtering.
HELP)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $uriPrefixes */
        $uriPrefixes = $input->getOption('uri-prefix');

        $this->generator->generate(['src'], $uriPrefixes);

        return Command::SUCCESS;
    }
}
