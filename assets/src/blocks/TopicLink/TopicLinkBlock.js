import {TopicLinkEditor} from './TopicLinkEditor.js';

const {registerBlockType} = wp.blocks;

const BLOCK_NAME = 'planet4-blocks/topic-link';

export const registerTopicLinkBlock = () => {
  registerBlockType(BLOCK_NAME, {
    title: 'Topic Link',
    icon: 'migrate',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    // This attributes definition mimics the one in the PHP side.
    attributes: {
      categoryId: {
        type: 'number',
      },
      categoryLink: {
        type: 'string',
        default: '',
      },
      categoryName: {
        type: 'string',
        default: '',
      },
      imageId: {
        type: 'number',
      },
      imageUrl: {
        type: 'string',
        default: '',
      },
      imageAlt: {
        type: 'string',
        default: '',
      },
      focal_points: {
        type: 'string',
        default: '50% 50%',
      },
    },
    edit: TopicLinkEditor,
    save() {
      return null;
    },
  });
};
