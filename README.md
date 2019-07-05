## LeezyPheanstalkBundle

[EvQueue workqueue](http://www.evqueue.net/) clients for Symfony2.

The Pyrowman\LeezyPheanstalkBundle is a fork from [LeezyPheanstalkBundle](https://github.com/armetiz/LeezyPheanstalkBundle) 

The Pyrowman\LeezyPheanstalkBundle is a Symfony2 Bundle that provides a [pheanstalk](https://github.com/pyrowman/pheanstalk) integration with the following features:
* Command Line Interface for manage the queues.
* An integration to the Symfony2 event system.
* An integration to the Symfony2 profiler system to monitor your beanstalk server.
* An integration to the Symfony2 logger system.
* A proxy system to customize the command features.
* Auto-wiring: `PheanstalkInterface`


Documentation :
- [Installation](https://github.com/armetiz/LeezyPheanstalkBundle/blob/master/src/Resources/doc/1-installation.md)
- [Configuration](https://github.com/armetiz/LeezyPheanstalkBundle/blob/master/src/Resources/doc/2-configuration.md)
- [CLI Usage](https://github.com/armetiz/LeezyPheanstalkBundle/blob/master/src/Resources/doc/3-cli.md)
- [Events](https://github.com/armetiz/LeezyPheanstalkBundle/blob/master/src/Resources/doc/4-events.md)
- [Custom proxy](https://github.com/armetiz/LeezyPheanstalkBundle/blob/master/src/Resources/doc/5-custom-proxy.md)
- [Extra - Beanstalk Manager](https://github.com/armetiz/LeezyPheanstalkBundle/blob/master/src/Resources/doc/6-extra-beanstalk-manager.md)
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
