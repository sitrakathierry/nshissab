$(document).ready(function(){
    var instance = new Loading(files.loading) ;
    var appBase = new AppBase()

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
                    var cycle = response.split("@##@")[1] ;
                    var tableau = JSON.parse(cycle)[0] ;
                    // console.log(tableau[0].numContrat)
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
        var montantparMois = parseFloat($("#fact_prest_lct_montant").val())
        var montantPayee = $("#fact_prest_lct_mtn_a_payer").val()
        var referenceForfait = $("#fact_prest_lct_ref_forfait").val()
        var dateIPaiement = $("#fact_prest_lct_date_paiement").val()
        montantPayee = parseFloat(montantPayee) ;
        var totalPayee = 0 ;
        $("#listePaiement").find("tr").each(function(){
            var ligne = $(this)
            ligne.find(".montantPayee").text("-")
            ligne.find(".statutPment").empty().html('<span class="text-dark font-weight-bold">-</span>')
            ligne.find(".datePaiement").text("-")
            ligne.find("#partie_montant_payee").val("")
            ligne.find("#partie_statut").val("")
            if(referenceForfait == "FORFAIT")
            {
                ligne.find(".moisPment").text("-")
                ligne.find("#partie_mois").val("")
            }
        })

        $("#listePaiement").find("tr").each(function(){
            var ligne = $(this)
            if(montantPayee > 0 && montantPayee < montantparMois)
            {
                totalPayee += montantPayee ;
                ligne.find(".montantPayee").text(montantPayee)
                ligne.find(".statutPment").empty().html('<span class="text-info font-weight-bold">ACOMPTE</span>')
                ligne.find(".datePaiement").text($("#fact_prest_lct_date_paiement").val())
                ligne.find("#partie_montant_payee").val(montantPayee)
                ligne.find("#partie_statut").val("ACOMPTE")
            }
            else if( montantPayee >= montantparMois)
            {
                totalPayee += montantparMois ;
                ligne.find(".statutPment").empty().html('<span class="text-success font-weight-bold">PAYEE</span>') ;
                ligne.find(".montantPayee").text(montantparMois)
                ligne.find(".datePaiement").text($("#fact_prest_lct_date_paiement").val())
                ligne.find("#partie_montant_payee").val(montantparMois)
                ligne.find("#partie_statut").val("PAYE")
            }

            if(referenceForfait == "FORFAIT")
            {
                dateIPaiement = new Date(appBase.convertirFormatDate(dateIPaiement))
                mois = appBase.getMonthName(dateIPaiement.getMonth()) ; 
                ligne.find(".moisPment").text(mois.toUpperCase()) ; 
                ligne.find("#partie_mois").val(dateIPaiement.getMonth()+1)
            }

            montantPayee -= montantparMois;
            if (montantPayee <= 0) {
                return false; // Sortez de la boucle si le montant total est épuisé
            }
        })

        var montantActuelPayee = $("#fact_prest_lct_mtn_a_payer").val()
        montantActuelPayee = montantActuelPayee == "" ? 0 : montantActuelPayee ;
        totalPayee = parseFloat(totalPayee) ;
        $("#montantTotalPayee").text(totalPayee)
        $("#montantRestant").text(parseFloat(montantActuelPayee) - totalPayee)
    }

    $(document).on('keyup',"#fact_prest_lct_mtn_a_payer",function(){
        repartirPaiement()
    })

})

// maquettes
// travail, mode fonctionnement
// prix

