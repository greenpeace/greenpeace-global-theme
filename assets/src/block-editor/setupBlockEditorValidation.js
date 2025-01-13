const {registerPlugin} = wp.plugins;
const {PluginPrePublishPanel} = wp.editor;
const {select, dispatch, subscribe, useSelect} = wp.data;
const {__} = wp.i18n;

// Object to store block-specific validations
const blockValidations = {};

// Messages array to hold validation messages
let messages = [];
let canPublish = true;

// Post types that require a featured image
const POST_TYPES_WITH_REQUIRED_FEATURED_IMAGE = ['p4_action', 'post', 'page', 'campaign'];

/**
 * Setup the block editor validation.
 * It subscribes to changes in the editor and performs validation checks on title, featured image, and blocks.
 */
export const setupBlockEditorValidation = () => {
  subscribe(() => {
    const {getEditedPostAttribute, getCurrentPostType, getEditedPostContent} = select('core/editor');
    const {getBlocks} = select('core/block-editor');

    // Get the necessary data from the editor state
    const title = getEditedPostAttribute('title');
    const featuredImage = getEditedPostAttribute('featured_media');
    const postType = getCurrentPostType();
    const postContent = getEditedPostContent();
    const blocks = getBlocks();
    const currentMessages = [];

    // Validation for title (must not be empty)
    const invalidTitle = !title || title.trim().length <= 0;
    if (invalidTitle) {
      currentMessages.push('Title is required.');
    }

    // Check if the post requires a featured image and if it's missing
    const hasImageInContent = /<img.+wp-image-(\d+).*>/i.test(postContent);
    const needsFeaturedImage = POST_TYPES_WITH_REQUIRED_FEATURED_IMAGE.includes(postType) &&
      !featuredImage &&
      !hasImageInContent;

    if (needsFeaturedImage) {
      currentMessages.push('Featured image is required.');
    }

    // Get an array of Topic Link blocks without background image
    const topicLinkBlocksNeedsImage = blocks
      .filter(block => block.name === 'planet4-blocks/topic-link' && !block.attributes.imageUrl);

    // If there are Topic Link blocks without background image, push an error message
    if (topicLinkBlocksNeedsImage.length) {
      currentMessages.push('Background image in the Topic Link block is required.');
    }

    const invalidBlocks = blocks.reduce((invalidBlocksArray, block) => {
      // Normally `blocks` contains a valid list of blocks, however it can happen that one of them is `null` in rare
      // cases. It happened to me once while running with WordPress 5.8.1 and undoing multiple edits. This made the
      // editor crash while it's trying to access `block.name`.
      if (!block) {
        return; // eslint-disable-line array-callback-return
      }
      const validations = blockValidations[block.name] || {};

      const results = Object.entries(validations).reduce((resultsArray, [attrName, validate]) => {
        const value = block.attributes[attrName];
        const result = validate(value);

        if (!result.isValid) {
          resultsArray.push(result);
        }

        return resultsArray;
      }, []);

      invalidBlocksArray.push(...results);

      return invalidBlocksArray;
    }, []);

    // Push invalid block validation messages to currentMessages
    invalidBlocks.forEach(block => currentMessages.push(...block.messages));

    // Determine if the post is currently valid (no validation errors)
    const currentlyValid =
      !invalidBlocks.length &&
      !topicLinkBlocksNeedsImage.length &&
      !invalidTitle &&
      !needsFeaturedImage;

    // Update the global messages array with the current validation messages
    messages = currentMessages;

    // Check if the validation status has changed
    if (canPublish === currentlyValid) {
      return;
    }
    canPublish = currentlyValid;

    // Lock or unlock post saving based on validity
    if (!canPublish) {
      dispatch('core/editor').lockPostSaving();
    } else {
      dispatch('core/editor').unlockPostSaving();
    }
  });

  // Register the pre-publish checklist panel plugin
  registerPlugin('pre-publish-checklist', {render: PrePublishCheckList});

  // Add filter for block type validation
  wp.hooks.addFilter(
    'blocks.registerBlockType',
    'planet4-plugin-gutenberg-blocks',
    registerAttributeValidations
  );
};

/**
 * Event listener for changes in the campaign name select field.
 * Updates the post meta with the selected campaign name.
 */
document.addEventListener('change', e => {
  if (!e.target.matches('select[name="p4_campaign_name"]')) {
    return;
  }
  dispatch('core/editor').editPost({meta: {p4_campaign_name: e.target.value}});
});

/**
 * Registers block attribute validations.
 *
 * @param {Object} settings  - The block settings object.
 * @param {string} blockName - The name of the block.
 * @return {Object}           - The updated block settings with validations.
 */
const registerAttributeValidations = (settings, blockName) => {
  const {attributes} = settings;

  // Register block attribute validations
  Object.keys(settings.attributes).forEach(attrName => {
    const attr = attributes[attrName];

    // If a validation function is defined for the attribute, store it
    if (typeof attr.validation === 'function') {
      blockValidations[blockName] = blockValidations[blockName] || {};
      blockValidations[blockName][attrName] = attr.validation;
    }
  });

  return settings;
};

/**
 * Pre-Publish checklist panel component.
 * Displays the current validation messages or a success message.
 *
 * @return {PluginPrePublishPanel}  The sidebar panel.
 */
const PrePublishCheckList = () => {
  // This doesn't assign anything from useSelect, which is intended. We want to update the component whenever anything
  // that can affect validity changes. This could probably be done more properly by adding a store with `canPublish`.
  // eslint-disable-next-line no-shadow
  useSelect(select => [select('core/editor').getEditedPostAttribute('meta'), select('core/block-editor').getBlocks()]);

  return (
    <PluginPrePublishPanel
      title={__('Publish Checklist', 'planet4-blocks-backend')}
      initialOpen="true"
      className={!canPublish ? 'p4-plugin-pre-publish-panel-error' : ''}
      icon="none">
      {!!canPublish && <p>{ __('All good.', 'planet4-blocks-backend') }</p>}
      {!canPublish && <ul>
        { messages.map(msg =>
          <li key={msg}><p>{ msg }</p></li>
        ) }
      </ul>}
    </PluginPrePublishPanel>
  );
};
