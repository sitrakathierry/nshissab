$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    var appBase = new AppBase()
    var tableauDates = [] ;
    $(document).on('change',"#fact_prest_lct_numContrat", function(){
        var realinstance = instance.loading()
            var self = $(this)
            var data = new FormData() ;
            data.append('id',self.val())
            $.ajax({
                url: routes.fact_prest_loctr_get_contrat,
                type:'post',
                cache: false,
                data: data,
                dataType: 'html',
                processData: false,
                contentType: false,
                success: function(response){
                    realinstance.close()
                    var contrat = response.split("@##@")[1] ;
                    var tableau = JSON.parse(contrat)[0] ;
                    
                    tableauDates = JSON.parse(response.split("@##@")[2]) ;

                    $("#fact_prest_lct_bailleur").val(tableau.bailleur)
                    $("#fact_prest_lct_bail").val(tableau.bail)
                    $("#fact_prest_lct_locataire").val(tableau.locataire)
                    $("#fact_prest_lct_duree").val(tableau.dureeContrat)
                    $("#fact_prest_lct_cycle").val((tableau.cycle).toUpperCase())
                    $("#fact_prest_lct_type_pment").val((tableau.typePaiement).toUpperCase())
                    $("#fact_prest_lct_montant").val(tableau.montantForfait)
                    $("#fact_prest_lct_date_debut").val(tableau.dateDebut)
                    $("#fact_prest_lct_date_fin").val(tableau.dateFin)
                    $("#fact_prest_lct_ref_forfait").val(tableau.refForfait)

                    $("#captionTypePment").text(tableau.forfaitLibelle)
                    $("#captionListePaiement").empty().html(response.split("@##@")[0])
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
    })

    function repartirPaiement()
    {
        var montantPayee = $("#fact_prest_lct_mtn_a_payer").val()
        var montantparMois = parseFloat($("#fact_prest_lct_montant").val())
        montantPayee = montantPayee == "" ? 0 : parseFloat(montantPayee) ;

        var totalPayee = 0 ;
        var totalInitial = 0 ;

        $("#listePaiement").empty() ;

        // $("#listePaiement").find("tr:first").each(function(){
        //     var ligne = $(this)
        //     var montantInitial = parseFloat(ligne.find("#partie_montant_initial").val())
        //     var statutInitial = ligne.find("#partie_statut_initial").val()
        //     ligne.find(".montantPayee").text(montantInitial == 0 ? "-" : montantInitial)
        //     ligne.find(".statutPment").empty().html(statutInitial)
        //     ligne.find("#partie_montant_payee").val("")
        //     ligne.find("#partie_statut").val("")
        // })
        
        // $("#listePaiement").find("tr:not(:first-child)").each(function(){
        //     $(this).remove()
        // })

        for (let i = 0; i < tableauDates.length; i++) {
            const element = tableauDates[i];
            
            // var ligne = $("#listePaiement").find("tr:nth-child("+(i + 1)+")")
            // var montantInitial = parseFloat(ligne.find("#partie_montant_initial").val())
            var montantInitial = parseFloat(element.montantInitial) ;
            var captionMtnPayee = "" ;
            var captionStatut = "" ;
            var valMontantPayee = 0 ; 
            var valStatut = 0 ; 
            var captionDesignation = "" ;
            montantPayee += montantInitial ;
            if(montantPayee > 0 && montantPayee < montantparMois)
            {
                totalPayee += montantPayee ;
                captionMtnPayee = montantPayee ;
                captionStatut = '<span class="text-info font-weight-bold">ACOMPTE</span>' ;
                valMontantPayee = montantPayee  ;
                valStatut = "ACOMPTE" ; 
                captionDesignation = "ACOMPTE. "
                // ligne.find(".montantPayee").text(montantPayee)
                // ligne.find(".statutPment").empty().html()
                // ligne.find("#partie_montant_payee").val()
                // ligne.find("#partie_statut").val()
            }
            else if( montantPayee >= montantparMois)
            {
                totalPayee += montantparMois ;
                captionMtnPayee = montantparMois ;
                captionStatut = '<span class="text-success font-weight-bold">PAYEE</span>' ;
                valMontantPayee = montantparMois ;
                valStatut = "PAYE" ; 
                captionDesignation = "PAIEMENT. "
                // ligne.find(".statutPment").empty().html() ;
                // ligne.find(".montantPayee").text(montantparMois)
                // ligne.find("#partie_montant_payee").val(valMontantPayee)
                // ligne.find("#partie_statut").val("PAYE")
            }
            
            totalInitial += montantInitial ;
            montantPayee -= montantparMois;
            var refForfait = $("#fact_prest_lct_ref_forfait").val() ;
            var itemPaiement = "" ;

            if(refForfait == "FMOIS")
            {
                itemPaiement = `
                <tr>
                    <td>
                        `+captionDesignation+tableauDates[i].designation+`
                        <input type="hidden" name="partie_designation[]" id="partie_designation" value="`+tableauDates[i].designation+`">
                        <input type="hidden" name="partie_montant_payee[]" id="partie_montant_payee" value="`+valMontantPayee+`">
                        <input type="hidden" name="partie_annee[]" id="partie_annee" value="`+tableauDates[i].annee+`">
                        <input type="hidden" name="partie_statut[]" id="partie_statut" value="`+valStatut+`">
                        <input type="hidden" id="partie_montant_initial" value="0"> 
                        <input type="hidden" id="partie_statut_initial" value="`+tableauDates[i].statut+`">
                    </td>
                    <td>
                        `+tableauDates[i].finLimite+`
                        <input type="hidden" name="partie_date_limite[]" id="partie_date_limite" value="`+tableauDates[i].finLimite+`">
                        <input type="hidden" name="partie_date_debut[]" id="partie_date_debut" value="`+ tableauDates[i].debutLimite +`">
                    </td>
                    <td>
                        `+tableauDates[i].mois.toUpperCase()+`
                        <input type="hidden" name="partie_mois[]" id="partie_mois" value="`+tableauDates[i].indexMois+`">
                    </td>
                    <td>
                        `+tableauDates[i].annee+`
                    </td>
                    <td class="montantPayee">`+captionMtnPayee+`</td>
                    <td class="statutPment">`+captionStatut+`</td>
                </tr>
                ` ;
            }
            else if(refForfait == "FJOUR")
            {
                itemPaiement = `
                <tr>
                    <td>
                        `+captionDesignation+tableauDates[i].designation+`
                        <input type="hidden" name="partie_designation[]" id="partie_designation" value="`+tableauDates[i].designation+`">
                        <input type="hidden" name="partie_montant_payee[]" id="partie_montant_payee" value="`+valMontantPayee+`">
                        <input type="hidden" name="partie_mois[]" id="partie_mois" value="`+tableauDates[i].indexMois+`">
                        <input type="hidden" name="partie_annee[]" id="partie_annee" value="`+tableauDates[i].annee+`">
                        <input type="hidden" name="partie_statut[]" id="partie_statut" value="`+valStatut+`">
                        <input type="hidden" id="partie_montant_initial" value="0"> 
                        <input type="hidden" id="partie_statut_initial" value="`+tableauDates[i].statut+`">
                    </td>
                    <td>
                        `+tableauDates[i].debutLimite+`
                        <input type="hidden" name="partie_date_debut[]" id="partie_date_debut" value="`+ tableauDates[i].debutLimite +`">
                    </td>
                    <td class="montantPayee">`+captionMtnPayee+`</td>
                    <td class="statutPment">`+captionStatut+`</td>
                </tr>
                ` ;
            }
            else
            {
                itemPaiement = `
                <tr>
                    <td>
                        `+captionDesignation+tableauDates[i].designation+`
                        <input type="hidden" name="partie_designation[]" id="partie_designation" value="`+tableauDates[i].designation+`">
                        <input type="hidden" name="partie_mois[]" id="partie_mois" >
                        <input type="hidden" name="partie_annee[]" id="partie_annee" value="`+tableauDates[i].annee+`">
                        <input type="hidden" name="partie_montant_payee[]" id="partie_montant_payee" value="`+valMontantPayee+`">
                        <input type="hidden" name="partie_statut[]" id="partie_statut" value="`+valStatut+`">
                        <input type="hidden" id="partie_montant_initial" value="0"> 
                        <input type="hidden" id="partie_statut_initial" value="`+tableauDates[i].statut+`">
                    </td>
                    <td class="montantPayee">`+captionMtnPayee+`</td>
                    <td class="statutPment">`+captionStatut+`</td>
                </tr>
                ` ;
            }

            $("#listePaiement").append(itemPaiement) ; 

            if (montantPayee <= 0) {
                break ;
            }
        }

        var montantActuelPayee = $("#fact_prest_lct_mtn_a_payer").val()
        montantActuelPayee = montantActuelPayee == "" ? 0 + totalInitial : parseFloat(montantActuelPayee) + totalInitial ;

        totalPayee = parseFloat(totalPayee) ;
  
        $("#montantTotalPayee").text(totalPayee) ;
        $("#montantRestant").text( montantActuelPayee - totalPayee) ;
    }

    $(document).on('keyup',"#fact_prest_lct_mtn_a_payer",function(){
        repartirPaiement()
    })

    

})

// maquettes
// travail, mode fonctionnement
// prix

