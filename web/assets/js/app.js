jQuery(document).ready(function(){
    const $ = jQuery;
    // credits to http://codyhouse.co/gem/css-jquery-image-comparison-slider/
    const drags = function (dragElement, resizeElement, container) {
        dragElement.on('mousedown vmousedown', function (e) {
            dragElement.addClass('draggable');
            resizeElement.addClass('resizable');
            const dragWidth = dragElement.outerWidth(),
                xPosition = dragElement.offset().left + dragWidth - e.pageX,
                containerOffset = container.offset().left,
                containerWidth = container.outerWidth(),
                minLeft = containerOffset + 10,
                maxLeft = containerOffset + containerWidth - dragWidth - 10;
            dragElement.parents().on('mousemove vmousemove', function (e) {
                let leftValue = e.pageX + xPosition - dragWidth;
                if(leftValue < minLeft ) {
                    leftValue = minLeft;
                } else if ( leftValue > maxLeft) {
                    leftValue = maxLeft;
                }
                const widthValue = (leftValue + dragWidth/2 - containerOffset)*100/containerWidth+'%';
                $('.draggable').css('left', widthValue).on('mouseup vmouseup', function () {
                    $(this).removeClass('draggable');
                    resizeElement.removeClass('resizable');
                });
                $('.resizable').css('width', widthValue);
            }).on('mouseup vmouseup', function (e){
                dragElement.removeClass('draggable');
                resizeElement.removeClass('resizable');
            });
            e.preventDefault();
        }).on('mouseup vmouseup', function (e) {
            dragElement.removeClass('draggable');
            resizeElement.removeClass('resizable');
        });
    };

    $('.cd-resize-img img').width($('.cd-image-container img').css('width'));

    drags($('.cd-handle'), $('.cd-resize-img'), $('.cd-image-container'));
    $('.cd-image-container').addClass('is-visible');

    $(window).on('resize', function () {
        $('.cd-resize-img img').width($('.cd-image-container img').css('width'));
    });

    const locationHash = document.location.hash;

    if (locationHash) {
        $('.nav-pills a[href="'+locationHash+'"]').tab('show');
    }
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        window.location.hash = e.target.hash;
    });

    $('#navbar').on('hidden.bs.collapse', function () {
        $('.navbar-header').removeClass('navbar-show');
        $('#navbar').removeClass('navbar-show').removeClass('navbar-list-show');
    }).on('show.bs.collapse', function () {
        $('.navbar-header').addClass('navbar-show');
        $('#navbar').addClass('navbar-show').addClass('navbar-list-show');
    });

    $("button.load-more").on('click', function() {
       const page = $(this).data('page');
       let order = $("#order_order").val();

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
            }
        })
    });

    $('form[name="package_order"] select').change(function() {
        const searchText = $("#package-list-search-query").val(),
        page = parseInt($(".pagerfanta span.current").html()),
        order  = $(this).val(),
        category = $('.package-list__filters li.active').data('query-param');

        let orderQueryParam = $(this).val() ? `/${order}` : '',
        categoryQueryParam = typeof category !== "undefined" ? `${category}/` : 'all/';

        if (searchText) {
            window.location.href = `/packages/search/all/${searchText}/${page}${orderQueryParam}`;
        }
        else {
            window.location.href = `/packages/category/${categoryQueryParam}${page}${orderQueryParam}`;
        }
    });

    $('#top-banner .ezrichtext-field .ezbutton').click(function() {
        $('#download a[href="#composer-option"]').click();
    });

    // Google Analytics
    const gaItem = function(selector, type, category, action, label) {
        this.selector = selector;
        this.type = type;
        this.category = category;
        this.action = action;
        this.label_parent = label && label[0] || '';
        this.label_selector = label && label[1] || '';
    };

    const gaEvents = [
        new gaItem('a.btn.download', 'event', 'Main block', 'Go to download'),
        new gaItem('.cd-handle', 'event', 'Main block', 'Slider'),
        new gaItem('#download a[href="#composer-option"]', 'event', 'Downloads', 'Composer tab'),
        new gaItem('#download #composer-option .description a', 'event', 'Downloads', 'Composer documentation'),
        new gaItem('#download #composer-option .launch .btn', 'event', 'Downloads', 'Install with Composer'),
        new gaItem('#download a[href="#ezlaunchpad-option"]', 'event', 'Downloads', 'eZ launchpad tab'),
        new gaItem('#download #ezlaunchpad-option .description a', 'eZ launchpad documentation'),
        new gaItem('#download #ezlaunchpad-option .window .content', 'event', 'Downloads', 'eZ launchpad documentation widget'),
        new gaItem('#download a[href="#platform-sh-option"]', 'event', 'Downloads', 'platform.sh tab'),
        new gaItem('#download #platform-sh-option .launch .btn', 'event', 'Downloads', 'platform.sh deploy'),
        new gaItem('#download a[href="#download-option"]', 'event', 'Downloads', 'Download tab'),
        new gaItem('#download #download-option .download-table tbody tr', 'event', 'Downloads', 'Download code'),
        new gaItem('#download #download-option .download-table tbody tr', 'event', 'Downloads', 'Download', ['', 'th[scope="row"] span']),
        new gaItem('#download #download-option .download-table tbody tr.ezpublish a', 'event', 'Downloads', 'Download eZ Publish'),
        new gaItem('.bundles-list-content button.load-more', 'event', 'Bundles', 'Load More'),
        new gaItem('.bundles-list-content .bundle-card-line-href a', 'event', 'Bundles', 'Bundle Click', ['.bundle-card-line', 'h2 .ezstring-field'])
    ];

    $(gaEvents).each(function (i, item) {
        $(item.selector).on('click', function() {
            let labelText;

            if (item.label_selector.length === 0) {
                ga('send', item.type, item.category, item.action);
                return;
            }

            if (item.label_parent.length > 0) {
                labelText = $(this).parents(item.label_parent).find(item.label_selector).html();
            } else {
                labelText = $(this).find(item.label_selector).html();
            }
            ga('send', item.type, item.category, item.action, labelText);
        });
    });

    const form = document.getElementsByName('bundle_search')[0];
    form && form.addEventListener('submit', function(event) {
        let label;
        event.preventDefault();
        setTimeout(submitForm, 1000);
        let formSubmitted = false;

        function submitForm() {
            if (!formSubmitted) {
                formSubmitted = true;
                form.submit();
            }
        }

        label = $('#bundles-list-search-query').val();
        ga('send', 'event', 'Bundles', 'Search', label, { hitCallback: submitForm });
    });
});
