<?php

namespace Genero\Sage\ArchivePages\Integrations;

use Genero\Sage\ArchivePages\ArchivePages;
use Yoast\WP\SEO\Helpers\Options_Helper;
use Yoast\WP\SEO\Repositories\Indexable_Repository;

class Yoast
{
    /**
     * @var \Genero\Sage\ArchivePages\ArchivePages $archive
     */
    protected $archive;

    public function __construct(ArchivePages $archive)
    {
        $this->archive = $archive;
    }

    public function addBindings()
    {
        add_filter('wpseo_breadcrumb_indexables', [$this, 'addTermBreadcrumbs']);
        add_filter('wpseo_breadcrumb_indexables', [$this, 'addBlogBreadcrumbs']);
        add_filter('wpseo_breadcrumb_indexables', [$this, 'translateArchiveInBreadcrumbs']);
    }

    public function addTermBreadcrumbs(array $indexables): array
    {
        if (get_query_var('original_archive_type') !== 'term') {
           return $indexables;
        }

        $repository = YoastSEO()->classes->get(Indexable_Repository::class);
        $options = YoastSEO()->classes->get(Options_Helper::class);

        $taxonomy = get_query_var('taxonomy');
        $termId = get_query_var('original_archive_id');

        $termIndexable = $repository->find_by_id_and_type($termId, 'term');
        $ancestors = $repository->get_ancestors($termIndexable);

        // Remove the regular page crumb
        array_pop($indexables);
        // Add the parent above the ancestors if available.
        if ($parent = $options->get("taxonomy-$taxonomy-ptparent")) {
            $indexables[] = $repository->find_for_post_type_archive($parent);
        }

        // Add all the ancestors
        foreach ($ancestors as $ancestor) {
            $indexables[] = $ancestor;
        }
        // Add the term itself
        $indexables[] = $termIndexable;

        return $indexables;
    }

    public function addBlogBreadcrumbs(array $indexables): array
    {
        // Prepend the Blog page on posts and categories.
        if (!is_singular('post') && !is_category()) {
            return $indexables;
        }

        $repository = YoastSEO()->classes->get(Indexable_Repository::class);

        // Prepend the Blog page on posts and categories.
        if ($pageId = $this->archive->getArchivePageFromPostType('post')) {
            $archive_indexable = $repository->find_by_id_and_type($pageId, 'post');
            array_splice($indexables, 1, 0, [$archive_indexable]);
        }

        return $indexables;
    }

    public function translateArchiveInBreadcrumbs(array $indexables): array
    {
        $repository = YoastSEO()->classes->get(Indexable_Repository::class);

        // Find all archive crumbs and translate them.
        foreach ($indexables as $idx => $indexable) {
            /* @var \Yoast\WP\SEO\Models\Indexable $indexable */
            if ($indexable->get('object_type') === 'post-type-archive') {
                $postType = $indexable->get('object_sub_type');

                if ($pageId = $this->archive->getArchivePageFromPostType($postType)) {
                    $archive_indexable = $repository->find_by_id_and_type($pageId, 'post');
                    $indexables[$idx] = $archive_indexable;
                }
            }
        }
        return $indexables;
    }
}
