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
            if(forfaitRef == "FJOUR")
            {
                // Si le fofait est par Jour et que le cycle est Journalier
                if(periodeRef == "J") // Jour
                {
                    nbJour = duree ;
                    montantTotal = duree * fftMontant ;
                }
                else if(periodeRef == "M") // Mois
                {
                    nbJour = 30 * duree ;
                    montantTotal = 30 * duree * fftMontant ;
                }
                else if(periodeRef == "A") // Année
                {
                    nbJour =  365 * duree ;
                    montantTotal = 365 * duree * fftMontant ;
                }

                dateFin = appBase.calculerDateApresNjours(dateDebut,parseInt(nbJour))
            }
            else if(forfaitRef == "FMOIS")
            {
                // Si le fofait est par Mois et que le cycle est Journalier
                if(periodeRef == "J") // Jour
                {
                    nbJour = duree ;
                    montantTotal = (duree * fftMontant) / 30 ;
                }
                else if(periodeRef == "M") // Mois
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
        }
        else if(cycleRef == "CMOIS")
        {
            if(forfaitRef == "FJOUR")
            {
                // Si le fofait est par Jour et que le cycle est Mensuel
                if(periodeRef == "J") // Jour
                {
                    nbJour = duree ;
                    montantTotal = duree * fftMontant ;
                    dateFin = appBase.calculerDateApresNjours(dateDebut,parseInt(nbJour))
                }
                else if(periodeRef == "M") // Mois
                {
                    var resultCalcul = appBase.calculerDureeEnJours(dateDebut,duree)
                    var ecartJour = resultCalcul.split("&##&")[0] ;
                    montantTotal = parseInt(ecartJour) * fftMontant ;
                    dateFin = resultCalcul.split("&##&")[1] ;
                }
                else if(periodeRef == "A") // Année
                {
                    var resultCalcul = appBase.calculerDureeEnJours(dateDebut,(12 * duree))
                    var ecartJour = resultCalcul.split("&##&")[0] ;
                    montantTotal = parseInt(ecartJour) * fftMontant ;
                    dateFin = resultCalcul.split("&##&")[1] ;
                }
            }
            else if(forfaitRef == "FMOIS")
            {
                // Si le fofait est par Mois et que le cycle est Mensuel
                if(periodeRef == "J") // Jour
                {
                    nbSpecJour = duree
                    calculMontantJourMois(dateDebut, fftMontant) ;
                    montantTotal = montantAllMois ; 
                    dateFin = dateFinAll ;
                }
                else if(periodeRef == "M") // Mois
                {
                    var resultCalcul = appBase.calculerDureeEnJours(dateDebut,duree)
                    var ecartEnJour = resultCalcul.split("&##&")[0] ;
                    nbSpecJour = ecartEnJour
                    calculMontantJourMois(dateDebut, fftMontant) ;
                    montantTotal = montantAllMois ; 
                    dateFin = resultCalcul.split("&##&")[1];
                    // montantTotal = parseInt(ecartJour) * fftMontant ;
                    // dateFin = resultCalcul.split("&##&")[1] ;
                }
                else if(periodeRef == "A") // Année
                {
                    var resultCalcul = appBase.calculerDureeEnJours(dateDebut,12 * duree)
                    var ecartEnJour = resultCalcul.split("&##&")[0] ;
                    nbSpecJour = ecartEnJour
                    calculMontantJourMois(dateDebut, fftMontant) ;
                    montantTotal = montantAllMois ; 
                    dateFin = resultCalcul.split("&##&")[1];
                }
            }
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

    var currentStep = 1;
    $(".next-btn").click(function() {
        var currentStepDiv = $("#step" + currentStep);
        var nextStepDiv = $("#step" + (currentStep + 1));
        
        var currentStepBtn = $(".step"+currentStep)
        var nextStepBtn = $(".step"+(currentStep + 1))

        currentStepBtn.removeClass("btn-info")
        currentStepBtn.addClass("btn-outline-info")

        nextStepBtn.removeClass("btn-outline-info")
        nextStepBtn.addClass("btn-info")

        currentStepDiv.hide();
        nextStepDiv.show();
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
})