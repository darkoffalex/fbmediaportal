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

},10);