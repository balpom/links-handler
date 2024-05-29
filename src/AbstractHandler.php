<?php

declare(strict_types=1);

namespace Balpom\LinksHandler;

use Psr\Link\EvolvableLinkProviderInterface;
use Psr\Link\LinkInterface;
use Symfony\Component\WebLink\GenericLinkProvider;
use Symfony\Component\WebLink\Link;
use Balpom\WebLink\WebLink;
use Balpom\WebLink\WebLinkInterface;

abstract class AbstractHandler implements LinksHandlerInterface
{

    abstract public function handle(EvolvableLinkProviderInterface $linkProvider): EvolvableLinkProviderInterface;

    protected function createLinkProvider(): EvolvableLinkProviderInterface
    {
        return new GenericLinkProvider();
    }

    protected function getBaseHref(EvolvableLinkProviderInterface $linkProvider): string|null
    {
        $linkProvider = $this->filterLinksByTags($linkProvider, 'base');
        $links = $linkProvider->getLinks();

        return isset($links[0]) ? $links[0]->getHref() : null;
    }

    protected function filterLinksByTags(EvolvableLinkProviderInterface $linkProvider, array|string $tags = ''): EvolvableLinkProviderInterface
    {
        $tags = $this->sanitizeTags($tags);
        if (empty($tags)) {
            return $linkProvider;
        }

        $links = $linkProvider->getLinks();
        foreach ($links as $link) {
            if (empty($tag = $link->getTag()) || !in_array(strtolower($tag), $tags)) {
                $linkProvider = $linkProvider->withoutLink($link);
            }
        }

        return $linkProvider;
    }

    private function sanitizeTags($tags): array
    {
        if (empty($tags)) {
            return [];
        }
        if (is_array($tags)) {
            return $tags;
        }
        if (false === strpos($tags, ',')) {
            return [$tags];
        }
        $tags = explode(',', $tags);
        $result = [];
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (empty($tag)) {
                continue;
            }
            $tag = strtolower($tag);
            if (!in_array($tag, $result)) {
                $result[] = strtolower($tag);
            }
        }

        return $result;
    }

    protected function changeLinkUri(LinkInterface $link, string $uri): LinkInterface
    {
        $templated = $link->isTemplated();
        $rels = $link->getRels();
        $attributes = $link->getAttributes();

        if ($link instanceof WebLinkInterface) {
            $newLink = new WebLink(null, $uri);
            $newLink = $newLink->withTag($link->getTag());
            $newLink = $newLink->withContent($link->getContent());
        } else {
            $newLink = new Link(null, $uri);
        }

        foreach ($rels as $rel) {
            $newLink = $newLink->withRel($rel);
        }
        foreach ($attributes as $attribute => $value) {
            $newLink = $newLink->withAttribute($attribute, $value);
        }

        return $newLink;
    }
}
