## Custom proxy

Add a custom proxy only if you can't do what you want using [Events](/src/Resources/doc/4-events.md) hook system.

# Create a proxy class

Two choices:
* Implement **Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxyInterface**
* Extend **Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxy**

```php
<?php

namespace Acme\DemoBundle\Proxy;

use Pheanstalk\Structure\Workflow;
use Pyrowman\PheanstalkBundle\Proxy\PheanstalkProxy as PheanstalkProxyBase;
use Pheanstalk\PheanstalkInterface;

class PheanstalkProxy extends PheanstalkProxyBase
{
    /**
     * {@inheritDoc}
     */
    public function put(Workflow $workflow)
    {
        //crazy job here

        return parent::put($workflow);
    }
}
?>
```

# Define proxy class on the container

The injection of a dispatcher isn't mandatory. Don't inject it and the logger will be disabled.

```xml
<service id="acme.demo.pheanstalk.proxy" class="Acme\DemoBundle\Proxy\PheanstalkProxy">
    <call method="setDispatcher">
        <argument type="service" id="event_dispatcher" on-invalid="ignore"/>
    </call>
</service>
```

# Configure pheanstalk_bundle

```yaml
# app/config/config.yml
pheanstalk:
    pheanstalks:
        foo_bar:
            server: evqueue-2.domain.tld
            default: true
            proxy: acme.demo.pheanstalk.proxy
```
