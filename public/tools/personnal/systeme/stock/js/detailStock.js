$(document).ready(function(){
    var produit_details_editor = new LineEditor(".produit_details_editor") ;
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    
    produit_details_editor.setEditorText($(".produit_details_editor").text())
    $(".mybarCode").html("")

    $(".mybarCode").barcode(
        {
            code: $(".barcode_produit").val(),
            rect: false,
        },
        "code128",
        {
            output: "svg",
            fontSize: 25,
            bgColor: "transparent",
            barWidth: 3,
            barHeight: 100,
        }
    );
    
    $(".qr_block").html("")
    $(".qr_block").qrcode({
        // render method: 'canvas', 'image' or 'div'
        render: 'image' ,
        size: 2400,
        text: $(".qr_code_produit").val(),
    });

    $("#formDetailProduit").submit(function(){
        console.log($("#add_new_type").val())
        var self = $(this)
        $(".produit_details_editor").text(produit_details_editor.getEditorText()) 
        $.confirm({
            title: 'Mise à jour',
            content:"Êtes vous sûre ?",
            type:"orange",
            theme:"modern",
            buttons : {
                NON : function(){},
                OUI : function(){
                    var data = self.serialize();
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.stock_update_produit,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            realinstance.close()
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons: {
                                    OK : function(){
                                        if(json.type == "green")
                                        {
                                            location.reload()
                                        }
                                    }
                                }
                            });
                        },
                        error: function(resp){
                            realinstance.close()
                            $.alert(JSON.stringify(resp)) ;
                        }
                    })
                }
            }
        })
        return false ;
    })

    function getPrdDesignation(url)
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
                $(".contentPrdDesignation").empty().html(response) 
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }

    $(document).on('click',".prd_new_designation",function(){
        if(!$(this).attr("disabled"))
        {
            getPrdDesignation(routes.stock_get_new_designation)
        }
        $(this).prop("disabled", true);
    })

    $(document).on('click',".prd_existing_designation",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            var formData = new FormData() ;
            formData.append("type",$("#prod_type").val()) ;
            $.ajax({
                url: routes.stock_get_existing_designation,
                type:'post',
                cache: false,
                data: formData,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $(".contentPrdDesignation").empty().html(response) 
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        $(this).prop("disabled", true);
    })

    $(document).on("change","#prod_type",function(){
        if(!$(this).is("select"))
            return false ;
            
        var realinstance = instance.loading()
        var self = $(this)
        var formData = new FormData() ;
        formData.append("type",self.val()) ;
        $.ajax({
            url: routes.stock_get_existing_designation,
            type:'post',
            cache: false,
            data: formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $(".contentPrdDesignation").empty().html(response) 
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })
})