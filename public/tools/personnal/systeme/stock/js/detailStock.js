$(document).ready(function(){
    var produit_details_editor = new LineEditor(".produit_details_editor") ;
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    $("#prod_variation_expiree").datepicker()

    produit_details_editor.setEditorText($(".produit_details_editor").text())
    $(".mybarCode").html("")

    $(".mybarCode").barcode(
        {
            code: $(".details_barcode_produit").val(),
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
        text: $(".details_qr_code_produit").val(),
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

    $("#formVariationProduit").submit(function(){
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Vous êtes sûre ?",
            type:"blue",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-blue',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        var data = self.serialize()
                        $.ajax({
                            url: routes.stock_variation_produit_save,
                            type:'post',
                            cache: false,
                            data:data,
                            dataType: 'json',
                            success: function(json){
                                realinstance.close() ;
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK: function(){
                                            if(json.type == "green")
                                            {
                                                $("#prod_variation_entrepot").val("")
                                                $("#prod_variation_fournisseur").val("")
                                                $("#prod_variation_prix_vente").val("")
                                                $("#prod_variation_stock").val("")
                                                $("#prod_variation_expiree").val("")

                                                $(".chosen_select").trigger("chosen:updated") ;
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
            }
        })
        return false ;
    })

    $(document).on("click",".prod_edit_variation",function(){
        var contenu = `
        <style>
            .jconfirm.jconfirm-modern .jconfirm-box
            {
                width: 450px ;
            }
            .jconfirm.jconfirm-modern .jconfirm-box div.jconfirm-content
            {
                margin-bottom: 0 ;
            }
        </style>
        <div id="contentModif" class="container text-left">
            <label for="nom" class="mt-2 font-weight-bold">Entrepôt(s)</label>
            <input type="text" name="nom" readonly id="nom" value="ENTREPOT TEST" class="form-control" placeholder=". . .">

            <div class="row">
                <div class="col-7">
                    <label for="nom" class="mt-2 font-weight-bold">Code</label>
                    <input type="text" readonly name="nom" value="TR500" id="nom" class="form-control" placeholder=". . .">
                </div>
                <div class="col-5">
                    <label for="nom" class="mt-2 font-weight-bold">Indice</label>
                    <input type="text" readonly name="nom" value="" id="nom" class="form-control" placeholder=". . .">
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <label for="modif_inpt_prix" class="mt-2 font-weight-bold">Prix de vente</label>
                    <input type="number" step="any" name="modif_inpt_prix" value="2400" id="modif_inpt_prix" class="form-control" placeholder=". . .">
                </div>
                <div class="col-6">
                    <div id="contentSolder">
                        <label for="nom" class="mt-2 font-weight-bold">&nbsp;</label>
                        <button class="btn btn-sm btn-primary modif_btn_solder btn-block"><i class="fa fa-percent"></i>&nbsp;Solder</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <label for="nom" class="mt-2 font-weight-bold">Stock</label>
                    <input type="text" readonly name="nom" value="45" id="nom" class="form-control" placeholder=". . .">
                </div>
                <div class="col-6">
                    <div id="contentDeduire">
                        <label for="nom" class="mt-2 font-weight-bold">&nbsp;</label>
                        <button class="btn btn-sm modif_btn_deduire btn-warning btn-block"><i class="fa fa-minus"></i>&nbsp;Déduire</button>
                    </div>
                </div>
            </div>
        </div>
        ` ;

        $.confirm({
            title:'Modification Variation',
            content:contenu,
            type:"orange",
            theme:"modern",
            buttons : {
                Annuler : function(){},
                Enregistrer : function(){
                    // var realinstance = instance.loading()
                    // $.ajax({
                    //     url: routes.stock_update_produit,
                    //     type:"post",
                    //     data:data,
                    //     dataType:"json",
                    //     success : function(json){
                    //         realinstance.close()
                    //         $.alert({
                    //             title: 'Message',
                    //             content: json.message,
                    //             type: json.type,
                    //             buttons: {
                    //                 OK : function(){
                    //                     if(json.type == "green")
                    //                     {
                    //                         location.reload()
                    //                     }
                    //                 }
                    //             }
                    //         });
                    //     },
                    //     error: function(resp){
                    //         realinstance.close()
                    //         $.alert(JSON.stringify(resp)) ;
                    //     }
                    // })
                }
            }
        })
        return false ;
    })

    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;

    $(document).on('click',".modif_btn_solder",function(){
        if(!$(this).attr("disabled"))
        {
            $("#modif_inpt_prix").attr("readonly",true) ;
            var contenu = `
            <label for="modif_inpt_solde" class="mt-2 font-weight-bold">Prix du solde</label>
            <input type="number" step="any" name="modif_inpt_solde" id="modif_inpt_solde" class="form-control" placeholder=". . ."> `;
            $("#contentSolder").empty().html(contenu) 
        }
        $(this).prop("disabled", true);
    })

    $(document).on('click',".modif_btn_deduire",function(){
        if(!$(this).attr("disabled"))
        {
            // $("#modif_inpt_prix").attr("readonly",true) ;
            var contenu = `
            <label for="modif_inpt_deduire" class="mt-2 font-weight-bold">Qté à déduire</label>
            <input type="number" step="any" name="modif_inpt_deduire" id="modif_inpt_deduire" class="form-control" placeholder=". . ."> `;
            $("#contentDeduire").empty().html(contenu) 
        }
        $(this).prop("disabled", true);
    })

})