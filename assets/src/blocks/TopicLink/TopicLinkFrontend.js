const {sprintf, __} = wp.i18n;

/**
 * TopicLinkFrontend component for rendering the Topic Link block on the frontend.
 *
 * @param {Object} props              - Component properties.
 * @param {string} props.imageUrl     - The URL of the background image.
 * @param {string} props.imageAlt     - The alt text for the background image.
 * @param {string} props.categoryName - The name of the selected category.
 * @param {string} props.categoryLink - The URL link to the selected category.
 * @param {string} props.focal_points - CSS-compatible object position string (e.g., "50% 50%").
 * @return {JSX.Element}               - The Topic Link Frontend component.
 */
export const TopicLinkFrontend = ({
  imageUrl,
  imageAlt,
  categoryName,
  categoryLink,
  focal_points,
}) => {
  return (
    <section className="topic-link-block block">
      <a href={categoryLink} target="_self">
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
      </a>
    </section>
  );
};
