<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Block_Parser;

/**
 * Migrate split-two-columns block to columns block.
 */
class M034MigrateSplit2ColumnBlock extends MigrationScript
{
    /**
     * Extract split-two-columns block from page/posts and transform it into columns block.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        // Get the list of posts using split-two-columns block.
        $posts = Utils\Functions::get_posts_using_specific_block(
            Utils\Constants::BLOCK_SPLIT_TWO_COLUMNS,
            Utils\Constants::ALL_POST_TYPES
        );

        // If there are no posts, abort.
        if (!$posts) {
            return;
        }

        $parser = new WP_Block_Parser();

        echo "Split 2 Columns block migration in progress...\n"; // phpcs:ignore

        foreach ($posts as $post) {
            if (empty($post->post_content)) {
                continue;
            }

            echo 'Parsing post ', $post->ID, "\n"; // phpcs:ignore
            $result = false;

            // Go through the post blocks to find the planet4-blocks/split-two-columns one.
            $blocks = $parser->parse($post->post_content);

            foreach ($blocks as &$block) {
                // Skip other blocks.
                if (! isset($block['blockName']) || $block['blockName'] !== Utils\Constants::BLOCK_SPLIT_TWO_COLUMNS) {
                    continue;
                }

                $block = self::get_transform_block($block);
            }

            // Unset the reference to prevent potential issues.
            unset($block);

            // Serialize the blocks content.
            $new_content = serialize_blocks($blocks);

            if ($post->post_content !== $new_content) {
                $post_update = array(
                    'ID' => $post->ID,
                    'post_content' => $new_content,
                );

                try {
                    // Update the post with the replaced blocks.
                    wp_update_post($post_update);
                    $result = true;
                } catch (\Throwable $e) {
                    echo 'Error on post ', $post->ID, "\n";
                    echo $e->getMessage(), "\n";
                }
            }

            echo $result
                ? "Migration successful\n"
                : "Migration wasn't executed\n"; // phpcs:ignore
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Transform a block attrs into columns block.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    private static function get_transform_block(array $block): array
    {
        // Gathering the data we want from split 2 columns block.
        $block_attrs = self::get_split_2_columns_block_attrs($block['attrs']);

        $template = '<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:media-text {"mediaId":' . $block_attrs['column1']['image_id'] . ',"mediaLink":"' . get_page_link($block_attrs['column1']['image_id']) . '","mediaType":"image"} -->
<div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media"><img src="' . $block_attrs['column1']['image_src'] . '" alt="" class="wp-image-' . $block_attrs['column1']['image_id'] . ' size-full"/></figure><div class="wp-block-media-text__content"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . $block_attrs['column1']['title'] . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . $block_attrs['column1']['description'] . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="' . $block_attrs['column1']['link_path'] . '">' . $block_attrs['column1']['link_text'] . '</a></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:media-text --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:media-text {"mediaId":' . $block_attrs['column2']['image_id'] . ',"mediaLink":"' . get_page_link($block_attrs['column2']['image_id']) . '","mediaType":"image"} -->
<div class="wp-block-media-text is-stacked-on-mobile"><figure class="wp-block-media-text__media"><img src="' . $block_attrs['column2']['image_src'] . '" alt="" class="wp-image-' . $block_attrs['column2']['image_id'] . ' size-full"/></figure><div class="wp-block-media-text__content"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">#' . $block_attrs['column2']['title'] . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . $block_attrs['column2']['description'] . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-cta"} -->
<div class="wp-block-button is-style-cta"><a class="wp-block-button__link wp-element-button" href="' . $block_attrs['column2']['button_link'] . '">' . $block_attrs['column2']['button_text'] . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div></div>
<!-- /wp:media-text --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->';

        $parser_new = new WP_Block_Parser();
        $blocks = $parser_new->parse($template);

        return $blocks[0];
    }

    /**
     * Get the split-two-columns block attrs.
     *
     * @param array $block - A parsed split-two-columns block.
     * @return array - A block attrs array.
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    private static function get_split_2_columns_block_attrs(array $block): array
    {
        $block_attrs = [];

        // For old split 2 column versions.
        $issue_id = (int) ( $block['select_issue'] ?? null );
        $tag_id = (int) ( $block['select_tag'] ?? null );
        $issue_image_id = (int) ( $block['issue_image'] ?? $block['issue_image_id'] ?? get_post_thumbnail_id($issue_id) ?? 0 );
        $tag_image_id = (int) ( $block['tag_image'] ?? $block['tag_image_id'] ?? get_term_meta($tag_id, 'tag_attachment_id', true) ?? 0 );

        if ($issue_id) {
            $issue_meta_data = get_post_meta($issue_id);
            $block['title'] = !empty($block['title']) ? $block['title'] : $issue_meta_data['p4_title'][0] ?? get_the_title($issue_id);
            $block['issue_description'] = $block['issue_description'] ?? $issue_meta_data['p4_description'][0] ?? '';
            $block['issue_link_path'] = $block['issue_link_path'] ?? get_permalink($issue_id);
            $block['issue_link_text'] = $block['issue_link_text'] ?? __('Learn more about this issue', 'planet4-blocks');

            $block['issue_image_id'] = $block['issue_image_id'] ?? $issue_image_id;
            $block['issue_image_src'] = $block['issue_image_src'] ?? ($issue_image_id ? wp_get_attachment_url($issue_image_id) : '');
        }

        if ($tag_id) {
            $tag = get_term($tag_id);
            if ($tag instanceof \WP_Term) {
                $block['tag_name'] = $block['tag_name'] ?? $tag->name ?? '';
                $block['tag_link'] = get_tag_link($tag);
                $block['tag_description'] = $block['tag_description'] ?? $tag->description ?? '';

                $block['tag_image_id'] = $block['tag_image_id'] ?? $tag_image_id;
                $block['tag_image_src'] = $block['tag_image_src'] ?? ($tag_image_id ? wp_get_attachment_url($tag_image_id) : '');

                $block['button_text'] = $block['button_text'] ?? __('Get involved', 'planet4-blocks');
                $block['button_link'] = $block['button_link'] ?? $block['tag_link'] ?? '';
            }
        }

        $block_attrs['column1']['title'] = $block['title'] ?? '';
        $block_attrs['column1']['description'] = wp_trim_words($block['issue_description'] ?? '', 12);
        $block_attrs['column1']['link_text'] = $block['issue_link_text'] ?? '';
        $block_attrs['column1']['link_path'] = $block['issue_link_path'] ?? '';
        $block_attrs['column1']['image_id'] = $block['issue_image_id'] ?? '';
        $block_attrs['column1']['image_src'] = $block['issue_image_src'] ?? '';

        $block_attrs['column2']['title'] = $block['tag_name'] ?? '';
        $block_attrs['column2']['description'] = wp_trim_words($block['tag_description'] ?? '', 12);
        $block_attrs['column2']['button_text'] = $block['button_text'] ?? '';
        $block_attrs['column2']['button_link'] = $block['button_link'] ?? '';
        $block_attrs['column2']['link_path'] = $block['tag_link'] ?? '';
        $block_attrs['column2']['image_id'] = $block['tag_image_id'] ?? '';
        $block_attrs['column2']['image_src'] = $block['tag_image_src'] ?? '';

        return $block_attrs;
    }
    // phpcs:enable Generic.Files.LineLength.MaxExceeded
}
