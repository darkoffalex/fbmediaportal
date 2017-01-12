/**
 * Rebuilds branches for category table (to show nesting levels)
 */
var rebuildBranches = function(){
    $(".branch-line-root").remove();
    $(".build-branches").each(function(){
        var parent = $('[data-id="'+$(this).data('parent')+'"]');

        if(parent.length > 0){
            var calcHeight = ($(this).offset().top - parent.offset().top);

            var connectorHtml = "<i class='branch-line-root' style='height: "+calcHeight+"px;'>";
            $(this).find(".connector-categories").append(connectorHtml);
        }
    })
};

$(document).ready(function () {

    /************************* N E S T E D  C A T E G O R Y  M U L T I P L E  S E L E C T O R *************************/

    /**
     * setting tag for category selector field
     * @param categoryId
     * @param categoryName
     * @param selector
     */
    var setTag = function(categoryId,categoryName,selector)
    {
        var tags = $(selector);

        if(tags.find('[data-category-id='+categoryId+']').length == 0){
            tags.append('<span class="label label-primary margin-r-5">'+categoryName+' <span class="fa fa-close icon-pointer" data-remove data-category-id="'+categoryId+'"></span><input type="hidden" name="Post[categoriesChecked][]" value="'+categoryId+'"></span>');
        }else{
            tags.find('[data-category-id='+categoryId+']').parent().remove();
        }
    };

    /**
     * when pressed on x icon on tag
     */
    $(document).on('click','[data-remove]',function(){
        $(this).parent().remove();
        return false;
    });

    /**
     * redefine drop-down events (to implement adding tags functionality)
     */
    setTimeout(function(){

        //clicking on items with sub-items within
        $('ul.dropdown-menu [data-toggle=dropdown]').off('click').on('mouseover', function (event) {
            event.preventDefault();
            event.stopPropagation();

            $(this).parent().siblings().removeClass('open');
            $(this).parent().addClass('open');
        }).on('click',function(event){
            var categoryAdd = $(this).parent().data('category-add');
            var categoryName = $(this).parent().data('category-name');

            setTag(categoryAdd,categoryName,'.categories-tags');

            return false;
        });


        //clicking on intems without sub-items
        $('ul.dropdown-menu [data-no-click=true] a').on('click',function(event){
            var categoryAdd = $(this).parent().data('category-add');
            var categoryName = $(this).parent().data('category-name');

            setTag(categoryAdd,categoryName,'.categories-tags');

            return false;
        }).on('mouseover',function(event){
            $(this).parent().siblings().removeClass('open');
        });

        /*
        $(document).mousemove(function(e)
        {
            var tg = $(e.target);
            var classNames = tg.attr('class');

            if(classNames != 'dropdown-menu' &&
                classNames != 'dropdown-toggle' && classNames != 'dropdown'
                && classNames != 'categories-tags' && classNames != undefined){
                $('.dropdown-menu').dropdown('toggle');
            }
        });
        */

    },1);

    /********************************** M O D A L  M A N A G E M E N T  W I N D O W ***********************************/

    /**
     * Toggle fields depending on image source type
     */
    $(document).on('change','[name="PostImage[is_external]"]',function(){
        $('.url_field').toggle();
        $('.file_field').toggle();
    });

    /**
     * Overriding submit action for form (to send via ajax) and reload table if returned OK
     */
    $(document).on('click','[data-ajax-form]',function(){

        var form = $($(this).data('ajax-form'));
        var formData = new FormData(form[0]);
        var okReload = $($(this).data('ok-reload'));

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            async: false,
            success: function (data) {
                if(data != 'OK'){
                    $('.modal-content').html(data);
                }else{
                    $.ajax({
                        url: okReload.data('reload-url'),
                        type: 'GET',
                        async: false,
                        success: function(reloaded_data){
                            okReload.html(reloaded_data);
                            $('.modal').modal('hide');
                        }
                    });
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });

        return false;
    });

    /**
     * Reloading links (updates container's html via ajax)
     */
    $(document).on('click','[data-ajax-reloader]', function(){

        var confirmMsg = $(this).data('confirm-ajax');

        if(confirmMsg){
            if(!confirm(confirmMsg)){
                return false;
            }
        }

        var container = $($(this).data('ajax-reloader'));
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

    /******************************************* B U I L D  B R A N C H E S *******************************************/

    rebuildBranches();

    $(window).resize(function(){
        rebuildBranches();
    });

    $(document).on('collapsed.pushMenu',function(){
        setTimeout(function(){rebuildBranches(); console.log('rebuilt');},350);
    });

    $(document).on('expanded.pushMenu',function(){
        setTimeout(function(){rebuildBranches(); console.log('rebuilt');},350);
    });
});

