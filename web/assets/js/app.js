$(document).ready(function(){
    var locationHash = document.location.hash;

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
       var url = $(this).data('url');
       var page = $(this).data('page');
       var $container = $($(this).data('target'));
       var $button = $(this);
       var $parent = $(this).parent();

       $button.hide();
       $parent.append('<div class="button-load-more-progress"></div>').fadeIn();

        $.ajax({
            type: 'GET',
            url: url + '/' + page,
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
});
