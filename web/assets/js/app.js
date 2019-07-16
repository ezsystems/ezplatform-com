import GoogleAnalyticsService from './GoogleAnalyticsService';

(function() {

    const $ = jQuery,
        locationHash = document.location.hash;

    if (locationHash) {
        $('.nav-pills a[href="' + locationHash + '"]').tab('show');
    }

    $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
        window.location.hash = e.target.hash;
    });

    $('#navbar').on('hidden.bs.collapse', function() {
        $('.navbar-header').removeClass('navbar-show');
        $('#navbar').removeClass('navbar-show').removeClass('navbar-list-show');
    }).on('show.bs.collapse', function() {
        $('.navbar-header').addClass('navbar-show');
        $('#navbar').addClass('navbar-show').addClass('navbar-list-show');
    });

    $('button.load-more').on('click', function() {
        const page = $(this).data('page');
        let order = $('#order_order').val();

        if (order === undefined || order === '') {
            order = 'default';
        }

        const $container = $($(this).data('target')),
            $button = $(this),
            $parent = $(this).parent();

        $button.hide();
        $parent.append('<div class="button-load-more-progress"></div>').fadeIn();

        let url = $(this).data('url');
        url = url + '/' + page + '/' + order;

        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function(data) {
                if (false === data.showLoadMoreButton) {
                    $button.parent().empty().hide();
                }
                $button.data('page', page + 1);
                $container.append(data.html);
                $parent.find('div').remove();
                $button.show();
            },
            error: function() {
                $button.hide();
                $parent.find('div').remove();
                $parent.addClass('button-load-more-error').append('Oh no, something went terribly wrong :-(');
            },
        });
    });

    const packageOrder = document.querySelector('form[name="package_order"] select');

    if (packageOrder) {
        packageOrder.addEventListener('change', (event) => {
            const searchText = document.querySelector('#package-list-search-query').value;
            const page = parseInt(document.querySelector('.pagerfanta span.current').innerHTML);
            const order  = event.target.value || '';
            const category = document.querySelector('.package-list__filters li.active').getAttribute('data-query-param') || 'all';

            if (searchText) {
                window.location.href = `/packages/search/all/${searchText}/${page}/${order}`;
            }
            else {
                window.location.href = `/packages/category/${category}/${page}/${order}`;
            }
        });
    }

    const gaService = new GoogleAnalyticsService();
    const form = document.querySelector('[name="package_search"]');

    gaService.init();

    form && form.addEventListener('submit', (event) => {
        let label;
        event.preventDefault();
        setTimeout(submitForm, 1000);
        let formSubmitted = false;

        function submitForm () {
            if (!formSubmitted) {
                formSubmitted = true;
                form.submit();
            }
        }

        label = form.querySelector('#package-list-search-query').value;

        gaService.sendGARequest(
            {
                hitType: 'event',
                eventCategory: 'Bundles',
                eventAction: 'Search',
                eventLabel: label,
                transport: { hitCallback: submitForm }
            }
        );
    });
})(window, document);
