## Events

On each pheanstalk command, an event is dispatched.

See events name above :
* CommandEvent::DELETE
* CommandEvent::LIST_TUBES                    
* CommandEvent::LIST_WORKFLOWS                
* CommandEvent::PEEK
* CommandEvent::PUT                           
* CommandEvent::STATS
* CommandEvent::STATS_TUBE                    
* CommandEvent::STATS_JOB
* CommandEvent::CREATE_TASK                   
* CommandEvent::CREATE_TUBE
* CommandEvent::CREATE_WORKFLOW               
* CommandEvent::UPDATE_WORKFLOW
* CommandEvent::CREATE_WORKFLOW_SCHEDULER     
* CommandEvent::WORKFLOW_EXISTS
* CommandEvent::WORKFLOW_INSTANCES            
* CommandEvent::WORKFLOW_INSTANCES_DETAILS
* CommandEvent::TASK_EXISTS                   
* CommandEvent::TUBE_EXISTS
* CommandEvent::CANCEL                        
* CommandEvent::KILL
* CommandEvent::CREATE_SCHEDULE               
* CommandEvent::UPDATE_SCHEDULE
* CommandEvent::LIST_SCHEDULE                 
* CommandEvent::DELETE_SCHEDULE
* CommandEvent::GET_SCHEDULE                  

**Note** FQDN is \Pyrowman\PheanstalkBundle\Event\CommandEvent  

## Usage example

```php
<?php

namespace Acme\DemoBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Pyrowman\PheanstalkBundle\Event\CommandEvent;

class PheanstalkSubscriber implements EventSubscriberInterface {

    public static function getSubscribedEvents()
    {
        return array(
            CommandEvent::DELETE => array('onDelete', 0),
            CommandEvent::PUT => array('onPut', 0),
        );
    }

    public function onDelete(CommandEvent $event)
    {
        // ...
    }

    public function onPut(CommandEvent $event)
    {
        $pheanstalk = $event->getPheanstalk();

        $payload = $event->getPayload();
        $payload['data'];
        $payload['priority'];
        $payload['delay'];
        $payload['ttr'];
        // ...
    }

}
?>
```

```php
<?php

namespace Acme\DemoBundle\Controller;

use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController {

    public function indexAction() {
        // ----------------------------------------
        // producer (queues jobs)

        /** @var PheanstalkProxy $pheanstalk */
        $pheanstalk = $this->get("pheanstalk");
        $workflow = $pheanstalk->createTask('Sleep', 'Test', '/bin/sleep 80');
        $pheanstalk->put($workflow);
    }

}
?>
```
