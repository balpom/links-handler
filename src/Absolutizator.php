<?php

declare(strict_types=1);

namespace Balpom\LinksHandler;

use Psr\Link\EvolvableLinkProviderInterface;
use League\Uri\BaseUri;

class Absolutizator extends AbstractHandler
{
    protected string|null $baseHref;

    public function __construct(string|null $baseHref = null)
    {
        $this->baseHref = $baseHref;
    }

    public function handle(EvolvableLinkProviderInterface $linkProvider): EvolvableLinkProviderInterface
    {
        $baseHref = $this->getBaseHref($linkProvider);
        $baseHref = empty($baseHref) ? $this->baseHref : $baseHref;
        $baseUri = BaseUri::from($baseHref);
        $links = $linkProvider->getLinks();
        $resultLinkProvider = $this->createLinkProvider();
        foreach ($links as $link) {
            $uri = $link->getHref();
            $uri = $baseUri->relativize($uri);
            $uri = $baseUri->resolve($uri)->getUriString();
            $link = $this->changeLinkUri($link, $uri);
            $resultLinkProvider = $resultLinkProvider->withLink($link);
        }

        return $resultLinkProvider;
    }
}
