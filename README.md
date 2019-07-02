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
        $pheanstalk = $this->get("leezy.pheanstalk");

        // ----------------------------------------
        // producer (queues jobs)

        $pheanstalk
          ->useTube('testtube')
          ->put("job payload goes here\n");

        // ----------------------------------------
        // worker (performs jobs)

        $job = $pheanstalk
          ->watch('testtube')
          ->ignore('default')
          ->reserve();

        echo $job->getData();

        $pheanstalk->delete($job);
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
