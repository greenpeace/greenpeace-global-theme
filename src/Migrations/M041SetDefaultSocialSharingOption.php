<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Set default social sharing option via the new P4 setting.
 */
class M041SetDefaultSocialSharingOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $options = get_option('planet4_options');
        $options['apply_social_sharing_options'] = 'posts_and_forms';
        update_option('planet4_options', $options);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
