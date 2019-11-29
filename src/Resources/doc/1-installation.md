## Installation

Installation is a quick 3 step process:

1. Download PheanstalkBundle using composer
2. Enable the Bundle
3. Configure your application's config.yml

### Step 1: Require PheanstalkBundle

Tell composer to require this bundle by running:

``` bash
$ composer require pyrowman/pheanstalk-bundle
```

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Pyrowman\PheanstalkBundle\PheanstalkBundle(),
    );
}
```

### Step 3: Configure your application's config.yml

Finally, add the following to your config.yml

``` yaml
# app/config/config.yml
pheanstalk:
    pheanstalks:
        primary:
            server: evqueue.domain.tld
            user: my_awesome_login_from_env_file
            password: my_awesome_password_from_env_file
            default: true
```
