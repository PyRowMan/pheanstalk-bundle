<?php

namespace Pyrowman\PheanstalkBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Pheanstalk\Exception;
use Pheanstalk\Structure\Workflow;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteWorkflowCommand extends AbstractPheanstalkCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('pyrowman:pheanstalk:delete-workflow')
            ->addArgument('name', InputArgument::REQUIRED, 'Workflow name to delete.')
            ->addArgument('group', InputArgument::REQUIRED, 'Workflow group to delete.')
            ->addArgument('pheanstalk', InputArgument::OPTIONAL, 'Pheanstalk name.')
            ->setDescription('Delete the specified workflow if it exists.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workflowName       = $input->getArgument('name');
        $workflowGroup      = $input->getArgument('group');
        $name       = $input->getArgument('pheanstalk');
        $pheanstalk = $this->getPheanstalk($name);

        try {
            $workflow = new Workflow($workflowName, $workflowGroup, new ArrayCollection([]));
            $pheanstalk->delete($workflow);

            $output->writeln("Pheanstalk: <info>$name</info>");
            $output->writeln("Workflow <info>$workflowName</info> deleted.");

            return 0;
        } catch (Exception $e) {
            $output->writeln("Pheanstalk: <info>$name</info>");
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }
    }
}
