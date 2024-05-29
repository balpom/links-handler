<?php

declare(strict_types=1);

namespace Balpom\LinksHandler;

use Psr\Link\EvolvableLinkProviderInterface;

interface LinksHandlerInterface
{

    public function handle(EvolvableLinkProviderInterface $linkProvider): EvolvableLinkProviderInterface;
}
