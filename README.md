## LeezyPheanstalkBundle

[![Packagist Version](https://img.shields.io/packagist/v/pyrowman/pheanstalk-bundle)](https://packagist.org/packages/pyrowman/pheanstalk-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/PyRowMan/pheanstalk-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/PyRowMan/pheanstalk-bundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/PyRowMan/pheanstalk-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/PyRowMan/pheanstalk-bundle/?branch=master)
[![Build Status](https://travis-ci.org/PyRowMan/pheanstalk-bundle.svg?branch=master)](https://travis-ci.org/PyRowMan/pheanstalk-bundle)

[EvQueue workqueue](http://www.evqueue.net/) clients for Symfony3.

The Pyrowman\LeezyPheanstalkBundle is a fork from [LeezyPheanstalkBundle](https://github.com/armetiz/LeezyPheanstalkBundle) 

The Pyrowman\LeezyPheanstalkBundle is a Symfony3 Bundle that provides a [pheanstalk](https://github.com/pyrowman/pheanstalk) integration with the following features:
* Command Line Interface for manage the queues.
* An integration to the Symfony3 event system.
* An integration to the Symfony3 profiler system to monitor your beanstalk server.
* An integration to the Symfony3 logger system.
* A proxy system to customize the command features.
* Auto-wiring: `PheanstalkInterface`


Documentation :
- [Installation](https://github.com/PyRowMan/pheanstalk-bundle/blob/master/src/Resources/doc/1-installation.md)
- [Configuration](https://github.com/PyRowMan/pheanstalk-bundle/blob/master/src/Resources/doc/2-configuration.md)
- [CLI Usage](https://github.com/PyRowMan/pheanstalk-bundle/blob/master/src/Resources/doc/3-cli.md)
- [Events](https://github.com/PyRowMan/pheanstalk-bundle/blob/master/src/Resources/doc/4-events.md)
- [Custom proxy](https://github.com/PyRowMan/pheanstalk-bundle/blob/master/src/Resources/doc/5-custom-proxy.md)
- [Extra - Beanstalk Manager](https://github.com/PyRowMan/pheanstalk-bundle/blob/master/src/Resources/doc/6-extra-beanstalk-manager.md)
- [Extra - Proxy to prefix tubes](https://github.com/h4cc/LeezyPheanstalkBundleExtra)

## Usage example

```php
<?php

namespace Acme\DemoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller {

    public function indexAction() {
        
        $sc = $this->get('service_container');
        /** @var PheanstalkProxy $pheanstalk */
        $pheanstalk = $sc->get("leezy.pheanstalk");

        // Create a simple Worflow with one task inside
        
        $workflow = $pheanstalk->createTask('Sleep', 'Test', '/bin/sleep 80');
        
        // Put the job into instance execution
        
        $pheanstalk->put($workflow);
        
        // ----------------------------------------
        // check server availability
        
        $pheanstalk->getConnection()->isServiceListening(); // true or false
        
        //-----------------------------------------
        // Add a scheduler for the job (by default in continous)
        
        $workflowSchedule = $pheanstalk->createSchedule($workflow, new TimeSchedule());
        
        //-----------------------------------------
        // Edit a workflow
        
        $workflow->setGroup('2nd test group');
        $pheanstalk->update($workflow);
        
        
        //-----------------------------------------
        // Getting infos on the execution of a workflow
        
        $workflowInstances = $pheanstalk->getWorkflowInstances($workflow);
        
        //-----------------------------------------
        // Delete a job 
        
        if ($workflow = $pheanstalk->workflowExists('Sleep'))
            $pheanstalk->delete($workflow);

    }
    
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            // ...
            'service_container' => ContainerInterface::class,
        ]);
    }

}
?>
```

## Testing

```bash
$ php composer.phar update
$ phpunit
```

## License

This bundle is under the MIT license. [See the complete license](http://www.opensource.org/licenses/mit-license.php).

## Credits
Author - [Valentin Corre](http://broken.fr)  
Original library Author - [Thomas Tourlourat](http://www.armetiz.info)

Contributor :
* [Peter Kruithof](https://github.com/pkruithof) : Version 3
* [Maxwell2022](https://github.com/Maxwell2022) : Symfony2 Profiler integration
