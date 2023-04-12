const {__} = wp.i18n;

export const setupExternalLinks = () => {
  const siteURL = window.location.host;

  const linkSelector = ['.page-content', 'article'].map(sel => `${sel} a:not(.btn):not(.cover-card-heading):not(.wp-block-button__link):not(.share-btn):not([href*="${siteURL}"]):not([href*=".pdf"]):not([href^="/"]):not([href^="#"]):not([href^="javascript:"])`).join(', ');
  const links = [...document.querySelectorAll(linkSelector)];

  links.forEach(link => {
    if (link.matches('.boxout *')) {
      return;
    }
    // We don't want to show the icon in headings/titles,
    // or in links that are images
    const text = link.textContent || link.innerText;
    if (['H1', 'H2', 'H3', 'H4', 'H5', 'H6'].includes(link.parentElement.nodeName) || text.trim().length === 0) {
      return;
    }

    link.target = link.target ? link.target : '';
    link.classList.add('external-link');

    const url = new URL(link.href);
    const domain = url.hostname.replace('www.', '');

    link.title = __('This link will lead you to ' + domain, 'planet4-master-theme');
  });
};
