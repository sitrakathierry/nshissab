$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    var appBase = new AppBase() ;
    var contrat_editor = new LineEditor("#contrat_editor") ;

    $("#location_search_dateContrat").datepicker() ;
    $("#location_search_dateDebut").datepicker() ;
    $("#location_search_dateFin").datepicker() ;

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

    $(document).on('change',"#prest_ctr_clt_nom",function(){
        if (!($(this).is("select"))) {
            return false ;
        }
        var realinstance = instance.loading()
        var self = $(this)
        $.ajax({
            url: routes.prest_location_locataire_get,
            type:'post',
            cache: false,
            data:{id:self.val()},
            dataType: 'json',
            success: function(json){
                realinstance.close()
                $("#prest_ctr_clt_telephone").val(json.telephone)
                $("#prest_ctr_clt_adresse").val(json.adresse)
                $("#prest_ctr_clt_email").val(json.email)
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

    $(document).on('click',"#prest_ctr_exist_loctr",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.prest_existing_locataire,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    if(response != "")
                    {
                        $("#captionContratLocataire").empty().html(response)
                    }else
                    {
                        $.alert({
                            title: "Message",
                            content: "Aucun locataire existant pour le moment",
                            type: "orange"
                        });
                    }
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
    
    $(document).on('click',"#prest_ctr_new_loctr",function(){
        if(!$(this).attr("disabled"))
        {
            var realinstance = instance.loading()
            var self = $(this)
            $.ajax({
                url: routes.prest_new_location_locataire,
                type:'post',
                cache: false,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    $("#captionContratLocataire").empty().html(response)
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

    // calculerDateApresNjours

    var montantAllMois = 0 ;
    var nbSpecJour = 0 ;
    var dateFinAll = "" ;

    function calculMontantJourMois(dateMois, montant)
    {
        var resultDateAFin = appBase.calculerDureeEnJours(dateMois,1) ;
        var finMois = (resultDateAFin.split("&##&")[1]).split("/")[0] ; // date fin du mois du premier mois
        var ecartJUnMois = resultDateAFin.split("&##&")[0] ;  // Ecart en jour à partir de la date quelconque du mois jusqu'à la fin du mois
        // console.log("Ecart : "+ecartJUnMois) ;
        if( nbSpecJour <= parseInt(ecartJUnMois))
        {
            montantAllMois += (parseInt(nbSpecJour) * montant) / parseInt(finMois) ;
            dateFinAll = appBase.calculerDateApresNjours(dateMois,parseInt(nbSpecJour)) ; 
        }
        else
        {
            montantAllMois += (parseInt(ecartJUnMois) * montant) / parseInt(finMois) ;
            nbSpecJour -= parseInt(ecartJUnMois) ;
            if (nbSpecJour > 0) {
                var dateDonnee = new Date(appBase.convertirFormatDate(resultDateAFin.split("&##&")[1]));
                var jourSuivant = new Date(dateDonnee.getTime() + 24 * 60 * 60 * 1000);
                var sjour = jourSuivant.getDate() ;
                var smois = jourSuivant.getMonth() + 1 ;
                var sannee = jourSuivant.getFullYear() ;
                var formatjourSuivant = appBase.str_pad(sjour,2,"0","left")+"/"+appBase.str_pad(smois,2,"0","left")+"/"+sannee
                calculMontantJourMois(formatjourSuivant,montant) ;
            }
        }

    }

    function calculDateFin()
    {
        var dateDebut = $("#prest_ctr_date_debut").val()
        var cycle = $("#prest_ctr_cycle").val()
        var cycleRef = $("#cycleRef").val()
        var forfait = $("#prest_ctr_forfait").val()
        var forfaitRef = $("#forfaitRef").val()
        var fftMontant = $("#prest_ctr_montant_forfait").val()
        var duree = $("#prest_ctr_duree").val()
        var periode = $("#prest_ctr_periode").val()
        var periodeRef = $("#periodeRef").val()
        var nbJour = 0 ;

        // var result = appBase.verificationElement([
        //     cycle,
        //     forfait,
        //     fftMontant,
        //     duree,
        //     periode,
        //     dateDebut,
        // ],[
        //     "Cycle",
        //     "Forfait",
        //     "Montant Forfait",
        //     "Durée",
        //     "Période",
        //     "Date Début",
        // ])

        // if(!result["allow"])
        // {
        //     $.alert({
        //         title: 'Message',
        //         content: result["message"],
        //         type: result["type"],
        //     });

        //     return result["allow"] ;
        // }
        var montantTotal = 0 ;
        var dateFin = "" ;
        duree = parseInt(duree) ;
        fftMontant = parseFloat(fftMontant) ;
        if(cycleRef == "CJOUR")
        {
            nbJour = duree ;
            dateFin = appBase.calculerDateApresNjours(dateDebut,parseInt(nbJour))

            if(forfaitRef == "FJOUR")
            {
                montantTotal = duree * fftMontant ;
            }
            else if(forfaitRef == "FORFAIT")
            {
                montantTotal = fftMontant ;
            }
        }
        else if(cycleRef == "CMOIS")
        {
            if(forfaitRef == "FORFAIT")
            {
                // Si le type de paiement est Forfaitaire et que le cycle est Journalier
                // if(periodeRef == "J") // Jour
                // {
                //     nbJour = duree ;
                //     montantTotal = duree * fftMontant ;
                // } else
                 if(periodeRef == "M") // Mois
                {
                    nbJour = 30 * duree ;
                }
                else if(periodeRef == "A") // Année
                {
                    nbJour =  365 * duree ;
                }
                montantTotal =  fftMontant ;

                dateFin = appBase.calculerDateApresNjours(dateDebut,parseInt(nbJour))
            }
            else if(forfaitRef == "FMOIS")
            {
                // Si le type de paiement est par Mois et que le cycle est Journalier
                // if(periodeRef == "J") // Jour
                // {
                //     nbJour = duree ;
                //     montantTotal = (duree * fftMontant) / 30 ;
                // } else
                if(periodeRef == "M") // Mois
                {
                    nbJour = 30 * duree ;
                    montantTotal = duree * fftMontant ;
                }
                else if(periodeRef == "A") // Année
                {
                    nbJour =  365 * duree ;
                    montantTotal = 12 * duree * fftMontant ;
                }

                dateFin = appBase.calculerDateApresNjours(dateDebut,parseInt(nbJour))
            }
            // if(forfaitRef == "FJOUR")
            // {
            //     // Si le fofait est par Jour et que le cycle est Mensuel
            //     if(periodeRef == "J") // Jour
            //     {
            //         nbJour = duree ;
            //         montantTotal = duree * fftMontant ;
            //         dateFin = appBase.calculerDateApresNjours(dateDebut,parseInt(nbJour))
            //     }
            //     else if(periodeRef == "M") // Mois
            //     {
            //         var resultCalcul = appBase.calculerDureeEnJours(dateDebut,duree)
            //         var ecartJour = resultCalcul.split("&##&")[0] ;
            //         montantTotal = parseInt(ecartJour) * fftMontant ;
            //         dateFin = resultCalcul.split("&##&")[1] ;
            //     }
            //     else if(periodeRef == "A") // Année
            //     {
            //         var resultCalcul = appBase.calculerDureeEnJours(dateDebut,(12 * duree))
            //         var ecartJour = resultCalcul.split("&##&")[0] ;
            //         montantTotal = parseInt(ecartJour) * fftMontant ;
            //         dateFin = resultCalcul.split("&##&")[1] ;
            //     }
            // }
            // else if(forfaitRef == "FMOIS")
            // {
            //     // Si le fofait est par Mois et que le cycle est Mensuel
            //     if(periodeRef == "J") // Jour
            //     {
            //         nbSpecJour = duree
            //         calculMontantJourMois(dateDebut, fftMontant) ;
            //         montantTotal = montantAllMois ; 
            //         dateFin = dateFinAll ;
            //     }
            //     else if(periodeRef == "M") // Mois
            //     {
            //         var resultCalcul = appBase.calculerDureeEnJours(dateDebut,duree)
            //         var ecartEnJour = resultCalcul.split("&##&")[0] ;
            //         nbSpecJour = ecartEnJour
            //         calculMontantJourMois(dateDebut, fftMontant) ;
            //         montantTotal = montantAllMois ; 
            //         dateFin = resultCalcul.split("&##&")[1];
            //         // montantTotal = parseInt(ecartJour) * fftMontant ;
            //         // dateFin = resultCalcul.split("&##&")[1] ;
            //     }
            //     else if(periodeRef == "A") // Année
            //     {
            //         var resultCalcul = appBase.calculerDureeEnJours(dateDebut,12 * duree)
            //         var ecartEnJour = resultCalcul.split("&##&")[0] ;
            //         nbSpecJour = ecartEnJour
            //         calculMontantJourMois(dateDebut, fftMontant) ;
            //         montantTotal = montantAllMois ; 
            //         dateFin = resultCalcul.split("&##&")[1];
            //     }
            // }
        }
        montantTotal = montantTotal % 1 !== 0 ? montantTotal.toFixed(2) : parseInt(montantTotal) ; 
        $("#prest_ctr_montant_contrat").val(montantTotal)
        $("#prest_ctr_montant_mois").val(montantTotal)
        $("#prest_ctr_date_fin").val(dateFin)
    }

    $("#prest_ctr_date_debut").change(function(){
        montantAllMois = 0 ;
        nbSpecJour = 0 ;
        dateFinAll = "" ;
        calculDateFin() ;
        
        var arrayData = [
            ".prest_ctr_cycle",
            ".prest_ctr_forfait",
            ".prest_ctr_periode",
            ".prest_ctr_montant_forfait",
            ".prest_ctr_duree",
        ]

        arrayData.forEach(elem => {
            $(elem).change(function(){
                montantAllMois = 0 ;
                nbSpecJour = 0 ;
                dateFinAll = "" ;
                calculDateFin() ;
            })

            $(elem).keyup(function(){
                montantAllMois = 0 ;
                nbSpecJour = 0 ;
                dateFinAll = "" ;
                calculDateFin() ;
            })
        })

    })

    $("#prest_ctr_forfait").change(function(){
        var optionSelected =  $(this).find("option:selected")
        var libelle = optionSelected.data("libelle") == "" ? "Aucun" : optionSelected.data("libelle")
        var reference = optionSelected.data("reference")

        if(reference == "FMOIS")
        {
            $("#captionModePaiement").show()
            $("#captionDateLimite").show()
            $("#captionRecapModeP").show()
            $("#captionRecapDateLimite").show()
        }
        else
        {
            $("#captionModePaiement").hide()
            $("#captionDateLimite").hide()
            $("#captionRecapModeP").hide()
            $("#captionRecapDateLimite").hide()
        }

        $("#lblMontant").text(libelle)
    })

    $(document).on('change',".prest_ctr_bail_caution",function(){
        var montantContrat = $("#prest_ctr_montant_mois").val()
        var caution = $(this).val() == "" ? 0 : parseFloat($(this).val()) ;
        var totalContrat = parseFloat(montantContrat) + caution ;
        totalContrat = totalContrat % 1 !== 0 ? totalContrat.toFixed(2) : parseInt(totalContrat)  ;
        $("#prest_ctr_montant_contrat").val(totalContrat) ;
    })

    $(document).on('keyup',".prest_ctr_bail_caution",function(){
        var montantContrat = $("#prest_ctr_montant_mois").val()
        var caution = $(this).val() == "" ? 0 : parseFloat($(this).val()) ;
        var totalContrat = parseFloat(montantContrat) + caution ;
        totalContrat = totalContrat % 1 !== 0 ? totalContrat.toFixed(2) : parseInt(totalContrat)  ;
        $("#prest_ctr_montant_contrat").val(totalContrat) ;
    })

    $("#submitFormContrat").click(function(){
        $("#formContrat").submit()
    })

    $("#formContrat").submit(function(){
        var self = $(this)
        $("#contrat_editor").val(contrat_editor.getEditorText('#contrat_editor'))
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
                                if(json.caution == "SANS")
                                {
                                    $.alert({
                                        title: 'Message',
                                        content: json.message,
                                        type: json.type,
                                        buttons:{
                                            OK: function(){
                                                if(json.type == "green")
                                                {
                                                    location.assign(routes.prest_location_contrat)
                                                }}
                                            ,
                                        }
                                    });
                                    return false ;
                                }
                                $.alert({
                                    title: 'Message',
                                    content: json.message+". Est-ce que la caution a-t-il été payée ? ",
                                    type: json.type,
                                    buttons: {
                                        NON: function(){
                                            if(json.type == "green")
                                            {
                                                location.assign(routes.prest_location_contrat)
                                            }
                                        },
                                        OUI: function(){
                                            var realinstance = instance.loading()
                                            $.ajax({
                                                url: routes.prest_save_caution_location,
                                                type:'post',
                                                cache: false,
                                                data : {contrat:json.contrat,montantCtn:json.montantCtn},
                                                dataType: 'json',
                                                success: function(respCtn){
                                                    realinstance.close()
                                                    $.alert({
                                                        title: 'Message',
                                                        content: "La caution a été enregistré",
                                                        type: "green",
                                                        buttons:{
                                                            IMPRIMER: function(){},
                                                            OK: function(){
                                                                    location.assign(routes.prest_location_contrat)
                                                                }
                                                            ,
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

    var currentStep = 1 ;
    var bailleur = {} ;
    var bail = {} ;
    var locataire = {} ;
    $(".next-btn").click(function() {
        var currentStepDiv = $("#step" + currentStep);
        var nextStepDiv = $("#step" + (currentStep + 1));
        
        var currentStepBtn = $(".step"+currentStep)
        var nextStepBtn = $(".step"+(currentStep + 1))

        if(currentStep == 1)
        {
            // INFORMATION DU BAILLEUR 
            var prest_ctr_prop_nom = $("#prest_ctr_prop_nom").val()
            var prest_ctr_prop_phone = $("#prest_ctr_prop_phone").val()
            var prest_ctr_prop_adresse = $("#prest_ctr_prop_adresse").val()

            var result = appBase.verificationElement([
                prest_ctr_prop_nom,
                prest_ctr_prop_phone,
                prest_ctr_prop_adresse,
            ],[
                "Nom",
                "Téléphone",
                "Adresse",
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

            if($("#prest_ctr_prop_nouveau").val() == "NON")
            {
                var prest_ctr_prop_nom = $("#prest_ctr_prop_nom")

                var optionSelected =  $(prest_ctr_prop_nom).find("option:selected")
                bailleur = {
                        "nom" : (optionSelected.text()).split(" | ")[0],
                        "telephone" : prest_ctr_prop_phone,
                        "adresse" : prest_ctr_prop_adresse
                    }   
            }
            else
            {
                bailleur =  {
                    "nom" : prest_ctr_prop_nom,
                    "telephone" : prest_ctr_prop_phone,
                    "adresse" : prest_ctr_prop_adresse
                }
            }
            currentStepDiv.hide();
            nextStepDiv.show();
        }
        else if(currentStep == 2)
        {
            // INFORMATION LOCATAIRE
            var prest_ctr_clt_nom = $("#prest_ctr_clt_nom").val()
            var prest_ctr_clt_telephone = $("#prest_ctr_clt_telephone").val()
            var prest_ctr_clt_adresse = $("#prest_ctr_clt_adresse").val()
            var prest_ctr_clt_email = $("#prest_ctr_clt_email").val()

            var result = appBase.verificationElement([
                prest_ctr_clt_nom,
                prest_ctr_clt_telephone,
                prest_ctr_clt_adresse,
                prest_ctr_clt_email,
            ],[
                "Nom",
                "Téléphone",
                "Adresse",
                "Email",
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

            if($("#prest_ctr_clt_nouveau").val() == "NON")
            {
                var prest_ctr_clt_nom = $("#prest_ctr_clt_nom")
                var optionSelected =  $(prest_ctr_clt_nom).find("option:selected")

                locataire = {
                    "nom" : optionSelected.text(),
                    "telephone" : prest_ctr_clt_telephone,
                    "adresse" : prest_ctr_clt_adresse,
                    "email" : prest_ctr_clt_email
                }
            }
            else
            {
                locataire = {
                    "nom" : prest_ctr_clt_nom,
                    "telephone" : prest_ctr_clt_telephone,
                    "adresse" : prest_ctr_clt_adresse,
                    "email" : prest_ctr_clt_email
                }
            }
            currentStepDiv.hide() ;
            nextStepDiv.show() ;
        }
        else if(currentStep == 3)
        {
            // INFORMATION BAIL
            var prest_ctr_bail_type_location = $("#prest_ctr_bail_type_location").val()
            var prest_ctr_bail_location = $("#prest_ctr_bail_location").val()
            var prest_ctr_bail_adresse = $("#prest_ctr_bail_adresse").val()
            var prest_ctr_bail_dimension = $("#prest_ctr_bail_dimension").val()

            var result = appBase.verificationElement([
                prest_ctr_bail_type_location,
                prest_ctr_bail_location,
                prest_ctr_bail_adresse,
                prest_ctr_bail_dimension,
            ],[
                "Type Location",
                "Nom du bail",
                "Adresse",
                "Dimension",
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

            if($("#prest_ctr_bail_nouveau").val() == "NON")
            {
                var prest_ctr_bail_type_location = $("#prest_ctr_bail_type_location")
                var prest_ctr_bail_location = $("#prest_ctr_bail_location")

                var option1Selected =  $(prest_ctr_bail_type_location).find("option:selected")
                var option2Selected =  $(prest_ctr_bail_location).find("option:selected")

                bail = {
                    "typeLocation" : option1Selected.text(),
                    "nom" : (option2Selected.text()).split(" | ")[0],
                    "adresse" : prest_ctr_bail_adresse,
                    "dimension" : prest_ctr_bail_dimension
                }
            }
            else
            {
                var prest_ctr_bail_type_location = $("#prest_ctr_bail_type_location")
                var option1Selected =  $(prest_ctr_bail_type_location).find("option:selected")

                bail = {
                    "typeLocation" : option1Selected.text(),
                    "nom" : prest_ctr_bail_location,
                    "adresse" : prest_ctr_bail_adresse,
                    "dimension" : prest_ctr_bail_dimension
                }
            }
            currentStepDiv.hide() ;
            nextStepDiv.show() ;
        }
        else if(currentStep == 4)
        { 
            // INFORMATION DE CONTRAT
            var prest_ctr_cycle = $("#prest_ctr_cycle") // SELECT
            var prest_ctr_forfait = $("#prest_ctr_forfait") // SELECT
            var prest_ctr_montant_forfait = $("#prest_ctr_montant_forfait").val()
            var prest_ctr_duree = $("#prest_ctr_duree").val()
            var prest_ctr_periode = $("#prest_ctr_periode") // SELECT
            var prest_ctr_date_debut = $("#prest_ctr_date_debut").val()
            var prest_ctr_date_fin = $("#prest_ctr_date_fin").val()
            var prest_ctr_retenu = $("#prest_ctr_retenu").val()
            var prest_ctr_renouvellement = $("#prest_ctr_renouvellement") // SELECT
            var prest_ctr_mode = $("#prest_ctr_mode") // SELECT
            var prest_ctr_delai_mode = $("#prest_ctr_delai_mode") // SELECT 
            var prest_ctr_bail_caution = $("#prest_ctr_bail_caution").val()
            var prest_ctr_montant_contrat = $("#prest_ctr_montant_contrat").val()
            var prest_ctr_delai_change = $("#prest_ctr_delai_change") // SELECT

            var result = appBase.verificationElement([
                prest_ctr_cycle.val(),
                prest_ctr_forfait.val(),
                prest_ctr_montant_forfait,
                prest_ctr_duree,
                prest_ctr_periode.val(),
                prest_ctr_date_debut,
                prest_ctr_date_fin,
                prest_ctr_montant_contrat,
            ],[
                "Cycle",
                "Type de paiement",
                "Montant Forfait",
                "Durée du contrat",
                "Période du contrat",
                "Date Début",
                "Date Fin",
                "Montant Contrat",
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

            currentStepDiv.hide();
            nextStepDiv.show();

            $(".recap_prop_nom").text(bailleur.nom)
            $(".recap_prop_tel").text(bailleur.telephone)
            $(".recap_prop_adresse").text(bailleur.adresse)

            $(".recap_loctr_nom").text(locataire.nom)
            $(".recap_loctr_tel").text(locataire.telephone)
            $(".recap_loctr_adresse").text(locataire.adresse)
            $(".recap_loctr_email").text(locataire.email)

            $(".recap_bail_type").text(bail.typeLocation)
            $(".recap_bail_nom").text(bail.nom)
            $(".recap_bail_adresse").text(bail.adresse)
            $(".recap_bail_dimension").text(bail.dimension)
            
            $("#recap_ctr_cycle").val(prest_ctr_cycle.find("option:selected").text())
            $("#recap_ctr_forfait").val(prest_ctr_forfait.find("option:selected").text())
            $("#recap_ctr_montant_forfait").val(prest_ctr_montant_forfait)
            $("#recap_ctr_duree").val(prest_ctr_duree)
            $("#recap_ctr_periode").val(prest_ctr_periode.find("option:selected").text())
            $("#recap_ctr_date_debut").val(prest_ctr_date_debut)
            $("#recap_ctr_date_fin").val(prest_ctr_date_fin)
            $("#recap_ctr_percent").val(prest_ctr_retenu)
            $("#recap_ctr_renouvment").val(prest_ctr_renouvellement.find("option:selected").text())
            $("#recap_ctr_mode").val(prest_ctr_mode.find("option:selected").text())
            $("#recap_ctr_date_limite").val(prest_ctr_delai_mode.find("option:selected").text())
            $("#recap_ctr_caution").val(prest_ctr_bail_caution)
            $("#recap_ctr_montant_contrat").val(prest_ctr_montant_contrat)
            if(prest_ctr_delai_change.val() == "AUTRE")
            {
                $("#recap_ctr_changement").val($("#prest_ctr_autre_valeur").val()+" Jours avant la fin du contrat")
            }
            else
            {
                $("#recap_ctr_changement").val(prest_ctr_delai_change.find("option:selected").text())
            }
        }

        currentStepBtn.removeClass("btn-info")
        currentStepBtn.addClass("btn-outline-info")

        nextStepBtn.removeClass("btn-outline-info")
        nextStepBtn.addClass("btn-info")

        currentStep++;
    });

    $(".prev-btn").click(function() {
        var currentStepDiv = $("#step" + currentStep);
        var prevStepDiv = $("#step" + (currentStep - 1));

        var currentStepBtn = $(".step"+currentStep)
        var prevStepBtn = $(".step"+(currentStep - 1))

        currentStepBtn.removeClass("btn-info")
        currentStepBtn.addClass("btn-outline-info")

        prevStepBtn.removeClass("btn-outline-info")
        prevStepBtn.addClass("btn-info")

        currentStepDiv.hide();
        prevStepDiv.show();
        currentStep--;
    });

    $("#prest_ctr_mode").change(function(){
        var optionSelected =  $(this).find("option:selected")
        var libelle = optionSelected.data("libelle") == "" ? "Aucun" : optionSelected.data("libelle")

        $("#delaiPaiement").text(libelle)
    })

    var selectArray = [
        "#prest_ctr_cycle",
        "#prest_ctr_forfait",
        "#prest_ctr_periode",
    ] ;

    selectArray.forEach(elem => {
        $(elem).change(function(){
            var target = $(this).find("option:selected").data("target")
            $(target).val($(this).find("option:selected").data("reference"))
        })
    })

    $(".prest_ctr_cycle").change(function(){
        var realinstance = instance.loading()
        var self = $(this)
        var data = new FormData() ;
        data.append('id',self.val())
        $.ajax({
            url: routes.prest_get_cycle_rules,
            type:'post',
            cache: false,
            data : data,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                realinstance.close()
                $("#prest_ctr_forfait").html(response.split("@##@")[0])
                $("#prest_ctr_periode").html(response.split("@##@")[1])
                $("#prest_ctr_renouvellement").html(response.split("@##@")[2])

                if(self.find("option:selected").data("reference") == "CJOUR")
                {
                    $("#captionModePaiement").hide()
                    $("#captionDateLimite").hide()
                    $("#captionRecapModeP").hide()
                    $("#captionRecapDateLimite").hide()
                }
                else
                {
                    $("#captionModePaiement").show()
                    $("#captionDateLimite").show()
                    $("#captionRecapModeP").show()
                    $("#captionRecapDateLimite").show()
                }

                $(".chosen_select").trigger("chosen:updated")
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
    })

    $("#captionAutreValeur").hide()
    $("#prest_ctr_delai_change").change(function(){
        if($(this).val() == "AUTRE")
        {
            $("#captionAutreValeur").show()
        }
        else
        {
            $("#captionAutreValeur").hide()
        }
    })

    $("#prest_ctr_renouvellement").change(function(){

        var elemAutre = `
        <div class="col-md-3" id="captionCtrRenouvAutre">
            <label for="prest_ctr_renouvellement_autre" class="font-weight-bold mt-3">Autre Type Renouvellmement</label>
            <input type="text" name="prest_ctr_renouvellement_autre" id="prest_ctr_renouvellement_autre" class="form-control" placeholder=". . .">
        </div>
        `
        var optionSelected =  $(this).find("option:selected")
        if(optionSelected.data("reference") == "AUTRE")
        {
            $(elemAutre).insertAfter("#captionCtrRenouv") ;
        }
        else
        {
            $("#captionCtrRenouvAutre").remove()
        }
    })

    $(".active_contrat").mouseenter(function(){
        if($(this).find("i").hasClass("fa-toggle-off"))
        {
            $(this).find("i").removeClass("fa-toggle-off");
            $(this).find("i").addClass("fa-toggle-on");
        }
    });
    
    $(".active_contrat").mouseleave(function(){
        if($(this).find("i").hasClass("fa-toggle-on"))
        {
            $(this).find("i").removeClass("fa-toggle-on");
            $(this).find("i").addClass("fa-toggle-off");
        }
    });

    $(".active_contrat").click(function(){
        var self = $(this)
        $.confirm({
            title: "Confirmation activation",
            content:"Vous êtes sûre ?",
            type:"green",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-green',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        var dataArray = {
                            id:self.attr("value"),
                        }
                        $.ajax({
                            url: routes.prest_location_contrat_active,
                            type:'post',
                            cache: false,
                            data:dataArray,
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
                                              location.assign(routes.prest_location_contrat_liste)
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

    $(".refresh_contrat").click(function(){
        var self = $(this)
        $.confirm({
            title: "Confirmation renouvellement",
            content:"Vous êtes sûre ?",
            type:"purple",
            theme:"modern",
            buttons:{
                btn1:{
                    text: 'Non',
                    action: function(){}
                },
                btn2:{
                    text: 'Oui',
                    btnClass: 'btn-purple',
                    keys: ['enter', 'shift'],
                    action: function(){
                        var realinstance = instance.loading()
                        var dataArray = {
                            id:self.attr("value"),
                        }
                        $.ajax({
                            url: routes.prest_location_contrat_renouvellement,
                            type:'post',
                            cache: false,
                            data:dataArray,
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
                                              location.assign(routes.prest_location_contrat_liste)
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

    function recherchePaiement(){
        var annee = $("#prest_ctr_critere_annee").val()
        var id = $("#prest_ctr_critere_id").val()

        var formData = new FormData() ;
        formData.append("annee",annee) ;
        formData.append("id",id) ;

        $("#contentTemplate").html(instance.otherSearch())
        $.ajax({
            url: routes.prest_location_paiement_search,
            type:'post',
            cache: false,
            data: formData,
            dataType: 'html',
            processData: false,
            contentType: false,
            success: function(response){
                $("#contentTemplate").html(response)
            },
            error: function(resp){
                $("#contentTemplate").html(resp)
                $.alert(JSON.stringify(resp)) ;
            }
        })
    }

    $("#prest_ctr_critere_annee").change(function(){
        recherchePaiement() ;
    })

    var elemSearch = [
        {
            name: "dateContrat",
            action:"change",
            selector : "#location_search_dateContrat"
        },
        {
            name: "dateDebut",
            action:"change",
            selector : "#location_search_dateDebut"
        },
        {
            name: "dateFin",
            action:"change",
            selector : "#location_search_dateFin"
        },
        {
            name: "id",
            action:"change",
            selector : "#location_search_numContrat"
        },
        {
            name: "bailleurId",
            action:"change",
            selector : "#location_search_bailleur"
        },
        {
            name: "bailId",
            action:"change",
            selector : "#location_search_bail"
        },
        {
            name: "locataireId",
            action:"change",
            selector : "#location_search_locataire"
        },
        {
            name: "refStatut",
            action:"change",
            selector : "#location_search_statut"
        }
    ]

    function searchContrat()
    {
        var instance = new Loading(files.search) ;
        $(".elem_contrat").html(instance.search(13)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemSearch.length; j++) {
            const search = elemSearch[j];
            formData.append(search.name,$(search.selector).val());
        }
        formData.append("typeSearch","CONTRAT") ;
        $.ajax({
            url: routes.prest_location_contrat_search_items ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, 
            contentType: false, 
            success: function(response){
                $(".elem_contrat").html(response) ;
            }
        })
    }

    elemSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchContrat()
        })
    })

    $(".search_vider").click(function(){
        searchContrat()
    })

    var elemComSearch = [
        {
            name: "id",
            action:"change",
            selector : "#location_com_search_numContrat"
        },
        {
            name: "bailId",
            action:"change",
            selector : "#location_com_search_bail"
        },
        {
            name: "locataireId",
            action:"change",
            selector : "#location_com_search_locataire"
        },
    ]

    function searchCommission()
    {
        var instance = new Loading(files.search) ;
        $(".elem_commission").html(instance.search(7)) ;
        var formData = new FormData() ;
        for (let j = 0; j < elemComSearch.length; j++) {
            const search = elemComSearch[j];
            formData.append(search.name,$(search.selector).val());
        }

        $.ajax({
            url: routes.prest_location_commission_search_items ,
            type: 'post',
            cache: false,
            data:formData,
            dataType: 'html',
            processData: false, 
            contentType: false, 
            success: function(response){
                $(".elem_commission").html(response) ;
            }
        })
    }

    elemComSearch.forEach(elem => {
        $(document).on(elem.action,elem.selector,function(){
            searchCommission()
        })
    })
    
    $(".lct_valider_versement").parent().hide()

    $(document).on('click',".lct_check_versement",function(){
        if($(this).hasClass("btn-outline-success"))
        {
            var idData = $(this).attr("value") ;
            var commission = $(this).data("commission") ;

            $(this).removeClass("btn-outline-success") ;
            $(this).addClass("btn-success") ;
            $(this).text(("Versé").toUpperCase())
            $(this).parent().append('<input type="hidden" class="lct_valeur_versement" value="'+idData+':'+commission+'">') ;
        }
        else
        {
            $(this).removeClass("btn-success") ;
            $(this).addClass("btn-outline-success") ;
            $(this).html('<i class="fa fa-hand-holding-dollar"></i>') ;
            $(this).parent().find(".lct_valeur_versement").remove()
        }

        var showButton = false ;
        $(".lct_check_versement").each(function(){
            if($(this).hasClass("btn-success"))
            {
                $(".lct_valider_versement").parent().show() ;
                showButton = true ;
                return ;
            }
        })

        if(!showButton)
            $(".lct_valider_versement").parent().hide() ;
    })

    $(document).on('click',".lct_valider_versement",function(){
        var self = $(this)
        $.confirm({
            title: "Validation",
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
                        var dataEnr = [] ;
                        $(".lct_valeur_versement").each(function(){
                            dataEnr.push($(this).val()) ;
                        })
                        $.ajax({
                            url: routes.prest_location_commission_versement,
                            type:'post',
                            cache: false, 
                            data:{
                                dataEnr:dataEnr,
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
        return false ;
    })

    $(document).on('click',".prest_bailleur_suppr", function(){
        var self = $(this)
        $.confirm({
            title: "Suppression",
            content:"Êtes-vous sûre de vouloir supprimer ?",
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
                            url: routes.param_location_bailleur_delete,
                            type:'post',
                            cache: false,
                            data:{idBailleur:self.data("value")},
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

    $(document).on('click',".lct_select_impression", function(){
        if($(this).hasClass("btn-outline-info"))
        {
            $(this).removeClass("btn-outline-info") ;
            $(this).addClass("btn-info") ;
            $(this).html('<span class="text-uppercase font-weight-bold">Ok</span>') ;
        }
        else
        {
            $(this).removeClass("btn-info") ;
            $(this).addClass("btn-outline-info") ;
            $(this).html('<i class="fa fa-check"></i>') ;
        }
    })

    $(document).on('click',".lct_btn_imprimer_quittance",function(){
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.param_modele_pdf_get,
            type:"post",
            dataType:"html",
            processData:false,
            contentType:false,
            success : function(response){
                realinstance.close()
                $.confirm({
                    title: "Impression Facture",
                    content:response,
                    type:"blue",
                    theme:"modern",
                    buttons:{
                        btn1:{
                            text: 'Annuler',
                            action: function(){}
                        },
                        btn2:{
                            text: 'Imprimer',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function(){
                                var dataLoyer = [] ;
                                $(".lct_select_impression.btn-info").each(function(){
                                    dataLoyer.push($(this).data("value")) ;
                                })
                                if (dataLoyer.length === 0)
                                {
                                    $.alert({
                                        title: 'Message',
                                        content: "Aucun élément seléctionné",
                                        type: "orange"
                                    });
                                    return false ;
                                }
                                var idModeleEntete = $("#modele_pdf_entete").val() ;
                                var idModeleBas = $("#modele_pdf_bas").val() ;
                                var realinstance = instance.loading()
                                $.ajax({
                                    url: routes.prest_location_repartition_file,
                                    type:'post',
                                    cache: false,
                                    data:{
                                        dataLoyer:dataLoyer,
                                    },
                                    dataType: 'json',
                                    success: function(json){
                                        realinstance.close() ;
                                        var idContrat = self.data("value") ;
                                        var url = routes.prest_location_imprimer_quittance + '/' + idContrat + '/' + idModeleEntete + '/' + idModeleBas;
                                        window.open(url, '_blank');
                                        location.reload() ;
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
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
        return false ;
    })

    $(document).on("click",".lct_print_quittance_exist",function(){
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.param_modele_pdf_get,
            type:"post",
            dataType:"html",
            processData:false,
            contentType:false,
            success : function(response){
                realinstance.close()
                $.confirm({
                    title: "Impression Facture",
                    content:response,
                    type:"blue",
                    theme:"modern",
                    buttons:{
                        btn1:{
                            text: 'Annuler',
                            action: function(){}
                        },
                        btn2:{
                            text: 'Imprimer',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function(){
                                var idModeleEntete = $("#modele_pdf_entete").val() ;
                                var idModeleBas = $("#modele_pdf_bas").val() ;
                                var idNumQtc = self.data("value") ;
                                var url = routes.prest_location_quittance_existant + '/' + idNumQtc + '/' + idModeleEntete + '/' + idModeleBas;
                                window.open(url, '_blank');
                                location.reload() ;
                            }
                        }
                    }
                })
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
        return false ;
    })

    $(document).on("click",".fiche_caution_display",function(){
        var self = $(this)
        var realinstance = instance.loading()
        $.ajax({
            url: routes.param_modele_pdf_get,
            type:"post",
            dataType:"html",
            processData:false,
            contentType:false,
            success : function(response){
                realinstance.close()
                $.confirm({
                    title: "Impression Facture",
                    content:response,
                    type:"blue",
                    theme:"modern",
                    buttons:{
                        btn1:{
                            text: 'Annuler',
                            action: function(){}
                        },
                        btn2:{
                            text: 'Imprimer',
                            btnClass: 'btn-blue',
                            keys: ['enter', 'shift'],
                            action: function(){
                                var idModeleEntete = $("#modele_pdf_entete").val() ;
                                var idModeleBas = $("#modele_pdf_bas").val() ;
                                var idContrat = self.data("value") ;
                                var url = routes.prest_location_caution_imprimer + '/' + idContrat + '/' + idModeleEntete + '/' + idModeleBas;
                                window.open(url, '_blank');
                                location.reload()
                            }
                        }
                    }
                })
            },
            error: function(resp){
                realinstance.close()
                $.alert(JSON.stringify(resp)) ;
            }
        })
        return false ;
    })
})