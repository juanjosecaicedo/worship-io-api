<?php

namespace App\Support\Scramble;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\RouteInfo;

class GlobalHeadersExtension extends OperationExtension
{
    /**
     * Handle the operation to add a global header.
     *
     * @param Operation $operation
     * @param RouteInfo $routeInfo
     * @return void
     */
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $operation->addParameters([
            Parameter::make('Accept-Language', 'header')
                ->description('The preferred language for the response (en, es).')
                ->setSchema(Schema::fromType((new StringType)->default('en')))
                ->example('es')
        ]);
    }
}
