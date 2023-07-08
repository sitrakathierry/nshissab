$(document).ready(function(){
    var instance = new Loading(files.loading) ;
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

                    $("#captionTypePment").text(tableau.forfaitLibelle)
                    $("#captionListePaiement").empty().html(response.split("@##@")[0])
                },
                error: function(resp){
                    realinstance.close()
                    $.alert(JSON.stringify(resp)) ;
                }
            })
    })

    var refLigne = 1 ;
    $(document).on('keyup',"#fact_prest_lct_mtn_a_payer",function(){
        var ligne = $("#listePaiement").find("tr:nth-child("+refLigne+")")
        var montantparMois = parseFloat($("#fact_prest_lct_montant").val())
        var montantPayee = $(this).val()
        ligne.find(".datePaiement").text($("#fact_prest_lct_date_paiement").val())
        ligne.find(".montantPayee").text(montantPayee)
        ligne.find(".statutPment").empty().html('<span class="text-info font-weight-bold">ACOMPTE</span>')
        if( montantPayee >= montantparMois)
        {
            refLigne++ ;
            ligne.find(".statutPment").empty().html('<span class="text-success font-weight-bold">PAYEE</span>') ;
            ligne.find(".montantPayee").text(montantparMois)
        }
    })
})

// maquettes
// travail, mode fonctionnement
// prix