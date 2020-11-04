<?php

namespace Genero\Sage\ArchivePages;

use Yoast\WP\SEO\Helpers\Options_Helper;

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
            'lang' => '',
        ]);

        if (!empty($pages)) {
            return $this->getTranslatedPageId(reset($pages));
        }

        return null;
    }

    public function getPostTypeFromArchivePage(int $postId): string
    {
        // With WPML and Polylang, assume the page selected as the archive is
        // the page created in the default language of the site.
        $defaultLanguage = apply_filters('wpml_default_language', null);
        $postId = $this->getTranslatedPageId($postId, $defaultLanguage);
        return get_post_meta($postId, '_post_type_mapped', true) ?: '';
    }

    public function getArchivePageFromTaxonomy(string $taxonomy): ?int
    {
        if (function_exists('YoastSEO')) {
            $yoastOptions = YoastSEO()->classes->get(Options_Helper::class);

            if ($postType = $yoastOptions->get(sprintf('taxonomy-%s-ptparent', $taxonomy))) {
                return $this->getArchivePageFromPostType($postType);
            }
        }
        // @todo alternative
        return null;
    }

    protected function getTranslatedPageId(int $postId, string $language = null): int
    {
        return apply_filters('wpml_object_id', $postId, 'page', true, $language);
    }
}
