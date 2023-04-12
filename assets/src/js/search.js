/* global localizations */

const showHiddenRow = row => {
  if (!row) {
    return;
  }
  row.classList.remove('row-hidden');
  row.style.display = 'block';
};

const addSelectedFiltersToForm = (isModal, idToRemove) => {
  const searchForm = document.getElementById('search_form');
  let selectedFilters = [...document.querySelectorAll(
    `input[name^="f["]${isModal ? '.modal-checkbox' : ':not(.modal-checkbox)'}:checked`
  )];

  if (idToRemove) {
    selectedFilters = selectedFilters.filter(selectedFilter => selectedFilter.value !== idToRemove);
  }

  selectedFilters.forEach(selectedFilter => {
    const selectedFilterCopy = selectedFilter.cloneNode(true);
    selectedFilterCopy.style.display = 'none';
    searchForm.appendChild(selectedFilterCopy);
  });
};

// Search page.
export const setupSearch = () => {
  const isSearch = !!document.querySelector('body.search');
  if (!isSearch) {
    return;
  }

  // Needed form and inputs
  const searchForm = document.getElementById('search_form');
  const orderInput = document.getElementById('orderby');

  // Submit form on Sort change event.
  const orderSelect = document.getElementById('select_order');
  if (orderSelect) {
    orderSelect.onchange = () => {
      orderInput.value = orderSelect.value;
      searchForm.submit();
    };
  }

  // Submit form on filter click event.
  const filterInputs = [...document.querySelectorAll('input[name^="f["]:not(.modal-checkbox)')];
  filterInputs.forEach(filterInput => {
    filterInput.onclick = () => {
      addSelectedFiltersToForm(false);
      searchForm.submit();
    };
  });

  // Submit form on Apply filters button click event.
  const applyFiltersButton = document.querySelector('.applybtn');
  applyFiltersButton.onclick = () => {
    addSelectedFiltersToForm(true);
    searchForm.submit();
  };

  // Clear single selected filter.
  const activeFilterTags = [...document.querySelectorAll('.activefilter-tag')];
  activeFilterTags.forEach(activeFilterTag => {
    const filterId = activeFilterTag.dataset.id;

    activeFilterTag.onclick = () => {
      addSelectedFiltersToForm(false, filterId);
      searchForm.submit();
    };
  });

  // Clear all selected filters.
  const clearAllButton = document.querySelector('.clearall');
  if (clearAllButton) {
    clearAllButton.onclick = () => {
      const selectedFilters = [...document.querySelectorAll('input[name^="f["]:checked')];
      selectedFilters.forEach(selectedFilter => {
        selectedFilter.checked = false;
      });
      searchForm.submit();
    };
  }

  // Add click event for load more button in blocks.
  const navSearchInput = document.getElementById('search_input');
  const loadMoreButton = document.querySelector('.btn-load-more-click-scroll');
  if (loadMoreButton) {
    loadMoreButton.onclick = () => {
      const {total_posts, posts_per_load, current_page} = loadMoreButton.dataset;
      const nextPage = parseInt(current_page) + 1;
      loadMoreButton.dataset.current_page = nextPage;

      const url = new URL(localizations.ajaxurl);
      url.searchParams.append('action', 'get_paged_posts');
      url.searchParams.append('search_query', navSearchInput.value.trim());
      url.searchParams.append('paged', nextPage);
      url.searchParams.append('orderby', orderInput.value);
      url.searchParams.append('query-string', decodeURIComponent(location.search).substring(1)); // Ignore the ? in the search url (first char)

      fetch(url)
        .then(response => response.text())
        .then(html => {
          // Append the response at the bottom of the results and then show it.
          const searchResults = document.querySelector('.multiple-search-result .list-unstyled');
          searchResults.innerHTML += html;

          const hiddenRow = document.querySelector('.row-hidden:last-child');
          showHiddenRow(hiddenRow);

          if (posts_per_load * nextPage > total_posts) {
            loadMoreButton.style.display = 'none';
          }
        }).catch(error => {
          console.log(error); //eslint-disable-line no-console
        });
    };
  }

  // Reveal more results just by scrolling down the first 'show_scroll_times' times.
  let loadMoreCount = 0;
  let loadedMore = false;
  window.onscroll = () => {
    if (!loadMoreButton) {
      return;
    }

    const elementTop = loadMoreButton.offsetTop;
    const elementHeight = loadMoreButton.clientHeight;
    const windowHeight = window.innerHeight;
    const windowScroll = window.scrollY;
    const loadEarlierOffset = 250;

    const {posts_per_load, total_posts} = loadMoreButton.dataset;

    if (loadMoreCount < localizations.show_scroll_times) {
      // If next page has not loaded then load next page as soon as scrolling
      // reaches 'loadEarlierOffset' pixels before the Load more button.
      if (!loadedMore &&
        windowScroll > (elementTop + elementHeight - windowHeight - loadEarlierOffset) &&
        (loadMoreCount + 1) * posts_per_load < total_posts) {
        loadMoreCount += 1;
        loadMoreButton.click();
        loadedMore = true;

        // Add a throttle to avoid multiple scroll events from firing together.
        setTimeout(() => {
          loadedMore = false;
        }, 500);
      }
      if (windowScroll > (elementTop + elementHeight - windowHeight)) {
        const hiddenRows = [...document.querySelectorAll('.row-hidden')];
        hiddenRows.forEach(showHiddenRow);
      }
    }
    return false;
  };
};
