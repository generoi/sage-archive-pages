<?php

namespace Genero\Sage\ArchivePages\Integrations;

use Genero\Sage\ArchivePages\ArchivePages;
use WP_Post;

/**
 * Tells generoi/gds-llms what a mapped page lists. Its markdown skips the
 * template, which is where the archive is rendered, so without this a mapped
 * page's markdown would be the page's own content only.
 */
class Llms
{
    protected ArchivePages $archive;

    public function __construct(ArchivePages $archive)
    {
        $this->archive = $archive;
    }

    public function addBindings()
    {
        add_filter('gds/llms/markdown_listing', [$this, 'markdownListing'], 10, 2);
    }

    public function markdownListing(?array $args, WP_Post $post): ?array
    {
        if ($postType = $this->archive->getPostTypeFromArchivePage($post->ID)) {
            return ['post_type' => $postType];
        }
        return $args;
    }
}
