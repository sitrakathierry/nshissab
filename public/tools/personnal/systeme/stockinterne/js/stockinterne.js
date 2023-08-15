$(document).ready(function(){
    var stock_int_materiel_editor = new LineEditor("#stock_int_materiel_editor") ;
    var instance = new Loading(files.loading)
    var appBase = new AppBase() ;
    $("#enr_int_appro_date").datepicker() ;
    $("#enr_int_sortie_date").datepicker() ;
    
    $("#formMateriel").submit(function(){
        var self = $(this) ;
        $("#stock_int_materiel_editor").text(stock_int_materiel_editor.getEditorText()) 
        $.confirm({
            title: 'Confirmation',
            content:"Voulez-vous vraiment enregistrer ?",
            type:"blue",
            theme:"modern",
            buttons : {
                NON : function(){},
                OUI : function(){
                    var data = self.serialize();
                    var realinstance = instance.loading()
                    $.ajax({
                        url: routes.stock_interne_save_materiel,
                        type:"post",
                        data:data,
                        dataType:"json",
                        success : function(json){
                            realinstance.close() ;
                            $.alert({
                                title: 'Message',
                                content: json.message,
                                type: json.type,
                                buttons: {
                                    OK: function(){
                                        if(json.type == "green")
                                        {
                                            $("input, select").val("")
                                            $(".chosen_select").trigger("chosen:updated")
                                            location.reload()
                                        }
                                    }
                                }
                            });
                        }
                    })
                }
            }
        })
        return false ;
    })

    function getPrdIntLibelle(url,type)
    {
        var realinstance = instance.loading()
            var self = $(this)
            formData = new FormData();
            formData.append("libelle_type",type)
            $.ajax({
                url: url,
                type:'post',
                cache: false,
                data:formData,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentLibelle").empty().html(response) 
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
    }

    $(document).on('click',".int_new_libelle",function(){
        if(!$(this).attr("disabled"))
        {
            getPrdIntLibelle(routes.stock_interne_get_data_libelle,"NEW")
        }
        $(this).prop("disabled", true);
    })

    $(document).on('click',".int_existing_libelle",function(){
        if(!$(this).attr("disabled"))
        {
            getPrdIntLibelle(routes.stock_interne_get_data_libelle,"EXISTING")
        }
        $(this).prop("disabled", true);
    })

    $("#int_appro_total_general").val(0)
    $("#int_sortie_total_general").val(0)

    $(".int_appro_ajouter").click(function(){
        var int_appro_designation = $("#int_appro_designation").val()
        var int_appro_quantite = $("#int_appro_quantite").val()
        var int_appro_stock = $("#int_appro_stock").val()
        var int_appro_prix_achat = $("#int_appro_prix_achat").val()

        result = appBase.verificationElement([
            int_appro_designation,
            int_appro_quantite,
            int_appro_stock,
            int_appro_prix_achat,
        ],[
            "Désignation",
            "Quantité",
            "Stock",
            "Prix d'achat",
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

        var textDesignation = $("#int_appro_designation").find("option:selected").text()
        var totalLigne = parseFloat(int_appro_stock) * parseFloat(int_appro_prix_achat) ;

        var item = `
            <tr>
                <td>
                    `+textDesignation+`
                    <input type="hidden" name="int_enr_appro_designation[]" value="`+int_appro_designation+`">
                </td>
                <td>
                    `+int_appro_quantite+`&nbsp;`+textDesignation.split(' | ')[1]+`
                    <input type="hidden" name="int_enr_appro_quantite[]" value="`+int_appro_quantite+`">
                </td>
                <td>
                    `+int_appro_stock+`&nbsp;`+textDesignation.split(' | ')[2]+`
                    <input type="hidden" name="int_enr_appro_stock[]" value="`+int_appro_stock+`">
                </td>
                <td>
                    `+int_appro_prix_achat+`
                    <input type="hidden" name="int_enr_appro_prix_achat[]" value="`+int_appro_prix_achat+`">
                </td>
                <td>
                    `+totalLigne+`
                    <input type="hidden" id="int_enr_appro_total" value="`+totalLigne+`">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm font-smaller int_suppr_ligne btn-outline-danger"><i class="fa fa-times"></i></button>
                </td>
            </tr>
            ` ;

            var oldTotalGeneral = $("#int_appro_total_general").val() == "" ? 0 : parseFloat($("#int_appro_total_general").val())  ;
            var newTotalGeneral = oldTotalGeneral + totalLigne ;

            $(".int_caption_total_general").text(newTotalGeneral) ;
            $("#int_appro_total_general").val(newTotalGeneral) ;

            $(".elemIntAppro").append(item) ;

            $("#int_appro_designation").val("")
            $("#int_appro_quantite").val("")
            $("#int_appro_stock").val("")
            $("#int_appro_prix_achat").val("")

            $(".chosen_select").trigger("chosen:updated")
    })

    $(document).on('click',".int_suppr_ligne", function(){
        if(!$(this).attr("disabled"))
        {
            var totalLigne = $(this).closest('tr').find("#int_enr_appro_total").val() ;
            var totalGeneral = $("#int_appro_total_general").val()
            var totalRestant = parseFloat(totalGeneral) - parseFloat(totalLigne) ;
    
            $(".int_caption_total_general").text(totalRestant) ;
            $("#int_appro_total_general").val(totalRestant) ;
        }
        $(this).attr("disabled","true")
        $(this).closest('tr').remove() ;
    })

    $("#formIntAppro,#formIntSortie").submit(function(){
        var data = $(this).serialize();
        $.confirm({
            title: "Confirmation",
            content:"Êtes vous sûre  ?",
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
                    $.ajax({
                        url: routes.stock_interne_save_mouvement,
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
    })

    $(".int_sortie_ajouter").click(function(){
        var int_sortie_designation = $("#int_sortie_designation").val()
        var int_sortie_stock = $("#int_sortie_stock").val()


        var result = appBase.verificationElement([
            int_sortie_designation,
            int_sortie_stock,
        ],[
            "Désignation",
            "Stock sorti",
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

        var textDesignation = $("#int_sortie_designation").find("option:selected").text()
        var totalLigne = parseFloat(int_sortie_stock) ;

        var item = `
            <tr>
                <td>
                    `+textDesignation+`
                    <input type="hidden" name="int_enr_sortie_designation[]" value="`+int_sortie_designation+`">
                </td>
                <td>
                    `+int_sortie_stock+`&nbsp;`+textDesignation.split(' | ')[2]+`
                    <input type="hidden" name="int_enr_sortie_stock[]" id="int_enr_sortie_stock" value="`+int_sortie_stock+`">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm font-smaller sortie_suppr_ligne btn-outline-danger"><i class="fa fa-times"></i></button>
                </td>
            </tr>
            ` ;

        var oldTotalGeneral = $("#int_sortie_total_general").val() == "" ? 0 : parseFloat($("#int_sortie_total_general").val())  ;
        var newTotalGeneral = oldTotalGeneral + totalLigne ;

        $(".sortie_caption_total_general").text(newTotalGeneral) ;
        $("#int_sortie_total_general").val(newTotalGeneral) ;

        $(".elemIntSortie").append(item) ;

        $("#int_sortie_designation").val("")
        $("#int_sortie_stock").val("")

        $(".chosen_select").trigger("chosen:updated")
    })

    $(document).on('click','.sortie_suppr_ligne', function(){
        if(!$(this).attr("disabled"))
        {
            var totalLigne = $(this).closest('tr').find("#int_enr_sortie_stock").val() ;
            var totalGeneral = $("#int_sortie_total_general").val()
            var totalRestant = parseFloat(totalGeneral) - parseFloat(totalLigne) ;
    
            $(".sortie_caption_total_general").text(totalRestant) ;
            $("#int_sortie_total_general").val(totalRestant) ;
        }
        $(this).attr("disabled","true")
        $(this).closest('tr').remove() ;
    })

    $(".int_sortie_enregistre").click(function(){
        $("#formIntSortie").submit()
    })

    var elementTo = ''
    var arrayElem = [
        $("#int_sortie_stock"),
    ]

    arrayElem.forEach(elem => {
        elem.click(function(){
            elementTo = $(this)
        })
    })


    $(".caisse_perso_btn").click(function(){
        if(!isNaN($(this).text()))
        {
            var quantite = elementTo.val()
            elementTo.val(quantite+$(this).text())
        }
        else if($(this).attr("value") == 1 )
        {
            elementTo.val("")
        }
        else
        {
            var oldChar = elementTo.val()
            var newChar = oldChar.slice(0, -1);
            elementTo.val(newChar)
        }

        elementTo.keyup()
    })
})