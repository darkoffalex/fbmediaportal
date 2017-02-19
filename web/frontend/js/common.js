$(document).ready(function () {

    /**
     * Reloading links (updates container's html via ajax)
     */
    $(document).on('click','[data-click-load]', function(){

        var confirmMsg = $(this).data('confirm-ajax');

        if(confirmMsg){
            if(!confirm(confirmMsg)){
                return false;
            }
        }

        var container = $($(this).data('click-load'));
        var link = $(this);

        $.ajax({
            url: link.attr('href'),
            type: 'GET',
            async: false,
            success: function(reloaded_data){
                container.html(reloaded_data);
            }
        });

        return false;
    });

    /**
     * When scrolled to the bottom
     */
    $(document).scroll(function () {
        if(($(window).scrollTop()+$(window).height())+450 >= $(document).height()){

            $('[data-postload]').each(function () {

                var url = $(this).data('postload');
                var page = parseInt($(this).data('current-page'));
                var container = $(this);

                if(!container.hasClass('no-load')){
                    container.addClass('no-load');

                    $.ajax({
                        url: url+'?page='+(page+1),
                        type: 'GET',
                        async: false,
                        success: function(data){
                            container.data('current-page',(page+1));
                            if(data != ''){
                                container.removeClass('no-load');
                                container.append(data);
                            }
                        }
                    });
                }


            })
        }
    });

    /**
     * Simple client validation (non-empty fields required)
     */
    $(document).on('click','[data-no-empty]',function () {
        var ok = true;

        $($(this).data('no-empty')).find('input, textarea').each(function () {
            if($(this).val() == ''){
                ok = false;
                $(this).addClass('has-error');
            }else{
                $(this).removeClass('has-error');
            }
        });

        return ok;
    });

    /**
     * Adding children comment via ajax
     */
    $(document).on('submit','.contentComments__card__child',function () {

        var ok = true;
        var url = $(this).attr('action');
        var container = $($(this).data('container'));
        var form = $(this);

        $(this).find('input, textarea').each(function () {
            if($(this).val() == ''){
                ok = false;
                $(this).addClass('has-error');
            }else{
                $(this).removeClass('has-error');
            }
        });

        if(ok){
            var serialized = $(this).serialize();
            $.ajax({
                url: url,
                data: serialized,
                type: 'POST',
                async: false,
                success: function(data){
                    if(data != ''){
                        container.html(data);
                        form[0].reset();
                    }
                }
            });
        }

        return false;

    });

    /**
     * When changed carousel
     */
    $('.topCarousel > div').on('beforeChange', function(event, slick, currentSlide, nextSlide){

        var url = $(this).data('loading');
        var page = parseInt($(this).data('current-page'));
        var carousel = $(this);

        var delta = nextSlide - currentSlide;
        if(delta > 1){
            //TODO: prevent
        }

        if(!carousel.hasClass('no-load')) {
            carousel.addClass('no-load');
            $.ajax({
                url: url + '&page=' + (page + 1),
                type: 'GET',
                async: false,
                success: function (data) {
                    carousel.data('current-page', (page + 1));
                    if (data != '') {
                        carousel.removeClass('no-load');
                        carousel.slick('slickAdd',data);
                    }
                }
            });
        }
    });

    /**
     * Adding children comment via ajax
     */
    $(document).on('change','[data-reloading-select]',function () {
        var name = $(this).attr('name');
        var value = $(this).val();
        var url = $(this).data('reloading-select')+'?'+name+'='+value;

        var link = document.createElement('a');
        link.href = url;
        document.body.appendChild(link);
        link.click();
    });
});
