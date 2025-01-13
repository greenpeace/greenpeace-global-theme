const {useSelect} = wp.data;
const {
  BlockControls,
  MediaUpload,
  MediaUploadCheck,
  InspectorControls,
} = wp.blockEditor;
const {
  SelectControl,
  PanelBody,
  ToolbarGroup,
  ToolbarButton,
  FocalPointPicker,
} = wp.components;
const {sprintf, __} = wp.i18n;

/**
 * TopicLinkEditor component for managing the Topic Link block in the editor.
 *
 * @param {Object}   props               - Component properties.
 * @param {Object}   props.attributes    - Block attributes.
 * @param {boolean}  props.isSelected    - Indicates if the block is selected.
 * @param {Function} props.setAttributes - Function to update block attributes.
 * @return {JSX.Element}                  - The Topic Link Editor component.
 */
export const TopicLinkEditor = ({
  attributes,
  isSelected,
  setAttributes,
}) => {
  const {
    categoryId,
    categoryName,
    focal_points,
    imageId: customImageId,
    imageUrl: customImageUrl,
  } = attributes;

  const {
    categoriesList,
    imageId,
    imageUrl,
    imageAlt,
    currentPostCategories,
  } = useSelect(select => {
    const categories = [].concat(
      select('core').getEntityRecords('taxonomy', 'category', {
        hide_empty: true,
        per_page: -1,
      }) || []
    );

    const customImage = customImageId ? select('core').getMedia(customImageId) : null;
    const fetchedImageUrl = customImage?.source_url || '';
    const fetchedImageId = customImageId || null;
    const fetchedImageAlt = customImage?.alt_text || '';
    const currentCategories = select('core/editor').getCurrentPost().categories || [];

    return {
      categoriesList: categories,
      imageId: fetchedImageId,
      imageUrl: fetchedImageUrl,
      imageAlt: fetchedImageAlt,
      currentPostCategories: currentCategories,
    };
  }, [customImageId, customImageUrl]);

  if (!categoriesList.length) {
    return __('Populating block\'s fields…', 'planet4-blocks-backend');
  }

  /**
   * Sets the block's category based on available categories or current post categories.
   */
  const setBlockCategory = () => {
    const postCategory = categoriesList.find(category => category.id === currentPostCategories[0]);
    const blockCategory = categoriesList.find(category => category.id === categoryId);

    let categoryData = categoriesList[0];

    if (blockCategory) {
      categoryData = blockCategory;
    } else if (postCategory) {
      categoryData = postCategory;
    }

    setAttributes({categoryId: parseInt(categoryData.id)});
    setAttributes({categoryLink: categoryData?.link || ''});
    setAttributes({categoryName: categoryData?.name || ''});
  };

  /**
   * Sets the object's focal position as a CSS-compatible value.
   *
   * @param {Object} focalPoints - Object containing x and y focal point values.
   * @return {void}
   */
  const setObjectPosition = focalPoints => {
    if (!focalPoints) {
      return '50% 50%';
    }
    const floatX = parseFloat(focalPoints.x).toFixed(2);
    const floatY = parseFloat(focalPoints.y).toFixed(2);
    setAttributes({focal_points: `${floatX * 100}% ${floatY * 100}%`});
  };

  /**
   * Converts a focal point string into an object with x and y properties.
   *
   * @param {string} focalPoints - The focal points string (e.g., "50% 50%").
   * @return {Object}            - An object with x and y properties as decimal values.
   */
  const getFocalPoint = focalPoints => {
    const [x, y] = focalPoints.split(' ').map(value => parseFloat(value) / 100);
    return {x, y};
  };

  setBlockCategory();

  // Update attributes with image data for frontend
  setAttributes({
    imageUrl: imageUrl || '',
    imageAlt: imageAlt || '',
  });

  /**
   * Renders the block preview in the editor.
   * @return {JSX.Element} The block preview element.
   */
  const renderEditInPlace = () => (
    <section className="topic-link-block block">
      <div className="topic-link-block_editor-container">
        <div className="background-image">
          {imageUrl && (
            <img
              src={imageUrl}
              alt={imageAlt}
              style={{objectPosition: focal_points}}
            />
          )}
        </div>
        <div className="topic-link-content">
          {
            // translators: %s: Category name
            <p>{sprintf(__('Learn more about %s', 'planet4-master-theme-backend'), categoryName)}</p>
          }
        </div>
      </div>
    </section>
  );

  /**
   * Renders the Inspector Controls for the block.
   * @return {JSX.Element} The Inspector Controls component.
   */
  const addInspectorControls = () => (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        <SelectControl
          label={__('Select Category:', 'planet4-blocks-backend')}
          value={categoryId}
          options={[...categoriesList.map(category => ({label: category.name, value: category.id}))]}
          onChange={id => setAttributes({categoryId: parseInt(id)})}
        />
        {imageUrl && (
          <div className="wp-block-master-theme-gallery__FocalPointPicker">
            <strong className="components-base-control__help">
              {__('Select image focal point', 'planet4-blocks-backend')}
            </strong>
            <p className="components-base-control__help">
              {__('Adjust the “Left” and “Top” fields to position the focal point of the image in percentage, where 0% is the top/left edge and 100% is the bottom/right edge.', 'planet4-blocks-backend')}
            </p>
            <FocalPointPicker
              url={imageUrl}
              dimensions={{width: 400, height: 100}}
              value={getFocalPoint(focal_points)}
              onChange={setObjectPosition}
            />
          </div>
        )}
      </PanelBody>
    </InspectorControls>
  );

  /**
   * Renders the Block Controls toolbar.
   * @return {JSX.Element} The Block Controls component.
   */
  const addBlockControls = () => (
    <BlockControls>
      <ToolbarGroup>
        <MediaUploadCheck>
          <MediaUpload
            onSelect={({id}) => setAttributes({imageId: id})}
            allowedTypes={['image']}
            value={imageId}
            type="image"
            render={({open}) => (
              <ToolbarButton
                className="components-icon-button components-toolbar__control"
                label={imageId ? __('Change Background Image', 'planet4-blocks-backend') : __('Select Background Image', 'planet4-blocks-backend')}
                onClick={open}
                icon={imageId ? 'edit' : 'upload'}
              />
            )}
          />
        </MediaUploadCheck>
        {imageId && (
          <ToolbarButton
            className="components-icon-button components-toolbar__control"
            label={__('Remove Image', 'planet4-blocks-backend')}
            onClick={() => setAttributes({imageId: null})}
            icon="trash"
          />
        )}
      </ToolbarGroup>
    </BlockControls>
  );

  return (
    <>
      {isSelected && addInspectorControls()}
      {isSelected && addBlockControls()}
      {renderEditInPlace()}
    </>
  );
};
