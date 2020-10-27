<?php

namespace Genero\Sage\ArchivePages;

class ArchivePages
{
    public function getArchivePageFromPostType(string $postType): ?int
    {
        $pages = get_posts([
            'post_type' => 'page',
            'meta_key' => '_post_type_mapped',
            'meta_value' => $postType,
            'fields' => 'ids',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'suppress_filters' => true,
        ]);

        if (!empty($pages)) {
            return reset($pages);
        }

        return null;
    }

    public function getPostTypeFromArchivePage(int $postId): string
    {
        // With WPML and Polylang, assume the page selected as the archive is
        // the page created in the default language of the site.
        $defaultLanguage = apply_filters('wpml_default_language', null);
        $postId = apply_filters('wpml_object_id', $postId, 'page', true, $defaultLanguage);
        return get_post_meta($postId, '_post_type_mapped', true) ?: '';
    }
}
