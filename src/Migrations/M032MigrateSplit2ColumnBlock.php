<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate split-two-columns block to columns block.
 */
class M032MigrateSplit2ColumnBlock extends MigrationScript
{
    /**
     * Extract split-two-columns block from page/posts and transform it into columns block.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        $check_is_valid_block = function ($block) {
            return self::check_is_valid_block($block);
        };

        $transform_block = function ($block) {
            return self::transform_block($block);
        };

        Utils\Functions::execute_block_migration(
            Utils\Constants::BLOCK_SPLIT_TWO_COLUMNS,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is a Split 2 columns block.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        // Check if the block is valid.
        if (!is_array($block)) {
            return false;
        }

        // Check if the block has a 'blockName' key.
        if (!isset($block['blockName'])) {
            return false;
        }

        // Check if the block is a split-two-columns block. If not, abort.
        return $block['blockName'] === Utils\Constants::BLOCK_SPLIT_TWO_COLUMNS;
    }

    /**
     * Transform the Split 2 columns block into a Columns block.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        $block_attrs = self::get_split_2_columns_block_attrs($block['attrs']);

        return Utils\Functions::create_block_columns(
            [],
            [
                self::create_left_column($block_attrs),
                self::create_right_column($block_attrs),
            ]
        );
    }

    /**
     * Create the left column of the new blocks.
     *
     * @param array $block_attrs - The original block attributes.
     * @return array - The left column blocks.
     */
    private static function create_left_column(array $block_attrs): array
    {
        $heading_block = Utils\Functions::create_block_heading(
            ['level' => 3],
            $block_attrs['column1']['title']
        );

        $paragraph_block_1 = Utils\Functions::create_block_paragraph(
            [],
            $block_attrs['column1']['description']
        );

        $paragraph_block_2 = Utils\Functions::create_block_paragraph(
            [],
            '<a href="' . $block_attrs['column1']['link_path'] . '">' . $block_attrs['column1']['link_text'] . '</a>'
        );

        $media_text_block = Utils\Functions::create_media_text_block(
            [
                'mediaId' => $block_attrs['column1']['image_id'],
                'mediaLink' => $block_attrs['column1']['image_src'],
                'mediaType' => 'image',
            ],
            [
                $heading_block,
                $paragraph_block_1,
                $paragraph_block_2,
            ],
            $block_attrs['column1']['image_src'],
            (int)$block_attrs['column1']['image_id']
        );

        $blocks = Utils\Functions::create_block_single_column(
            ['verticalAlignment' => 'center'],
            [$media_text_block],
        );

        return $blocks;
    }

    /**
     * Create the right column of the new blocks.
     *
     * @param array $block_attrs - The original block attributes.
     * @return array - The right column blocks.
     */
    private static function create_right_column(array $block_attrs): array
    {
        $heading_block = Utils\Functions::create_block_heading(
            ['level' => 3],
            $block_attrs['column2']['title'] ? '#' . $block_attrs['column2']['title'] : ''
        );

        $paragraph_block = Utils\Functions::create_block_paragraph(
            [],
            $block_attrs['column2']['description']
        );

        if ($block_attrs['column2']['button_link']) {
            $single_button_block = Utils\Functions::create_block_single_button(
                ['className' => 'is-style-cta'],
                $block_attrs['column2']['button_text'],
                $block_attrs['column2']['button_link'],
            );
        }

        $buttons_block = Utils\Functions::create_block_buttons(
            ['className' => 'carousel-controls'],
            [$single_button_block],
        );

        $media_text_block = Utils\Functions::create_media_text_block(
            [
                'mediaId' => $block_attrs['column2']['image_id'],
                'mediaLink' => $block_attrs['column2']['image_src'],
                'mediaType' => 'image',
            ],
            [
                $heading_block,
                $paragraph_block,
                $buttons_block,
            ],
            $block_attrs['column2']['image_src'],
            (int)$block_attrs['column2']['image_id']
        );

        $blocks = Utils\Functions::create_block_single_column(
            ['verticalAlignment' => 'center'],
            [$media_text_block],
        );

        return $blocks;
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
        $block_attrs['column1']['link_path'] = (string)$block['issue_link_path'] ?? '';
        $block_attrs['column1']['image_id'] = $block['issue_image_id'] ?? '';
        $block_attrs['column1']['image_src'] = (string)$block['issue_image_src'] ?? '';

        $block_attrs['column2']['title'] = $block['tag_name'] ?? '';
        $block_attrs['column2']['description'] = wp_trim_words($block['tag_description'] ?? '', 12);
        $block_attrs['column2']['button_text'] = $block['button_text'] ?? '';
        $block_attrs['column2']['button_link'] = $block['button_link'] ?? '';
        $block_attrs['column2']['link_path'] = $block['tag_link'] ?? '';
        $block_attrs['column2']['image_id'] = $block['tag_image_id'] ?? '';
        $block_attrs['column2']['image_src'] = (string)$block['tag_image_src'] ?? '';

        return $block_attrs;
    }
    // phpcs:enable Generic.Files.LineLength.MaxExceeded
}
