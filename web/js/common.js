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
            $(this).parent().toggleClass('open');
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
        });
    },1);


    /************************* I M A G E  M A N A G E M E N T  W I N D O W *************************/

    $(document).on('change','[name="PostImage[is_external]"]',function(){
        $('.url_field').toggle();
        $('.file_field').toggle();
    });


    $(document).on('click','#create-image-form .submit-btn',function(){

        var form = $('#create-image-form');
        var formData = new FormData(form[0]);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            async: false,
            success: function (data) {
                if(data != 'OK'){
                    $('.modal-content').html(data);
                }else{
                    var table = $('.ajax-reloadable');
                    table.load(table.data('reload-url'));
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });

        return false;
    });
});