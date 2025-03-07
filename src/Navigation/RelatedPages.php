<?php

declare(strict_types=1);

namespace Hofff\Contao\ContentNavigation\Navigation;

use Contao\PageModel;
use Hofff\Contao\ContentNavigation\Navigation\Query\ArticlePageQuery;
use Hofff\Contao\ContentNavigation\Navigation\Query\JumpToPageQuery;

use function array_key_exists;

final class RelatedPages
{
    /** @var ArticlePageQuery */
    private $articlePageQuery;

    /** @var JumpToPageQuery */
    private $jumpToPageQuery;

    /** @var array<string,array<int,PageModel|null>> */
    private $cache = [];

    /**
     * Mapping between table and ptable for jump to relations.
     *
     * @var array<string,string>
     */
    private $jumpToMapping;

    /**
     * @param array<string,string> $jumpToMapping
     */
    public function __construct(
        ArticlePageQuery $articlePageQuery,
        JumpToPageQuery $jumpToPageQuery,
        array $jumpToMapping
    ) {
        $this->articlePageQuery = $articlePageQuery;
        $this->jumpToPageQuery  = $jumpToPageQuery;
        $this->jumpToMapping    = $jumpToMapping;
    }

    /**
     * Get related page for a given item.
     *
     * @param object $item
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function ofItem($item): ?PageModel
    {
        $parentId = (int) $item->pid;

        if (isset($this->jumpToMapping[$item->ptable])) {
            return $this->getJumpToPage($parentId, $item->ptable, $this->jumpToMapping[$item->ptable]);
        }

        switch ($item->ptable) {
            case '':
            case 'tl_article':
                return $this->getArticlePage($parentId);
        }

        return $GLOBALS['objPage'] ?? null;
    }

    /**
     * Get article of a page.
     */
    private function getArticlePage(int $articleId): ?PageModel
    {
        if (! isset($this->cache['tl_article']) || ! array_key_exists($articleId, $this->cache['tl_article'])) {
            $this->cache['tl_article'][$articleId] = ($this->articlePageQuery)($articleId);
        }

        return $this->cache['tl_article'][$articleId];
    }

    private function getJumpToPage(int $parentId, string $parentTable, string $categoryTable): ?PageModel
    {
        if (! isset($this->cache[$parentTable]) || ! array_key_exists($parentId, $this->cache[$parentTable])) {
            $this->cache[$parentTable][$parentId] = ($this->jumpToPageQuery)($parentId, $parentTable, $categoryTable);
        }

        return $this->cache[$parentTable][$parentId];
    }
}
