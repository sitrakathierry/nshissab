$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;

    function getPrdType(url)
    {
        var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: url,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $(".contenPrdType").empty().html(response) 
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
    }

    $(document).on('click',".prd_new_type",function(){
        if(!$(this).attr("disabled"))
        {
            getPrdType(routes.stock_get_new_type)
        }
        $(this).prop("disabled", true);
    })

    $(document).on('click',".prd_existing_type",function(){
        if(!$(this).attr("disabled"))
        {
            getPrdType(routes.stock_get_existing_type)
        }
        $(this).prop("disabled", true);
    })
})