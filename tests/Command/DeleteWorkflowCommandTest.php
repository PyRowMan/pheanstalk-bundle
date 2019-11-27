<?php

namespace Pyrowman\PheanstalkBundle\Tests\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Pheanstalk\Structure\Workflow;
use Pyrowman\PheanstalkBundle\Command\DeleteWorkflowCommand;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteWorkflowCommandTest extends AbstractPheanstalkCommandTest
{
    public function testExecute()
    {
        $args = $this->getCommandArgs();
        $workflow  = new Workflow($args['name'], $args['group'], new ArrayCollection([]));

        $this->pheanstalk->expects($this->once())->method('delete')->with($workflow);

        $command = $this->application->find('pyrowman:pheanstalk:delete-workflow');
        $commandTester = new CommandTester($command);
        $commandTester->execute($args);

        $this->assertContains(sprintf('Workflow %s deleted', $workflow->getName()), $commandTester->getDisplay());
    }

    /**
     * @inheritdoc
     */
    protected function getCommand()
    {
        return new DeleteWorkflowCommand($this->locator);
    }

    /**
     * @inheritdoc
     */
    protected function getCommandArgs()
    {
        return ['name' => 'test', 'group' => 'testGroup'];
    }
}
