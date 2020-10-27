<?php

namespace Genero\Sage\ArchivePages\Integrations;

use Genero\Sage\ArchivePages\ArchivePages;

use WP_Query;

class Blade
{
    public function __construct(ArchivePages $archive)
    {
        $this->archive = $archive;
    }

    public function addBindings()
    {
        add_filter('template_include', [$this, 'templateInclude']);
        add_filter('pre_get_posts', [$this, 'preGetPosts'], 9);
    }

    /**
     * Setup the archive page globals and set the template to the original
     * archive template.
     */
    public function templateInclude(string $template): string
    {
        global $wp_query;
        if ($postType = $wp_query->get('mapped_post_archive')) {
            $this->setupArchiveGlobals($postType);

            return $this->getArchivePageTemplate($postType);
        }
        return $template;
    }

    /**
     *
     */
    public function preGetPosts(WP_Query $query): WP_Query
    {
        if (is_admin() || !$query->is_main_query()) {
            return $query;
        }

        // By default pages are redirected to archives, but we want to keep the URL
        // structure.
        if (is_page()) {
            if ($post_type = $this->archive->getPostTypeFromArchivePage(get_queried_object_id())) {
                $query->set('mapped_post_archive', $post_type);
                $query->is_archive = true;
                // Do not redirect
                $query->set('redirected', true);
            }
        }

        // Redirect the archive pages to their pages.
        if (is_post_type_archive() || is_home()) {
            $archivePage = $this->archive->getArchivePageFromPostType(
                get_queried_object()->name
            );
            if (!$archivePage) {
                return $query;
            }

            if (is_home()) {
                $query->set('mapped_post_archive', 'post');
                return $query;
            }

            if (!empty($pages)) {
                wp_safe_redirect(get_permalink(reset($pages)));
                exit;
            }
        }

        return $query;
    }

    protected function setupArchiveGlobals(string $postType): void
    {
        global $wp_query, $post;
        $original_wp_query = $wp_query;

        $wp_query = new WP_Query([
            'post_type' => $postType,
            'paged' => get_query_var('paged') ?: 1,
        ]);
        $wp_query->queried_object_id = $original_wp_query->queried_object_id;
        $wp_query->queried_object = $original_wp_query->queried_object;
        $post = $wp_query->queried_object;
    }


    protected function getArchivePageTemplate(string $postType): string
    {
        if ($postType === 'post') {
            return get_home_template();
        }

        $templates[] = "archive-{$postType}.php";
        $templates[] = 'archive.php';
        $template = get_query_template('archive', $templates);

        if (!$template) {
            $template = get_index_template();
        }
        return $template;
    }
}
