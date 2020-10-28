import { setupCookies } from './cookies';
import { setupAuthor } from './author';
import { setupCommentsAnchor } from './comments_anchor';
import { setupCountrySelect } from './country_select';
import { setImageTitlesFromAltText } from './global';
import { setupHeader } from './header';
import { setupImageAlign } from './img_align';
import { setupLoadMore } from './load_more';
import { setupPDFIcon } from './pdf_icon';
import { setupSearch } from './search';
import { setupLightBox } from './lightbox';
import { setupExternalLinks } from './external_links';
import { setupCSSVarsPonyfill } from './cssvarsponyfill';
import { setupEnhancedDonateButton } from './enhancedDonateButton';

import 'bootstrap';

function requireAll(r) {
  r.keys().forEach(r);
}

requireAll(require.context('../scss/styleguide/src/icons/', true, /\.svg$/));

window.$ = $ || jQuery;

jQuery(function($) {
  setupCookies($);
  setupAuthor($);
  setupCommentsAnchor($);
  setupCountrySelect($);
  setImageTitlesFromAltText($);
  setupHeader($);
  setupImageAlign($);
  setupLoadMore($);
  setupPDFIcon($);
  setupSearch($);
  setupLightBox($);
  setupExternalLinks($);
  setupCSSVarsPonyfill();
  setupEnhancedDonateButton();
});
