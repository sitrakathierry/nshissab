$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    var appBase = new AppBase() ;

    $("#prest_ctr_date_debut").datepicker()

    $("#formBailleur").submit(function(){
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
                        var data = self.serialize()
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.prest_location_bailleur_save,
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
                                                $("#prest_lct_prop_nom").val("")
                                                $("#prest_lct_prop_tel").val("")
                                                $("#prest_lct_prop_adresse").val("")
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

    $("#formBail").submit(function(){
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
                        var data = self.serialize()
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.prest_location_bail_save,
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
                                                $("#prest_lct_bail_nom").val("")
                                                $("#prest_lct_bail_dimension").val("")
                                                $("#prest_lct_bail_montant").val("")
                                                $("#prest_lct_bail_caution").val("")
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

    $(document).on('change',"#prest_ctr_prop_nom",function(){
        if (!($(this).is("select"))) {
            return false ;
        }
        var realinstance = instance.loading()
        var self = $(this)
        $.ajax({
            url: routes.prest_location_bailleur_get,
            type:'post',
            cache: false,
            data:{id:self.val()},
            dataType: 'json',
            success: function(json){
                realinstance.close()
                $("#prest_ctr_prop_phone").val(json.telephone)
                $("#prest_ctr_prop_adresse").val(json.adresse)

                var options = '<option value="">-</option>'
                for (let i = 0; i < json.bails.length; i++) {
                    const element = json.bails[i];
                    options += '<option value="'+element.id+'">'+(element.nom).toUpperCase()+' | '+(element.lieu).toUpperCase()+'</option>'
                }

                if($("#prest_ctr_bail_location").is("select"))
                {
                    $("#prest_ctr_bail_location").html(options) ;
                    $("#prest_ctr_bail_location").trigger("chosen:updated")
                }

                $("#prest_ctr_existing_bail").removeAttr("disabled")
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $(document).on('click',"#prest_ctr_new_prop",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.prest_new_location_bailleur,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#captionContratBailleur").empty().html(response)
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

    $(document).on('click',"#prest_ctr_existing_prop",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.prest_existing_location_bailleur,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#captionContratBailleur").empty().html(response)
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

    $(document).on('click',"#prest_ctr_new_bail", function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.prest_new_location_bail,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#captionBailLocation").empty().html(response)
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

    $(document).on('click',"#prest_ctr_existing_bail",function(){
        if(!$(this).attr("disabled"))
        {
            if(!$("#prest_ctr_prop_nom").is("select"))
            {
                $.alert({
                    title: "Message",
                    content: "Demande non pris en charge",
                    type: "orange"
                });

                return false ;
            }
            var realinstance = instance.loading()
            var self = $(this)
            var data = new FormData() ;
            data.append('id',$("#prest_ctr_prop_nom").val())
            $.ajax({
                url: routes.prest_existing_location_bail,
                type:'post',
                cache: false,
                data:data,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    if(response == "")
                    {
                        $.alert({
                            title: "Message",
                            content: "Le Bailleur n'a pas de location disponible",
                            type: "orange"
                        });
        
                        return false ;
                    }
                    $("#captionBailLocation").empty().html(response)
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

    $(document).on('change',"#prest_ctr_bail_location", function(){
        if (!($(this).is("select"))) {
            return false ;
        }
        var realinstance = instance.loading()
        var self = $(this)
        $.ajax({
            url: routes.prest_location_bail_get,
            type:'post',
            cache: false,
            data:{id:self.val()},
            dataType: 'json',
            success: function(json){
                realinstance.close()
                $("#prest_ctr_bail_adresse").val(json.adresse)
                $("#prest_ctr_bail_dimension").val(json.dimension)
                $("#prest_ctr_bail_montant").val(json.montant)
                $("#prest_ctr_bail_caution").val(json.caution)
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    // calculerDateApresNjours

    function calculDateFin()
    {
        var dateDebut = $("#prest_ctr_date_debut").val()
        var duree = $("#prest_ctr_duree").val()
        var periode = $("#prest_ctr_periode").val()
        var periodeSelected = $("#prest_ctr_periode").find("option:selected")
        var refPeriode = periodeSelected.data("reference")
        var nbJour = 0 ;

        var result = appBase.verificationElement([
            dateDebut,
            duree,
            periode,
        ],[
            "Date Début",
            "Durée",
            "Période",
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
        duree = parseInt(duree) ;
        if(refPeriode == "J") // Jour
        {
            nbJour = duree 
        }
        else if(refPeriode == "M") // Mois
        {
            nbJour = 30 * duree ;
        }
        else if(refPeriode == "A") // Année
        {
            nbJour = 365 * duree ;
        }

        var dateFin = appBase.calculerDateApresNjours(dateDebut,parseInt(nbJour))

        $("#prest_ctr_date_fin").val(dateFin)
    }

    $("#prest_ctr_date_debut").change(function(){
        calculDateFin() 
        if($("#prest_ctr_date_debut").val() != "")
        {
            $("#prest_ctr_periode").change(function(){
                calculDateFin() 
            })
        
            $(".prest_ctr_duree").change(function(){
                calculDateFin() 
            })
        
            $(".prest_ctr_duree").keyup(function(){
                calculDateFin() 
            }) 
        }
    })

    $(document).on('change',".prest_ctr_bail_caution",function(){
        var montantLoc = $("#prest_ctr_bail_montant").val()
        $("#prest_ctr_montant_contrat").val(parseFloat(montantLoc) + parseFloat($(this).val()))
    })

    $(document).on('keyup',".prest_ctr_bail_caution",function(){
        var montantLoc = $("#prest_ctr_bail_montant").val()
        $("#prest_ctr_montant_contrat").val(parseFloat(montantLoc) + parseFloat($(this).val()))
    })

    $("#formContrat").submit(function(){
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
                        var data = self.serialize()
                        var realinstance = instance.loading()
                        $.ajax({
                            url: routes.prest_location_contrat_save,
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
                                                location.assign(routes.prest_location_contrat)
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