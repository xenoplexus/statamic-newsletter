<?php

namespace Xenoplexus\StatamicNewsletter\Tests;

use Xenoplexus\StatamicNewsletter\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;
}
