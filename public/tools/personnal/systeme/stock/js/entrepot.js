$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    var appBase = new AppBase() ;

    $(document).on("click",".depot_vers_entrepot",function(){
        var realinstance = instance.loading()
        $.ajax({
            url: routes.stock_get_record_entrepot,
            type:'post',
            cache: false,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $(".content_operation").html(response) ;
                $("#elemAppro").hide()
                $("#formAppro").hide()
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }) ;

    $(document).on("submit","#formDepotEntrepot",function(){
        var self = $(this)
        $.confirm({
            title: "Confirmation",
            content:"Êtes-vous sûre ?",
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
                    keys: ['enter'],
                    action: function(){
                        var realinstance = instance.loading()
                        var data = self.serialize() ;
                        $.ajax({
                            url: routes.stock_record_entrepot_save,
                            type:'post',
                            cache: false,
                            data:data,
                            dataType: 'json',
                            success: function(json){
                                realinstance.close()
                                $.alert({
                                    title: 'Message',
                                    content: json.message,
                                    type: json.type,
                                    buttons: {
                                        OK: function(){
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
            }
        })
        return false ;
    }) ;


    $(document).on("change","#trans_entrepot_source",function(){
        var realinstance = instance.loading()
        $.ajax({
            url: routes.stock_find_produit_in_entrepot,
            type:'post',
            cache: false,
            data: {
                idE:$(this).val()
            },
            dataType: 'json',
            success: function(response){
                realinstance.close()
                // $(".entrepot_source").html(response) ;
                var options = '<option value="">-</option>'
                for (let i = 0; i < response.produitEntrepots.length; i++) {
                    const elementP = response.produitEntrepots[i];
                    options += '<option value="'+elementP.id+'" data-stock="'+elementP.stock+'" >'+elementP.codeProduit+' | '+elementP.nomType+' | '+elementP.nom+' | stock : '+elementP.stock+'</option>'
                }

                $("#trans_produit_source").html(options) ;
                $("#trans_produit_source").trigger("chosen:updated"); 
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(document).on("click",".trans_entrepot_ajouter",function(){
        var trans_entrepot_dest = $("#trans_entrepot_dest").val() ;
        var trans_produit_source = $("#trans_produit_source").val() ;

        result = appBase.verificationElement([
            trans_entrepot_dest,
            trans_produit_source
        ],[
            "Entrepôt Destination",
            "Produit Source",
        ])

        if(!result["allow"])
        {
            $.alert({
                title: 'Message',
                content: result["message"],
                type: result["type"],
            });

            return result["allow"] ;
        }

        var trans_entrepot_source = $("#trans_entrepot_source").val() ;

        if(trans_entrepot_source == trans_entrepot_dest)
        {
            $.alert({
                title: 'Message',
                content: "Entrepot identique. Veuiller changer d'entrepot",
                type: "orange",
            });

            return false ;
        }

        var optionEntrepotDest = $("#trans_entrepot_dest").find("option:selected")
        var optionProduitSource = $("#trans_produit_source").find("option:selected") ;
        var stockProduit = parseFloat(optionProduitSource.data("stock")) ;

        if(stockProduit <= 0)
        {
            $.alert({
                title: 'Message',
                content: "Stock insuffisant. Veuiller choisir un autre produit",
                type: "red",
            });

            return false ;
        }

        var doublons = false ;
        $(".content_depot_produit").find("tr").each(function(){
            var ligne_entrepot_dest = $(this).find(".depot_enr_entrepot_dest").val() ;
            var ligne_produit_source = $(this).find(".depot_enr_produit_source").val() ;
            
            if(ligne_entrepot_dest == trans_entrepot_dest && ligne_produit_source == trans_produit_source)
            {
                doublons = true ;
            }
        }) ;

        if (doublons) {
            $.alert({
                title: 'Message',
                content: "Doublons détecté. Veuiller choisir un autre produit",
                type: "orange",
            });

            return false ;
        }

        var item = `
            <tr>
                <td class="align-middle" >`+optionEntrepotDest.text()+`</td>
                <td class="align-middle" >`+optionProduitSource.text()+`</td>
                <td class="align-middle" >`+stockProduit+`</td>
                <td class="text-center align-middle">
                    <button class="btn btn-sm btn-outline-danger font-smaller depot_delete_ligne"><i class="fa fa-trash"></i></button>
                    <input type="hidden" name="depot_enr_entrepot_source[]" class="depot_enr_entrepot_source" value="`+trans_entrepot_source+`"> 
                    <input type="hidden" name="depot_enr_entrepot_dest[]" class="depot_enr_entrepot_dest" value="`+trans_entrepot_dest+`"> 
                    <input type="hidden" name="depot_enr_produit_source[]" class="depot_enr_produit_source" value="`+trans_produit_source+`"> 
                    <input type="hidden" name="depot_enr_quantite[]" class="depot_enr_quantite" value="`+stockProduit+`"> 
                </td>
            </tr>
        `

        $(".content_depot_produit").append(item) ;

        $("#trans_produit_source").val("") ;
        $("#trans_produit_source").trigger("chosen:updated"); 
    })

    $(document).on("click",".depot_delete_ligne",function(){
        $(this).closest("tr").remove() ;
    })

})