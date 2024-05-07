$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;

    $("#search_affichage").change(function(){
        searchStockGen() ;
    })

    $("#search_gen_produit").change(function(){
        searchStockGen() ;
    })

    function searchStockGen()
    {
        $(".contentTableStock").html(instance.otherSearch()) ;
        var formData = new FormData() ;
        formData.append("contenu",$(this).val()) ;
        $.ajax({
            url: routes.stock_display_content_stock,
            type:'post',
            cache: false,
            data: formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                $(".contentTableStock").html(response)
            },
            error: function(resp){
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }
})