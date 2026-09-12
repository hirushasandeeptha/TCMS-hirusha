<?php

namespace Modules\ClassSchedule\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class ClassScheduleServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'ClassSchedule';

    protected string $nameLower = 'classschedule';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
