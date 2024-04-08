$(document).ready(function(){
    $("#agd_echance").hide()
    $(".agd_acompte").hide()
    var firstAdd = false ;
    $(document).on('click',".agd_btn_ajouter", function(){
        var agd_ech_date = $("#agd_ech_date").val()
        var agd_ech_montant = $("#agd_ech_montant").val()

        var data = [
            agd_ech_date,
            agd_ech_montant,
        ]

        var dataMessage = [
            "Date échéance",
            "Montant échéance",
        ]
        var vide = false ;
        var caption = "" ;
        for (let i = 0; i < data.length; i++) 
        {
            const elem = data[i] ;
            if(elem == "")
            {
                vide = true ;
                caption = dataMessage[i] ;
                break ;
            }
        }

        if(vide)
        {
            $.alert({
                title: 'Champ vide',
                content: caption+" est vide",
                type:'orange',
            })
            return false;
        }
        
        // if(agd_ech_date == "")
        // {
        //     var dataActuel = new Date();
        //     var dateNow = dataActuel.getDay()+"/"+dataActuel.getMonth()+"/"+dataActuel.getFullYear()
        //     agd_ech_date = dateNow
        // }
        agd_ech_montant = parseFloat(agd_ech_montant).toFixed(2) ;

        var element = `
            <tr>
                <td>
                    `+agd_ech_date+`
                    <input type="hidden" name="agd_ech_enr_date[]" value="`+agd_ech_date+`">
                </td>
                <td>
                    `+agd_ech_montant+`
                    <input type="hidden" name="agd_ech_enr_montant[]" id="enr_ech_montant" value="`+agd_ech_montant+`">
                </td>
                <td class="align-middle text-center">
                    <button type="button" class="btn agd_ech_suppr_ligne btn-sm btn-outline-danger font-smaller"><i class="fa fa-times"></i></button>
                </td>
            </tr>
        ` 
        $(".elem_echeance").append(element)
        var totalRestant = $("#agd_total_restant").text()
        totalRestant = parseFloat(totalRestant).toFixed(2)  ;
        $("#agd_val_total_restant").val(totalRestant)
        firstAdd = true ;
        $("#agd_ech_date").val("")
        $("#agd_ech_montant").val("")
    })

    $(document).on('click','.agd_ech_suppr_ligne',function(){
        $(this).closest('tr').remove()
    })

    function updateMontant(montant)
    {
        var valMontant = montant.val() != "" ? parseFloat(montant.val()) : 0
        var totalRestant = $("#agd_val_total_restant").val()
        var totalPaye = $("#fact_libelle").val() != "" ? parseFloat($("#fact_libelle").val()) : 0
        if(!firstAdd)
            var newTotalRestant = parseFloat(totalRestant) - valMontant - totalPaye
        else
            var newTotalRestant = parseFloat(totalRestant) - valMontant

        newTotalRestant = newTotalRestant.toFixed(2) ;

        $("#agd_total_restant").text(newTotalRestant)

        var totalEcheance = valMontant
        $(".elem_echeance").find('tr').each(function(){
            var montantPaye = parseFloat($(this).find("#enr_ech_montant").val())
            totalEcheance += montantPaye
        })
        
        totalEcheance = totalEcheance.toFixed(2) ;

        $("#agd_total_echeance").text(totalEcheance)
    }

    $(document).on('change',"#fact_libelle",function(){
        updateMontant($('#agd_ech_montant'))
    })

    $(document).on('keyup',"#fact_libelle",function(){
        updateMontant($('#agd_ech_montant'))
    })

    $(document).on('keyup','#agd_ech_montant',function(){
        updateMontant($(this))
    })

    $(document).on('change','#agd_ech_montant',function(){
        updateMontant($(this))
    })
})