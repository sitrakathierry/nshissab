$(document).ready(function(){
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;

    $("#search_affichage").change(function(){
        searchStockGen($(this)) ;
    })

    $("#search_gen_produit").change(function(){
        searchStockGen($(this)) ;
    })

    function searchStockGen(self)
    {
        $(".contentTableStock").html(instance.otherSearch()) ;
        var formData = new FormData() ;
        formData.append("contenu",self.val()) ;
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