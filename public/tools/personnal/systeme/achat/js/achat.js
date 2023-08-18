$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    var appBase = new AppBase() ;
    var compta_achat_editor = new LineEditor("#compta_achat_editor") ;
    var ach_commande_editor = new LineEditor("#ach_commande_editor") ;
    
    $("#ach_date").datepicker(); 

    $(document).on('click',"#achat_bon_new_marchandise",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.achat_new_marchandise,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentMarchandise").empty().html(response)
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        $(this).prop("disabled", true);
        // $(this).closest('tr').remove() ;
    })

    $(document).on('click',"#achat_bon_existing_marchandise",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.achat_existing_marchandise,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#contentMarchandise").empty().html(response)
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
        }
        $(this).prop("disabled", true);
        // $(this).closest('tr').remove() ;
    })

    $(document).on('change',"#achat_bon_designation", function(){
        if(!$(this).is("select"))
            return false ;
        var realinstance = instance.loading()
        var self = $(this)
        $.ajax({
            url: routes.achat_marchandise_prix_get,
            type:'post',
            cache: false,
            data:{id:self.val()},
            dataType: 'json',
            success: function(json){
                realinstance.close();
                if (json.type == "green") {
                    $("#achat_bon_prix").val(json.prix) ;
                }
                else
                {
                    $("#achat_bon_prix").val("") ;
                }
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    var totalGeneral = 0
    $(document).on('click',"#ajout_marchandise", function(){

        var designation = $("#achat_bon_designation").val()
        if($("#new_marchandise").val() == "NON")
        {
            if($("#achat_bon_designation").val() != "")
            {
                var optionSelected = $("#achat_bon_designation").find("option:selected")
                designation = optionSelected.text().split(" | ")[0]
            }
            else
            {
                designation = "" ;
            }
        }
        var quantite = $("#achat_bon_quantite").val()
        var prix = $("#achat_bon_prix").val()

        var result = appBase.verificationElement([
            designation,
            quantite,
            prix,
        ],[
            "Désignation",
            "Quantité",
            "Prix",
        ]) ;

        

        if(!result["allow"])
        {
            $.alert({
                title: 'Message',
                content: result["message"],
                type: result["type"],
            });

            return result["allow"] ;
        }
        
        var item = '' ;
        var total = 0 ;
        if($("#new_marchandise").val() == "NON")
        {
            total = parseFloat(quantite) * parseFloat(prix) ;
            totalGeneral += total ;

            item = `
                    <tr>
                        <td>
                            `+designation+`
                            <input type="hidden" name="achat_bon_enr_designation[]" id="achat_bon_enr_designation" value="`+designation+`">
                            <input type="hidden" name="achat_bon_enr_design_id[]" id="achat_bon_enr_design_id" value="`+$("#achat_bon_designation").val()+`">
                        </td>
                        <td>
                            `+quantite+`
                            <input type="hidden" name="achat_bon_enr_quantite[]" id="achat_bon_enr_quantite" value="`+quantite+`">
                        </td>
                        <td>
                            `+prix+`
                            <input type="hidden" name="achat_bon_enr_prix[]" id="achat_bon_enr_prix" value="`+prix+`">
                        </td>
                        <td>`+total+`</td>
                        <td class="text-center align-middle">
                            <button class="btn btn-sm btn-outline-danger font-smaller"><i class="fa fa-times"></i></button>
                        </td>
                    </tr>
                `
            $("#contentItemMarchandise").append(item) ;
            $("#achat_bon_total_Gen").text(totalGeneral)
            $("#achat_bon_val_total_Gen").val(totalGeneral)
            $("#achat_bon_designation").val("")
            $(".chosen_select").trigger("chosen:updated")
            $("#achat_bon_quantite").val("")
            $("#achat_bon_prix").val("")

            return false ;
        }

        var realinstance = instance.loading()
        var self = $(this)
        $.ajax({
            url: routes.achat_marchandise_creation,
            type:'post',
            cache: false,
            data:{designation:designation,prix:prix},
            dataType: 'json',
            success: function(json){
                realinstance.close();
                total = parseFloat(quantite) * parseFloat(prix) ;
                totalGeneral += total ;

                item = `
                    <tr>
                        <td>
                            `+designation+`
                            <input type="hidden" name="achat_bon_enr_designation[]" id="achat_bon_enr_designation" value="`+designation+`">
                            <input type="hidden" name="achat_bon_enr_design_id[]" id="achat_bon_enr_design_id" value="`+json.id+`">
                        </td>
                        <td>
                            `+quantite+`
                            <input type="hidden" name="achat_bon_enr_quantite[]" id="achat_bon_enr_quantite" value="`+quantite+`">
                        </td>
                        <td>
                            `+prix+`
                            <input type="hidden" name="achat_bon_enr_prix[]" id="achat_bon_enr_prix" value="`+prix+`">
                        </td>
                        <td>`+total+`</td>
                        <td class="text-center align-middle">
                            <button class="btn btn-sm btn-outline-danger font-smaller"><i class="fa fa-times"></i></button>
                        </td>
                    </tr>
                `

                $("#contentItemMarchandise").append(item) ;
                $("#achat_bon_total_Gen").text(totalGeneral)
                $("#achat_bon_val_total_Gen").val(totalGeneral)
                $("#achat_bon_designation").val("")
                $("#achat_bon_quantite").val("")
                $("#achat_bon_prix").val("")
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $("#formbonCommande").submit(function(){
        var self = $(this)
        $("#compta_achat_editor").val(compta_achat_editor.getEditorText('#compta_achat_editor'))
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
                            url: routes.achat_bon_commande_save,
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
    })

    $("#ach_mrch_designation").val("")
    $("#ach_mrch_prix").val("")

    $("#formMarchandise").submit(function(){
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
                            url: routes.achat_marchandise_creation,
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
    })

    $(document).on('click',".ach_edit_marchandise",function(){
        var designation = $(this).closest("tr").find(".elem_designation").text() ;
        var prix = $(this).closest("tr").find(".elem_prix").text() ;
        var idM = $(this).attr("value") ;

        var element = `
            <div class="w-100 text-left">
                <label for="mrch_modif_designation" class="font-weight-bold">Désignation</label>
                <input type="text" name="designation" id="mrch_modif_designation" oninput="this.value = this.value.toUpperCase();" value="`+designation+`" class="form-control" placeholder=". . .">
                
                <label for="mrch_modif_prix" class="font-weight-bold mt-3">Prix</label>
                <input type="number" step="any" name="prix" id="mrch_modif_prix" value="`+prix+`" class="form-control" placeholder=". . .">
            </div>
            `
        $.confirm({
            title: "Modification",
            content:element,
            type:"orange",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Annuler',
                    action: function(){}
                },
                btn2:{
                    text: 'Valider',
                    btnClass: 'btn-orange',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.achat_marchandise_creation,
                            type:'post',
                            cache: false,
                            data: {
                                designation: $("#mrch_modif_designation").val(),
                                prix: $("#mrch_modif_prix").val(),
                                idM:idM,
                            },
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
    })

    $(document).on('click',".ach_delete_marchandise", function(){
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Vous êtes sûre ?",
            type:"red",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-red',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.achat_marchandise_supprime,
                            type:'post',
                            cache: false,
                            data:{idM:self.attr('value')},
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
    })
})