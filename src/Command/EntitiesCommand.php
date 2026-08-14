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
            ->addOption(
                'exclude-uri-prefix',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Drop the operations whose uriTemplate starts with this prefix (repeatable). The mirror of --uri-prefix: hands a front everything BUT one route family.',
            )
            ->setHelp(<<<'HELP'
Generate TypeScript types from PHP DTOs carrying #[JsDto].

  <info>%command.full_name%</info>
    Everything.

  <info>%command.full_name% --uri-prefix=/v4/customer</info>
    Only the DTOs serving /v4/customer routes, plus the enums they use.

  <info>%command.full_name% --exclude-uri-prefix=/v4/customer</info>
    Everything except those — the two runs partition the model between
    the promoter front and the consumer one.

⚠️ An operation without an explicit <comment>uriTemplate</comment> has a path derived by
API Platform, unknowable from the attribute alone. Both filters therefore
treat it as "not provably part of the named family": <comment>--uri-prefix</comment> skips it,
<comment>--exclude-uri-prefix</comment> keeps it.
HELP)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $uriPrefixes */
        $uriPrefixes = $input->getOption('uri-prefix');
        /** @var string[] $excludeUriPrefixes */
        $excludeUriPrefixes = $input->getOption('exclude-uri-prefix');

        $this->generator->generate(['src'], $uriPrefixes, $excludeUriPrefixes);

        return Command::SUCCESS;
    }
}
