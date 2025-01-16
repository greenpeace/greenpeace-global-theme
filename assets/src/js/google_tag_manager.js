/* global dataLayer, googleTagManagerData */

const google_tag_value = googleTagManagerData.google_tag_value;
const google_tag_domain = googleTagManagerData.google_tag_domain;
const consent_default_analytics_storage = googleTagManagerData.consent_default_analytics_storage;
const consent_default_ad_storage = googleTagManagerData.consent_default_ad_storage;
const consent_default_ad_user_data = googleTagManagerData.consent_default_ad_user_data;
const consent_default_ad_personalization = googleTagManagerData.consent_default_ad_personalization;
window.dataLayer = window.dataLayer || [];

function gtag() { dataLayer.push(arguments); };

const cookie_content = document.cookie.split(';').map(s => s.trim());
function cookie_contains(value) {
  return cookie_content.indexOf(value) !== -1;
};
const no_track = cookie_contains('no_track=1');
const active_consent = cookie_contains('active_consent_choice=1');
const marketing_consent = !no_track && active_consent &&
      (cookie_contains('greenpeace=2') || cookie_contains('greenpeace=4'));
const analytical_consent = !no_track && active_consent &&
      (cookie_contains('greenpeace=3') || cookie_contains('greenpeace=4'));
const cookie_consent = marketing_consent || analytical_consent;

// If Google Consent Mode is enabled, set default ad storage and analytics storage
// to 'denied' as first action on every page until consent is given.
// If consent given, update consent on every page.
if (googleTagManagerData.cookies) {
  let capabilities = {
    ad_storage: consent_default_ad_storage,
    ad_user_data: consent_default_ad_user_data,
    ad_personalization: consent_default_ad_personalization,
    analytics_storage: consent_default_analytics_storage,
  };

  if (cookie_consent) {
    capabilities = {
      ad_storage: marketing_consent ? 'granted' : 'denied',
      ad_user_data: marketing_consent ? 'granted' : 'denied',
      ad_personalization: marketing_consent ? 'granted' : 'denied',
      analytics_storage: analytical_consent ? 'granted' : 'denied',
    };
  }
  gtag('consent', 'default', capabilities);
  gtag('set', 'url_passthrough', true);
  gtag('set', 'ads_data_redaction', capabilities.ad_storage === 'denied');
  dataLayer.push({event: 'defaultConsent', ...capabilities});
}

dataLayer.push({
  'pageType' : '{{ page_category }}',
  'signedIn' : '{{ p4_signedin_status }}',
  'visitorType' : '{{ p4_visitor_type }}',
  'userID' : '',
  'post_tags': '{{ post_tags }}',
  'gPlatform': 'Planet 4',
  'p4_blocks': '{{ p4_blocks }}',
  'post_categories': '{{ post_categories }}',
  // {{ reading_time ? "'reading_time': #{reading_time},"|raw : '' }}
  // {{ page_date ? "'page_date': '#{page_date}',"|raw : '' }}
});

console.log(googleTagManagerData.page_category);

// {% if google_tag_value %}
//     var google_tag_value = '{{ google_tag_value }}';
//     var google_tag_domain = '{{ google_tag_domain }}';
//     var consent_default_analytics_storage = '{{ consent_default_analytics_storage }}';
//     var consent_default_ad_storage = '{{ consent_default_ad_storage }}';
//     var consent_default_ad_user_data = '{{ consent_default_ad_user_data }}';
//     var consent_default_ad_personalization = '{{ consent_default_ad_personalization }}';
//     window.dataLayer = window.dataLayer || [];

//     function gtag() { dataLayer.push(arguments); };

//     var cookie_content = document.cookie.split(';').map(s => s.trim());
//     function cookie_contains(value) {
//       return cookie_content.indexOf(value) !== -1;
//     };
//     var no_track = cookie_contains('no_track=1');
//     var active_consent = cookie_contains('active_consent_choice=1');
//     var marketing_consent = !no_track && active_consent
//       && (cookie_contains('greenpeace=2') || cookie_contains('greenpeace=4'));
//     var analytical_consent = !no_track && active_consent
//       && (cookie_contains('greenpeace=3') || cookie_contains('greenpeace=4'));
//     var cookie_consent = marketing_consent || analytical_consent;

