<script>
    (function($) {
        $(function() {
            // Smart Search Functionality
            let searchTimeout;
            const searchDelay = 300;

            function initSmartSearch(inputId, resultsId) {
                const searchInput = $(`#${inputId}`);
                const searchResults = $(`#${resultsId}`);
                let activeIndex = -1;

                searchInput.on('input', function() {
                    clearTimeout(searchTimeout);
                    const query = $(this).val().trim();

                    if (query.length < 2) {
                        searchResults.hide().empty();
                        activeIndex = -1;
                        return;
                    }

                    searchTimeout = setTimeout(function() {
                        performSearch(query, searchResults);
                    }, searchDelay);
                });

                searchInput.on('keydown', function(e) {
                    const items = searchResults.find('.search-result-item');
                    if (!items.length) return;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        activeIndex = (activeIndex + 1) % items.length;
                        updateActiveItem(items);
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        activeIndex = (activeIndex - 1 + items.length) % items.length;
                        updateActiveItem(items);
                    } else if (e.key === 'Enter') {
                        if (activeIndex >= 0) {
                            e.preventDefault();
                            items.eq(activeIndex)[0].click();
                        }
                    } else if (e.key === 'Escape') {
                        searchResults.hide();
                    }
                });

                function updateActiveItem(items) {
                    items.removeClass('active');
                    if (activeIndex >= 0) {
                        const activeItem = items.eq(activeIndex);
                        activeItem.addClass('active');

                        // Scroll into view if needed
                        const container = searchResults[0];
                        const item = activeItem[0];
                        if (item.offsetTop < container.scrollTop) {
                            container.scrollTop = item.offsetTop;
                        } else if (item.offsetTop + item.offsetHeight > container.scrollTop + container.offsetHeight) {
                            container.scrollTop = item.offsetTop + item.offsetHeight - container.offsetHeight;
                        }
                    }
                }

                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.search-form').length) {
                        searchResults.hide();
                    }
                });

                searchInput.on('focus', function() {
                    if ($(this).val().trim().length >= 2 && searchResults.children().length > 0) {
                        searchResults.show();
                    }
                });
            }

            function performSearch(query, resultsContainer) {
                $.ajax({
                    url: '{{ route("search.pages") }}',
                    method: 'GET',
                    data: {
                        q: query
                    },
                    success: function(results) {
                        displaySearchResults(results, resultsContainer);
                    },
                    error: function(xhr) {
                        console.error('Search error:', xhr);
                    }
                });
            }

            function displaySearchResults(results, container) {
                container.empty();

                if (results.length === 0) {
                    container.html(`
                    <div class="search-no-results">
                        <i class="fa fa-search text-muted mb-2"></i>
                        <p class="mb-0">No pages found</p>
                    </div>
                `).show();
                    return;
                }

                let html = '<div class="search-results-list">';
                const grouped = {};
                results.forEach(result => {
                    if (!grouped[result.category]) {
                        grouped[result.category] = [];
                    }
                    grouped[result.category].push(result);
                });

                Object.keys(grouped).forEach(category => {
                    html += `<div class="search-category">${category}</div>`;
                    grouped[category].forEach(result => {
                        html += `
                        <a href="${result.url}" class="search-result-item">
                            <div class="search-result-icon">
                                <i class="fa fa-${result.icon}"></i>
                            </div>
                            <div class="search-result-content">
                                <div class="search-result-title">${result.title}</div>
                                <div class="search-result-desc">${result.description}</div>
                            </div>
                            <div class="search-result-arrow">
                                <i class="fa fa-arrow-right"></i>
                            </div>
                        </a>
                    `;
                    });
                });

                html += '</div>';
                container.html(html).show();
            }

            initSmartSearch('navbar-search-desktop', 'search-results-desktop');
            initSmartSearch('navbar-search-mobile', 'search-results-mobile');

            // Global Search Shortcut (Ctrl/Cmd + K)
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    $('#navbar-search-desktop').focus();
                }
            });
        });
    })(jQuery);
</script>

<style>
    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        margin-top: 8px;
        max-height: 500px;
        overflow-y: auto;
        z-index: 1050;
        border: 1px solid #e9ecef;
    }

    .search-results-list {
        padding: 8px 0;
    }

    .search-category {
        padding: 8px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.5px;
        background-color: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    .search-category:first-child {
        border-top: none;
    }

    .search-result-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }

    .search-result-item:hover,
    .search-result-item.active {
        background-color: #f8f9fa;
        border-left-color: #7366ff;
    }

    .search-result-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: linear-gradient(135deg, #7366ff 0%, #9e8cfc 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .search-result-icon i {
        color: white;
        font-size: 16px;
    }

    .search-result-content {
        flex: 1;
        min-width: 0;
    }

    .search-result-title {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .search-result-desc {
        font-size: 12px;
        color: #6c757d;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .search-result-arrow {
        margin-left: 12px;
        color: #adb5bd;
        font-size: 12px;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .search-result-item:hover .search-result-arrow {
        opacity: 1;
    }

    .search-no-results {
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }

    .search-no-results i {
        font-size: 32px;
        display: block;
    }

    .search-no-results p {
        font-size: 14px;
        margin-top: 8px;
    }

    @media (max-width: 767.98px) {
        .search-results-dropdown {
            position: fixed;
            left: 10px;
            right: 10px;
            top: 70px;
            max-height: calc(100vh - 100px);
        }
    }

    .search-results-dropdown::-webkit-scrollbar {
        width: 6px;
    }

    .search-results-dropdown::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .search-results-dropdown::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .search-results-dropdown::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .search-form {
        position: relative;
    }
</style>