//     {#
//       If Google Consent Mode is enabled,
//       set default ad storage and analytics storage to 'denied' as first action on every page until consent is given.
//       If consent given, update consent on every page.
//     #}
//     {% if cookies.enable_google_consent_mode %}
//     if ( cookie_consent ) {
//       var capabilities = {
//         ad_storage: marketing_consent ? 'granted' : 'denied',
//         ad_user_data: marketing_consent ? 'granted' : 'denied',
//         ad_personalization: marketing_consent ? 'granted' : 'denied',
//         analytics_storage: analytical_consent ? 'granted' : 'denied'
//       };
//     } else {
//       var capabilities = {
//         ad_storage: consent_default_ad_storage,
//         ad_user_data: consent_default_ad_user_data,
//         ad_personalization: consent_default_ad_personalization,
//         analytics_storage: consent_default_analytics_storage
//       };
//     }
//     gtag('consent', 'default', capabilities);
//     gtag('set', 'url_passthrough', true);
//     gtag('set', 'ads_data_redaction', capabilities.ad_storage === 'denied');
//     dataLayer.push({event: 'defaultConsent', ...capabilities});
//     {% endif %}

//     dataLayer.push({
//       'pageType' : '{{ page_category }}',
//       'signedIn' : '{{ p4_signedin_status }}',
//       'visitorType' : '{{ p4_visitor_type }}',
//       'userID' : '',
//       'post_tags': '{{ post_tags }}',
//       'gPlatform': 'Planet 4',
//       'p4_blocks': '{{ p4_blocks }}',
//       'post_categories': '{{ post_categories }}',
//       {{ reading_time ? "'reading_time': #{reading_time},"|raw : '' }}
//       {{ page_date ? "'page_date': '#{page_date}',"|raw : '' }}
//     });

//     {% if not post.password_required %}
//       var cf_campaign_name = '{% if cf_campaign_name is defined and cf_campaign_name is not null %}{{ cf_campaign_name|raw }}{% endif %}';
//       var cf_project_id    = '{% if cf_project_id is defined and cf_project_id is not null %}{{ cf_project_id|raw }}{% endif %}';
//       var cf_local_project = '{% if cf_local_project is defined and cf_local_project is not null %}{{ cf_local_project|raw }}{% endif %}';
//       var cf_basket_name   = '{% if cf_basket_name is defined and cf_basket_name is not null %}{{ cf_basket_name|raw }}{% endif %}';
//       var cf_scope         = '{% if cf_scope is defined and cf_scope is not null %}{{ cf_scope|raw }}{% endif %}';
//       var cf_department    = '{% if cf_department is defined and cf_department is not null %}{{ cf_department|raw }}{% endif %}';

//       if ( cf_campaign_name || cf_basket_name || cf_scope || cf_department ) {
//         dataLayer.push({
//           'gCampaign' : cf_campaign_name,
//           'gLocalProject' : cf_local_project,
//           'projectID' : cf_project_id,
//           'gBasket' : cf_basket_name,
//           'gScope': cf_scope,
//           'gDepartment': cf_department,
//         });
//       }
//     {% endif %}

//     var gtm_allow = '{{ enforce_cookies_policy }}' ? cookie_consent : true;

//     if ( google_tag_value && gtm_allow ) {
//       (function (w, d, s, l, i) {
//         w[l] = w[l] || [];
//         w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
//         var f = d.getElementsByTagName(s)[0],
//             j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
//         j.async = true;
//         j.src = 'https://' + google_tag_domain + '/gtm.js?id=' + i + dl;
//         f.parentNode.insertBefore(j, f);
//       })(window, document, 'script', 'dataLayer', google_tag_value);
//     }
// {% endif %}
